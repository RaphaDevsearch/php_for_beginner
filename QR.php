<?php

/**
 * Validates the input based on which mode was selected in the dropdown.
 *
 * - "link" mode: must pass FILTER_VALIDATE_URL, same as before
 * - "text" mode: any non-empty text is allowed — a QR code can encode
 *   plain words just as easily as a URL, so the rule here is much looser
 *
 * Returns: ['valid' => true, 'value' => '...']
 *      or: ['valid' => false, 'reason' => '...']
 */
function validateInput(string $type, string $rawValue): array {

    if ($rawValue === '') {
        return ['valid' => false, 'reason' => 'blank'];
    }

    if ($type === 'link') {
        if (filter_var($rawValue, FILTER_VALIDATE_URL) === false) {
            return ['valid' => false, 'reason' => 'not a valid URL'];
        }
        return ['valid' => true, 'value' => $rawValue];
    }

    // "text" mode — just cap the length so the QR code doesn't become
    // impossible to scan (very long text makes a very dense QR code).
    // Using strlen() here instead of mb_strlen() on purpose: strlen()
    // needs no extra PHP extension, while mb_strlen() requires "mbstring"
    // to be enabled — a common thing to be missing on local dev setups.
    if (strlen($rawValue) > 300) {
        return ['valid' => false, 'reason' => 'text is too long (max 300 characters)'];
    }

    return ['valid' => true, 'value' => $rawValue];
}


/**
 * Builds the actual QR code image URL using the QR Server API.
 * This is the "PHP does the work" part — no JavaScript, no CDN script.
 * The browser just requests this URL like any other <img src="">.
 *
 * urlencode() is essential here: it converts characters like "?", "&", "/"
 * in the link into a safe format so they don't break the QR API's own URL.
 */
function buildQrCodeUrl(string $link, int $size = 300): string {
    $encodedLink = urlencode($link);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedLink}";
}


/**
 * Builds the whole result — either the QR image + link, or an error message —
 * as one HTML string. Same separation-of-concerns rule as before: this function
 * only returns text, it doesn't calculate or validate anything itself.
 */
function buildResultHtml(?string $validValue, ?string $reason, string $type): string {

    // Nothing submitted yet
    if ($validValue === null && $reason === null) {
        return 'Your QR code will appear here';
    }

    // Failed validation — but skip showing an error for a simple blank field
    if ($validValue === null) {
        if ($reason === 'blank') {
            return 'Your QR code will appear here';
        }
        $message = $type === 'link'
            ? 'Please enter a valid link (must include http:// or https://).'
            : htmlspecialchars(ucfirst($reason)) . '.';
        return '<p class="error">' . $message . '</p>';
    }

    $qrUrl = buildQrCodeUrl($validValue);

    // Escape everything printed — the value came from user input
    $safeValue = htmlspecialchars($validValue);
    $safeQrUrl = htmlspecialchars($qrUrl);

    return '<div class="qr-output">'
         . '<img src="' . $safeQrUrl . '" alt="QR code for ' . $safeValue . '" class="qr-image">'
         . '<p class="qr-value">' . $safeValue . '</p>'
         . '<a href="' . $safeQrUrl . '" download="qrcode.png" class="qr-download">Download QR code</a>'
         . '</div>';
}


// ---------- Main logic ----------

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$rawValue = $_POST['value'] ?? '';
$rawType = $_POST['type'] ?? 'link';

// Whitelist validation: only these two values are ever allowed for $type,
// no matter what actually arrives in $_POST — protects against someone
// submitting an unexpected value for the dropdown.
$allowedTypes = ['link', 'text'];
$type = in_array($rawType, $allowedTypes, true) ? $rawType : 'link';

$validValue = null;
$reason = null;

if ($isPost) {
    $result = validateInput($type, $rawValue);

    if ($result['valid']) {
        $validValue = $result['value'];
    } else {
        $reason = $result['reason'];
    }
}

