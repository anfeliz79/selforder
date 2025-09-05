<?php
require __DIR__ . "/../auth_middleware.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Menú</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container py-4">
  <h3 class="mb-4">🍽️ Gestión de Menú</h3>

  <button class="btn btn-primary mb-3" onclick="showProductModal()">+ Nuevo Producto</button>

  <table id="productsTable" class="table table-striped table-hover w-100">
    <thead>
      <tr>
        <th>Imagen</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Precio Base</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="productModalTitle" class="modal-title">Nuevo Producto</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="productId">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" id="productName" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea id="productDesc" class="form-control"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Imagen</label>
          <input type="file" id="productImage" class="form-control" onchange="previewImage(event)">
          <img id="previewImg" class="img-fluid mt-2 d-none" style="max-height:150px;">
        </div>

        <div class="mb-3">
          <label class="form-label">Precio Base</label>
          <input type="number" id="productPrice" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Categoría</label>
          <input type="text" id="productCategory" class="form-control">
        </div>

        <hr>
        <h6>Variantes</h6>
        <table class="table table-sm" id="variantsTable">
          <thead><tr><th>Nombre</th><th>Precio</th><th></th></tr></thead>
          <tbody></tbody>
        </table>
        <button class="btn btn-outline-primary btn-sm" onclick="addVariantRow()">+ Variante</button>

        <hr>
        <h6>Extras / Addons</h6>
        <table class="table table-sm" id="addonsTable">
          <thead><tr><th>Nombre</th><th>Precio</th><th></th></tr></thead>
          <tbody></tbody>
        </table>
        <button class="btn btn-outline-primary btn-sm" onclick="addAddonRow()">+ Extra</button>
    <hr>
<h6>Disponibilidad por sucursal</h6>
<div id="branchesSection" class="row g-2"></div>
     
    </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="saveProduct()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="menu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
