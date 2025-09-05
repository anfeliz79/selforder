<?php
require __DIR__ . "/../auth_middleware.php";
?>

<?php
// layout.php - plantilla base para admin
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title ?? "Admin - SelfOrder" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
    }
    .sidebar {
      width: 250px;
      background: #343a40;
      color: white;
      flex-shrink: 0;
    }
    .sidebar .nav-link {
      color: #adb5bd;
    }
    .sidebar .nav-link.active {
      background: #0d6efd;
      color: white;
    }
    .content {
      flex-grow: 1;
      background: #f8f9fa;
      padding: 20px;
    }
    .sidebar .logo {
      text-align: center;
      padding: 20px;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .sidebar .logo img {
      max-height: 50px;
    }
  </style>
</head>
<body>
  <!-- 🔹 Sidebar -->
  <div class="sidebar d-flex flex-column p-2">
    <div class="logo">
      <img src="/images/logo-white.png" alt="Logo">
    </div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="/admin/dashboard.php" class="nav-link <?= ($active=="dashboard"?"active":"") ?>">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="/admin/branches.php" class="nav-link <?= ($active=="branches"?"active":"") ?>">
          <i class="bi bi-building me-2"></i> Sucursales
        </a>
      </li>
      <li class="nav-item">
        <a href="/admin/products.php" class="nav-link <?= ($active=="products"?"active":"") ?>">
          <i class="bi bi-basket me-2"></i> Productos
        </a>
      </li>
          <li class="nav-item">
        <a href="/admin/categories.php" class="nav-link <?= ($active=="categories"?"active":"") ?>">
          <i class="bi bi-basket me-2"></i> Categorias
        </a>
      </li>
      <li class="nav-item">
        <a href="/admin/customers.php" class="nav-link <?= ($active=="customers"?"active":"") ?>">
          <i class="bi bi-people me-2"></i> Clientes
        </a>
      </li>
      <li class="nav-item">
        <a href="/admin/orders.php" class="nav-link <?= ($active=="orders"?"active":"") ?>">
          <i class="bi bi-receipt me-2"></i> Pedidos
        </a>
      </li>
    </ul>
  </div>

  <!-- 🔹 Contenido -->
  <div class="content">
    <?php if (isset($content)) echo $content; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</html>
