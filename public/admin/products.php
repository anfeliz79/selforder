<?php
require __DIR__ . "/../auth_middleware.php";
$title = "Productos - SelfOrder";
$active = "products";
ob_start();

?>


<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">🛒 Gestión de Productos</h3>
  <button class="btn btn-primary" onclick="showProductModal()">+ Nuevo Producto</button>
</div>

<table id="productsTable" class="table table-hover table-striped w-100">
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

<!-- 🔹 Modal Producto -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="productModalTitle" class="modal-title">Nuevo Producto</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="productId">

        <!-- Nombre -->
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input id="productName" class="form-control" required>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea id="productDesc" class="form-control"></textarea>
        </div>

        <!-- Imagen -->
        <div class="mb-3">
          <label class="form-label">Imagen</label>
          <input type="file" id="productImage" class="form-control" accept="image/*" onchange="previewProductImage(event)">
          <img id="productPreview" src="" class="img-fluid mt-2 d-none" style="max-height: 120px;">
        </div>

        <!-- Precio -->
        <div class="mb-3">
          <label class="form-label">Precio Base</label>
          <input type="number" id="productPrice" class="form-control" step="0.01" required>
        </div>

        <!-- Categoría -->
        <div class="mb-3">
<label>Categoría</label>
<select id="productCategory" class="form-select mb-2"></select>

        </div>

        <!-- Variantes -->
        <div class="mb-3">
          <label class="form-label">Variantes</label>
          <div id="variantsList"></div>
          <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addVariant()">+ Agregar Variante</button>
        </div>

        <!-- Adicionales -->
        <div class="mb-3">
          <label class="form-label">Adicionales</label>
          <div id="addonsList"></div>
          <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addAddon()">+ Agregar Adicional</button>
        </div>
<!-- Sucursales -->
<div class="mb-3">
  <label class="form-label">Sucursales</label>
  <div id="branchesList"></div>
</div>


      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="saveProduct()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables depende de jQuery -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap después (solo para estilos JS de modal y demás) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tu script al final -->
<script src="/js/products.js"></script>


<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
?>
