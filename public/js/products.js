let productTable;

$(document).ready(function () {
  productTable = $('#productsTable').DataTable({
    ajax: {
      url: '/products',
      dataSrc: 'data'
    },
    columns: [
      { data: 'image', render: d => d ? `<img src="${d}" style="height:40px;">` : '<span class="text-muted">Sin Imagen</span>' },
      { data: 'name' },
      { data: 'category', defaultContent: '<span class="text-muted">Sin categoría</span>' },
      { data: 'base_price', render: d => "RD$ " + parseFloat(d).toFixed(2) },
      {
        data: null,
        render: (d) => `
          <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(${d.id})">✏️</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${d.id})">🗑️</button>
        `
      }
    ]
  });
});


// -------- Categorías --------
function loadCategories(selectedValue = "") {
  fetch("/categories")
    .then(r => r.json())
    .then(cats => {
      const sel = document.getElementById("productCategory");
      sel.innerHTML = cats.map(c =>
        `<option value="${c}" ${selectedValue === c ? "selected" : ""}>${c}</option>`
      ).join("");
    });
}

// -------- Modal Nuevo --------
function showProductModal() {
  $('#productModalTitle').text('Nuevo Producto');
  $('#productId').val('');
  $('#productName').val('');
  $('#productDesc').val('');
  $('#productPrice').val('');
  document.getElementById('productImage').value = '';
  const prev = document.getElementById("productPreview");
  prev.src = "";
  prev.classList.add("d-none");
  $('#productImage').removeData("current");

  // limpiar listas dinámicas
  document.getElementById("variantsList").innerHTML = "";
  document.getElementById("addonsList").innerHTML = "";

  loadBranches();

  loadCategories(); 
  new bootstrap.Modal(document.getElementById('productModal')).show();
}

// -------- Guardar (crear/editar con FormData) --------
function saveProduct() {
  const id = $('#productId').val();
  const name = $('#productName').val().trim();
  const price = parseFloat($('#productPrice').val());
  const category = $('#productCategory').val();

  // 🔹 Validaciones obligatorias
  if (!name) {
    Swal.fire("Campo requerido", "El nombre es obligatorio", "warning");
    return;
  }
  if (isNaN(price) || price <= 0) {
    Swal.fire("Campo requerido", "El precio debe ser mayor a 0", "warning");
    return;
  }
  if (!category) {
    Swal.fire("Campo requerido", "Debes seleccionar una categoría", "warning");
    return;
  }

  // 🔹 sucursales seleccionadas
  const branches = [];
  document.querySelectorAll("#branchesList input[type=checkbox]:checked").forEach(chk => {
    branches.push({ branch_id: parseInt(chk.value, 10) });
  });

  if (branches.length === 0) {
    Swal.fire("Campo requerido", "Selecciona al menos una sucursal", "warning");
    return;
  }

  // 🔹 variantes / adicionales
  const variants = collectVariants();
  const addons   = collectAddons();

  const fd = new FormData();
  if (id) fd.append("id", id);

  fd.append("name", name);
  fd.append("description", $('#productDesc').val().trim());
  fd.append("base_price", price.toFixed(2));
  fd.append("category", category);
  fd.append("branches", JSON.stringify(branches));
  fd.append("variants", JSON.stringify(variants));
  fd.append("addons", JSON.stringify(addons));

  // 🔹 Imagen
  const imgInput = document.getElementById("productImage");
  if (imgInput.files && imgInput.files[0]) {
    const file = imgInput.files[0];

    // ⚠️ límite de 5MB (ajústalo a lo que quieras)
    const maxSize = 5 * 1024 * 1024; 
    if (file.size > maxSize) {
      Swal.fire({
        icon: "error",
        title: "Imagen demasiado grande",
        text: `El archivo supera el límite de 5MB (${(file.size / 1024 / 1024).toFixed(1)} MB). 
               Sube una imagen más pequeña.`,
        confirmButtonText: "Entendido"
      });
      return; // 🚫 no seguimos guardando
    }

    fd.append("image", file);
  } else if (id) {
    const current = $('#productImage').data("current") || "";
    fd.append("current_image", current);
  }

  // 🔹 Llamada al backend
  fetch("/products" + (id ? `?id=${id}` : ""), {
    method: "POST", // 🚀 mismo endpoint para crear/editar
    body: fd
  })
    .then(async r => {
      const text = await r.text();
      try {
        return JSON.parse(text);
      } catch (e) {
        throw new Error("Respuesta inválida del servidor: " + text);
      }
    })
    .then(res => {
      if (res.error) {
        Swal.fire("Error", res.error, "error");
      } else {
        Swal.fire({
          icon: "success",
          title: res.message || "Producto guardado",
          timer: 1500,
          showConfirmButton: false
        });

        // cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
        if (modal) modal.hide();

        // recargar tabla
        if (productTable) productTable.ajax.reload();
      }
    })
    .catch(err => {
      Swal.fire("Error", err.message, "error");
      console.error("Error al guardar producto:", err);
    });
}


