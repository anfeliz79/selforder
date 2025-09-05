<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SelfOrder - Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style id="dynamic-style"></style>
  <style>
    /* Animaciones modernas para estatus */

    @keyframes pulse { 0%{opacity:1;} 50%{opacity:.6;} 100%{opacity:1;} }
    @keyframes bounce { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-3px);} }
    @keyframes flash { 0%,50%,100%{opacity:1;} 25%,75%{opacity:0.5;} }
    @keyframes fadeIn { from {opacity:0; transform:translateY(5px);} to {opacity:1; transform:translateY(0);} }
  

.status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.3em 0.6em;
  border-radius: 8px;
}

.status-pendiente   { background: #fff3cd; color: #856404; } /* amarillo suave */
.status-preparando  { background: #cce5ff; color: #004085; } /* azul suave */
.status-listo       { background: #d4edda; color: #155724; } /* verde suave */
.status-entregado   { background: #e2e3e5; color: #383d41; } /* gris */
.status-cancelado   { background: #f8d7da; color: #721c24; } /* rojo suave */


.order-tracking {
  display: flex;
  justify-content: space-between;
  margin: 1rem 0;
  position: relative;
}

.order-tracking-step {
  text-align: center;
  flex: 1;
  position: relative;
}

.order-tracking-step:before {
  content: "";
  position: absolute;
  top: 12px;
  left: 50%;
  width: 100%;
  height: 3px;
  background: #e9ecef;
  z-index: 1;
}

.order-tracking-step:first-child:before {
  display: none;
}

.order-tracking-circle {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #e9ecef;
  margin: 0 auto 0.5rem;
  z-index: 2;
  position: relative;
}

.order-tracking-step.active .order-tracking-circle {
  background: #0d6efd;
}

.order-tracking-step.done .order-tracking-circle {
  background: #20c997;
}

.order-tracking-label {
  font-size: 0.7rem;
  color: #6c757d;
}

  </style>
</head>
<body>

<!-- ================= NAVBAR SUPERIOR ================= -->
<nav class="navbar navbar-dark fixed-top">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <span class="navbar-brand mb-0 h6">
      <img src="logo.png" alt="Logo" class="app-logo me-2" style="height:30px;">
      SelfOrder
    </span>
    <button class="btn btn-outline-light btn-sm" onclick="showMyOrders()">
      📦 Mis Pedidos
    </button>
  </div>
</nav>
<div class="mt-5 pt-4"></div> <!-- espacio para navbar -->

<div class="container py-3">

  <!-- ================= REGISTRO ================= -->
  <div id="register-section" class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Bienvenido 🍽️</h5>
      <p>Ingresa tus datos para ordenar:</p>
      <input type="text" id="customerName" class="form-control mb-2" placeholder="Nombre completo">
      <input type="tel" id="customerPhone" class="form-control mb-2" placeholder="Teléfono">
      <button class="btn btn-primary w-100" onclick="registerCustomer()">Iniciar</button>
    </div>
  </div>

<!-- ================= MENÚ ================= -->
<div id="menu-section" class="d-none">

  <!-- Header -->
  <div class="text-center mb-3">
    <h5 id="menuTitle" class="fw-bold">Explora el menú 🍔</h5>
  </div>

  <!-- Categorías estilo chips -->
  <div class="d-flex overflow-auto mb-3 px-2" id="categoryTabs" style="gap: .5rem;"></div>

  <!-- Productos estilo app -->
  <div id="productList" class="row row-cols-2 g-3 px-2"></div>
</div>

<!-- Botón flotante carrito -->
<button id="floatingCartBtn" class="btn btn-danger position-fixed bottom-0 start-50 translate-middle-x mb-3 rounded-pill px-4 shadow d-none"
        onclick="toggleCart()">
  🛒 Carrito <span id="cartCountFloat" class="badge bg-light text-dark">0</span>
</button>


<!-- ================= CARRITO ================= -->
<div id="cart-section" 
     class="d-none position-fixed bottom-0 start-0 end-0 bg-white border-top p-3"
     style="max-height: 45%; overflow-y: auto; z-index: 1050;">
  <h6>
    🛒 Carrito <span id="cartCount" class="badge bg-primary">0</span>
  </h6>
  <ul id="cartItems" class="list-group small mb-2"></ul>
  <button class="btn btn-success w-100 mb-2 d-none" onclick="placeOrder()">Confirmar Pedido</button>
  <button class="btn btn-outline-danger w-100 mb-2 d-none" onclick="clearCart()">❌ Cancelar Todo</button>
  <div class="d-flex gap-2">
    <button class="btn btn-warning flex-fill" onclick="callWaiter()">🚨 Llamar mesero</button>
    <button class="btn btn-danger flex-fill" onclick="requestBill()">💳 Pedir cuenta</button>
  </div>
</div>

<!-- ================= MODAL PRODUCTO ================= -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalProductName" class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <img id="modalProductImage" class="img-fluid mb-2" src="">
        <p id="modalProductDesc"></p>

        <div id="variantsSection" class="mb-2"></div>
        <div id="addonsSection" class="mb-2"></div>

        <label class="form-label">Cantidad</label>
        <input type="number" id="modalQty" value="1" min="1" class="form-control mb-2">

        <label class="form-label">Comentario</label>
        <textarea id="modalComment" class="form-control" placeholder="Ej: sin cebolla, extra picante"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary w-100" onclick="confirmAddToCart()">Agregar al carrito</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL PEDIDOS ENVIADOS ================= -->
<div class="modal fade" id="ordersModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold text-primary">📦 Mis Pedidos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light">
        <div id="myOrders" class="d-flex flex-column gap-3"></div>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="client.js"></script>
</body>
</html>
