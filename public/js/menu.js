let table;
let editMode = false;

$(document).ready(function() {
  table = $('#productsTable').DataTable({
    ajax: '/products',
    columns: [
      { data: 'image', render: d => `<img src="${d||'placeholder.png'}" class="img-thumbnail" style="max-height:50px;">` },
      { data: 'name' },
      { data: 'category' },
      { data: 'base_price', render: d => `$${d}` },
      { data: null, render: (d) => `
          <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(${d.id})">✏️</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${d.id})">🗑️</button>
        `}
    ]
  });
});

function showProductModal() {
  editMode = false;
  $('#productModalTitle').text('Nuevo Producto');
  $('#productId').val('');
  $('#productName').val('');
  $('#productDesc').val('');
  $('#productImage').val('');
  $('#previewImg').addClass('d-none');
  $('#productPrice').val('');
  $('#productCategory').val('');
  $('#variantsTable tbody').empty();
  $('#addonsTable tbody').empty();
  $('#branchesSection').empty();

  loadBranchesForProduct(); // ⚡ cargar sucursales
  new bootstrap.Modal(document.getElementById('productModal')).show();
}


function previewImage(event) {
  const img = document.getElementById('previewImg');
  img.src = URL.createObjectURL(event.target.files[0]);
  img.classList.remove('d-none');
}

function addVariantRow(name='', price='') {
  $('#variantsTable tbody').append(`
    <tr>
      <td><input class="form-control form-control-sm" value="${name}"></td>
      <td><input type="number" class="form-control form-control-sm" value="${price}"></td>
      <td><button class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()">❌</button></td>
    </tr>
  `);
}

function addAddonRow(name='', price='') {
  $('#addonsTable tbody').append(`
    <tr>
      <td><input class="form-control form-control-sm" value="${name}"></td>
      <td><input type="number" class="form-control form-control-sm" value="${price}"></td>
      <td><button class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()">❌</button></td>
    </tr>
  `);
}

function saveProduct() {
  const id = $('#productId').val();
  const name = $('#productName').val();
  const description = $('#productDesc').val();
  const price = $('#productPrice').val();
  const category = $('#productCategory').val();

  // variants
  const variants = [];
  $('#variantsTable tbody tr').each(function() {
    const tds = $(this).find('input');
    variants.push({ name: tds.eq(0).val(), price: parseFloat(tds.eq(1).val()) });
  });

  // addons
  const addons = [];
  $('#addonsTable tbody tr').each(function() {
    const tds = $(this).find('input');
    addons.push({ name: tds.eq(0).val(), price: parseFloat(tds.eq(1).val()) });
  });

  // subir imagen si existe
  const fileInput = document.getElementById("productImage");
  if (fileInput.files.length > 0) {
    const formData = new FormData();
    formData.append("file", fileInput.files[0]);
    fetch("/upload.php", { method:"POST", body: formData })
      .then(r=>r.json())
      .then(res=>{
        if (res.success) doSave(id,name,description,price,category,res.url,variants,addons);
      });
  } else {
    doSave(id,name,description,price,category,null,variants,addons);
  }
}


function doSave(id,name,description,price,category,image,variants,addons) {
  // ✅ armar listado de sucursales seleccionadas
  const branches = [];
  document.querySelectorAll(".branch-check").forEach(cb => {
    if (cb.checked) {
      const priceInput = document.querySelector(`.branch-price[data-id="${cb.dataset.id}"]`);
      branches.push({
        branch_id: cb.dataset.id,
        custom_price: priceInput.value || null
      });
    }
  });

  const payload = { id,name,description,base_price:price,category,image,variants,addons,branches };
  fetch("/products", {
    method: id? "PUT":"POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload)
  }).then(r=>r.json()).then(()=>{
    Swal.fire({ icon:"success", title:"Guardado", timer:1500, showConfirmButton:false });
    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
    table.ajax.reload();
  });
}
function editProduct(id) {
  fetch(`/products?id=${id}`)
    .then(r=>r.json())
    .then(p=>{
      editMode = true;
      $('#productModalTitle').text('Editar Producto');
      $('#productId').val(p.id);
      $('#productName').val(p.name);
      $('#productDesc').val(p.description);
      $('#productPrice').val(p.base_price);
      $('#productCategory').val(p.category);
      if (p.image) { $('#previewImg').attr('src',p.image).removeClass('d-none'); }

      $('#variantsTable tbody').empty();
      p.variants.forEach(v=> addVariantRow(v.name,v.price));

      $('#addonsTable tbody').empty();
      p.addons.forEach(a=> addAddonRow(a.name,a.price));

      $('#branchesSection').empty();
      loadBranchesForProduct(p.id); // ⚡ cargar sucursales con disponibilidad
      new bootstrap.Modal(document.getElementById('productModal')).show();
    });
}

function deleteProduct(id) {
  Swal.fire({
    title:"¿Eliminar producto?",
    text:"Esta acción no se puede deshacer",
    icon:"warning",
    showCancelButton:true,
    confirmButtonText:"Sí, eliminar",
    cancelButtonText:"Cancelar"
  }).then(res=>{
    if (res.isConfirmed) {
      fetch(`/products?id=${id}`, { method:"DELETE" })
        .then(()=> {
          Swal.fire("Eliminado","","success");
          table.ajax.reload();
        });
    }
  });
}

function loadBranchesForProduct(productId = null) {
  fetch("/branches")
    .then(r => r.json())
    .then(branches => {
      const container = document.getElementById("branchesSection");
      container.innerHTML = "";
      branches.forEach(b => {
        container.innerHTML += `
          <div class="col-12 col-md-6">
            <div class="form-check mb-1">
              <input class="form-check-input branch-check" type="checkbox" id="branch_${b.id}" data-id="${b.id}">
              <label class="form-check-label" for="branch_${b.id}">
                ${b.name}
              </label>
            </div>
            <input type="number" class="form-control form-control-sm mb-2 branch-price d-none" placeholder="Precio en ${b.name}" data-id="${b.id}">
          </div>
        `;
      });

      if (productId) {
        fetch(`/products?id=${productId}`)
          .then(r => r.json())
          .then(p => {
            p.branches.forEach(pb => {
              const cb = document.getElementById("branch_"+pb.branch_id);
              const priceInput = document.querySelector(`.branch-price[data-id="${pb.branch_id}"]`);
              if (cb) {
                cb.checked = true;
                if (priceInput) {
                  priceInput.classList.remove("d-none");
                  if (pb.custom_price) priceInput.value = pb.custom_price;
                }
              }
            });
          });
      }

      // toggle precio cuando se selecciona sucursal
      document.querySelectorAll(".branch-check").forEach(cb => {
        cb.addEventListener("change", function() {
          const input = document.querySelector(`.branch-price[data-id="${this.dataset.id}"]`);
          if (this.checked) input.classList.remove("d-none");
          else { input.classList.add("d-none"); input.value = ""; }
        });
      });
    });
}
