<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction'])) {
    $_SESSION['direction'] = $_POST['direction'];
}

$selected = $_SESSION['direction'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Auto Set Direction</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<div class="container">
  <h3>Select Direction</h3>
  <form method="POST">
    <div class="form-check">
      <input class="form-check-input" type="radio" name="direction" id="we" value="WE"
        <?= ($selected === 'WE') ? 'checked' : '' ?>>
      <label class="form-check-label" for="we">WE</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="direction" id="ew" value="EW"
        <?= ($selected === 'EW') ? 'checked' : '' ?>>
      <label class="form-check-label" for="ew">EW</label>
    </div>
  </form>

  <?php if ($selected): ?>
    <div class="alert alert-success mt-3">
      You selected: <strong><?= htmlspecialchars($selected) ?></strong>
    </div>
  <?php endif; ?>
</div>

<script>
  document.querySelectorAll('input[name="direction"]').forEach((radio) => {
    radio.addEventListener('change', () => {
      radio.closest('form').submit();
    });
  });
</script>

</body>
</html>