$resultHtml = buildResultHtml($validValue, $reason, $type);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code Generator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --paper:#F5F6FB;
    --paper-dim:#ECEEF7;
    --ink:#14162B;
    --ink-soft:#54577A;
    --ink-faint:#9A9DBD;
    --primary:#5A4FCF;
    --primary-dark:#3D34A8;
    --primary-soft:#EAE8FB;
    --teal:#2FB6A3;
    --line:#DEE1F0;
    --white:#FFFFFF;
    --radius:18px;
    --shadow:0 20px 44px -20px rgba(20,22,43,0.16), 0 2px 8px -2px rgba(20,22,43,0.06);
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{
    font-family:'Inter', sans-serif;
    background:
      radial-gradient(680px 420px at 12% -8%, #EEEBFC 0%, transparent 60%),
      radial-gradient(600px 380px at 100% 0%, #E3F5F1 0%, transparent 55%),
      var(--paper);
    color:var(--ink);
    line-height:1.6;
    min-height:100vh;
    display:flex; align-items:flex-start; justify-content:center;
    padding:64px 20px 80px;
  }
  h1{font-family:'Space Grotesk', sans-serif; letter-spacing:-0.02em;}
  code{font-family:'JetBrains Mono', monospace; font-size:0.85em; background:var(--ink); color:#D7D9F5; padding:2px 7px; border-radius:5px;}

  .page{width:100%; max-width:520px;}

  .brand-row{display:flex; align-items:center; gap:12px; margin-bottom:22px;}
  .brand-mark{
    width:40px; height:40px; border-radius:12px;
    background:linear-gradient(155deg, var(--teal), #1F8477);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1.05rem;
    box-shadow:0 8px 18px -8px rgba(47,182,163,0.55);
  }
  .brand-text .eyebrow{font-family:'JetBrains Mono', monospace; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--ink-faint);}
  .brand-text h1{font-size:1.5rem; margin-top:2px;}
  .page > p.lead{color:var(--ink-soft); font-size:0.98rem; margin-bottom:32px;}

  .form-card{background:var(--white); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden;}
  .card-strip{display:flex; align-items:center; justify-content:space-between; padding:13px 22px; background:var(--ink); font-family:'JetBrains Mono', monospace; font-size:0.76rem; color:#C7CAE8;}
  .card-strip .dots{display:flex; gap:6px;}
  .card-strip .dots span{width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.28);}
  .card-strip .dots span:nth-child(1){background:#F2A93B;}
  .card-strip .dots span:nth-child(2){background:#2FB6A3;}
  .card-strip .dots span:nth-child(3){background:#8F86EE;}

  .card-body{padding:30px 28px 28px;}

  .field-top{font-family:'JetBrains Mono', monospace; font-size:0.74rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-faint); margin-bottom:12px;}

  .type-select select{
    width:100%;
    font-family:'JetBrains Mono', monospace; font-size:0.9rem; color:var(--ink);
    background:var(--paper); border:1px solid var(--line); border-radius:12px;
    padding:12px 14px; outline:none; cursor:pointer;
    appearance:none;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2354577A' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
    background-repeat:no-repeat;
    background-position:right 14px center;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .type-select select:focus{border-color:var(--teal); box-shadow:0 0 0 3px #E4F6F3;}

  .qr-value{
    font-family:'JetBrains Mono', monospace; font-size:0.85rem; color:var(--ink-soft);
    word-break:break-word; text-align:center; max-width:280px;
  }

  .link-field{
    display:flex; align-items:center; gap:10px;
    background:var(--paper); border:1px solid var(--line); border-radius:12px;
    padding:5px 6px 5px 16px;
    transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .link-field:focus-within{border-color:var(--teal); background:var(--white); box-shadow:0 0 0 3px #E4F6F3;}
  .link-field input{
    flex:1; border:none; background:transparent; outline:none;
    font-family:'JetBrains Mono', monospace; font-size:0.95rem; color:var(--ink);
    padding:12px 0; min-width:0;
  }
  .link-field input::placeholder{color:var(--ink-faint);}

  .divider{height:1px; background:var(--line); margin:22px 0;}

  button[type="submit"]{
    width:100%;
    font-family:'Inter', sans-serif; font-weight:600; font-size:0.95rem;
    background:var(--teal); color:#fff; border:none; border-radius:11px;
    padding:14px 24px; cursor:pointer;
    box-shadow:0 10px 22px -10px rgba(47,182,163,0.55);
    transition:filter .15s ease, transform .1s ease;
  }
  button[type="submit"]:hover{filter:brightness(1.06);}
  button[type="submit"]:active{transform:scale(.98);}

  .result-shell{
    margin-top:22px;
    border:1px dashed var(--line); border-radius:12px;
    padding:20px; text-align:center;
    font-family:'JetBrains Mono', monospace; font-size:0.85rem; color:var(--ink-faint);
    background:repeating-linear-gradient(135deg, rgba(20,22,43,0.02) 0 10px, transparent 10px 20px);
  }

  .qr-output{display:flex; flex-direction:column; align-items:center; gap:14px;}
  .qr-image{
    width:220px; height:220px; border-radius:12px; border:1px solid var(--line);
    padding:12px; background:#fff;
  }
  .qr-download{
    font-family:'JetBrains Mono', monospace; font-size:0.82rem; font-weight:600;
    color:var(--teal); background:#E4F6F3; padding:8px 16px; border-radius:20px;
  }
  .error{color:#D9534F; font-weight:600; font-size:0.9rem;}

  @media (max-width:520px){
    body{padding:44px 16px 60px;}
    .card-body{padding:24px 18px 24px;}
  }
</style>
</head>
<body>

<div class="page">

  <div class="brand-row">
    <div class="brand-mark">QR</div>
    <div class="brand-text">
      <div class="eyebrow">link to qr code</div>
      <h1>QR Code Generator</h1>
    </div>
  </div>

  <p class="lead">Paste a link, get a scannable QR code back — generated entirely server-side.</p>

  <form class="form-card" method="POST" action="">
    <div class="card-strip">
      <span>qr_generator.php</span>
      <span class="dots"><span></span><span></span><span></span></span>
    </div>

    <div class="card-body">
      <div class="field-top">Type</div>

      <div class="type-select">
        <select name="type" id="typeSelect">
          <option value="link" <?= $type === 'link' ? 'selected' : '' ?>>Link (URL)</option>
          <option value="text" <?= $type === 'text' ? 'selected' : '' ?>>Text</option>
        </select>
      </div>

      <div class="field-top" style="margin-top:18px;">Value</div>

      <div class="link-field">
        <input
          type="text"
          name="value"
          id="valueInput"
          placeholder="<?= $type === 'link' ? 'https://www.example.com' : 'e.g. I love you' ?>"
          value="<?= htmlspecialchars($rawValue) ?>">
      </div>

      <div class="divider"></div>

      <button type="submit">Generate QR code</button>

      <div class="result-shell"><?= $resultHtml ?></div>
    </div>
  </form>

</div>

<script>
  // Just a UX nicety: swaps the placeholder text immediately when the
  // dropdown changes, so the hint matches before the form is even submitted.
  const typeSelect = document.getElementById('typeSelect');
  const valueInput = document.getElementById('valueInput');

  typeSelect.addEventListener('change', () => {
    valueInput.placeholder = typeSelect.value === 'link'
      ? 'https://www.example.com'
      : 'e.g. I love you';
  });
</script>

</body>
</html>