<?php
/* BulkWA — Diagnose + Reconnect + Send (PHP 5.6+) */
@ini_set('display_errors', 1);
@error_reporting(E_ALL);

define('BULKWA_BASE', 'https://bulkwapanel.com');
define('BULKWA_INSTANCE_ID', '690427C3B8215');   // ← confirm this matches the panel
define('BULKWA_ACCESS_TOKEN', '67344839386a2');   // ← your token

function e($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function http_get($url)
{
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_HEADER => 1,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_SSL_VERIFYHOST => 2
  ]);
  $raw = curl_exec($ch);
  $err = curl_error($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  $hs = (int) $info['header_size'];
  $hdr = substr($raw, 0, $hs);
  $body = substr($raw, $hs);
  $ct = '';
  foreach (explode("\r\n", $hdr) as $line) {
    if (stripos($line, 'Content-Type:') === 0) {
      $ct = trim(substr($line, 13));
      break;
    }
  }
  return ['http' => (int) $info['http_code'], 'ct' => $ct, 'hdr' => $hdr, 'body' => $body, 'err' => $err, 'info' => $info];
}
function http_post_json($url, $payload)
{
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => 1,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_SSL_VERIFYHOST => 2
  ]);
  $body = curl_exec($ch);
  $err = curl_error($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  return ['http' => (int) $info['http_code'], 'body' => $body, 'json' => json_decode($body, true), 'err' => $err, 'info' => $info, 'req' => json_encode($payload)];
}

/* ---- 1) Diagnose: is the instance logged in? (QR endpoint returns an image if NOT logged) ---- */
$qrURL = BULKWA_BASE . '/api/get_qrcode?instance_id=' . rawurlencode(BULKWA_INSTANCE_ID) . '&access_token=' . rawurlencode(BULKWA_ACCESS_TOKEN);
$qr = http_get($qrURL);
$needsLogin = ($qr['http'] === 200 && stripos($qr['ct'], 'image/') !== false);

/* ---- 2) Optional: try reconnect if not logged in ---- */
$recon = null;
if ($needsLogin) {
  $reURL = BULKWA_BASE . '/api/reconnect?instance_id=' . rawurlencode(BULKWA_INSTANCE_ID) . '&access_token=' . rawurlencode(BULKWA_ACCESS_TOKEN);
  $recon = http_get($reURL);
}

/* ---- 3) Send (fill phone/message here or use POST fields) ---- */
$number = isset($_POST['number']) ? preg_replace('/\D+/', '', $_POST['number']) : '918452925291';
$message = isset($_POST['message']) ? trim($_POST['message']) : 'test message';

$sendRes = null;
if ($number && $message) {
  $sendRes = http_post_json(BULKWA_BASE . '/api/send', [
    'number' => $number,
    'type' => 'text',
    'message' => $message,
    'instance_id' => BULKWA_INSTANCE_ID,
    'access_token' => BULKWA_ACCESS_TOKEN
  ]);
  $apiOk = is_array($sendRes['json']) && (
    (!empty($sendRes['json']['success'])) ||
    (isset($sendRes['json']['status']) && in_array(strtolower($sendRes['json']['status']), ['ok', 'success', 'sent'], true))
  );
  $sendOK = ($sendRes['http'] >= 200 && $sendRes['http'] < 300) && ($apiOk || is_array($sendRes['json']));
}

/* ---- HTML report ---- */
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BulkWA Diagnose + Send</title>
  <style>
    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Arial, sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      margin: 0;
      padding: 20px
    }

    h1 {
      font-size: 20px;
      margin: 0 0 12px
    }

    .pan {
      background: #0b1220;
      border: 1px solid #1f2a44;
      border-radius: 14px;
      padding: 12px;
      margin: 10px 0
    }

    .ok {
      color: #22c55e
    }

    .warn {
      color: #f59e0b
    }

    .err {
      color: #ef4444
    }

    .sub {
      color: #94a3b8
    }

    input,
    textarea {
      width: 100%;
      background: #0a1020;
      border: 1px solid #243153;
      color: #e2e8f0;
      padding: 10px;
      border-radius: 10px
    }

    button {
      background: #2563eb;
      border: none;
      color: #fff;
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px
    }

    pre {
      white-space: pre-wrap;
      word-break: break-word;
      font-size: 12px;
      margin: 0
    }

    img.qr {
      background: #fff;
      border-radius: 8px;
      max-width: 100%;
      padding: 6px
    }
  </style>
</head>

<body>
  <h1>BulkWA — Diagnose + Reconnect + Send</h1>

  <div class="pan">
    <div>Instance: <b><?= e(BULKWA_INSTANCE_ID) ?></b> · Token: <span
        class="sub"><?= e(substr(BULKWA_ACCESS_TOKEN, 0, 4)) ?>…</span></div>
    <?php if ($needsLogin): ?>
      <div class="err">NOT LOGGED IN — scan the QR below in WhatsApp to activate the session.</div>
      <?php if ($qr['http'] === 200 && stripos($qr['ct'], 'image/') !== false): ?>
        <div style="margin-top:8px">
          <img class="qr" src="data:<?= e($qr['ct']) ?>;base64,<?= base64_encode($qr['body']) ?>" alt="QR">
        </div>
      <?php endif; ?>
      <?php if ($recon): ?>
        <div class="sub" style="margin-top:8px">Reconnect HTTP: <?= $recon['http'] ?> (this only nudges the session; scanning
          QR is still required).</div>
      <?php endif; ?>
    <?php else: ?>
      <div class="ok">Logged-in state detected (QR endpoint did not return an image).</div>
    <?php endif; ?>
  </div>

  <form class="pan" method="post">
    <div class="grid">
      <div><label class="sub">Phone (with country code, digits only)</label><input name="number" value="<?= e($number) ?>"
          required></div>
      <div><label class="sub">Message</label><textarea name="message" required><?= e($message) ?></textarea></div>
    </div>
    <div style="margin-top:10px"><button type="submit">Send Text</button> <span class="sub">→ posts to /api/send</span>
    </div>
  </form>

  <?php if ($sendRes): ?>
    <div class="pan">
      <?php if (!empty($sendOK)): ?>
        <div class="ok">✓ Request accepted by API (HTTP <?= $sendRes['http'] ?>).</div>
        <div class="sub">If it still doesn’t deliver, the device may be offline or the number may not be WhatsApp-active.
        </div>
      <?php else: ?>
        <div class="err">✗ Send failed (HTTP <?= $sendRes['http'] ?>).</div>
        <?php if ($sendRes['err'])
          echo '<div class="warn">cURL: ' . e($sendRes['err']) . '</div>'; ?>
      <?php endif; ?>
    </div>
    <div class="pan"><b class="sub">Request JSON</b>
      <pre><?= e($sendRes['req']) ?></pre>
    </div>
    <div class="pan"><b class="sub">Raw Response</b>
      <pre><?= e($sendRes['body']) ?></pre>
    </div>
    <div class="pan"><b class="sub">Parsed JSON</b>
      <pre><?= e(print_r($sendRes['json'], true)) ?></pre>
    </div>
  <?php endif; ?>

  <div class="pan">
    <b>Still not delivered?</b>
    <ul>
      <li>Scan the QR above to log the instance in; keep the phone online (no battery saver).</li>
      <li>Try a different known-good WhatsApp number.</li>
      <li>In your provider panel, set a webhook to capture delivery errors.</li>
      <li>If stuck, try <i>reboot/reset instance</i> in the panel, then scan QR again.</li>
    </ul>
  </div>
</body>

</html>