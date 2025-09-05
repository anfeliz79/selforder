const urlParams = new URLSearchParams(window.location.search);
const branchId = urlParams.get("branch");
const tableId = urlParams.get("table");

let customerId = localStorage.getItem("customerId");
let cart = [];
let allProducts = [];

//
// ================== CONFIGURACIÓN VISUAL ==================
//
function loadSettings() {
  fetch("/settings")
    .then((r) => r.json())
    .then((cfg) => {
      let css = `
        body {
          background: ${
            cfg.background_image
              ? `url(${cfg.background_image}) no-repeat center center / cover`
              : cfg.background_color
          };
          font-family: ${cfg.font_family};
        }
        .btn-primary { background-color: ${cfg.primary_color}; border-color: ${cfg.primary_color}; }
        .btn-outline-primary { color: ${cfg.primary_color}; border-color: ${cfg.primary_color}; }
        .btn-outline-primary:hover { background-color: ${cfg.primary_color}; color: #fff; }
        .navbar { background-color: ${cfg.primary_color} !important; }
        .card { border-radius: 15px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .card img { height: 140px; object-fit: cover; }
        .nav-pills .nav-link.active { background-color: ${cfg.primary_color}; }
      `;
      document.getElementById("dynamic-style").innerHTML = css;

      if (cfg.logo) {
        const logoEls = document.querySelectorAll(".app-logo");
        logoEls.forEach((el) => (el.src = cfg.logo));
      }
    });
}

//
// ================== REGISTRO CLIENTE ==================
//
function registerCustomer() {
  const name = document.getElementById("customerName").value;
  const phone = document.getElementById("customerPhone").value;

  if (!name || !phone) {
    Swal.fire({
      icon: "warning",
      title: "Datos incompletos",
      text: "Debes ingresar nombre y teléfono",
    });
    return;
  }

  fetch("/customers", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, phone, table_id: tableId }),
  })
    .then((r) => r.json())
    .then((res) => {
      customerId = res.customer_id;
      localStorage.setItem("customerId", customerId);
      document.getElementById("register-section").classList.add("d-none");
      document.getElementById("menu-section").classList.remove("d-none");
      document.getElementById("cart-section").classList.remove("d-none");
      loadSettings();
      loadProducts();
      loadMyOrders();
    });
}

//
// ================== MENÚ ==================
//
function loadProducts() {
  fetch(`/products?branch_id=${branchId}`)
    .then(r => r.json())
    .then(products => {
      allProducts = products;
      const categories = [...new Set(products.map(p => p.category))];
      const catTabs = document.getElementById("categoryTabs");

      catTabs.innerHTML = categories.map((c,i) => `
        <button class="btn ${i===0?'btn-primary':'btn-outline-primary'} btn-sm rounded-pill"
                onclick="filterCategory('${c}', this)">
          ${c}
        </button>
      `).join("");

      filterCategory(categories[0]);
    });
}

function filterCategory(cat, btn=null) {
  if (btn) {
    document.querySelectorAll("#categoryTabs button").forEach(b=>b.classList.replace("btn-primary","btn-outline-primary"));
    btn.classList.replace("btn-outline-primary","btn-primary");
  }
  const container = document.getElementById("productList");
  container.innerHTML = "";
  allProducts.filter(p => p.category === cat).forEach(p => {
    container.innerHTML += `
      <div class="col">
        <div class="card h-100 shadow-sm position-relative">
          <img src="${p.image || "placeholder.png"}" class="card-img-top">
          <div class="card-body">
            <h6 class="card-title mb-1">${p.name}</h6>
            <p class="fw-bold text-primary">$${p.price}</p>
          </div>
          <button class="btn btn-sm btn-danger rounded-circle position-absolute" 
                  style="bottom:10px; right:10px;"
                  onclick="showProductModal(${p.id}, '${p.name}', '${p.description}', '${p.image}')">+</button>
        </div>
      </div>`;
  });
}



//
// ================== MODAL PRODUCTO ==================
//
let selectedProduct = null;
let selectedVariant = null;
let selectedAddons = [];

