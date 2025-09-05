<?php
require __DIR__ . "/../auth_middleware.php";
$title = "Órdenes Globales - Admin";
$active = "orders";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">📦 Órdenes Globales</h3>
</div>

<!-- 🔹 Filtros -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Rango</label>
        <select id="filterType" class="form-select">
          <option value="today">Hoy</option>
          <option value="week">Semana</option>
          <option value="month">Mes</option>
          <option value="custom">Personalizado</option>
        </select>
      </div>
      <div class="col-md-2 d-none" id="dateStartCol">
        <label class="form-label">Desde</label>
        <input type="date" id="filterStart" class="form-control">
      </div>
      <div class="col-md-2 d-none" id="dateEndCol">
        <label class="form-label">Hasta</label>
        <input type="date" id="filterEnd" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Sucursal</label>
        <select id="filterBranch" class="form-select">
          <option value="">Todas las sucursales</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select id="filterStatus" class="form-select">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="preparacion">En preparación</option>
          <option value="listo">Listo</option>
          <option value="entregado">Entregado</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Totales -->
<div class="row mb-4" id="ordersSummary">
  <!-- Se llena dinámicamente -->
</div>

<!-- 🔹 Tabla -->
<div class="card shadow-sm">
  <div class="card-body">
    <table id="ordersTable" class="table align-middle table-hover w-100">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Sucursal</th>
          <th>Cliente</th>
          <th>Estado</th>
          <th>Total</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- 🔹 Modal Detalle -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Detalle de Orden</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="orderTimeline" class="mb-4"></div>
        <div id="orderDetails"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/orders.js"></script>

<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
?>
