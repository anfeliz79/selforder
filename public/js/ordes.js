let ordersTable;

$(document).ready(function () {
  // 🔹 Cargar sucursales en el filtro
  fetch("/branches")
    .then(r => r.json())
    .then(branches => {
      const sel = document.getElementById("filterBranch");
      sel.innerHTML += branches.map(b => `<option value="${b.id}">${b.name}</option>`).join("");
    });

  // 🔹 Inicializar DataTable
  ordersTable = $('#ordersTable').DataTable({
    ajax: {
      url: '/orders/global',
      data: function (d) {
        d.filter    = $('#filterType').val();
        d.start     = $('#filterStart').val();
        d.end       = $('#filterEnd').val();
        d.branch_id = $('#filterBranch').val();
        d.status    = $('#filterStatus').val();
      },
      dataSrc: function (json) {
        if (json.summary) updateSummary(json.summary);
        return json.data || [];
      }
    },
    columns: [
      { data: 'id' },
      { data: 'branch_name' },
      { data: 'customer_name', defaultContent: 'N/A' },
      { data: 'status', render: s => renderStatusBadge(s) },
      { data: 'total', render: d => "RD$ " + parseFloat(d).toFixed(2) },
      { data: 'created_at' },
      {
        data: null,
        render: d => `
          <button class="btn btn-sm btn-outline-info" onclick="viewOrder(${d.id})">👁️</button>
          <button class="btn btn-sm btn-outline-warning" onclick="changeStatus(${d.id})">🔄</button>
          <button class="btn btn-sm btn-outline-danger" onclick="cancelOrder(${d.id})">❌</button>
        `
      }
    ]
  });

  // 🔹 Filtros → recargar tabla
  $('#filterType, #filterStart, #filterEnd, #filterBranch, #filterStatus').on('change', () => {
    if ($('#filterType').val() === 'custom') {
      $('#dateStartCol, #dateEndCol').removeClass("d-none");
    } else {
      $('#dateStartCol, #dateEndCol').addClass("d-none");
      $('#filterStart, #filterEnd').val('');
    }
    ordersTable.ajax.reload();
  });
});

// 🔹 Renderiza estado como badge de colores
function renderStatusBadge(status) {
  const colors = {
    pendiente: "secondary",
    preparacion: "warning",
    listo: "info",
    entregado: "success",
    cancelado: "danger"
  };
  return `<span class="badge bg-${colors[status] || 'dark'}">${status}</span>`;
}

// 🔹 Ver orden con modal
function viewOrder(id) {
  fetch(`/orders?id=${id}`)
    .then(r => r.json())
    .then(order => {
      const steps = ["pendiente","preparacion","listo","entregado"];
      let timeline = `<div class="d-flex justify-content-between mb-3">`;
      steps.forEach(s => {
        const active = steps.indexOf(order.status) >= steps.indexOf(s);
        timeline += `
          <div class="text-center flex-fill">
            <div class="rounded-circle mx-auto mb-2 ${active ? 'bg-success' : 'bg-light'}" 
                 style="width:20px; height:20px;"></div>
            <small>${s}</small>
          </div>
        `;
      });
      timeline += "</div>";

      let details = `
        <h6>Cliente: ${order.customer_name || 'N/A'}</h6>
        <h6>Sucursal: ${order.branch_name}</h6>
        <h6>Total: RD$ ${parseFloat(order.total).toFixed(2)}</h6>
        <hr>
        <h5>Productos</h5>
        <ul class="list-group">
      `;
      (order.items || []).forEach(item => {
        details += `
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong>${item.product_name}</strong><br>
              <small>${item.variant_name || ''}</small>
            </div>
            <span>${item.qty} x RD$ ${parseFloat(item.price).toFixed(2)}</span>
          </li>
        `;
      });
      details += "</ul>";

      document.getElementById("orderTimeline").innerHTML = timeline;
      document.getElementById("orderDetails").innerHTML = details;

      new bootstrap.Modal(document.getElementById('orderModal')).show();
    });
}

// 🔹 Cambiar estado
function changeStatus(id) {
  Swal.fire({
    title: "Cambiar estado",
    input: "select",
    inputOptions: {
      pendiente: "Pendiente",
      preparacion: "En preparación",
      listo: "Listo",
      entregado: "Entregado",
      cancelado: "Cancelado"
    },
    inputPlaceholder: "Selecciona nuevo estado",
    showCancelButton: true,
  }).then(res => {
    if (res.isConfirmed && res.value) {
      fetch(`/orders?id=${id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: res.value })
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.error) {
          Swal.fire("Error", resp.error, "error");
        } else {
          Swal.fire("Estado actualizado", "", "success");
          ordersTable.ajax.reload();
        }
      });
    }
  });
}

// 🔹 Cancelar orden
function cancelOrder(id) {
  Swal.fire({
    title: "¿Cancelar orden?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar"
  }).then(res => {
    if (res.isConfirmed) {
      fetch(`/orders/cancel?id=${id}`, { method: "PUT" })
        .then(r => r.json())
        .then(resp => {
          if (resp.error) {
            Swal.fire("Error", resp.error, "error");
          } else {
            Swal.fire("Cancelada", "", "success");
            ordersTable.ajax.reload();
          }
        });
    }
  });
}

// 🔹 Actualiza resumen de totales
function updateSummary(summary) {
  const container = document.getElementById("ordersSummary");
  if (!container) return;
  container.innerHTML = `
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h6>Total Órdenes</h6>
          <h4>${summary.total_orders || 0}</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h6>Total Ingresos</h6>
          <h4>RD$ ${(summary.total_amount || 0).toFixed(2)}</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h6>Órdenes Canceladas</h6>
          <h4>${summary.cancelled || 0}</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h6>Órdenes Entregadas</h6>
          <h4>${summary.delivered || 0}</h4>
        </div>
      </div>
    </div>
  `;
}
