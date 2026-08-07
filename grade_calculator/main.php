<?php

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$debugHtml = '';

if ($isPost) {
    // Temporary: just confirming the array arrives correctly.
    // This gets replaced with real validation + calculation next.
    $debugHtml = '<pre>' . htmlspecialchars(print_r($_POST, true)) . '</pre>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Grade Calculator</title>
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
    --amber:#F2A93B;
    --teal:#2FB6A3;
    --teal-soft:#E4F6F3;
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
    -webkit-font-smoothing:antialiased;
    min-height:100vh;
    display:flex;
    align-items:flex-start;
    justify-content:center;
    padding:64px 20px 80px;
  }
  h1{font-family:'Space Grotesk', sans-serif; letter-spacing:-0.02em;}
  code{
    font-family:'JetBrains Mono', monospace; font-size:0.85em;
    background:var(--ink); color:#D7D9F5;
    padding:2px 7px; border-radius:5px;
  }

  .page{width:100%; max-width:600px;}

  /* ---------- brand / heading ---------- */
  .brand-row{display:flex; align-items:center; gap:12px; margin-bottom:22px;}
  .brand-mark{
    width:40px; height:40px; border-radius:12px;
    background:linear-gradient(155deg, var(--primary), var(--primary-dark));
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1.05rem;
    box-shadow:0 8px 18px -8px rgba(90,79,207,0.55);
  }
  .brand-text .eyebrow{
    font-family:'JetBrains Mono', monospace; font-size:0.72rem;
    letter-spacing:0.12em; text-transform:uppercase; color:var(--ink-faint);
  }
  .brand-text h1{font-size:1.5rem; margin-top:2px;}

  .page > p.lead{
    color:var(--ink-soft); font-size:0.98rem; max-width:480px; margin-bottom:32px;
  }

  /* ---------- FORM CARD ---------- */
  .form-card{
    background:var(--white);
    border:1px solid var(--line);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  .card-strip{
    display:flex; align-items:center; justify-content:space-between;
    padding:13px 22px; background:var(--ink);
    font-family:'JetBrains Mono', monospace; font-size:0.76rem; color:#C7CAE8;
  }
  .card-strip .dots{display:flex; gap:6px;}
  .card-strip .dots span{width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.28);}
  .card-strip .dots span:nth-child(1){background:#F2A93B;}
  .card-strip .dots span:nth-child(2){background:#2FB6A3;}
  .card-strip .dots span:nth-child(3){background:#8F86EE;}

  .card-body{padding:30px 28px 28px;}

  .field-top{display:flex; align-items:baseline; justify-content:space-between; margin-bottom:14px;}
  .field-top .label{
    font-family:'JetBrains Mono', monospace; font-size:0.74rem; letter-spacing:0.08em;
    text-transform:uppercase; color:var(--ink-faint);
  }
  .field-top .count{
    font-family:'JetBrains Mono', monospace; font-size:0.76rem; color:var(--primary);
    background:var(--primary-soft); padding:3px 9px; border-radius:20px;
  }

  .score-fields{display:flex; flex-direction:column; gap:10px;}
  .score-field{
    display:flex; align-items:center; gap:12px;
    background:var(--paper); border:1px solid var(--line); border-radius:12px;
    padding:5px 6px 5px 16px;
    transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .score-field:focus-within{
    border-color:var(--primary); background:var(--white);
    box-shadow:0 0 0 3px var(--primary-soft);
  }
  .score-field .index{
    font-family:'JetBrains Mono', monospace; font-size:0.78rem; font-weight:600;
    color:var(--primary); width:20px; flex-shrink:0;
  }
  .score-field input{
    flex:1; border:none; background:transparent; outline:none;
    font-family:'JetBrains Mono', monospace; font-size:0.96rem; color:var(--ink);
    padding:11px 0;
    min-width:0;
  }
  .score-field input::placeholder{color:var(--ink-faint);}
  .score-field .unit{
    font-family:'JetBrains Mono', monospace; font-size:0.72rem; color:var(--ink-soft);
    background:var(--white); border:1px solid var(--line); border-radius:8px;
    padding:5px 9px; flex-shrink:0;
  }

  .field-hint{
    display:flex; align-items:flex-start; gap:9px;
    margin-top:16px; font-size:0.85rem; color:var(--ink-soft);
  }
  .field-hint .dot{
    width:16px; height:16px; border-radius:50%; background:var(--teal-soft); color:var(--teal);
    font-family:'JetBrains Mono',monospace; font-size:0.68rem; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;
  }

  .divider{height:1px; background:var(--line); margin:24px 0;}

  .actions{display:flex; align-items:center; justify-content:space-between; gap:14px;}
  button[type="submit"]{
    font-family:'Inter', sans-serif; font-weight:600; font-size:0.95rem;
    background:var(--primary); color:#fff; border:none; border-radius:11px;
    padding:14px 24px; cursor:pointer;
    box-shadow:0 10px 22px -10px rgba(90,79,207,0.65);
    transition:filter .15s ease, transform .1s ease, box-shadow .15s ease;
  }
  button[type="submit"]:hover{filter:brightness(1.06); box-shadow:0 12px 26px -10px rgba(90,79,207,0.75);}
  button[type="submit"]:active{transform:scale(.98);}
  .clear-link{
    font-family:'JetBrains Mono', monospace; font-size:0.8rem; color:var(--ink-faint);
    background:none; border:none; cursor:pointer;
  }
  .clear-link:hover{color:var(--primary);}

  /* result placeholder */
  .result-shell{
    margin-top:22px;
    border:1px dashed var(--line); border-radius:12px;
    padding:16px 18px;
    display:flex; align-items:center; gap:10px;
    font-family:'JetBrains Mono', monospace; font-size:0.82rem; color:var(--ink-faint);
    background:repeating-linear-gradient(135deg, rgba(20,22,43,0.02) 0 10px, transparent 10px 20px);
  }
  .result-shell::before{content:'○'; font-size:9px;}

  @media (max-width:520px){
    body{padding:44px 16px 60px;}
    .card-body{padding:24px 18px 24px;}
  }
</style>
</head>
<body>

<div class="page">

  <div class="brand-row">
    <div class="brand-mark">GC</div>
    <div class="brand-text">
      <div class="eyebrow">grade &amp; average</div>
      <h1>Grade Calculator</h1>
    </div>
  </div>

  <p class="lead">Enter a set of scores and get back the average and the letter grade it converts to.</p>

  <form class="form-card" method="POST" action="">
    <div class="card-strip">
      <span>grad_calculator.php</span>
      <span class="dots"><span></span><span></span><span></span></span>
    </div>

    <div class="card-body">
      <div class="field-top">
        <span class="label">Scores</span>
        <span class="count">up to 5</span>
      </div>

      <div class="score-fields">
        <div class="score-field">
          <span class="index">01</span>
          <input type="number" name="scores[]" placeholder="e.g. 78" min="0" max="100" step="any">
          <span class="unit">/ 100</span>
        </div>
        <div class="score-field">
          <span class="index">02</span>
          <input type="number" name="scores[]" placeholder="e.g. 85" min="0" max="100" step="any">
          <span class="unit">/ 100</span>
        </div>
        <div class="score-field">
          <span class="index">03</span>
          <input type="number" name="scores[]" placeholder="e.g. 92" min="0" max="100" step="any">
          <span class="unit">/ 100</span>
        </div>
        <div class="score-field">
          <span class="index">04</span>
          <input type="number" name="scores[]" placeholder="optional" min="0" max="100" step="any">
          <span class="unit">/ 100</span>
        </div>
        <div class="score-field">
          <span class="index">05</span>
          <input type="number" name="scores[]" placeholder="optional" min="0" max="100" step="any">
          <span class="unit">/ 100</span>
        </div>
      </div>

      <div class="field-hint">
        <span class="dot">i</span>
        <span>Every field shares <code>name="scores[]"</code>, so PHP receives them as a single array. Leave any unused field blank.</span>
      </div>

      <div class="divider"></div>

      <div class="actions">
        <button type="submit">Calculate average</button>
        <button type="button" class="clear-link" id="clearBtn">Clear fields</button>
      </div>

      <div class="result-shell">
        <?= $debugHtml !== '' ? $debugHtml : 'Result will render here' ?>
      </div>
    </div>
  </form>

</div>

<script>
  document.getElementById('clearBtn').addEventListener('click', () => {
    document.querySelectorAll('.score-field input').forEach(i => i.value = '');
    document.querySelector('.score-field input').focus();
  });
</script>

</body>
</html>