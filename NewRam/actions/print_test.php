<!DOCTYPE html>
<html>
<head>
  <title>Receipt</title>
  <style>
    body {
      font-family: monospace;
      width: 300px;
      margin: auto;
    }
    .center {
      text-align: center;
    }
    .bold {
      font-weight: bold;
    }
    @media print {
      button {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="center bold">=== TEST PRINT ===</div>
  <div class="center">Printerrrrrr</div>
  <div class="center"><?= date("Y-m-d H:i:s") ?></div>
  <br>
  <div class="center">Thank you!</div>

  <button onclick="window.print()">Print Receipt</button>
</body>
</html>