// -------- Editar --------
function editProduct(id) {
  fetch(`/products?id=${id}`)
    .then(r => r.json())
    .then(p => {
      $('#productModalTitle').text('Editar Producto');
      $('#productId').val(p.id);
      $('#productName').val(p.name);
      $('#productDesc').val(p.description);
      $('#productPrice').val(p.base_price);

      // Imagen
      const prev = document.getElementById("productPreview");
      if (p.image) {
        prev.src = p.image;
        prev.classList.remove("d-none");
        $('#productImage').data("current", p.image);
      } else {
        prev.src = "";
        prev.classList.add("d-none");
        $('#productImage').removeData("current");
      }
      document.getElementById('productImage').value = '';

      // Categoría
      loadCategories(p.category);

      // Variantes (incluye id oculto)
      const vList = document.getElementById("variantsList");
      vList.innerHTML = "";
      (p.variants || []).forEach(v => addVariant(v.name, v.price, v.id));

      // Adicionales
      const aList = document.getElementById("addonsList");
      aList.innerHTML = "";
      (p.addons || []).forEach(a => addAddon(a.name, a.price));

      // Sucursales
      loadBranches((p.branches || []).map(b => b.branch_id));

      new bootstrap.Modal(document.getElementById('productModal')).show();
    });
}

// -------- Eliminar --------
function deleteProduct(id) {
  Swal.fire({
    title: "¿Eliminar producto?",
    text: "Se borrará permanentemente",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
  }).then(res => {
    if (res.isConfirmed) {
      fetch(`/products?id=${id}`, { method: "DELETE" })
        .then(r => r.json())
        .then(() => {
          Swal.fire("Eliminado", "", "success");
          productTable.ajax.reload();
        });
    }
  });
}

// -------- Preview imagen --------
function previewProductImage(event) {
  const preview = document.getElementById("productPreview");
  const file = event.target.files[0];
  if (file) {
    preview.src = URL.createObjectURL(file);
    preview.classList.remove("d-none");
  } else {
    preview.src = "";
    preview.classList.add("d-none");
  }
}

// -------- Variantes dinámicas --------
function addVariant(name = "", price = "", id = null) {
  const container = document.getElementById("variantsList");
  const div = document.createElement("div");
  div.classList.add("input-group", "mb-2");
  div.innerHTML = `
    <input type="hidden" class="variant-id" value="${id || ''}">
    <input type="text" class="form-control" placeholder="Nombre" value="${name}">
    <input type="number" class="form-control" placeholder="Precio" step="0.01" value="${price}">
    <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">❌</button>
  `;
  container.appendChild(div);
}

// -------- Adicionales dinámicos --------
function addAddon(name = "", price = "", id = null) {
  const container = document.getElementById("addonsList");
  const div = document.createElement("div");
  div.classList.add("input-group", "mb-2");
  div.innerHTML = `
    <input type="hidden" class="addon-id" value="${id || ''}">
    <input type="text" class="form-control" placeholder="Nombre" value="${name}">
    <input type="number" class="form-control" placeholder="Precio" step="0.01" value="${price}">
    <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">❌</button>
  `;
  container.appendChild(div);
}

// -------- Recolectar listas --------
function collectVariants() {
  const list = [];
  document.querySelectorAll("#variantsList .input-group").forEach(div => {
    const id = div.querySelector(".variant-id").value || null;
    const inputs = div.querySelectorAll("input:not(.variant-id)");
    const name = inputs[0].value.trim();
    const price = parseFloat(inputs[1].value) || 0;
    if (name) list.push({ id: id ? parseInt(id) : null, name, price });
  });
  return list;
}

function collectAddons() {
  const list = [];
  document.querySelectorAll("#addonsList .input-group").forEach(div => {
    const id = div.querySelector(".addon-id").value || null;
    const inputs = div.querySelectorAll("input:not(.addon-id)");
    const name = inputs[0].value.trim();
    const price = parseFloat(inputs[1].value) || 0;
    if (name) list.push({ id: id ? parseInt(id) : null, name, price });
  });
  return list;
}

function loadBranches(selected = []) {
  fetch("/branches")
    .then(r => r.json())
    .then(branches => {
      const container = document.getElementById("branchesList");
      if (!container) return;

      container.innerHTML = branches.map(b => `
        <div class="form-check">
          <input class="form-check-input" type="checkbox" 
                 id="branch_${b.id}" value="${b.id}"
                 ${selected.includes(parseInt(b.id,10)) ? "checked" : ""}>
          <label class="form-check-label" for="branch_${b.id}">
            ${b.name}
          </label>
        </div>
      `).join("");
    });
}



