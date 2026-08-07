<?php

/**
 * Validates whatever the person typed as a "link" to turn into a QR code.
 *
 * Returns: ['valid' => true, 'value' => 'https://...']
 *      or: ['valid' => false, 'reason' => '...']
 *
 * Same shape as validateScore() from your Grade Calculator — one function,
 * one job: decide if this piece of input is usable, and say why if not.
 */
function validateLink(string $rawLink): array {

    // Nothing typed yet — this is the normal "before submit" state, not an error
    if ($rawLink === '') {
        return ['valid' => false, 'reason' => 'blank'];
    }

    // filter_var with FILTER_VALIDATE_URL checks the string actually looks
    // like a real URL (has a scheme like http/https, a valid structure, etc.)
    // It returns the URL back if valid, or false if not.
    if (filter_var($rawLink, FILTER_VALIDATE_URL) === false) {
        return ['valid' => false, 'reason' => 'not a valid URL'];
    }

    return ['valid' => true, 'value' => $rawLink];
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
function buildResultHtml(?string $validLink, ?string $reason): string {

    // Nothing submitted yet
    if ($validLink === null && $reason === null) {
        return 'Your QR code will appear here';
    }

    // Failed validation — but skip showing an error for a simple blank field
    if ($validLink === null) {
        if ($reason === 'blank') {
            return 'Your QR code will appear here';
        }
        return '<p class="error">Please enter a valid link (must include http:// or https://).</p>';
    }

    $qrUrl = buildQrCodeUrl($validLink);

    // Escape everything printed — the link came from user input
    $safeLink = htmlspecialchars($validLink);
    $safeQrUrl = htmlspecialchars($qrUrl);

    return '<div class="qr-output">'
         . '<img src="' . $safeQrUrl . '" alt="QR code for ' . $safeLink . '" class="qr-image">'
         . '<a href="' . $safeQrUrl . '" download="qrcode.png" class="qr-download">Download QR code</a>'
         . '</div>';
}


// ---------- Main logic ----------

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$rawLink = $_POST['link'] ?? '';

$validLink = null;
$reason = null;

if ($isPost) {
    $result = validateLink($rawLink);

    if ($result['valid']) {
        $validLink = $result['value'];
    } else {
        $reason = $result['reason'];
    }
}

$resultHtml = buildResultHtml($validLink, $reason);

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
      <div class="field-top">Link</div>

      <div class="link-field">
        <input type="text" name="link" placeholder="https://www.example.com" value="<?= htmlspecialchars($rawLink) ?>">
      </div>

      <div class="divider"></div>

      <button type="submit">Generate QR code</button>

      <div class="result-shell"><?= $resultHtml ?></div>
    </div>
  </form>

</div>

</body>
</html>