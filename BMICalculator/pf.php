<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PHP Practice Bench — 3 Reps</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --paper:#F8F6F1;
    --ink:#1C1B19;
    --ink-soft:#5B584F;
    --line:#DEDACD;
    --indigo:#4338CA;
    --indigo-soft:#EDEBFB;
    --amber:#C98A00;
    --amber-soft:#FBF0DA;
    --teal:#0F8C7F;
    --teal-soft:#E1F3F0;
    --danger:#B4463B;
    --card-bg:#FFFFFF;
    --radius:14px;
  }

  *{box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    margin:0;
    background:var(--paper);
    color:var(--ink);
    font-family:'Inter',sans-serif;
    -webkit-font-smoothing:antialiased;
  }

  /* ---------- header ---------- */
  header{
    padding:64px 24px 40px;
    max-width:1080px;
    margin:0 auto;
    border-bottom:1px solid var(--line);
  }
  .eyebrow{
    font-family:'JetBrains Mono',monospace;
    font-size:12px;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:var(--indigo);
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:14px;
  }
  .eyebrow::before{
    content:'';
    width:7px;height:7px;border-radius:50%;
    background:var(--indigo);
    display:inline-block;
  }
  h1{
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(32px,5vw,48px);
    line-height:1.08;
    margin:0 0 14px;
    letter-spacing:-.01em;
  }
  header p{
    max-width:560px;
    color:var(--ink-soft);
    font-size:16px;
    line-height:1.6;
    margin:0;
  }

  /* ---------- grid ---------- */
  main{
    max-width:1080px;
    margin:0 auto;
    padding:48px 24px 100px;
    display:flex;
    flex-direction:column;
    gap:28px;
    align-items:start;
  }

  /* ---------- card ---------- */
  .card{
    background:var(--card-bg);
    border:1px solid var(--line);
    border-radius:var(--radius);
    overflow:hidden;
    display:flex;
    flex-direction:column;
    transition:box-shadow .25s ease, transform .25s ease;
    width: 550px;
  }
  .card:hover{
    box-shadow:0 12px 28px -14px rgba(28,27,25,.18);
    transform:translateY(-2px);
  }

  /* terminal-tab style header strip, ties to the "coding practice" subject */
  .card-strip{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 18px;
    font-family:'JetBrains Mono',monospace;
    font-size:12px;
    color:#fff;
  }
  .card-strip .dots{display:flex;gap:6px;}
  .card-strip .dots span{
    width:8px;height:8px;border-radius:50%;
    background:rgba(255,255,255,.55);
  }
  .card-strip .file{
    opacity:.9;
    letter-spacing:.02em;
  }
  .card--sign .card-strip{background:var(--indigo);}
  .card--sum  .card-strip{background:var(--amber);}
  .card--evenodd .card-strip{background:var(--teal);}

  .card-body{padding:24px 22px 26px;display:flex;flex-direction:column;gap:16px;flex:1;}

  .card-tag{
    font-family:'JetBrains Mono',monospace;
    font-size:11px;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--ink-soft);
  }
  .card h2{
    font-family:'Space Grotesk',sans-serif;
    font-size:21px;
    margin:2px 0 0;
    letter-spacing:-.01em;
  }
  .card .desc{
    font-size:14px;
    color:var(--ink-soft);
    line-height:1.55;
    margin:0;
  }

  form{display:flex;flex-direction:column;gap:12px;margin-top:4px;}
  .field{display:flex;flex-direction:column;gap:6px;}
  .field label{
    font-size:12.5px;
    font-weight:600;
    color:var(--ink);
  }
  .field .hint{
    font-size:12px;
    color:var(--ink-soft);
    font-family:'JetBrains Mono',monospace;
  }
  input[type="text"],
  input[type="number"]{
    font-family:'JetBrains Mono',monospace;
    font-size:14px;
    padding:11px 12px;
    border:1px solid var(--line);
    border-radius:9px;
    background:var(--paper);
    color:var(--ink);
    outline:none;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  input::placeholder{color:#B4B0A2;}
  input:focus-visible{
    border-color:var(--indigo);
    box-shadow:0 0 0 3px var(--indigo-soft);
  }
  .card--sum input:focus-visible{border-color:var(--amber);box-shadow:0 0 0 3px var(--amber-soft);}
  .card--evenodd input:focus-visible{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-soft);}

  .row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}

  button[type="submit"]{
    margin-top:4px;
    font-family:'Inter',sans-serif;
    font-weight:600;
    font-size:14px;
    padding:12px 16px;
    border:none;
    border-radius:9px;
    cursor:pointer;
    color:#fff;
    transition:filter .15s ease, transform .1s ease;
  }
  button[type="submit"]:hover{filter:brightness(1.08);}
  button[type="submit"]:active{transform:scale(.98);}
  button[disabled]{opacity:.45;cursor:not-allowed;filter:none !important;}

  .card--sign button{background:var(--indigo);}
  .card--sum button{background:var(--amber);}
  .card--evenodd button{background:var(--teal);}

  /* result placeholder — where $resultHtml will render server-side */
  .result{
    margin-top:2px;
    border:1px dashed var(--line);
    border-radius:9px;
    padding:14px 14px;
    font-family:'JetBrains Mono',monospace;
    font-size:12.5px;
    color:#A6A297;
    background:repeating-linear-gradient(135deg, rgba(0,0,0,0.015) 0 10px, transparent 10px 20px);
    display:flex;
    align-items:center;
    gap:8px;
  }
  .result::before{
    content:'○';
    font-size:10px;
  }
  .result.is-filled{
    border-style:solid;
    color:var(--ink);
    background:none;
  }
  .card--sign .result.is-filled{border-color:var(--indigo);background:var(--indigo-soft);}
  .card--sum .result.is-filled{border-color:var(--amber);background:var(--amber-soft);}
  .card--evenodd .result.is-filled{border-color:var(--teal);background:var(--teal-soft);}

  .error{color:var(--danger);font-weight:600;}

  footer{
    text-align:center;
    padding:0 24px 60px;
    font-family:'JetBrains Mono',monospace;
    font-size:12px;
    color:var(--ink-soft);
  }

  @media (max-width:520px){
    header{padding:48px 20px 32px;}
    main{padding:36px 20px 80px;}
    .row-2{grid-template-columns:1fr;}
  }

  @media (prefers-reduced-motion: reduce){
    .card, button{transition:none;}
  }
