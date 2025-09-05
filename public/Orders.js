const branchId = 1; // 👉 puedes cambiar esto o leerlo desde la URL (?branch=1)

function loadOrders() {
  fetch('/orders?branch_id=' + encodeURIComponent(branchId))
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

        const table = document.createElement("b");
        table.textContent = `Mesa ${o.table_number}`;
        card.appendChild(table);
        card.appendChild(document.createElement("br"));

        const customer = document.createElement("div");
        customer.textContent = `Cliente: ${o.customer_name}`;
        card.appendChild(customer);

        const phone = document.createElement("small");
        phone.textContent = `Tel: ${o.customer_phone}`;
        card.appendChild(phone);
        card.appendChild(document.createElement("br"));

        const time = document.createElement("small");
        time.textContent = `Hora: ${o.created_at}`;
        card.appendChild(time);
        card.appendChild(document.createElement("br"));

        const btnContainer = document.createElement("div");
        btnContainer.className = "mt-2";
        btnContainer.appendChild(renderButtons(o));
        card.appendChild(btnContainer);

        document.getElementById(`col-${o.status}`).appendChild(card);
      });
    });
}

function renderButtons(order) {
  let btn;
  switch(order.status) {
    case "pendiente":
      btn = document.createElement("button");
      btn.className = "btn btn-sm btn-primary w-100";
      btn.textContent = "Iniciar";
      btn.addEventListener("click", () => updateStatus(order.id, 'preparando'));
      return btn;
    case "preparando":
      btn = document.createElement("button");
      btn.className = "btn btn-sm btn-warning w-100";
      btn.textContent = "Marcar Listo";
      btn.addEventListener("click", () => updateStatus(order.id, 'listo'));
      return btn;
    case "listo":
      btn = document.createElement("button");
      btn.className = "btn btn-sm btn-success w-100";
      btn.textContent = "Entregar";
      btn.addEventListener("click", () => updateStatus(order.id, 'entregado'));
      return btn;
    case "entregado":
      const span = document.createElement("span");
      span.className = "badge bg-success";
      span.textContent = "✔️ Entregado";
      return span;
    default:
      return document.createDocumentFragment();
  }
}

function updateStatus(id, status) {
  fetch('/orders/status?id=' + encodeURIComponent(id), {
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
