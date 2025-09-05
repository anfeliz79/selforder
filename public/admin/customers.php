<?php
$title = "Clientes - SelfOrder";
$active = "customers";

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">👥 Gestión de Clientes</h3>
</div>

<table id="customersTable" class="table table-hover table-striped w-100"></table>

<!-- Modal Cliente -->
<div class="modal fade" id="customerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="customerModalTitle" class="modal-title">Nuevo Cliente</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="customerId">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input id="customerName" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Teléfono</label>
          <input id="customerPhone" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="saveCustomer()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts específicos -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="/admin/customers.js"></script>
<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
