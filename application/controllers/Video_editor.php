<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Video_editor extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Template_model', 'assetModel');
        $this->load->model('Template_video_overlay_model', 'overlayModel');
    }

    public function index($template_id = null)
    {
        if (!$template_id) show_404();
        $template = $this->assetModel->getById($template_id);
        if (!$template) show_404();

        $overlays = $this->overlayModel->get_by_template($template_id);
        $this->data['template']      = $template;
        $this->data['video']         = $template;
        $this->data['overlays_json'] = json_encode($overlays, JSON_UNESCAPED_SLASHES);
        $this->data['title']         = translate('Marketing_template_manager(Video_editor)');
        $this->data['sub_page']      = 'template_manager/video_editor';
        $this->data['main_menu']     = 'Template_manager';
        $this->data['headerelements'] = [
            'css' => ['css/video_editor.css'],
            'js'  => ['vendor/interactjs/interact.min.js'],
        ];
        $this->load->view('layout/index', $this->data);
    }

    /* ─────────────────────────────────────────────────────────
       SAVE OVERLAYS
    ───────────────────────────────────────────────────────── */
    public function save_overlays()
    {
        if (!is_loggedin() || !is_superadmin_loggedin()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); return;
        }

        $template_id = (int)$this->input->post('template_id');
        $raw         = $this->input->post('overlays');

        if (!$template_id || !$raw) {
            echo json_encode(['status' => 'error', 'message' => 'Missing data']); return;
        }

        $overlays = json_decode($raw, true);
        if (!is_array($overlays)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid overlay data']); return;
        }

        $this->db->where('template_id', $template_id)->delete('template_video_overlays');

        $rows = [];
        foreach ($overlays as $ov) {
            $type = $ov['overlay_type'] ?? 'text';
            if (!in_array($type, ['logo', 'text'])) $type = 'text';

            $settings = $ov['settings'] ?? [];
            if ($type === 'text' && empty($settings['text_key'])) {
                $settings['text_key'] = $ov['variable'] ?? 'branch_name';
            }

            $rows[] = [
                'template_id'  => $template_id,
                'overlay_type' => $type,
                'x'            => (float)($ov['x']          ?? 0),
                'y'            => (float)($ov['y']          ?? 0),
                'width'        => (float)($ov['width']      ?? 0.2),
                'height'       => (float)($ov['height']     ?? 0.1),
                'start_time'   => (float)($ov['start_time'] ?? 0),
                'end_time'     => (float)($ov['end_time']   ?? 5),
                'settings'     => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($rows)) $this->db->insert_batch('template_video_overlays', $rows);

        echo json_encode(['status' => 'success', 'message' => 'Template saved successfully']);
    }

    /* ─────────────────────────────────────────────────────────
       BRANCH PREVIEW
    ───────────────────────────────────────────────────────── */
    public function preview($template_id = null)
    {
        $template = $this->assetModel->getById($template_id);
        $overlays = $this->overlayModel->get_by_template($template_id);
        usort($overlays, fn($a, $b) => $a['start_time'] <=> $b['start_time']);

        $branch     = $this->db->select('*')->from('branch')
                        ->where('id', get_loggedin_branch_id())->get()->row_array();
        $branchLogo = get_branch_logo(get_loggedin_branch_id());

        $this->data['template']      = $template;
        $this->data['video']         = $template;
        $this->data['overlays_json'] = json_encode($overlays);
        $this->data['branch']        = $branch;
        $this->data['branch_logo']   = $branchLogo;
        $this->data['title']         = translate('preview_template');
        $this->data['sub_page']      = 'template_manager/video_preview';
        $this->load->view('layout/index', $this->data);
    }

    /* ─────────────────────────────────────────────────────────
       DOWNLOAD — burn overlays into video via FFmpeg
       Fixed for Windows: paths use forward slashes,
       colons escaped inside filter_complex strings.
    ───────────────────────────────────────────────────────── */
    public function download($template_id)
    {
        if (!is_loggedin() || is_superadmin_loggedin()) {
            redirect(base_url('dashboard')); return;
        }

        $template = $this->assetModel->getById($template_id);
        if (empty($template) || $template['status'] != 1 || $template['type'] !== 'video') {
            show_404();
        }

        $branch     = $this->db->select('*')->from('branch')
                        ->where('id', get_loggedin_branch_id())->get()->row_array();
        $branchLogo = get_branch_logo(get_loggedin_branch_id());

        $branchData = [
            'branch_name'    => $branch['name']     ?? '',
            'branch_address' => $branch['address']  ?? '',
            'branch_contact' => $branch['mobileno'] ?? '',
        ];

        $overlays = $this->overlayModel->get_by_template($template_id);
        usort($overlays, fn($a, $b) => $a['start_time'] <=> $b['start_time']);

        // ── Paths ──────────────────────────────────────────────────────
        $videoPath  = FCPATH . ltrim(str_replace('\\', '/', $template['file_path']), '/');
        $outputDir  = FCPATH . 'uploads/video_output/';
        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
        $outputFile = $outputDir . 'branch_' . get_loggedin_branch_id()
                    . '_tpl_' . $template_id . '_' . time() . '.mp4';

        // Shell quoting: always use double-quotes (works on both Win/Linux)
        $q = fn($p) => '"' . str_replace('\\', '/', trim($p)) . '"';

        // ── Build filter_complex ───────────────────────────────────────
        $filters     = [];
        $extraInputs = [];   // logo file paths (in order)
        $lastChain   = '[0:v]';
        $chainIdx    = 0;

        // Debug: log what overlays are loaded and branchLogo value
        log_message('debug', 'DOWNLOAD: branchLogo raw=' . $branchLogo);
        log_message('debug', 'DOWNLOAD: overlay count=' . count($overlays));
        foreach ($overlays as $dbgOv) {
            log_message('debug', 'OVERLAY: type=' . $dbgOv['overlay_type'] . ' settings=' . $dbgOv['settings']);
        }

        foreach ($overlays as $ov) {
            $settings  = json_decode($ov['settings'] ?? '{}', true) ?: [];
            $type      = $ov['overlay_type'];
            $start     = round((float)$ov['start_time'], 3);
            $end       = round((float)$ov['end_time'],   3);
            // Commas inside between() must be escaped as \, in filter_complex
            // otherwise FFmpeg treats them as filter chain separators
            $enable    = "enable=between(t\\,{$start}\\,{$end})";

            // For overlay/scale filters: iw/ih = input width/height
            // For drawtext filter: w/h = video width/height (iw/ih not available)
            $xOv = 'iw*' . round((float)$ov['x'],      6); // overlay/scale
            $yOv = 'ih*' . round((float)$ov['y'],      6);
            $w   = 'iw*' . round((float)$ov['width'],  6);
            $h   = 'ih*' . round((float)$ov['height'], 6);
            $xTx = 'w*'  . round((float)$ov['x'],      6); // drawtext
            $yTx = 'h*'  . round((float)$ov['y'],      6);

            /* ── LOGO ── */
            if ($type === 'logo' && !empty($branchLogo)) {
                // branchLogo may be a full URL — extract just the path part
                $logoPath = $branchLogo;
                if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
                    $logoPath = parse_url($logoPath, PHP_URL_PATH); // e.g. /futurecampus/uploads/branch/logo.jpg
                    // Remove leading /appname/ segment if FCPATH already has it
                    $baseName = trim(basename(rtrim(FCPATH, '/\\')));
                    $logoPath = preg_replace('#^/' . preg_quote($baseName, '#') . '#', '', $logoPath);
                }
                $logoAbs = rtrim(FCPATH, '/\\') . '/' . ltrim(str_replace('\\', '/', $logoPath), '/');
                log_message('debug', 'LOGO CHECK: logoAbs=' . $logoAbs);
                log_message('debug', 'LOGO CHECK: file_exists=' . (file_exists($logoAbs) ? 'YES' : 'NO'));
                if (!file_exists($logoAbs)) continue;

                // Get video pixel dimensions via ffprobe so we can use exact px values
                // — avoids all ratio-expression escaping issues inside filter_complex
                static $vidW = 0, $vidH = 0;
                if (!$vidW) {
                    $probe = shell_exec('ffprobe -v error -select_streams v:0'
                           . ' -show_entries stream=width,height -of csv=p=0 '
                           . '"' . str_replace('\\', '/', $videoPath) . '"');
                    [$vidW, $vidH] = array_map('intval', explode(',', trim($probe ?? '')));
                    if (!$vidW) { $vidW = 1280; $vidH = 720; } // fallback
                }

                $logoW = max(1, (int)round($vidW * (float)$ov['width']));
                $logoH = max(1, (int)round($vidH * (float)$ov['height']));
                $logoX = (int)round($vidW * (float)$ov['x']);
                $logoY = (int)round($vidH * (float)$ov['y']);

                $inputIdx      = 1 + count($extraInputs);
                $extraInputs[] = $logoAbs;

                $scaled   = "[ls{$chainIdx}]";
                $chainOut = "[v{$chainIdx}]";

                // Scale logo to exact px, overlay at exact px — no expressions, no escaping
                $filters[] = "[{$inputIdx}:v]scale={$logoW}:{$logoH}:force_original_aspect_ratio=disable{$scaled}";
                $filters[] = "{$lastChain}{$scaled}overlay={$logoX}:{$logoY}:{$enable}{$chainOut}";

                $lastChain = $chainOut;
                $chainIdx++;
            /* ── TEXT ── */
            } elseif ($type === 'text') {
                $textKey = $settings['text_key'] ?? '';
                $text    = $branchData[$textKey] ?? '';
                if ($text === '') continue;

                // ── AUTO-FIT text into overlay box ──────────────────────────
                // FFmpeg drawtext does NOT support multi-line text.
                // Solution: one drawtext filter per line, stacked vertically.
                static $tvW = 0, $tvH = 0;
                if (!$tvW) {
                    $tvp = shell_exec('ffprobe -v error -select_streams v:0'
                         . ' -show_entries stream=width,height -of csv=p=0 '
                         . '"' . str_replace('\\', '/', $videoPath) . '"');
                    [$tvW, $tvH] = array_map('intval', explode(',', trim($tvp ?? '')));
                    if (!$tvW) { $tvW = 1280; $tvH = 720; }
                }

                $boxW      = (int)round($tvW * (float)$ov['width']);
                $boxH      = (int)round($tvH * (float)$ov['height']);
                $boxX      = (int)round($tvW * (float)$ov['x']);
                $boxY      = (int)round($tvH * (float)$ov['y']);
                $cleanText = trim(preg_replace('/[\r\n]+/', ' ', $text));

                // Find best font size via binary search
                $bestSize = 8;
                for ($fs = max(8, (int)($boxH * 0.85)); $fs >= 8; $fs--) {
                    $charsPerLine = max(1, (int)floor($boxW / ($fs * 0.55)));
                    $wrapped      = wordwrap($cleanText, $charsPerLine, "\n", true);
                    $numLines     = substr_count($wrapped, "\n") + 1;
                    if (($numLines * $fs * 1.25) <= $boxH) {
                        $bestSize = $fs;
                        break;
                    }
                }

                // Split into lines
                $charsPerLine = max(1, (int)floor($boxW / ($bestSize * 0.55)));
                $wrapped      = wordwrap($cleanText, $charsPerLine, "\n", true);
                $textLines    = explode("\n", $wrapped);
                $lineH        = (int)round($bestSize * 1.25);
                $color        = ltrim($settings['color'] ?? '#ffffff', '#');

                $bgPart = '';
                if (!empty($settings['bg']['enabled'])) {
                    $bgColor = ltrim($settings['bg']['color'] ?? '#000000', '#');
                    $bgPad   = max(0, (int)($settings['bg']['padding'] ?? 5));
                    $bgPart  = ":box=1:boxcolor=0x{$bgColor}@0.85:boxborderw={$bgPad}";
                }

                // One drawtext per line — stacked at boxY + (lineIndex * lineH)
                foreach ($textLines as $lineIdx => $tl) {
                    $tl = str_replace('\\',  '\\\\', trim($tl));
                    $tl = str_replace(':',     '\\:',    $tl);
                    $tl = str_replace(',',     '\\,',    $tl);
                    $tl = str_replace("'",     "\\'",    $tl);
                    if ($tl === '') continue;

                    $lineY    = $boxY + ($lineIdx * $lineH);
                    $chainOut = "[v{$chainIdx}]";

                    $filters[] = "{$lastChain}drawtext=text={$tl}"
                               . ":fontcolor=0x{$color}"
                               . ":fontsize={$bestSize}"
                               . ":x={$boxX}:y={$lineY}"
                               . $bgPart
                               . ":{$enable}"
                               . "{$chainOut}";
                    $lastChain = $chainOut;
                    $chainIdx++;
                }
            }
        }

        // ── Assemble FFmpeg command ────────────────────────────────────
        $inputArgs = '-i ' . $q($videoPath);
        foreach ($extraInputs as $img) {
            $inputArgs .= ' -i ' . $q($img);
        }

        $filterStr = implode(';', $filters);

        if (empty($filterStr)) {
            $cmd = "ffmpeg -y {$inputArgs}"
                 . " -c:v libx264 -c:a aac -movflags +faststart"
                 . " " . $q($outputFile) . " 2>&1";
        } else {
            $cmd = "ffmpeg -y {$inputArgs}"
                 . " -filter_complex \"{$filterStr}\""
                 . " -map \"{$lastChain}\" -map 0:a?"
                 . " -c:v libx264 -preset fast -crf 22"
                 . " -c:a aac -movflags +faststart"
                 . " " . $q($outputFile) . " 2>&1";
        }

        log_message('debug', 'FFmpeg CMD: ' . $cmd);
        $output = shell_exec($cmd);
        log_message('debug', 'FFmpeg OUT: ' . substr($output, -2000));

        if (!file_exists($outputFile) || filesize($outputFile) < 1000) {
            log_message('error', 'FFmpeg FAILED. CMD: ' . $cmd . ' | OUT: ' . $output);
            set_alert('error', 'Video processing failed. Please try again.');
            redirect(base_url('Template_manager/branch_templates'));
            return;
        }

        $filename = preg_replace('/[^a-z0-9_\-]/i', '_', $template['title'])
                  . '_' . date('Ymd') . '.mp4';
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($outputFile));
        header('Cache-Control: no-cache');
        readfile($outputFile);

        register_shutdown_function(function () use ($outputFile) {
            if (file_exists($outputFile)) @unlink($outputFile);
        });
        exit;
    }

    /* ─────────────────────────────────────────────────────────
       HASH helper (for caching — used by download_ legacy)
    ───────────────────────────────────────────────────────── */
    private function renderHash($template, $overlays, $branch, $logoUrl)
    {
        return sha1(json_encode([
            $template['file_path'],
            $branch['name'], $branch['address'], $branch['mobileno'], $logoUrl,
            $overlays,
        ]));
    }
}