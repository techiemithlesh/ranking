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
        $template = $this->CI->Template_model->get($template_id);
        $overlays = $this->CI->TemplateOverlay_model->get_by_template($template_id);
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
                'created_by' => get_loggedin_user_id()
            ];
            return $this->CI->Template_model->saveTemplate($arrayData);
        } else {
            // Return the actual upload error so it shows in Swal
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    public function renderImage($templatePath, $logoUrl, $overlays, $filename)
    {
        log_message('info', 'Rendering template image: ' . $templatePath . ' with logo: ' . $logoUrl);

        $basePath = $this->toFilePath($templatePath);
        $logoPath = $this->toFilePath($logoUrl);

        if (!file_exists($basePath)) {
            log_message('error', 'Base image missing: ' . $basePath);
            show_error('Base image not found');
        }

        if (!file_exists($logoPath)) {
            log_message('error', 'Logo image missing: ' . $logoPath);
            show_error('Logo image not found');
        }

        $base = $this->loadImage($basePath);
        if (!$base) {
            show_error('Failed to load base image');
        }

        $baseW = imagesx($base);
        $baseH = imagesy($base);

        foreach ($overlays as $ov) {

            $settings = json_decode($ov['settings'], true) ?? [];
            $bg = $settings['bg'] ?? [];

            // ratios → pixels
            $x = (int)round((float)$ov['x'] * $baseW);
            $y = (int)round((float)$ov['y'] * $baseH);
            $w = (int)round((float)$ov['width'] * $baseW);
            $h = (int)round((float)$ov['height'] * $baseH);

            if ($w <= 0 || $h <= 0) continue;

            $padding = (int)($bg['padding'] ?? 0);

            // background
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

            // logo
            $this->drawImage(
                $base,
                $logoPath,
                $x + $padding,
                $y + $padding,
                $w - ($padding * 2),
                $h - ($padding * 2)
            );
        }

        $this->outputImage($base, $filename);
        imagedestroy($base);
        exit;
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