</style>
</head>
<body>

<header>
  <div class="eyebrow">Practice Bench · Reps 02–04</div>
  <h1>Three small forms.<br>Three PHP reps.</h1>
  <p>Front end is done — each card posts to itself and has a spot for the result. Wire up the <code style="font-family:'JetBrains Mono',monospace;">$_POST</code> handling, validation, and math, and echo into the result panel.</p>
</header>

<main>

  <!-- Project: Sign of a Number -->
  <section class="card card--sign">
    <div class="card-strip">
      <span class="file">sign-checker.php</span>
      <span class="dots"><span></span><span></span><span></span></span>
    </div>
    <div class="card-body">
      <span class="card-tag">Rep 02 · comparisons</span>
      <h2>Sign of a Number</h2>
      <p class="desc">Enter a number. Decide whether it's positive, negative, or zero.</p>

      <!-- PHP logic will go here -->
      <?php 
       
      function check_sign($number){
        $sign = ["positive", "negative", "zero"];
        if($number > 0){return $sign[0];}
        if($number < 0){return $sign[1];}
        return $sign[2];
      }

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $numberInput = $_POST['number'] ?? '';
          if ($numberInput === '' || !is_numeric($numberInput)) {
              echo '<div class="result is-filled error">Please enter a valid number.</div>';
          } else {
              $number = (float) $numberInput;
              $sign = check_sign($number);
              $safeNumber = htmlspecialchars($numberInput);
              echo "<div class='result is-filled'>The number $safeNumber is $sign.</div>";
          }
      }
       
      ?>

      <form method="POST" action="">
        <div class="field">
          <label for="number">Number</label>
          <input type="number" step="any" name="number" id="number" placeholder="e.g. -7 or 12">
          <span class="hint">accepts negatives &amp; decimals</span>
        </div>
        <button type="submit">Check sign</button>
      </form>

      <div class="result">Result will appear here once the form is submitted</div>
    </div>
  </section>

  <!-- Project: Sum Calculator -->
  <section class="card card--sum">
    <div class="card-strip">
      <span class="file">sum-calculator.php</span>
      <span class="dots"><span></span><span></span><span></span></span>
    </div>
    <div class="card-body">
      <span class="card-tag">Rep 03 · multi-input validation</span>
      <h2>Sum Calculator</h2>
      <p class="desc">Enter two numbers. Add them together — validate both before doing the math.</p>

      <form method="POST" action="">
        <div class="row-2">
          <div class="field">
            <label for="num1">First number</label>
            <input type="number" step="any" name="num1" id="num1" placeholder="0">
          </div>
          <div class="field">
            <label for="num2">Second number</label>
            <input type="number" step="any" name="num2" id="num2" placeholder="0">
          </div>
        </div>
        <button type="submit">Calculate sum</button>
      </form>

      <div class="result">Result will appear here once the form is submitted</div>
    </div>
  </section>

  <!-- Project: Even or Odd Checker -->
  <section class="card card--evenodd">
    <div class="card-strip">
      <span class="file">even-odd.php</span>
      <span class="dots"><span></span><span></span><span></span></span>
    </div>
    <div class="card-body">
      <span class="card-tag">Rep 04 · modulo</span>
      <h2>Even or Odd Checker</h2>
      <p class="desc">Enter a number. Use <code style="font-family:inherit;">%</code> to decide even or odd — think about how to handle decimals.</p>

      <form method="POST" action="">
        <div class="field">
          <label for="evenodd-number">Number</label>
          <input type="number" step="any" name="number" id="evenodd-number" placeholder="e.g. 4 or 17">
          <span class="hint">whole numbers only? your call</span>
        </div>
        <button type="submit">Check even / odd</button>
      </form>

      <div class="result">Result will appear here once the form is submitted</div>
    </div>
  </section>

</main>

<footer>front-end only — PHP logic goes in the &lt;?php ?&gt; block at the top of each file</footer>

</body>
</html>