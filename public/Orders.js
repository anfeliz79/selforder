const branchId = 1; // 👉 puedes cambiar esto o leerlo desde la URL (?branch=1)

function loadOrders() {
  fetch(`/orders?branch_id=${branchId}`)
    .then(r => r.json())
    .then(orders => {
      // Limpiar columnas
      ["pendiente", "preparando", "listo", "entregado"].forEach(s => {
        document.getElementById(`col-${s}`).innerHTML = "";
      });

      // Renderizar pedidos
      orders.forEach(o => {
        const card = document.createElement("div");
        card.className = "kanban-card";
        card.innerHTML = `
          <b>Mesa ${o.table_number}</b> <br>
          Cliente: ${o.customer_name} <br>
          <small>Tel: ${o.customer_phone}</small><br>
          <small>Hora: ${o.created_at}</small><br>
          <div class="mt-2">
            ${renderButtons(o)}
          </div>
        `;
        document.getElementById(`col-${o.status}`).appendChild(card);
      });
    });
}

function renderButtons(order) {
  switch(order.status) {
    case "pendiente":
      return `<button class="btn btn-sm btn-primary w-100" onclick="updateStatus(${order.id}, 'preparando')">Iniciar</button>`;
    case "preparando":
      return `<button class="btn btn-sm btn-warning w-100" onclick="updateStatus(${order.id}, 'listo')">Marcar Listo</button>`;
    case "listo":
      return `<button class="btn btn-sm btn-success w-100" onclick="updateStatus(${order.id}, 'entregado')">Entregar</button>`;
    case "entregado":
      return `<span class="badge bg-success">✔️ Entregado</span>`;
    default:
      return "";
  }
}

function updateStatus(id, status) {
  fetch(`/orders/status?id=${id}`, {
    method: "PUT",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({status})
  })
  .then(r => r.json())
  .then(res => {
    console.log(res);
    loadOrders();
  });
}

// Cargar cada 5 segundos
setInterval(loadOrders, 5000);
loadOrders();
