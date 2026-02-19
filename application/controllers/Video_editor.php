<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 4.0
 * @developed by : Eduprojects Global Tech (http://eduprojectsglobal.com)
 * @support : http://eduprojectsglobal.com/support
 * @author url : http://codewithmithlesh.com
 * @filename : Video_editor.php
 * @copyright : Reserved EduprojectsGlobalTech Team
 */

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
        if (!$template_id) {
            show_404();
        }

        $template = $this->assetModel->getById($template_id);
        if (!$template) {
            show_404();
        }

        $overlays = $this->overlayModel->get_by_template($template_id);
        $this->data['template'] = $template;
        $this->data['video'] = $template;

        $this->data['overlays_json'] = json_encode($overlays, JSON_UNESCAPED_SLASHES);


        $this->data['title'] = translate('Marketing_template_manager(Video_editor)');
        $this->data['sub_page'] = 'template_manager/video_editor';
        $this->data['main_menu'] = 'Template_manager';

        $this->data['headerelements'] = array(
            'css' => array('css/video_editor.css'),
            'js'  => array(
                'vendor/interactjs/interact.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function save_overlays()
    {
        if ($_POST) {
            $template_id = $this->input->post('template_id');
            $overlays_data = json_decode($this->input->post('overlays'), true);

            if (empty($template_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Template ID']);
                return;
            }

            $this->db->where('template_id', $template_id);
            $this->db->delete('template_video_overlays');


            $insert_data = [];
            foreach ($overlays_data as $ov) {

                $settings = null;
                if (isset($ov['settings']) && is_array($ov['settings'])) {
                    $settings = json_encode($ov['settings'], JSON_UNESCAPED_UNICODE);
                    if ($settings === false) $settings = null;
                }

                $overlayType = $ov['overlay_type'] ?? 'logo';
                if (!in_array($overlayType, ['logo', 'text'])) {
                    $overlayType = 'logo';
                }


                $insert_data[] = [
                    'template_id'  => $template_id,
                    'overlay_type' => $ov['overlay_type'],
                    'x'            => $ov['x'],      // Stored as float (e.g., 0.125)
                    'y'            => $ov['y'],
                    'width'        => $ov['width'],
                    'height'       => $ov['height'],
                    'start_time'   => $ov['start_time'],
                    'end_time'     => $ov['end_time'],
                    'settings'     => $settings,
                    'updated_at'   => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($insert_data)) {
                $this->db->insert_batch('template_video_overlays', $insert_data);
            }

            echo json_encode(['status' => 'success', 'message' => 'Template coordinates updated!']);
        }
    }

    public function preview($template_id = null)
    {
        $template = $this->assetModel->getById($template_id);
        $overlays = $this->overlayModel->get_by_template($template_id);


        $branch = $this->db->select('*')->from('branch')->where('id', get_loggedin_branch_id())->get()->row_array();
        $branchLogo = get_branch_logo(get_loggedin_branch_id());
        $this->data['template'] = $template;
        $this->data['video'] = $template;
        $this->data['overlays_json'] = json_encode($overlays);

        $this->data['branch'] = $branch;
        $this->data['branch_logo'] = $branchLogo;

        $this->data['mode'] = 'preview';
        $this->data['title'] = translate('preview_template');

        $this->data['sub_page'] = 'template_manager/video_preview';

        $this->load->view('layout/index', $this->data);
    }

    public function download($template_id)
    {
        $template = $this->assetModel->getById($template_id);
        $overlays = $this->overlayModel->get_by_template($template_id);

        /* IMPORTANT: render in timeline order */
        usort($overlays, function ($a, $b) {
            return $a['start_time'] <=> $b['start_time'];
        });

        $branch = $this->db->where('id', get_loggedin_branch_id())->get('branch')->row_array();

        $branchTextMap = [
            'branch_name'    => $branch['name'],
            'branch_address' => $branch['address'],
            'branch_contact' => $branch['mobileno']
        ];

        $logoUrl = get_branch_logo(get_loggedin_branch_id());

        /* ---------- RENDER CACHE ---------- */
        /* ---------- CACHE HASH ---------- */
        $hash = $this->renderHash($template, $overlays, $branch, $logoUrl);

        $outputDir = FCPATH . 'uploads/generated/';
        if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

        $outputVideo = $outputDir . $hash . '.mp4';

        // already rendered → direct download
        if (file_exists($outputVideo)) {

            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . $branch['name'] . '_template.mp4"');
            header('Content-Length: ' . filesize($outputVideo));
            readfile($outputVideo);
            exit;
        }


        // If another process is rendering → wait & serve when done
        $inputVideo = FCPATH . ltrim($template['file_path'], '/');

        $logoPath = $this->toFilePath($logoUrl);

        $outputDir = FCPATH . 'uploads/generated/';
        if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);



        $filters = [];
        $current = "[0:v]";
        $index = 1;

        foreach ($overlays as $ov) {

            $settings = json_decode($ov['settings'], true) ?? [];

            $start = floatval($ov['start_time']);
            $end   = floatval($ov['end_time']);

            $x = "W*{$ov['x']}";
            $y = "H*{$ov['y']}";

            /* ================= TEXT ================= */
            if ($ov['overlay_type'] === 'text') {

                $textKey = $settings['text_key'] ?? '';
                $text = $branchTextMap[$textKey] ?? '';
                if ($text == '') continue;

                // escape text for ffmpeg
                $text = str_replace(["\\", ":", "'", "\n"], ["\\\\", "\\:", "\\'", "\\n"], $text);

                $fontSize = intval($settings['font_size'] ?? 18);
                $color = ltrim($settings['color'] ?? '#ffffff', '#');

                $draw = "drawtext=text='{$text}':fontsize={$fontSize}:fontcolor={$color}:x={$x}:y={$y}:enable='between(t,$start,$end)'";

                if (!empty($settings['bg']['enabled'])) {
                    $bgColor = ltrim($settings['bg']['color'] ?? '#000000', '#');
                    $pad     = intval($settings['bg']['padding'] ?? 5);

                    $draw .= ":box=1:boxcolor={$bgColor}@0.7:boxborderw={$pad}";
                }

                $next = "[v{$index}]";
                $filters[] = "{$current}{$draw}{$next}";
                $current = $next;
                $index++;
            }

            /* ================= LOGO ================= */
            if ($ov['overlay_type'] === 'logo' && file_exists($logoPath)) {

                $ffmpegLogo = $this->ffmpegPath($logoPath);

                $wRatio = floatval($ov['width']);
                $hRatio = floatval($ov['height']);

                // Step 1: load logo
                $filters[] = "movie='{$ffmpegLogo}'[logo{$index}]";

                // Step 2: scale logo relative to video (CORRECT METHOD)
                $filters[] = "[logo{$index}]{$current}scale2ref=w=iw*{$wRatio}:h=ih*{$hRatio}[logoScaled{$index}][base{$index}]";

                // Step 3: overlay
                $next = "[v{$index}]";
                $filters[] = "[base{$index}][logoScaled{$index}]overlay={$x}:{$y}:enable='between(t,$start,$end)'{$next}";

                $current = $next;
                $index++;
            }
        }

        $filterComplex = implode(';', $filters);

        $cmd = "ffmpeg -y -i \"$inputVideo\" -filter_complex \"$filterComplex\" -map \"$current\" -map 0:a? -preset veryfast \"$outputVideo\" 2>&1";

        exec($cmd, $out, $code);

        if (!file_exists($outputVideo)) {
            echo "<pre>";
            print_r($out);
            exit;
        }

        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . basename($outputVideo) . '"');
        header('Content-Length: ' . filesize($outputVideo));
        readfile($outputVideo);
        exit;
    }

    private function toFilePath($path)
    {
        if (!$path) return null;

        // If full URL → remove domain
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH);
        }

        // Normalize slashes
        $path = str_replace('\\', '/', $path);

        // Remove base folder duplication
        $baseFolder = basename(FCPATH); // futurecampus

        if (strpos($path, '/' . $baseFolder . '/') === 0) {
            $path = substr($path, strlen('/' . $baseFolder));
        }

        // Final absolute path
        return rtrim(FCPATH, '/\\') . '/' . ltrim($path, '/');
    }

    private function ffmpegPath($path)
    {
        // normalize slashes
        $path = str_replace('\\', '/', $path);

        // escape colon for filter_complex
        $path = str_replace(':', '\\:', $path);

        return $path;
    }


    private function renderHash($template, $overlays, $branch, $logoUrl)
    {
        return sha1(json_encode([
            'template' => $template['file_path'],
            'branch' => [
                $branch['name'],
                $branch['address'],
                $branch['mobileno'],
                $logoUrl
            ],
            'overlays' => $overlays
        ]));
    }
}
