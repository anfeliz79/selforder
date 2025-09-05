<?php
session_start();
$error = $_SESSION['error'] ?? null;
$oldBranch = $_SESSION['old_branch'] ?? "";
unset($_SESSION['error'], $_SESSION['old_branch']);

require __DIR__ . "/../../vendor/autoload.php";
use App\Models\Branch;
$branches = (new Branch())->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Meseros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .card { max-width: 400px; width: 100%; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
<div class="card shadow p-4">
  <h4 class="mb-3 text-center">🔑 Acceso Meseros</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center py-2">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/waiter/login">
    <div class="mb-3">
      <label class="form-label">Sucursal</label>
      <select name="branch_id" class="form-select" required>
        <option value="">Seleccione...</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= $b['id'] ?>" 
            <?= $oldBranch == $b['id'] ? "selected" : "" ?>>
            <?= htmlspecialchars($b['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Clave</label>
      <input type="password" name="password" class="form-control" required autocomplete="off">
    </div>
    <button class="btn btn-primary w-100" type="submit">
      Ingresar
    </button>
  </form>
</div>
</body>
</html>
