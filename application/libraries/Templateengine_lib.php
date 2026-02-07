<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Templateengine_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('template_model');
        $this->CI->load->model('templateOverlay_model');
    }

    // Get template + overlays at once
    public function get_template_data($template_id)
    {
        $template = $this->CI->template_model->get($template_id);
        $overlays = $this->CI->templateOverlay_model->get_by_template($template_id);
        return ['template' => $template, 'overlays' => $overlays];
    }

    public function storeTemplate()
    {
        $type = $this->CI->input->post('type');
        $config = [
            'upload_path'   => 'uploads/template-manager/' . $type . '/',
            'allowed_types' => ($type == 'image' ? 'jpg|jpeg|png' : 'mp4'),
            'encrypt_name'  => true
        ];

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);

        if ($this->CI->upload->do_upload('template_file')) { // MUST match view name
            $uploadData = $this->CI->upload->data();
            $arrayData = [
                'title'     => $this->CI->input->post('title'),
                'type'      => $type,
                'file_path' => $config['upload_path'] . $uploadData['file_name'],
                'upload_status' => 'completed',
                'created_by' => get_loggedin_user_id()
            ];
            return $this->CI->template_model->saveTemplate($arrayData);
        } else {
            // Return the actual upload error so it shows in Swal
            log_message('error', 'Template upload error: ' . json_encode($this->CI->upload->display_errors('', '')));
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    public function renderImage($templatePath, $logoUrl, $overlays, $filename, $branchTextMap)
    {
        $basePath = $this->toFilePath($templatePath);
        $logoPath = $this->toFilePath($logoUrl);

        if (!file_exists($basePath)) show_error('Base image not found');
        if (!file_exists($logoPath)) show_error('Logo image not found');

        $base = $this->loadImage($basePath);
        if (!$base) show_error('Failed to load base image');

        $baseW = imagesx($base);
        $baseH = imagesy($base);

        foreach ($overlays as $ov) {

            $settings = json_decode($ov['settings'], true) ?? [];
            $bg = $settings['bg'] ?? [];

            $x = (int)round($ov['x'] * $baseW);
            $y = (int)round($ov['y'] * $baseH);
            $w = (int)round($ov['width'] * $baseW);
            $h = (int)round($ov['height'] * $baseH);

            if ($w <= 0 || $h <= 0) continue;

            $padding = (int)($bg['padding'] ?? 0);

            // Background
            if (!empty($bg['enabled'])) {
                $this->drawBackground(
                    $base,
                    $x,
                    $y,
                    $w,
                    $h,
                    $bg['color'] ?? '#ffffff'
                );
            }

            /* ==========================
           LOGO
        ========================== */
            if ($ov['overlay_type'] === 'logo') {

                $this->drawImage(
                    $base,
                    $logoPath,
                    $x + $padding,
                    $y + $padding,
                    $w - ($padding * 2),
                    $h - ($padding * 2)
                );
            }

            /* ==========================
           TEXT
        ========================== */ elseif ($ov['overlay_type'] === 'text') {

                $textKey = $settings['text_key'] ?? '';
                $text = $branchTextMap[$textKey] ?? '';

                if ($text !== '') {
                    $this->drawText(
                        $base,
                        $text,
                        $x + $padding,
                        $y + $padding,
                        $w - ($padding * 2),
                        $h - ($padding * 2),
                        $settings
                    );
                }
            }
        }

        $this->outputImage($base, $filename);
        imagedestroy($base);
        exit;
    }

    private function drawText($img, $text, $x, $y, $w, $h, $settings)
    {
        $fontSize = (int)($settings['font_size'] ?? 18);
        $colorHex = $settings['color'] ?? '#000000';

        [$r, $g, $b] = $this->hexToRgb($colorHex);
        $color = imagecolorallocate($img, $r, $g, $b);

        // Use default GD font (Phase-1 safe)
        $font = 5; // GD built-in font

        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        // center text
        $tx = $x + max(0, ($w - $textWidth) / 2);
        $ty = $y + max(0, ($h - $textHeight) / 2);

        imagestring($img, $font, (int)$tx, (int)$ty, $text, $color);
    }


    /* ==============================
       HELPERS
    ============================== */

    // 🔑 URL or relative path → absolute filesystem path
    private function toFilePath($path)
    {
        // If full URL → extract path
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH);
        }

        // Remove base folder from path (futurecampus)
        $base = trim(parse_url(base_url(), PHP_URL_PATH), '/');

        if ($base && strpos($path, '/' . $base . '/') === 0) {
            $path = substr($path, strlen('/' . $base));
        }

        return FCPATH . ltrim($path, '/');
    }


    private function loadImage($path)
    {
        $info = getimagesize($path);
        if (!$info) return false;

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($path);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                return $img;
            default:
                return false;
        }
    }

    private function drawImage($canvas, $path, $x, $y, $w, $h)
    {
        $logo = $this->loadImage($path);
        if (!$logo) return;

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        imagealphablending($logo, true);
        imagesavealpha($logo, true);

        imagecopyresampled(
            $canvas,
            $logo,
            $x,
            $y,
            0,
            0,
            $w,
            $h,
            imagesx($logo),
            imagesy($logo)
        );

        imagedestroy($logo);
    }

    private function drawBackground($img, $x, $y, $w, $h, $hex)
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $color = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $color);
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    private function outputImage($img, $name)
    {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . url_title($name) . '.png"');
        imagepng($img);
    }
}
