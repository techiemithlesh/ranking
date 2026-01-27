<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class TemplateEngine_lib
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

    public function renderImage($templatePath, $logoPath, $overlays, $filename)
    {
        log_message('error', 'DOWNLOAD renderImage called');
        log_message('error', 'Base path=' . $templatePath);
        log_message('error', 'Logo path=' . $logoPath);
        log_message('error', 'Overlays=' . json_encode($overlays));

        $base = $this->loadImage($templatePath);
        if (!$base) {
            show_error('Base image load failed');
        }

        $baseW = imagesx($base);
        $baseH = imagesy($base);

        foreach ($overlays as $ov) {

            $settings = json_decode($ov['settings'], true) ?? [];
            $bg = $settings['bg'] ?? [];

            // Convert ratio → pixels
            $x = (int)($ov['x'] * $baseW);
            $y = (int)($ov['y'] * $baseH);
            $w = (int)($ov['width'] * $baseW);
            $h = (int)($ov['height'] * $baseH);

            // Padding
            $padding = (int)($bg['padding'] ?? 0);

            // Background box
            if (!empty($bg['enabled'])) {
                $this->drawRoundedRect(
                    $base,
                    $x,
                    $y,
                    $w,
                    $h,
                    (int)($bg['radius'] ?? 0),
                    $bg['color'] ?? '#ffffff'
                );
            }

            // Draw logo
            $this->drawImage(
                $base,
                $logoPath,
                $x + $padding,
                $y + $padding,
                $w - ($padding * 2),
                $h - ($padding * 2)
            );
        }

        // Output
        $this->outputImage($base, $filename);
        imagedestroy($base);
        exit;
    }

    private function loadImage($path)
    {
        $full = FCPATH . $path;
        if (!file_exists($full)) return false;

        $info = getimagesize($full);
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($full);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($full);
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

    private function drawRoundedRect($img, $x, $y, $w, $h, $r, $hex)
    {
        [$rC, $gC, $bC] = $this->hexToRgb($hex);
        $color = imagecolorallocate($img, $rC, $gC, $bC);

        imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $color);
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
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
