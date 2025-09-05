<?php
require __DIR__ . "/../auth_middleware.php";
?>

<?php
$title = "Dashboard";
$active = "dashboard";
ob_start();
?>

<h3 class="mb-4">📊 Dashboard</h3>

<!-- Filtros -->
<div class="mb-3">
  <select id="dateFilter" class="form-select w-auto d-inline-block">
    <option value="today">Hoy</option>
    <option value="yesterday">Ayer</option>
    <option value="week">Esta Semana</option>
    <option value="month">Este Mes</option>
    <option value="last_month">Mes Anterior</option>
    <option value="year">Este Año</option>
    <option value="custom">Rango de Fechas</option>
  </select>
  <input type="date" id="startDate" class="form-control d-inline-block w-auto d-none">
  <input type="date" id="endDate" class="form-control d-inline-block w-auto d-none">
  <button class="btn btn-primary" onclick="loadDashboard()">Filtrar</button>
</div>

<!-- Métricas -->
<div class="row g-3">
  <div class="col-md-3"><div class="card text-center shadow-sm"><div class="card-body"><h6>Sucursales</h6><h3 id="metricBranches">0</h3></div></div></div>
  <div class="col-md-3"><div class="card text-center shadow-sm"><div class="card-body"><h6>Productos</h6><h3 id="metricProducts">0</h3></div></div></div>
  <div class="col-md-3"><div class="card text-center shadow-sm"><div class="card-body"><h6>Clientes</h6><h3 id="metricCustomers">0</h3></div></div></div>
  <div class="col-md-3"><div class="card text-center shadow-sm"><div class="card-body"><h6>Pedidos</h6><h3 id="metricOrders">0</h3></div></div></div>

<div class="col-md-3">
  <div class="card text-center shadow-sm">
    <div class="card-body">
      <h6>Ingresos</h6>
      <h3 id="metricIncome">$0</h3>
    </div>
  </div>
</div>

</div>

<!-- Gráfica -->
<div class="card mt-4 shadow-sm">
  <div class="card-body">
    <h6 class="mb-3">Ventas por Día</h6>
    <canvas id="salesChart" height="100"></canvas>
  </div>
</div>

<!-- Ranking -->
<div class="row mt-4">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>🥇 Top Productos</h6>
        <ul id="topProducts" class="list-group"></ul>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>👥 Clientes Frecuentes</h6>
        <ul id="topCustomers" class="list-group"></ul>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/dashboard.js"></script>

<?php
$content = ob_get_clean();
require __DIR__ . "/layout.php";
