
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign of a Number</title>
</head>
<body>

  <h1>Check the Sign of a Number</h1>

  <form method="POST" action="">
    <label for="number">Enter a number:</label>
    <input type="number" step="any" name="number" id="number" placeholder="e.g. -7 or 12">
    <button type="submit">Check</button>
  </form>

  <?php 

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      var_dump($_POST);
      $number = $_POST['number'];
      echo "<h2>Result:</h2>";
      if($number > 0){
        echo "<p>The number $number is positive.</p>";
      } elseif($number < 0){
        echo "<p>The number $number is negative.</p>";
      } else {
        echo "<p>The number is zero.</p>";
      }
    }
  
  ?>

</body>
</html>