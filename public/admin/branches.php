<?php
$title = "Sucursales - SelfOrder";
$active = "branches";

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">🏢 Gestión de Sucursales</h3>
  <button class="btn btn-primary" onclick="showBranchModal()">+ Nueva Sucursal</button>
</div>

<table id="branchesTable" class="table table-striped table-hover w-100">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Dirección</th>
      <th>Teléfono</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<!-- Modal Sucursal -->
<div class="modal fade" id="branchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="branchModalTitle" class="modal-title">Nueva Sucursal</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="branchId">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" id="branchName" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Dirección</label>
          <input type="text" id="branchAddress" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Teléfono</label>
          <input type="text" id="branchPhone" class="form-control">
        </div>
      </div>
<div class="mb-3">
  <label for="branchAccessKey" class="form-label">Clave de Acceso</label>
  <input type="text" id="branchAccessKey" class="form-control" placeholder="Clave para login de meseros">
</div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="saveBranch()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Mesas -->
<div class="modal fade" id="tablesModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="tablesModalTitle" class="modal-title">Mesas</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="tablesBranchId">

        <button class="btn btn-success mb-3" onclick="addTableRow()">+ Nueva Mesa</button>
        <table class="table table-striped" id="tablesTable">
          <thead><tr><th># Mesa</th><th>QR</th><th>Acciones</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Scripts específicos -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="/js/branches.js"></script>
<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
