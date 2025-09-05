<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SelfOrder - Pedidos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .kanban-board { display: flex; gap: 1rem; overflow-x: auto; }
    .kanban-column { min-width: 250px; flex: 1; background: #f8f9fa; border-radius: 10px; padding: .5rem; }
    .kanban-card { background: white; padding: .75rem; border-radius: 8px; margin-bottom: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .kanban-card small { color: #666; }
  </style>
</head>
<body class="bg-light">

<div class="container-fluid py-3">
  <h5 class="mb-3">📦 Pedidos Sucursal</h5>

  <div class="kanban-board">
    <div class="kanban-column">
      <h6 class="text-center">Pendientes</h6>
      <div id="col-pendiente"></div>
    </div>
    <div class="kanban-column">
      <h6 class="text-center">Preparando</h6>
      <div id="col-preparando"></div>
    </div>
    <div class="kanban-column">
      <h6 class="text-center">Listos</h6>
      <div id="col-listo"></div>
    </div>
    <div class="kanban-column">
      <h6 class="text-center">Entregados</h6>
      <div id="col-entregado"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="orders.js"></script>
</body>
</html>
