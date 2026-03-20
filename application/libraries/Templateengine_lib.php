<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Template Engine Library — Imagick backend (GD fallback)
 * Imagick uses PIXELS natively — no pt/px correction needed.
 * Falls back to GD automatically if Imagick not installed.
 */
class Templateengine_lib
{
    protected $CI;
    private $fontRegular;
    private $fontBold;
    private $useImagick;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('template_model');
        $this->CI->load->model('templateOverlay_model');
        $this->fontRegular = APPPATH . 'fonts/OpenSans-Regular.ttf';
        $this->fontBold    = APPPATH . 'fonts/OpenSans-Bold.ttf';
        $this->useImagick  = extension_loaded('imagick');
    }

    /* ================================================================
       HELPERS — used by controller
    ================================================================ */
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
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);
        if ($this->CI->upload->do_upload('template_file')) {
            $uploadData = $this->CI->upload->data();
            $arrayData = [
                'title'         => $this->CI->input->post('title'),
                'type'          => $type,
                'file_path'     => $config['upload_path'] . $uploadData['file_name'],
                'upload_status' => 'completed',
                'created_by'    => get_loggedin_user_id()
            ];
            return $this->CI->template_model->saveTemplate($arrayData);
        } else {
            $this->CI->db->rollback();
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    public function storeTemplate()
    {
        $this->CI->db->trans_start();
        $type = $this->CI->input->post('type');
        $config = [
            'upload_path'   => './uploads/template-manager/' . $type . '/',
            'allowed_types' => ($type == 'image' ? 'jpg|jpeg|png' : 'mp4'),
            'encrypt_name'  => true
        ];
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
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
            $insertId = $this->CI->template_model->saveTemplate($arrayData);
            $this->CI->db->trans_complete();
            return $insertId;
        } else {
            $this->CI->db->trans_rollback();
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }

    /* ================================================================
       PUBLIC: renderImage — auto-selects Imagick or GD
    ================================================================ */
    public function renderImage($templatePath, $logoUrl, $overlays, $filename, $branchTextMap)
    {
        $basePath = $this->toFilePath($templatePath);
        $logoPath = $this->toFilePath($logoUrl);

        if (!file_exists($basePath)) show_error('Base image not found: ' . $basePath);
        if (!file_exists($logoPath)) show_error('Logo image not found: ' . $logoPath);

        if ($this->useImagick) {
            $this->renderImagick($basePath, $logoPath, $overlays, $filename, $branchTextMap);
        } else {
            $this->renderGD($basePath, $logoPath, $overlays, $filename, $branchTextMap);
        }
    }

    /* ================================================================
       IMAGICK RENDERER — font sizes in pixels, exact match to browser
    ================================================================ */
    private function renderImagick($basePath, $logoPath, $overlays, $filename, $branchTextMap)
    {
        $base  = new Imagick($basePath);
        $base->setImageFormat('png');
        $baseW = $base->getImageWidth();
        $baseH = $base->getImageHeight();

        foreach ($overlays as $ov) {
            $settings = $this->decodeSettings($ov['settings'] ?? '');
            $bg       = $settings['bg'] ?? [];
            $type     = $ov['overlay_type'] ?? 'logo';

            $x = (int)round((float)$ov['x']      * $baseW);
            $y = (int)round((float)$ov['y']      * $baseH);
            $w = (int)round((float)$ov['width']  * $baseW);
            $h = (int)round((float)$ov['height'] * $baseH);
            if ($w <= 0 || $h <= 0) continue;

            // Text background
            if ($type === 'text' && !empty($bg['enabled'])) {
                $this->imkBackground($base, $x, $y, $w, $h,
                    $bg['color'] ?? '#ffffff', (int)($bg['radius'] ?? 0));
            }

            if ($type === 'logo') {
                $this->imkLogo($base, $logoPath, $x, $y, $w, $h,
                    (float)($settings['opacity']   ?? 1.0),
                    (int)($settings['radius']      ?? 0),
                    (int)($settings['borderWidth'] ?? 0),
                    $settings['borderColor']       ?? '#ffffff',
                    $settings['objectFit']         ?? 'contain');

            } elseif ($type === 'text') {
                $textKey = $settings['text_key'] ?? '';
                $text    = $branchTextMap[$textKey] ?? '';
                $padding = !empty($bg['enabled']) ? (int)($bg['padding'] ?? 0) : 0;
                if ($text !== '') {
                    $this->imkText($base, $text,
                        $x + $padding, $y + $padding,
                        $w - $padding * 2, $h - $padding * 2,
                        $settings);
                }
            }
        }

        $safe = url_title($filename);
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $safe . '.png"');
        echo $base->getImageBlob();
        $base->clear();
        exit;
    }

    private function imkBackground(Imagick $base, $x, $y, $w, $h, $hex, $radius = 0)
    {
        $draw = new ImagickDraw();
        $draw->setFillColor($this->hexToPixel($hex));
        $draw->setStrokeWidth(0);
        $draw->setStrokeColor('none');
        if ($radius > 0) {
            $r = min($radius, (int)($w/2), (int)($h/2));
            $draw->roundRectangle($x, $y, $x+$w, $y+$h, $r, $r);
        } else {
            $draw->rectangle($x, $y, $x+$w, $y+$h);
        }
        $base->drawImage($draw);
        $draw->clear();
    }

    private function imkLogo(Imagick $base, $logoPath, $x, $y, $w, $h,
                              $opacity, $radius, $borderWidth, $borderColor, $objectFit)
    {
        $logo = new Imagick($logoPath);
        $logo->setImageFormat('png');
        $srcW = $logo->getImageWidth();
        $srcH = $logo->getImageHeight();

        [$dstX, $dstY, $dstW, $dstH] = $this->calcObjectFit($objectFit, $srcW, $srcH, $w, $h);
        $logo->resizeImage($dstW, $dstH, Imagick::FILTER_LANCZOS, 1);

        $canvas = new Imagick();
        $canvas->newImage($w, $h, new ImagickPixel('transparent'));
        $canvas->setImageFormat('png');
        $canvas->compositeImage($logo, Imagick::COMPOSITE_OVER, $dstX, $dstY);
        $logo->clear();

        // Border radius mask
        if ($radius > 0) {
            $r    = min($radius, (int)($w/2), (int)($h/2));
            $mask = new Imagick();
            $mask->newImage($w, $h, new ImagickPixel('black'));
            $mask->setImageFormat('png');
            $md = new ImagickDraw();
            $md->setFillColor('white');
            $md->setStrokeWidth(0);
            $md->roundRectangle(0, 0, $w, $h, $r, $r);
            $mask->drawImage($md);
            $md->clear();
            $canvas->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $mask->clear();
        }

        // Border
        if ($borderWidth > 0) {
            $bd = new ImagickDraw();
            $bd->setFillColor('transparent');
            $bd->setStrokeColor($this->hexToPixel($borderColor));
            $bd->setStrokeWidth($borderWidth);
            $half = $borderWidth / 2;
            if ($radius > 0) {
                $r = min($radius, (int)($w/2), (int)($h/2));
                $bd->roundRectangle($half, $half, $w-$half, $h-$half, $r, $r);
            } else {
                $bd->rectangle($half, $half, $w-$half, $h-$half);
            }
            $canvas->drawImage($bd);
            $bd->clear();
        }

        if ($opacity < 1.0) {
            $canvas->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity, Imagick::CHANNEL_ALPHA);
        }

        $base->compositeImage($canvas, Imagick::COMPOSITE_OVER, $x, $y);
        $canvas->clear();
    }

    private function imkText(Imagick $base, $text, $x, $y, $w, $h, $settings)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') return;

        $colorHex = $settings['color']       ?? '#000000';
        $align    = $settings['align']       ?? 'center';
        $lhMult   = (float)($settings['line_height'] ?? 1.2);
        $weight   = $settings['weight']      ?? 'normal';
        $fontFile = ($weight === 'bold' || $weight === '600' || $weight === '800')
                  ? $this->fontBold : $this->fontRegular;
        if (!file_exists($fontFile)) return;

        // 98% width safety margin prevents character-spacing overflow
        $safeW    = (int)floor($w * 0.98);
        $fontSize = $this->imkAutoFit($base, $text, $safeW, $h, $fontFile, $lhMult);

        $draw = new ImagickDraw();
        $draw->setFont($fontFile);
        $draw->setFontSize($fontSize);
        $draw->setFillColor($this->hexToPixel($colorHex));
        $draw->setStrokeWidth(0);
        $draw->setStrokeColor('none');

        // Wrap at same safeW used during autoFit search
        $lines   = $this->imkWrap($base, $draw, $text, $safeW);
        $metrics = $base->queryFontMetrics($draw, 'Ag', false);
        $lineH   = (int)ceil($metrics['textHeight'] * $lhMult);
        $totalH  = count($lines) * $lineH;
        $ascender= (int)ceil($metrics['ascender']);
        $startY  = $y + max(0, (int)(($h - $totalH) / 2)) + $ascender;

        foreach ($lines as $i => $ln) {
            $lm = $base->queryFontMetrics($draw, $ln ?: ' ', false);
            $tw = $lm['textWidth'];
            if ($align === 'left')      $tx = $x;
            elseif ($align === 'right') $tx = $x + $w - $tw;
            else                        $tx = $x + (int)(($w - $tw) / 2);
            $draw->annotation($tx, $startY + ($i * $lineH), $ln);
        }

        $base->drawImage($draw);
        $draw->clear();
    }

    private function imkAutoFit(Imagick $base, $text, $maxW, $maxH,
                                 $fontFile, $lhMult = 1.2, $min = 6, $max = 500)
    {
        $draw = new ImagickDraw();
        $draw->setFont($fontFile);
        $draw->setStrokeWidth(0);
        $lo = $min; $hi = min($max, $maxH); $best = $min;

        while ($lo <= $hi) {
            $mid = (int)(($lo + $hi) / 2);
            $draw->setFontSize($mid);
            $lines  = $this->imkWrap($base, $draw, $text, $maxW);
            $m      = $base->queryFontMetrics($draw, 'Ag', false);
            $lineH  = (int)ceil($m['textHeight'] * $lhMult);
            $totalH = count($lines) * $lineH;
            $maxLW  = 0;
            foreach ($lines as $ln) {
                $lm = $base->queryFontMetrics($draw, $ln ?: ' ', false);
                if ($lm['textWidth'] > $maxLW) $maxLW = $lm['textWidth'];
            }
            if ($maxLW <= $maxW && $totalH <= $maxH) { $best = $mid; $lo = $mid + 1; }
            else                                     { $hi  = $mid - 1; }
        }
        $draw->clear();
        return $best;
    }

    private function imkWrap(Imagick $base, ImagickDraw $draw, $text, $maxW)
    {
        $text       = preg_replace('/\r\n|\r/', "\n", $text);
        $text       = preg_replace('/[ \t]+/', ' ', $text);
        $paragraphs = explode("\n", $text);
        $lines      = [];
        foreach ($paragraphs as $para) {
            $para  = trim($para);
            $words = explode(' ', $para);
            $line  = '';
            foreach ($words as $word) {
                if ($word === '') continue;
                $test = $line === '' ? $word : $line . ' ' . $word;
                $m    = $base->queryFontMetrics($draw, $test, false);
                if ($m['textWidth'] > $maxW && $line !== '') {
                    $lines[] = $line; $line = $word;
                } else {
                    $line = $test;
                }
            }
            if ($line !== '') $lines[] = $line;
        }
        return $lines ?: [''];
    }

    /* ================================================================
       GD FALLBACK — used when Imagick not available
    ================================================================ */
    private function renderGD($basePath, $logoPath, $overlays, $filename, $branchTextMap)
    {
        $base  = $this->gdLoadImage($basePath);
        if (!$base) show_error('Failed to load base image');
        $baseW = imagesx($base); $baseH = imagesy($base);

        foreach ($overlays as $ov) {
            $settings = $this->decodeSettings($ov['settings'] ?? '');
            $bg       = $settings['bg'] ?? [];
            $type     = $ov['overlay_type'] ?? 'logo';
            $x = (int)round((float)$ov['x']      * $baseW);
            $y = (int)round((float)$ov['y']      * $baseH);
            $w = (int)round((float)$ov['width']  * $baseW);
            $h = (int)round((float)$ov['height'] * $baseH);
            if ($w <= 0 || $h <= 0) continue;

            if ($type === 'text' && !empty($bg['enabled'])) {
                $bgColor = $bg['color'] ?? '#ffffff'; $bgR = (int)($bg['radius'] ?? 0);
                if ($bgR > 0) $this->drawRoundedRect($base, $x, $y, $w, $h, $bgColor, $bgR);
                else          $this->drawBackground($base, $x, $y, $w, $h, $bgColor);
            }

            if ($type === 'logo') {
                $this->drawLogo($base, $logoPath, $x, $y, $w, $h,
                    (float)($settings['opacity']   ?? 1.0),
                    (int)($settings['radius']      ?? 0),
                    (int)($settings['borderWidth'] ?? 0),
                    $settings['borderColor']       ?? '#ffffff',
                    $settings['objectFit']         ?? 'contain');
            } elseif ($type === 'text') {
                $textKey = $settings['text_key'] ?? '';
                $text    = $branchTextMap[$textKey] ?? '';
                $padding = !empty($bg['enabled']) ? (int)($bg['padding'] ?? 0) : 0;
                if ($text !== '') {
                    $this->drawText($base, $text,
                        $x + $padding, $y + $padding,
                        $w - $padding * 2, $h - $padding * 2, $settings);
                }
            }
        }

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . url_title($filename) . '.png"');
        imagepng($base, null, 6);
        imagedestroy($base);
        exit;
    }

    private function drawText($img, $text, $x, $y, $w, $h, $settings)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') return;
        $colorHex = $settings['color']       ?? '#000000';
        $align    = $settings['align']       ?? 'center';
        $lhMult   = (float)($settings['line_height'] ?? 1.2);
        $weight   = $settings['weight']      ?? 'normal';
        [$r, $g, $b] = $this->hexToRgb($colorHex);
        $color = imagecolorallocate($img, $r, $g, $b);
        $fontFile = ($weight === 'bold' || $weight === '600' || $weight === '800')
            ? $this->fontBold : $this->fontRegular;
        if (!file_exists($fontFile)) return;
        $ptW      = (int)floor($w * 0.75);
        $fontSize = $this->autoFitFontSize($text, $w, $h, $fontFile, $lhMult);
        $lines    = $this->wrapText($text, $fontSize, $fontFile, $ptW);
        $lineH    = (int)ceil($fontSize * (96.0/72.0) * $lhMult);
        $totalH   = count($lines) * $lineH;
        $sb       = imagettfbbox($fontSize, 0, $fontFile, 'Ag');
        $ascender = (int)ceil(abs($sb[7]) * (96.0/72.0));
        $startY   = $y + max(0, (int)(($h - $totalH) / 2)) + $ascender;
        foreach ($lines as $i => $ln) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $ln);
            $tw  = abs($box[4] - $box[0]);
            if ($align === 'left')      $tx = $x;
            elseif ($align === 'right') $tx = $x + $w - $tw;
            else                        $tx = $x + (int)(($w - $tw) / 2);
            imagettftext($img, $fontSize, 0, $tx, $startY + ($i * $lineH), $color, $fontFile, $ln);
        }
    }

    private function autoFitFontSize($text, $maxW, $maxH, $fontFile, $lhMult=1.2, $min=6, $max=300)
    {
        $ptW = (int)floor($maxW * 0.75); $hi = min($max, $maxH); $lo = $min; $best = $min;
        while ($lo <= $hi) {
            $mid = (int)(($lo + $hi) / 2);
            [$tw, $th] = $this->measureWrappedText($text, $mid, $fontFile, $ptW, $lhMult);
            if ($tw <= $ptW && $th <= $maxH) { $best = $mid; $lo = $mid + 1; }
            else                             { $hi  = $mid - 1; }
        }
        return $best;
    }

    private function measureWrappedText($text, $fontSize, $fontFile, $maxW, $lhMult=1.2)
    {
        $text  = preg_replace('/\s+/', ' ', trim($text));
        $lines = $this->wrapText($text, $fontSize, $fontFile, $maxW);
        $maxLW = 0;
        foreach ($lines as $line) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $line ?: 'Ag');
            $lw  = abs($box[4] - $box[0]);
            if ($lw > $maxLW) $maxLW = $lw;
        }
        $lineH  = (int)ceil($fontSize * (96.0/72.0) * $lhMult);
        return [$maxLW, count($lines) * $lineH];
    }

    private function wrapText($text, $fontSize, $fontFile, $maxW)
    {
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $lines = [];
        foreach (explode("\n", $text) as $para) {
            $para = trim($para); $words = explode(' ', $para); $line = '';
            foreach ($words as $word) {
                if ($word === '') continue;
                $test = $line === '' ? $word : $line . ' ' . $word;
                $box  = imagettfbbox($fontSize, 0, $fontFile, $test);
                if (abs($box[4] - $box[0]) > $maxW && $line !== '') {
                    $lines[] = $line; $line = $word;
                } else { $line = $test; }
            }
            if ($line !== '') $lines[] = $line;
        }
        return $lines ?: [''];
    }

    private function drawLogo($canvas, $path, $x, $y, $w, $h,
                               $opacity=1.0, $radius=0, $borderWidth=0,
                               $borderColor='#ffffff', $objectFit='contain')
    {
        $logo = $this->gdLoadImage($path); if (!$logo) return;
        $srcW = imagesx($logo); $srcH = imagesy($logo);
        [$dstX, $dstY, $dstW, $dstH] = $this->calcObjectFit($objectFit, $srcW, $srcH, $w, $h);
        $tmp = imagecreatetruecolor($w, $h);
        imagealphablending($tmp, false); imagesavealpha($tmp, true);
        imagefilledrectangle($tmp, 0, 0, $w, $h, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
        imagealphablending($tmp, true);
        imagecopyresampled($tmp, $logo, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($logo);
        if ($radius > 0) $tmp = $this->applyRadiusMask($tmp, $w, $h, $radius);
        if ($borderWidth > 0) {
            [$br, $bg2, $bb] = $this->hexToRgb($borderColor);
            $bc = imagecolorallocatealpha($tmp, $br, $bg2, $bb, 0);
            for ($i = 0; $i < $borderWidth; $i++) {
                if ($radius > 0) $this->drawRoundedRectBorder($tmp, $i, $i, $w-$i*2, $h-$i*2, $bc, $radius);
                else imagerectangle($tmp, $i, $i, $w-1-$i, $h-1-$i, $bc);
            }
        }
        $pct = max(0, min(100, (int)round($opacity * 100)));
        if ($pct >= 100) { imagealphablending($canvas, true); imagecopy($canvas, $tmp, $x, $y, 0, 0, $w, $h); }
        else             { imagecopymerge($canvas, $tmp, $x, $y, 0, 0, $w, $h, $pct); }
        imagedestroy($tmp);
    }

    private function applyRadiusMask($src, $w, $h, $radius)
    {
        $radius = min($radius, (int)($w/2), (int)($h/2));
        $masked = imagecreatetruecolor($w, $h);
        imagealphablending($masked, false); imagesavealpha($masked, true);
        imagefilledrectangle($masked, 0, 0, $w, $h, imagecolorallocatealpha($masked, 0, 0, 0, 127));
        for ($px = 0; $px < $w; $px++) for ($py = 0; $py < $h; $py++) {
            $cx = $px < $radius ? $radius : $w-$radius-1;
            $cy = $py < $radius ? $radius : $h-$radius-1;
            $ic = (($px<$radius&&$py<$radius)||($px>=$w-$radius&&$py<$radius)||
                   ($px<$radius&&$py>=$h-$radius)||($px>=$w-$radius&&$py>=$h-$radius));
            if ($ic) { $dx=$px-$cx; $dy=$py-$cy; if ($dx*$dx+$dy*$dy>$radius*$radius) continue; }
            $c = imagecolorat($src, $px, $py);
            imagesetpixel($masked, $px, $py, imagecolorallocatealpha($masked,
                ($c>>16)&0xFF, ($c>>8)&0xFF, $c&0xFF, ($c>>24)&0x7F));
        }
        imagedestroy($src); return $masked;
    }

    private function drawBackground($img, $x, $y, $w, $h, $hex)
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        imagefilledrectangle($img, $x, $y, $x+$w, $y+$h, imagecolorallocate($img, $r, $g, $b));
    }

    private function drawRoundedRect($img, $x, $y, $w, $h, $hex, $radius)
    {
        [$r, $g, $b] = $this->hexToRgb($hex); $color = imagecolorallocate($img, $r, $g, $b);
        $rad = min($radius, (int)($w/2), (int)($h/2));
        imagefilledrectangle($img, $x+$rad, $y, $x+$w-$rad, $y+$h, $color);
        imagefilledrectangle($img, $x, $y+$rad, $x+$w, $y+$h-$rad, $color);
        imagefilledellipse($img, $x+$rad, $y+$rad, $rad*2, $rad*2, $color);
        imagefilledellipse($img, $x+$w-$rad, $y+$rad, $rad*2, $rad*2, $color);
        imagefilledellipse($img, $x+$rad, $y+$h-$rad, $rad*2, $rad*2, $color);
        imagefilledellipse($img, $x+$w-$rad, $y+$h-$rad, $rad*2, $rad*2, $color);
    }

    private function drawRoundedRectBorder($img, $x, $y, $w, $h, $color, $radius)
    {
        $r = min($radius, (int)($w/2), (int)($h/2));
        imageline($img, $x+$r, $y, $x+$w-$r, $y, $color);
        imageline($img, $x+$r, $y+$h, $x+$w-$r, $y+$h, $color);
        imageline($img, $x, $y+$r, $x, $y+$h-$r, $color);
        imageline($img, $x+$w, $y+$r, $x+$w, $y+$h-$r, $color);
        imagearc($img, $x+$r, $y+$r, $r*2, $r*2, 180, 270, $color);
        imagearc($img, $x+$w-$r, $y+$r, $r*2, $r*2, 270, 360, $color);
        imagearc($img, $x+$r, $y+$h-$r, $r*2, $r*2, 90, 180, $color);
        imagearc($img, $x+$w-$r, $y+$h-$r, $r*2, $r*2, 0, 90, $color);
    }

    private function gdLoadImage($path)
    {
        $info = @getimagesize($path); if (!$info) return false;
        switch ($info[2]) {
            case IMAGETYPE_JPEG: return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($path);
                imagealphablending($img, true); imagesavealpha($img, true); return $img;
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false;
            default: return false;
        }
    }

    /* ================================================================
       SHARED HELPERS
    ================================================================ */
    private function decodeSettings($raw)
    {
        if (is_array($raw)) return $raw;
        if (empty($raw))    return [];
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }

    private function calcObjectFit($fit, $srcW, $srcH, $boxW, $boxH)
    {
        if ($fit === 'fill') return [0, 0, $boxW, $boxH];
        $sr = $srcW/$srcH; $br = $boxW/$boxH;
        if ($fit === 'cover') {
            if ($sr > $br) { $dstH=$boxH; $dstW=(int)round($boxH*$sr); }
            else           { $dstW=$boxW; $dstH=(int)round($boxW/$sr); }
        } else {
            if ($sr > $br) { $dstW=$boxW; $dstH=(int)round($boxW/$sr); }
            else           { $dstH=$boxH; $dstW=(int)round($boxH*$sr); }
        }
        return [(int)round(($boxW-$dstW)/2), (int)round(($boxH-$dstH)/2), $dstW, $dstH];
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    }

    private function hexToPixel($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return new ImagickPixel('#'.$hex);
    }

    private function toFilePath($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) $path = parse_url($path, PHP_URL_PATH);
        $base = trim(parse_url(base_url(), PHP_URL_PATH), '/');
        if ($base && strpos($path, '/'.$base.'/') === 0) $path = substr($path, strlen('/'.$base));
        return FCPATH . ltrim($path, '/');
    }

    // Legacy loadImage alias kept for any direct GD usage
    private function loadImage($path) { return $this->gdLoadImage($path); }
}