<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Templateengine_lib
{
    protected $CI;
    private $fontRegular;
    private $fontBold;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('template_model');
        $this->CI->load->model('templateOverlay_model');
        $this->fontRegular = APPPATH . 'fonts/OpenSans-Regular.ttf';
        $this->fontBold    = APPPATH . 'fonts/OpenSans-Bold.ttf';
    }

    // Get template + overlays at once
    public function get_template_data($template_id)
    {
        $template = $this->CI->template_model->get($template_id);
        $overlays = $this->CI->templateOverlay_model->get_by_template($template_id);
        return ['template' => $template, 'overlays' => $overlays];
    }

    public function storeTemplate_()
    {
        $this->CI->db->trans_start();

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
            log_message('info', 'Template uploaded: ' . json_encode($arrayData));
            log_message('info', 'Upload data: ' . $this->CI->template_model->saveTemplate($arrayData));
            return $this->CI->template_model->saveTemplate($arrayData);
        } else {
            $this->CI->db->rollback();
            log_message('error', 'Template upload error: ' . json_encode($this->CI->upload->display_errors('', '')));
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    public function storeTemplate()
    {
        $this->CI->db->trans_start(); // Start Transaction

        $type = $this->CI->input->post('type');
        $config = [
            'upload_path'   => './uploads/template-manager/' . $type . '/', // Added ./ for path safety
            'allowed_types' => ($type == 'image' ? 'jpg|jpeg|png' : 'mp4'),
            'encrypt_name'  => true
        ];

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);

        if ($this->CI->upload->do_upload('template_file')) {
            $uploadData = $this->CI->upload->data();
            $arrayData = [
                'title'         => $this->CI->input->post('title', true),
                'type'          => $type,
                'file_path'     => $config['upload_path'] . $uploadData['file_name'],
                'upload_status' => 'completed',
                'created_by'    => get_loggedin_user_id()
            ];

            // Perform the insert
            $insertId = $this->CI->template_model->saveTemplate($arrayData);

            $this->CI->db->trans_complete(); // Commit the transaction

            log_message('info', 'Template uploaded successfully ID: ' . $insertId);
            return $insertId;
        } else {
            $this->CI->db->trans_rollback(); // Rollback on upload failure
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    public function renderImage($templatePath, $logoUrl, $overlays, $filename, $branchTextMap)
    {
        $basePath = $this->toFilePath($templatePath);
        $logoPath = $this->toFilePath($logoUrl);

        if (!file_exists($basePath)) show_error('Base image not found: ' . $basePath);
        if (!file_exists($logoPath)) show_error('Logo image not found: ' . $logoPath);

        $base = $this->loadImage($basePath);
        if (!$base) show_error('Failed to load base image');

        $baseW = imagesx($base);
        $baseH = imagesy($base);

        foreach ($overlays as $ov) {

            /* Safe decode — handles both JSON string and already-decoded array */
            $settings = $this->decodeSettings($ov['settings'] ?? '');
            $bg       = $settings['bg'] ?? [];
            $type     = $ov['overlay_type'] ?? 'logo';

            /* Ratios → natural image pixels */
            $x = (int)round((float)$ov['x']      * $baseW);
            $y = (int)round((float)$ov['y']      * $baseH);
            $w = (int)round((float)$ov['width']  * $baseW);
            $h = (int)round((float)$ov['height'] * $baseH);

            if ($w <= 0 || $h <= 0) continue;

            /* Text background — only for text layers when bg.enabled = true */
            if ($type === 'text' && !empty($bg['enabled'])) {
                $bgRadius = (int)($bg['radius'] ?? 0);
                $bgColor  = $bg['color'] ?? '#ffffff';
                if ($bgRadius > 0) {
                    $this->drawRoundedRect($base, $x, $y, $w, $h, $bgColor, $bgRadius);
                } else {
                    $this->drawBackground($base, $x, $y, $w, $h, $bgColor);
                }
            }

            /* ── LOGO ── */
            if ($type === 'logo') {

                $this->drawLogo(
                    $base,
                    $logoPath,
                    $x,
                    $y,
                    $w,
                    $h,
                    (float)($settings['opacity']     ?? 1.0),
                    (int)($settings['radius']        ?? 0),
                    (int)($settings['borderWidth']   ?? 0),
                    $settings['borderColor']         ?? '#ffffff',
                    $settings['objectFit']           ?? 'contain'
                );

                /* ── TEXT ── */
            } elseif ($type === 'text') {

                $textKey = $settings['text_key'] ?? '';
                $text    = $branchTextMap[$textKey] ?? '';
                $padding = !empty($bg['enabled']) ? (int)($bg['padding'] ?? 0) : 0;

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


    /* ================================================================
       DRAW LOGO — opacity + border-radius + border + object-fit
       ================================================================ */
    private function drawLogo(
        $canvas,
        $path,
        $x,
        $y,
        $w,
        $h,
        $opacity = 1.0,
        $radius = 0,
        $borderWidth = 0,
        $borderColor = '#ffffff',
        $objectFit = 'contain'
    ) {
        $logo = $this->loadImage($path);
        if (!$logo) return;

        $srcW = imagesx($logo);
        $srcH = imagesy($logo);

        /* object-fit: calculate destination rect inside box */
        [$dstX, $dstY, $dstW, $dstH] = $this->calcObjectFit($objectFit, $srcW, $srcH, $w, $h);

        /* working canvas — same size as destination box, transparent */
        $tmp = imagecreatetruecolor($w, $h);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagefilledrectangle(
            $tmp,
            0,
            0,
            $w,
            $h,
            imagecolorallocatealpha($tmp, 0, 0, 0, 127)
        );
        imagealphablending($tmp, true);

        /* resample logo into tmp */
        imagecopyresampled($tmp, $logo, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($logo);

        /* border-radius mask */
        if ($radius > 0) {
            $tmp = $this->applyRadiusMask($tmp, $w, $h, $radius);
        }

        /* border */
        if ($borderWidth > 0) {
            [$br, $bg2, $bb] = $this->hexToRgb($borderColor);
            $bColor = imagecolorallocatealpha($tmp, $br, $bg2, $bb, 0);
            for ($i = 0; $i < $borderWidth; $i++) {
                if ($radius > 0) {
                    $this->drawRoundedRectBorder($tmp, $i, $i, $w - $i * 2, $h - $i * 2, $bColor, $radius);
                } else {
                    imagerectangle($tmp, $i, $i, $w - 1 - $i, $h - 1 - $i, $bColor);
                }
            }
        }

        /* merge onto canvas with opacity */
        $pct = max(0, min(100, (int)round($opacity * 100)));
        if ($pct >= 100) {
            imagealphablending($canvas, true);
            imagecopy($canvas, $tmp, $x, $y, 0, 0, $w, $h);
        } else {
            imagecopymerge($canvas, $tmp, $x, $y, 0, 0, $w, $h, $pct);
        }

        imagedestroy($tmp);
    }


    /* ================================================================
       DRAW TEXT — auto-fit font size via binary search
       No manual font_size needed. Finds the largest size that fits.
       ================================================================ */
    private function drawText($img, $text, $x, $y, $w, $h, $settings)
    {
        $colorHex   = $settings['color']       ?? '#000000';
        $align      = $settings['align']       ?? 'center';
        $lineHeight = (float)($settings['line_height'] ?? 1.2);
        $weight     = $settings['weight']      ?? 'normal';

        [$r, $g, $b] = $this->hexToRgb($colorHex);
        $color = imagecolorallocate($img, $r, $g, $b);

        $fontFile = ($weight === 'bold' || $weight === '600' || $weight === '800')
            ? $this->fontBold
            : $this->fontRegular;

        if (file_exists($fontFile)) {
            /* TTF: binary search for largest fitting font size */
            $fontSize = $this->autoFitFontSize(
                $text,
                $w,
                $h,
                $fontFile,
                $lineHeight,
                6,
                min(300, (int)($h * 0.9))
            );
            $this->drawTTFText(
                $img,
                $text,
                $x,
                $y,
                $w,
                $h,
                $fontFile,
                $fontSize,
                $color,
                $align,
                $lineHeight
            );
        } else {
            /* GD fallback */
            $this->drawGDText($img, $text, $x, $y, $w, $h, $color, $align);
        }
    }

    /* Binary search: largest integer font size where wrapped text fits w×h */
    private function autoFitFontSize(
        $text,
        $maxW,
        $maxH,
        $fontFile,
        $lineHeightMult = 1.2,
        $minSize = 6,
        $maxSize = 200
    ) {
        $lo = $minSize;
        $hi = $maxSize;
        $best = $minSize;
        while ($lo <= $hi) {
            $mid = (int)(($lo + $hi) / 2);
            [$tw, $th] = $this->measureWrappedText($text, $mid, $fontFile, $maxW, $lineHeightMult);
            if ($tw <= $maxW && $th <= $maxH) {
                $best = $mid;
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }
        return $best;
    }

    /* Returns [totalWidth, totalHeight] for text wrapped at given font size.
       Uses actual bbox height (not fontSize*lineHeight) so GD font size
       matches browser auto-fit proportionally. */
    private function measureWrappedText($text, $fontSize, $fontFile, $maxW, $lineHeightMult = 1.2)
    {
        $lines    = $this->wrapText($text, $fontSize, $fontFile, $maxW);
        $maxLineW = 0;
        $maxLineH = 0;

        foreach ($lines as $line) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $line ?: 'Ag');
            $lw  = abs($box[4] - $box[0]);
            // Actual rendered height = ascender + descender from bbox
            $lh  = abs($box[1] - $box[7]);
            if ($lw > $maxLineW) $maxLineW = $lw;
            if ($lh > $maxLineH) $maxLineH = $lh;
        }

        // Line spacing = actual bbox height * lineHeightMult
        // This matches browser behaviour much more closely than fontSize*lineHeight
        $lineH  = (int)ceil($maxLineH * $lineHeightMult);
        $totalH = count($lines) * $lineH;

        return [$maxLineW, $totalH];
    }

    /* Word-wrap text into lines that fit maxW at given font size */
    private function wrapText($text, $fontSize, $fontFile, $maxW)
    {
        $words = explode(' ', $text);
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            $box  = imagettfbbox($fontSize, 0, $fontFile, $test);
            if (abs($box[4] - $box[0]) > $maxW && $line !== '') {
                $lines[] = $line;
                $line    = $word;
            } else {
                $line = $test;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines;
    }

    /* Render TTF text with alignment + vertical centering.
       Uses same bbox-based lineH as measureWrappedText for consistency. */
    private function drawTTFText(
        $img,
        $text,
        $x,
        $y,
        $w,
        $h,
        $fontFile,
        $fontSize,
        $color,
        $align = 'center',
        $lineHeightMult = 1.2
    ) {
        $lines = $this->wrapText($text, $fontSize, $fontFile, $w);

        // Measure actual line height via bbox (same as measureWrappedText)
        $sampleBox = imagettfbbox($fontSize, 0, $fontFile, 'Ag');
        $bboxH     = abs($sampleBox[1] - $sampleBox[7]);
        $lineH     = (int)ceil($bboxH * $lineHeightMult);
        $totalH    = count($lines) * $lineH;

        // Vertical center: offset by ascender (distance from baseline to top)
        // bbox[7] is the top-left Y (negative = above baseline)
        $ascender = abs($sampleBox[7]); // distance from baseline to top of glyph
        $startY   = $y + max(0, (int)(($h - $totalH) / 2)) + $ascender;

        foreach ($lines as $i => $ln) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $ln);
            $tw  = abs($box[4] - $box[0]);
            if ($align === 'left')       $tx = $x;
            elseif ($align === 'right')  $tx = $x + $w - $tw;
            else                         $tx = $x + (int)(($w - $tw) / 2);
            imagettftext($img, $fontSize, 0, $tx, $startY + ($i * $lineH), $color, $fontFile, $ln);
        }
    }

    /* GD built-in font fallback with word-wrap + alignment */
    private function drawGDText($img, $text, $x, $y, $w, $h, $color, $align = 'center')
    {
        $font     = 5;
        $charW    = imagefontwidth($font);
        $charH    = imagefontheight($font);
        $maxChars = max(1, (int)($w / $charW));
        $wrapped  = wordwrap($text, $maxChars, "\n", true);
        $lines    = explode("\n", $wrapped);
        $lineH    = $charH + 4;
        $totalH   = count($lines) * $lineH;
        $startY   = $y + max(0, (int)(($h - $totalH) / 2));
        foreach ($lines as $i => $ln) {
            $tw = strlen($ln) * $charW;
            if ($align === 'left')      $tx = $x;
            elseif ($align === 'right') $tx = $x + $w - $tw;
            else                        $tx = $x + (int)(($w - $tw) / 2);
            imagestring($img, $font, $tx, $startY + ($i * $lineH), $ln, $color);
        }
    }


    /* ================================================================
       HELPERS
       ================================================================ */

    /* Safe settings decode — handles JSON string OR already-decoded array */
    private function decodeSettings($raw)
    {
        if (is_array($raw))  return $raw;
        if (empty($raw))     return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /* object-fit: returns [dstX, dstY, dstW, dstH] within w×h box */
    private function calcObjectFit($fit, $srcW, $srcH, $boxW, $boxH)
    {
        if ($fit === 'fill') return [0, 0, $boxW, $boxH];
        $srcRatio = $srcW / $srcH;
        $boxRatio = $boxW / $boxH;
        if ($fit === 'cover') {
            if ($srcRatio > $boxRatio) {
                $dstH = $boxH;
                $dstW = (int)round($boxH * $srcRatio);
            } else {
                $dstW = $boxW;
                $dstH = (int)round($boxW / $srcRatio);
            }
        } else { /* contain */
            if ($srcRatio > $boxRatio) {
                $dstW = $boxW;
                $dstH = (int)round($boxW / $srcRatio);
            } else {
                $dstH = $boxH;
                $dstW = (int)round($boxH * $srcRatio);
            }
        }
        $dstX = (int)round(($boxW - $dstW) / 2);
        $dstY = (int)round(($boxH - $dstH) / 2);
        return [$dstX, $dstY, $dstW, $dstH];
    }

    /* Apply rounded corner mask to an image resource */
    private function applyRadiusMask($src, $w, $h, $radius)
    {
        $radius = min($radius, (int)($w / 2), (int)($h / 2));
        $masked = imagecreatetruecolor($w, $h);
        imagealphablending($masked, false);
        imagesavealpha($masked, true);
        $transparent = imagecolorallocatealpha($masked, 0, 0, 0, 127);
        imagefilledrectangle($masked, 0, 0, $w, $h, $transparent);
        for ($px = 0; $px < $w; $px++) {
            for ($py = 0; $py < $h; $py++) {
                $cx2 = $px < $radius ? $radius : $w - $radius - 1;
                $cy2 = $py < $radius ? $radius : $h - $radius - 1;
                $inCornerZone = (
                    ($px < $radius       && $py < $radius) ||
                    ($px >= $w - $radius && $py < $radius) ||
                    ($px < $radius       && $py >= $h - $radius) ||
                    ($px >= $w - $radius && $py >= $h - $radius)
                );
                if ($inCornerZone) {
                    $dx = $px - $cx2;
                    $dy = $py - $cy2;
                    if ($dx * $dx + $dy * $dy > $radius * $radius) continue;
                }
                $c = imagecolorat($src, $px, $py);
                $a = ($c >> 24) & 0x7F;
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8)  & 0xFF;
                $b = $c & 0xFF;
                imagesetpixel($masked, $px, $py, imagecolorallocatealpha($masked, $r, $g, $b, $a));
            }
        }
        imagedestroy($src);
        return $masked;
    }

    private function drawRoundedRect($img, $x, $y, $w, $h, $hex, $radius)
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $color = imagecolorallocate($img, $r, $g, $b);
        $rad   = min($radius, (int)($w / 2), (int)($h / 2));
        imagefilledrectangle($img, $x + $rad, $y,        $x + $w - $rad, $y + $h,        $color);
        imagefilledrectangle($img, $x,        $y + $rad, $x + $w,        $y + $h - $rad, $color);
        imagefilledellipse($img, $x + $rad,         $y + $rad,         $rad * 2, $rad * 2, $color);
        imagefilledellipse($img, $x + $w - $rad,    $y + $rad,         $rad * 2, $rad * 2, $color);
        imagefilledellipse($img, $x + $rad,         $y + $h - $rad,    $rad * 2, $rad * 2, $color);
        imagefilledellipse($img, $x + $w - $rad,    $y + $h - $rad,    $rad * 2, $rad * 2, $color);
    }

    private function drawRoundedRectBorder($img, $x, $y, $w, $h, $color, $radius)
    {
        $r = min($radius, (int)($w / 2), (int)($h / 2));
        imageline($img, $x + $r,     $y,         $x + $w - $r, $y,           $color);
        imageline($img, $x + $r,     $y + $h,    $x + $w - $r, $y + $h,      $color);
        imageline($img, $x,          $y + $r,    $x,           $y + $h - $r, $color);
        imageline($img, $x + $w,     $y + $r,    $x + $w,      $y + $h - $r, $color);
        imagearc($img, $x + $r,         $y + $r,         $r * 2, $r * 2, 180, 270, $color);
        imagearc($img, $x + $w - $r,    $y + $r,         $r * 2, $r * 2, 270, 360, $color);
        imagearc($img, $x + $r,         $y + $h - $r,    $r * 2, $r * 2,  90, 180, $color);
        imagearc($img, $x + $w - $r,    $y + $h - $r,    $r * 2, $r * 2,   0,  90, $color);
    }

    /* URL or relative path → absolute filesystem path */
    private function toFilePath($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH);
        }
        $base = trim(parse_url(base_url(), PHP_URL_PATH), '/');
        if ($base && strpos($path, '/' . $base . '/') === 0) {
            $path = substr($path, strlen('/' . $base));
        }
        return FCPATH . ltrim($path, '/');
    }

    private function loadImage($path)
    {
        $info = @getimagesize($path);
        if (!$info) return false;
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($path);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                return $img;
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp')
                    ? imagecreatefromwebp($path) : false;
            default:
                return false;
        }
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
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function outputImage($img, $name)
    {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . url_title($name) . '.png"');
        imagepng($img, null, 6);
    }
}
