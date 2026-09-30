<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Burns branch logo/text overlays into a video template via FFmpeg.
 * Shared by the single download (Video_editor) and the bulk ZIP download (Template_manager).
 * Fixed for Windows: paths use forward slashes, colons escaped inside filter_complex strings.
 */
class Videorender_lib
{
    protected $CI;
    protected $dimensions = []; // video path => [width, height], probed once per request

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Template_video_overlay_model', 'overlayModel');
    }

    // configured binary (config/video.php) if present, else rely on PATH
    protected function bin($name)
    {
        $paths = config_item($name);
        $path  = is_array($paths) ? ($paths[stripos(PHP_OS, 'WIN') === 0 ? 'windows' : 'linux'] ?? '') : '';
        return ($path && is_file($path)) ? '"' . str_replace('\\', '/', $path) . '"' : $name;
    }

    /**
     * @return string|false absolute path of the rendered mp4 (caller deletes it), or false on failure
     */
    public function render(array $template, $branchId)
    {
        $branch     = $this->CI->db->select('*')->from('branch')
                        ->where('id', $branchId)->get()->row_array();
        $logoAbs    = FCPATH . get_branch_logo_file($branchId);

        $branchData = [
            'branch_name'    => $branch['name']     ?? '',
            'branch_address' => $branch['address']  ?? '',
            'branch_contact' => $branch['mobileno'] ?? '',
        ];

        $overlays = $this->CI->overlayModel->get_by_template($template['id']);
        usort($overlays, fn($a, $b) => $a['start_time'] <=> $b['start_time']);

        // ── Paths ──────────────────────────────────────────────────────
        $videoPath  = FCPATH . ltrim(str_replace('\\', '/', $template['file_path']), '/');
        $outputDir  = FCPATH . 'uploads/video_output/';
        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
        $outputFile = $outputDir . 'branch_' . $branchId
                    . '_tpl_' . $template['id'] . '_' . uniqid() . '.mp4';

        // Shell quoting: always use double-quotes (works on both Win/Linux)
        $q = fn($p) => '"' . str_replace('\\', '/', trim($p)) . '"';

        // Video pixel dimensions via ffprobe so we can use exact px values
        // — avoids all ratio-expression escaping issues inside filter_complex
        if (!isset($this->dimensions[$videoPath])) {
            $probe = shell_exec($this->bin('ffprobe') . ' -v error -select_streams v:0'
                   . ' -show_entries stream=width,height -of csv=p=0 '
                   . '"' . str_replace('\\', '/', $videoPath) . '"');
            [$w, $h] = array_pad(array_map('intval', explode(',', trim($probe ?? ''))), 2, 0);
            $this->dimensions[$videoPath] = $w ? [$w, $h] : [1280, 720]; // fallback
        }
        [$vidW, $vidH] = $this->dimensions[$videoPath];

        // ── Build filter_complex ───────────────────────────────────────
        $filters     = [];
        $extraInputs = [];   // logo file paths (in order)
        $lastChain   = '[0:v]';
        $chainIdx    = 0;

        log_message('debug', 'VIDEO RENDER: branch=' . $branchId . ' logo=' . $logoAbs . ' overlays=' . count($overlays));

        foreach ($overlays as $ov) {
            $settings  = json_decode($ov['settings'] ?? '{}', true) ?: [];
            $type      = $ov['overlay_type'];
            $start     = round((float)$ov['start_time'], 3);
            $end       = round((float)$ov['end_time'],   3);
            // Commas inside between() must be escaped as \, in filter_complex
            // otherwise FFmpeg treats them as filter chain separators
            $enable    = "enable=between(t\\,{$start}\\,{$end})";

            /* ── LOGO ── */
            if ($type === 'logo') {
                if (!file_exists($logoAbs)) {
                    log_message('debug', 'VIDEO RENDER: logo not found ' . $logoAbs);
                    continue;
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
                $boxW      = (int)round($vidW * (float)$ov['width']);
                $boxH      = (int)round($vidH * (float)$ov['height']);
                $boxX      = (int)round($vidW * (float)$ov['x']);
                $boxY      = (int)round($vidH * (float)$ov['y']);
                $cleanText = trim(preg_replace('/[\r\n]+/', ' ', $text));

                // Find best font size
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
            $cmd = $this->bin('ffmpeg') . " -y {$inputArgs}"
                 . " -c:v libx264 -c:a aac -movflags +faststart"
                 . " " . $q($outputFile) . " 2>&1";
        } else {
            $cmd = $this->bin('ffmpeg') . " -y {$inputArgs}"
                 . " -filter_complex \"{$filterStr}\""
                 . " -map \"{$lastChain}\" -map 0:a?"
                 . " -c:v libx264 -preset fast -crf 22"
                 . " -c:a aac -movflags +faststart"
                 . " " . $q($outputFile) . " 2>&1";
        }

        log_message('debug', 'FFmpeg CMD: ' . $cmd);
        $output = shell_exec($cmd);
        log_message('debug', 'FFmpeg OUT: ' . substr((string)$output, -2000));

        if (!file_exists($outputFile) || filesize($outputFile) < 1000) {
            log_message('error', 'FFmpeg FAILED. CMD: ' . $cmd . ' | OUT: ' . $output);
            if (file_exists($outputFile)) @unlink($outputFile);
            return false;
        }

        return $outputFile;
    }
}