function showProductModal(id, name, desc, img) {
  selectedProduct = { id, name, desc, img };
  selectedVariant = null;
  selectedAddons = [];
  document.getElementById("modalProductName").innerText = name;
  document.getElementById("modalProductImage").src = img || "placeholder.png";
  document.getElementById("modalProductDesc").innerText = desc || "";
  document.getElementById("modalQty").value = 1;
  document.getElementById("modalComment").value = "";

  fetch(`/products?id=${id}`)
    .then((r) => r.json())
    .then((res) => {
      const vSec = document.getElementById("variantsSection");
      vSec.innerHTML = "<label class='form-label'>Tamaños</label>";
      res.variants.forEach((v) => {
        vSec.innerHTML += `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="variant" value="${v.id}" data-price="${v.price}" onchange="selectVariant(this)">
            <label class="form-check-label">${v.name} - $${v.price}</label>
          </div>`;
      });

      const aSec = document.getElementById("addonsSection");
      aSec.innerHTML = "<label class='form-label'>Extras</label>";
      res.addons.forEach((a) => {
        aSec.innerHTML += `
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="${a.id}" data-name="${a.name}" data-price="${a.price}" onchange="toggleAddon(this)">
            <label class="form-check-label">${a.name} - $${a.price}</label>
          </div>`;
      });
    });

  new bootstrap.Modal(document.getElementById("productModal")).show();
}

function selectVariant(radio) {
  selectedVariant = {
    id: radio.value,
    price: parseFloat(radio.dataset.price),
  };
}

function toggleAddon(checkbox) {
  if (checkbox.checked) {
    selectedAddons.push({
      id: checkbox.value,
      name: checkbox.dataset.name,
      price: parseFloat(checkbox.dataset.price),
    });
  } else {
    selectedAddons = selectedAddons.filter((a) => a.id !== checkbox.value);
  }
}

function confirmAddToCart() {
  const qty = parseInt(document.getElementById("modalQty").value) || 1;
  const comment = document.getElementById("modalComment").value || "";
  const basePrice = selectedVariant ? selectedVariant.price : 0;
  const addonsPrice = selectedAddons.reduce((s, a) => s + a.price, 0);
  const totalPrice = (basePrice + addonsPrice) * qty;

  cart.push({
    product_id: selectedProduct.id,
    name: selectedProduct.name,
    quantity: qty,
    price: totalPrice,
    variant_id: selectedVariant ? selectedVariant.id : null,
    addons: selectedAddons,
    comment: comment,
  });
  renderCart();
  bootstrap.Modal.getInstance(
    document.getElementById("productModal")
  ).hide();
}

