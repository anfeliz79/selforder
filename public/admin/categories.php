<?php
require __DIR__ . "/../auth_middleware.php";

$title = "Categorías - SelfOrder";
$active = "categories";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">📂 Gestión de Categorías</h3>
  <button class="btn btn-primary" onclick="showCategoryModal()">+ Nueva Categoría</button>
</div>

<table id="categoriesTable" class="table table-striped table-hover w-100">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<!-- Modal Categoría -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="categoryModalTitle" class="modal-title">Nueva Categoría</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="categoryId">
        <label class="form-label">Nombre</label>
        <input type="text" id="categoryName" class="form-control">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="saveCategory()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts específicos -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="/js/categories.js"></script>

<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
