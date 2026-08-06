<?php 

function check_sign($number){
  if($number > 0){
    return "positive";
  } elseif($number < 0){
    return "negative";
  } else {
    return "zero";
  }
}

$numberInput = $_POST['number'] ?? '';
$resultHtml = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($numberInput === '' || !is_numeric($numberInput)) {
        $resultHtml = '<p class="error">Please enter a valid number.</p>';
    } else {
        $number = (float) $numberInput;
        $sign = check_sign($number);
        $safeNumber = htmlspecialchars($numberInput);
        $resultHtml = "<h2>Result:</h2><p>The number $safeNumber is $sign.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign of a Number</title>
  <style>
    .error { color: #D9534F; }
  </style>
</head>
<body>

  <h1>Check the Sign of a Number</h1>

  <form method="POST" action="">
    <label for="number">Enter a number:</label>
    <input type="number" step="any" name="number" id="number"
           placeholder="e.g. -7 or 12"
           value="<?= htmlspecialchars($numberInput) ?>">
    <button type="submit">Check</button>
  </form>

  <?= $resultHtml ?>

</body>
</html>