//
// ================== CARRITO ==================
//
function toggleCart() {
  const cartSec = document.getElementById("cart-section");
  cartSec.classList.toggle("d-none");
}
function renderCart() {
  const ul = document.getElementById("cartItems");
  const countBadge = document.getElementById("cartCount");
  const countFloat = document.getElementById("cartCountFloat");
  ul.innerHTML = "";

  countBadge.textContent = cart.length;
  countFloat.textContent = cart.length;

  // mostrar/ocultar botón flotante
  document.getElementById("floatingCartBtn").classList.toggle("d-none", cart.length === 0);

  if (cart.length === 0) {
    document.querySelector("#cart-section button.btn-success").classList.add("d-none");
    document.querySelector("#cart-section button.btn-outline-danger").classList.add("d-none");
  } else {
    document.querySelector("#cart-section button.btn-success").classList.remove("d-none");
    document.querySelector("#cart-section button.btn-outline-danger").classList.remove("d-none");

    cart.forEach((item, i) => {
      const addonsText =
        item.addons && item.addons.length
          ? `<br><small>Extras: ${item.addons.map((a) => a.name).join(", ")}</small>`
          : "";
      const commentText = item.comment
        ? `<br><small>Nota: ${item.comment}</small>`
        : "";

      ul.innerHTML += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            ${item.name} x${item.quantity}
            ${addonsText} ${commentText}
          </div>
          <div>
            <button class="btn btn-sm btn-outline-secondary me-1" onclick="decreaseQty(${i})">➖</button>
            <span>${item.quantity}</span>
            <button class="btn btn-sm btn-outline-secondary ms-1" onclick="increaseQty(${i})">➕</button>
            <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${i})">❌</button>
          </div>
        </li>`;
    });
  }
}


function removeFromCart(index) {
  cart.splice(index, 1);
  renderCart();
}

function clearCart() {
  if (cart.length === 0) return;
  Swal.fire({
    title: "¿Vaciar carrito?",
    text: "Se eliminarán todos los productos que estás armando.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, vaciar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      cart = [];
      renderCart();
      Swal.fire({
        icon: "success",
        title: "Carrito vacío",
        timer: 1500,
        showConfirmButton: false,
      });
    }
  });
}

function increaseQty(index) {
  const item = cart[index];
  const unitPrice = item.price / item.quantity;
  item.quantity += 1;
  item.price = unitPrice * item.quantity;
  renderCart();
}

function decreaseQty(index) {
  const item = cart[index];
  const unitPrice = item.price / item.quantity;
  if (item.quantity > 1) {
    item.quantity -= 1;
    item.price = unitPrice * item.quantity;
  } else {
    cart.splice(index, 1);
  }
  renderCart();
}

function placeOrder() {
  fetch("/orders", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      branch_id: branchId,
      table_id: tableId,
      customer_id: customerId,
      items: cart,
    }),
  })
    .then((r) => r.json())
    .then(() => {
      Swal.fire({
        icon: "success",
        title: "Pedido enviado",
        text: "Tu orden fue registrada con éxito",
        confirmButtonColor: "#198754",
      });
      cart = [];
      renderCart();
      loadMyOrders();
    });
}

//
// ================== PEDIDOS (MODAL) ==================
//
function loadMyOrders() {
  fetch(`/orders?branch_id=${branchId}`)
    .then(r => r.json())
    .then(orders => {
      const container = document.getElementById("myOrders");
      container.innerHTML = "";

      orders.filter(o => o.customer_id == customerId).forEach(o => {
        // Determinar el estado actual
        const statusOrder = ["pendiente", "preparando", "listo", "entregado"];
        const currentIndex = statusOrder.indexOf(o.status);

        // construir tracking
        let trackingHtml = `
          <div class="order-tracking">
            ${statusOrder.map((st, i) => `
              <div class="order-tracking-step ${i < currentIndex ? 'done' : i === currentIndex ? 'active' : ''}">
                <div class="order-tracking-circle"></div>
                <div class="order-tracking-label">${st.charAt(0).toUpperCase() + st.slice(1)}</div>
              </div>
            `).join("")}
          </div>
        `;

        // Detalles de productos
        let detailsHtml = "";
        o.details.forEach(d => {
          const addons = d.addons.map(a => a.name).join(", ");
          const comment = d.comment ? `<br><small>Nota: ${d.comment}</small>` : "";
          const cancelBtn = d.status === "pendiente"
            ? `<button class="btn btn-sm btn-outline-secondary ms-2" onclick="cancelItem(${d.id})">Cancelar</button>`
            : "";

          detailsHtml += `
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <b>${d.product_name}</b> ${d.variant_name ? "(" + d.variant_name + ")" : ""}
                x${d.quantity} - $${d.price}
                ${addons ? "<br><small>Extras: " + addons + "</small>" : ""}
                ${comment}
              </div>
              <div>${cancelBtn}</div>
            </div>`;
        });

        container.innerHTML += `
          <div class="order-card">
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold">Mesa ${o.table_number}</span>
              <span class="text-muted">#${o.id}</span>
            </div>
            ${trackingHtml}
            ${detailsHtml}
          </div>`;
      });
    });
}



function showMyOrders() {
  loadMyOrders();
  new bootstrap.Modal(document.getElementById("ordersModal")).show();
}

function cancelItem(detailId) {
  fetch(`/orders/cancel?id=${detailId}&customer=${customerId}`, {
    method: "PUT",
  })
    .then((r) => r.json())
    .then((res) => {
      Swal.fire({
        icon: "info",
        title: "Item cancelado",
        text: res.message || "Se eliminó tu selección antes de ser preparada",
        confirmButtonColor: "#0d6efd",
      });
      loadMyOrders();
    });
}

//
// ================== BOTONES RÁPIDOS ==================
//
function callWaiter() {
  Swal.fire({
    icon: "info",
    title: "Llamando al mesero 🚨",
    text: "Se ha notificado al personal",
    confirmButtonColor: "#ffc107",
  });
}

function requestBill() {
  Swal.fire({
    icon: "info",
    title: "Cuenta solicitada 💳",
    text: "Un mesero traerá tu cuenta pronto",
    confirmButtonColor: "#dc3545",
  });
}

//
// ================== AUTO ARRANQUE ==================
//
if (customerId) {
  document.getElementById("register-section").classList.add("d-none");
  document.getElementById("menu-section").classList.remove("d-none");
  document.getElementById("cart-section").classList.remove("d-none");
  loadSettings();
  loadProducts();
  loadMyOrders();
}

// refrescar pedidos cada 10s
setInterval(loadMyOrders, 10000);
