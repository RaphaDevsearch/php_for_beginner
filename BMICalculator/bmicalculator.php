<?php
declare(strict_types=1);

/**
 * Calculate BMI.
 * $weightKg : weight in kilograms
 * $heightCm : height in centimeters
 * Returns null if input is invalid (avoids division by zero).
 */
function calculateBMI(float $weightKg, float $heightCm): ?float
{
    if ($heightCm <= 0 || $weightKg <= 0) {
        return null;
    }
    $heightM = $heightCm / 100;
    return round($weightKg / ($heightM ** 2), 1);
}

/**
 * Map a BMI value to a category using a data-driven lookup
 * (thresholds as keys, in ascending order) instead of an if/elif chain.
 */
function bmiCategory(float $bmi): array
{
    $categories = [
        18.5 => ['label' => 'Underweight', 'tone' => 'under'],
        24.9 => ['label' => 'Normal weight', 'tone' => 'normal'],
        29.9 => ['label' => 'Overweight',    'tone' => 'over'],
    ];

    foreach ($categories as $threshold => $info) {
        if ($bmi < $threshold) {
            return $info;
        }
    }

    return ['label' => 'Obesity', 'tone' => 'obese'];
}

/* ---------- Handle form submission ---------- */

$errors  = [];
$bmi     = null;
$category = null;

