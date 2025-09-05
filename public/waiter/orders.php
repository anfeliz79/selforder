<?php
session_start();
if (!isset($_SESSION['branch_id'])) {
    header("Location: /waiter/login");
    exit;
}
$branchId = $_SESSION['branch_id'];
$branchName = $_SESSION['branch_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedidos - <?= htmlspecialchars($branchName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: #f8f9fa; }
    header {
      position: sticky; top:0; z-index:1000;
      background:#fff; border-bottom:1px solid #dee2e6;
      padding: .8rem 1rem;
      display:flex; justify-content:space-between; align-items:center;
    }
    .order-card {
      border-left: 5px solid transparent;
      transition: transform .2s;
    }
    .order-card:hover { transform: scale(1.01); }
    .status-pendiente { border-color: #6c757d; }
    .status-preparacion { border-color: #ffc107; }
    .status-listo { border-color: #0dcaf0; }
    .status-entregado { border-color: #198754; opacity: .8; }
    .status-cancelado { border-color: #dc3545; opacity: .7; }
    .btn-action { flex:1; font-size:14px; }
  </style>
</head>
<body>
<header>
  <h5 class="mb-0">📋 <?= htmlspecialchars($branchName) ?></h5>
  <a href="/waiter/logout" class="btn btn-sm btn-outline-danger">Salir</a>
</header>

<main class="container-fluid p-2">
  <!-- Filtro -->
  <div class="mb-2 d-flex gap-2">
    <select id="filterStatus" class="form-select form-select-sm">
      <option value="">Todos</option>
      <option value="pendiente">Pendientes</option>
      <option value="preparacion">En preparación</option>
      <option value="listo">Listos</option>
      <option value="entregado">Entregados</option>
      <option value="cancelado">Cancelados</option>
    </select>
    <button class="btn btn-sm btn-secondary" onclick="loadOrders()">🔄</button>
  </div>

  <!-- Pedidos -->
  <div id="ordersList" class="row g-2"></div>
</main>

<script>
const branchId = <?= $branchId ?>;

function renderStatusBadge(status) {
  const colors = {
    pendiente: "secondary",
    preparacion: "warning",
    listo: "info",
    entregado: "success",
    cancelado: "danger"
  };
  const labels = {
    pendiente: "Pendiente",
    preparacion: "En preparación",
    listo: "Listo",
    entregado: "Entregado",
    cancelado: "Cancelado"
  };
  return `<span class="badge bg-${colors[status]||'dark'}">${labels[status]||status}</span>`;
}

function loadOrders() {
  const status = document.getElementById("filterStatus").value;
  fetch(`/orders?branch_id=${branchId}&status=${status}`)
    .then(r=>r.json())
    .then(res=>{
      const list = document.getElementById("ordersList");
      list.innerHTML = "";
      (res.data||res).forEach(o=>{
        list.innerHTML += `
          <div class="col-12">
            <div class="card shadow-sm order-card status-${o.status}">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h6>#${o.id} ${renderStatusBadge(o.status)}</h6>
                  <small>${o.created_at}</small>
                </div>
                <p class="mb-1"><strong>Mesa:</strong> ${o.table_number || 'N/A'}</p>
                <p class="mb-1"><strong>Cliente:</strong> ${o.customer_name || 'N/A'}</p>
                <p class="mb-2"><strong>Total:</strong> RD$ ${parseFloat(o.total||0).toFixed(2)}</p>
                <div class="d-flex gap-2">
                  ${renderButtons(o)}
                </div>
              </div>
            </div>
          </div>
        `;
      });
    })
    .catch(err=>{
      Swal.fire("Error", "No se pudieron cargar pedidos", "error");
      console.error(err);
    });
}

function renderButtons(order) {
  const s = order.status;
  if (s === "cancelado" || s === "entregado") return "";
  const next = {
    pendiente: "preparacion",
    preparacion: "listo",
    listo: "entregado"
  }[s] || null;

  let btns = "";
  if (next) {
    btns += `<button class="btn btn-sm btn-primary btn-action" onclick="changeStatus(${order.id}, '${next}')">➡️ ${next}</button>`;
  }
  btns += `<button class="btn btn-sm btn-danger btn-action" onclick="cancelOrder(${order.id})">❌ Cancelar</button>`;
  return btns;
}

function changeStatus(id, status) {
  fetch(`/orders?id=${id}`, {
    method: "PUT",
    headers: { "Content-Type":"application/json" },
    body: JSON.stringify({ status })
  }).then(()=> loadOrders());
}

function cancelOrder(id) {
  fetch(`/orders/cancel?id=${id}`, { method:"PUT" })
    .then(()=> loadOrders());
}

loadOrders();
setInterval(loadOrders, 5000); // refresco cada 5s
</script>
</body>
</html>