$weightInput = $_POST['weight'] ?? '';
$heightInput = $_POST['height'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($weightInput === '' || !is_numeric($weightInput)) {
        $errors[] = 'Enter a valid weight in kilograms.';
    }
    if ($heightInput === '' || !is_numeric($heightInput)) {
        $errors[] = 'Enter a valid height in centimeters.';
    }

    if (empty($errors)) {
        $bmi = calculateBMI((float) $weightInput, (float) $heightInput);
        if ($bmi === null) {
            $errors[] = 'Weight and height must be greater than zero.';
        } else {
            $category = bmiCategory($bmi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BMI Calculator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --paper:#F5F6FB;
    --paper-dim:#ECEEF7;
    --ink:#14162B;
    --ink-soft:#54577A;
    --primary:#5A4FCF;
    --primary-dark:#3D34A8;
    --amber:#F2A93B;
    --teal:#2FB6A3;
    --red:#D9534F;
    --line:#DEE1F0;
    --white:#FFFFFF;
    --radius:14px;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{
    font-family:'Inter', sans-serif;
    background:var(--paper);
    color:var(--ink);
    min-height:100vh;
    display:flex; align-items:center; justify-content:center;
    padding:32px 16px;
  }
  h1,h2{font-family:'Space Grotesk', sans-serif; letter-spacing:-0.02em;}

  .card{
    width:100%; max-width:920px;
    background:var(--white);
    border:1px solid var(--line);
    border-radius:20px;
    display:grid;
    grid-template-columns:1fr 1fr;
    overflow:hidden;
    box-shadow:0 24px 50px -30px rgba(20,22,43,0.25);
  }

  /* LEFT: form panel */
  .panel-form{padding:44px 40px; background:var(--white);}
  .eyebrow{
    font-family:'JetBrains Mono', monospace; font-size:0.74rem;
    letter-spacing:0.1em; text-transform:uppercase; color:var(--primary);
    display:flex; align-items:center; gap:8px; margin-bottom:10px;
  }
  .eyebrow::before{content:''; width:18px; height:2px; background:var(--amber);}
  .panel-form h1{font-size:1.7rem; margin-bottom:8px;}
  .panel-form > p{color:var(--ink-soft); font-size:0.92rem; margin-bottom:28px;}

  .field{margin-bottom:20px;}
  .field label{
    display:block; font-size:0.82rem; font-weight:600; color:var(--ink);
    margin-bottom:8px;
  }
  .input-wrap{position:relative;}
  .input-wrap input{
    width:100%; padding:13px 50px 13px 14px;
    border:1.5px solid var(--line); border-radius:10px;
    font-size:1rem; font-family:'Inter', sans-serif; color:var(--ink);
    background:var(--paper-dim);
    transition:border-color .15s ease, background .15s ease;
  }
  .input-wrap input:focus{
    outline:none; border-color:var(--primary); background:var(--white);
  }
  .input-wrap .unit{
    position:absolute; right:14px; top:50%; transform:translateY(-50%);
    font-family:'JetBrains Mono', monospace; font-size:0.8rem; color:var(--ink-soft);
    pointer-events:none;
  }

  .btn{
    width:100%; padding:14px; border:none; border-radius:10px;
    background:var(--primary); color:var(--white);
    font-weight:600; font-size:0.98rem; cursor:pointer;
    transition:background .15s ease, transform .1s ease;
    margin-top:6px;
  }
  .btn:hover{background:var(--primary-dark);}
  .btn:active{transform:scale(0.99);}

  .error-box{
    background:#FDEDEC; border:1px solid #F3C6C4; color:var(--red);
    border-radius:10px; padding:12px 14px; margin-bottom:20px;
    font-size:0.86rem;
  }
  .error-box ul{margin-left:18px; margin-top:4px;}

  /* RIGHT: result panel */
  .panel-result{
    padding:44px 40px;
    background:var(--ink);
    color:#E4E5F5;
    display:flex; flex-direction:column; justify-content:center;
    position:relative;
    overflow:hidden;
  }
  .panel-result::before{
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; border-radius:50%;
    background:radial-gradient(circle, rgba(90,79,207,0.35), transparent 70%);
  }
  .panel-result .placeholder{position:relative; z-index:1;}
  .placeholder .icon{
    width:52px; height:52px; border-radius:50%;
    background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
    display:flex; align-items:center; justify-content:center;
    font-family:'Space Grotesk', sans-serif; font-weight:600; font-size:1.1rem;
    color:var(--teal); margin-bottom:18px;
  }
  .placeholder h2{color:var(--white); font-size:1.15rem; margin-bottom:8px;}
  .placeholder p{color:#A7AAD1; font-size:0.88rem; max-width:280px;}

  .result{position:relative; z-index:1;}
  .result .stage-label{
    font-family:'JetBrains Mono', monospace; font-size:0.74rem;
    text-transform:uppercase; letter-spacing:0.1em; color:var(--teal);
    margin-bottom:6px; display:block;
  }
  .result .bmi-number{
    font-family:'Space Grotesk', sans-serif; font-weight:700;
    font-size:4rem; line-height:1; color:var(--white); margin-bottom:14px;
  }
  .badge{
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 16px; border-radius:30px;
    font-weight:600; font-size:0.92rem;
    margin-bottom:26px;
  }
  .badge .dot{width:8px; height:8px; border-radius:50%;}
  .badge.under{background:rgba(90,79,207,0.18); color:#B4ACFF;}
  .badge.under .dot{background:#B4ACFF;}
  .badge.normal{background:rgba(47,182,163,0.18); color:#6FE3D0;}
  .badge.normal .dot{background:#6FE3D0;}
  .badge.over{background:rgba(242,169,59,0.18); color:#F6C877;}
  .badge.over .dot{background:#F6C877;}
  .badge.obese{background:rgba(217,83,79,0.18); color:#F19490;}
  .badge.obese .dot{background:#F19490;}

  .scale{
    display:flex; height:8px; border-radius:6px; overflow:hidden; margin-bottom:10px;
  }
  .scale div{flex:1;}
  .scale .s-under{background:#5A4FCF;}
  .scale .s-normal{background:#2FB6A3;}
  .scale .s-over{background:#F2A93B;}
  .scale .s-obese{background:#D9534F;}
  .scale-labels{
    display:flex; justify-content:space-between;
    font-family:'JetBrains Mono', monospace; font-size:0.68rem; color:#8083AD;
  }

  @media (max-width:760px){
    .card{grid-template-columns:1fr;}
    .panel-form, .panel-result{padding:34px 26px;}
    .bmi-number{font-size:3rem;}
  }
</style>
</head>
<body>

<div class="card">

  <div class="panel-form">
    <div class="eyebrow">bmi calculator</div>
    <h1>Check your BMI</h1>
    <p>Enter your weight and height to get an instant Body Mass Index reading.</p>

    <?php if (!empty($errors)): ?>
      <div class="error-box">
        <strong>Please fix the following:</strong>
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field">
        <label for="weight">Weight</label>
        <div class="input-wrap">
          <input
            type="number" step="0.1" min="0" name="weight" id="weight"
            placeholder="e.g. 65"
            value="<?= htmlspecialchars($weightInput) ?>"
            required
          >
          <span class="unit">kg</span>
        </div>
      </div>

      <div class="field">
        <label for="height">Height</label>
        <div class="input-wrap">
          <input
            type="number" step="0.1" min="0" name="height" id="height"
            placeholder="e.g. 170"
            value="<?= htmlspecialchars($heightInput) ?>"
            required
          >
          <span class="unit">cm</span>
        </div>
      </div>

      <button type="submit" class="btn">Calculate BMI</button>
    </form>
  </div>

  <div class="panel-result">
    <?php if ($bmi !== null && $category !== null): ?>
      <div class="result">
        <span class="stage-label">Your result</span>
        <div class="bmi-number"><?= htmlspecialchars((string) $bmi) ?></div>
        <div class="badge <?= htmlspecialchars($category['tone']) ?>">
          <span class="dot"></span><?= htmlspecialchars($category['label']) ?>
        </div>
        <div class="scale">
          <div class="s-under"></div><div class="s-normal"></div><div class="s-over"></div><div class="s-obese"></div>
        </div>
        <div class="scale-labels">
          <span>&lt;18.5</span><span>18.5–24.9</span><span>25–29.9</span><span>30+</span>
        </div>
      </div>
    <?php else: ?>
      <div class="placeholder">
        <div class="icon">i</div>
        <h2>No result yet</h2>
        <p>Fill in your weight and height on the left and your BMI will appear right here.</p>
      </div>
    <?php endif; ?>
  </div>

</div>

</body>
</html>