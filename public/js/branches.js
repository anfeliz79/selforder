let branchTable;

$(document).ready(function() {
  branchTable = $('#branchesTable').DataTable({
    ajax: '/branches',
    columns: [
      { data: 'name' },
      { data: 'address' },
      { data: 'phone' },
      {
        data: null,
        render: (d) => `
          <button class="btn btn-sm btn-outline-primary me-1" 
            onclick="editBranch(${d.id}, '${d.name}', '${d.address}', '${d.phone}', '${d.access_key}')">✏️</button>
          <button class="btn btn-sm btn-outline-info me-1" onclick="manageTables(${d.id}, '${d.name}')">📋 Mesas</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteBranch(${d.id})">🗑️</button>
        `
      }
    ]
  });
});

//
// 🔹 Sucursal CRUD
//
function showBranchModal() {
  $('#branchModalTitle').text('Nueva Sucursal');
  $('#branchId').val('');
  $('#branchName').val('');
  $('#branchAddress').val('');
  $('#branchPhone').val('');
  $('#branchAccessKey').val('');
  new bootstrap.Modal(document.getElementById('branchModal')).show();
}

function saveBranch() {
  const id = $('#branchId').val();
  const name = $('#branchName').val();
  const address = $('#branchAddress').val();
  const phone = $('#branchPhone').val();
  const accessKey = $('#branchAccessKey').val();

  if (!name || !address || !phone || !accessKey) {
    Swal.fire({ icon:"warning", title:"Faltan datos", text:"Completa todos los campos" });
    return;
  }

  const payload = { id, name, address, phone, access_key: accessKey };

  fetch("/branches" + (id ? `?id=${id}` : ""), {
    method: id ? "PUT" : "POST",
    headers: { "Content-Type":"application/json" },
    body: JSON.stringify(payload)
  })
  .then(async r => {
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("Respuesta no JSON:", text);
      throw new Error("Respuesta inválida del servidor");
    }
  })
  .then(res => {
    if (res.error) {
      Swal.fire("Error", res.error, "error");
    } else {
      Swal.fire({
        icon:"success",
        title: res.message || "Guardado correctamente",
        timer:1500,
        showConfirmButton:false
      });
      bootstrap.Modal.getInstance(document.getElementById('branchModal')).hide();
      branchTable.ajax.reload();
    }
  })
  .catch(err => Swal.fire("Error", err.message, "error"));
}

function editBranch(id, name, address, phone, accessKey) {
  $('#branchModalTitle').text('Editar Sucursal');
  $('#branchId').val(id);
  $('#branchName').val(name);
  $('#branchAddress').val(address);
  $('#branchPhone').val(phone);
  $('#branchAccessKey').val(accessKey);
  new bootstrap.Modal(document.getElementById('branchModal')).show();
}

function deleteBranch(id) {
  Swal.fire({
    title:"¿Eliminar sucursal?",
    text:"Se borrará permanentemente",
    icon:"warning",
    showCancelButton:true,
    confirmButtonText:"Sí, eliminar",
    cancelButtonText:"Cancelar"
  }).then(res=>{
    if (res.isConfirmed) {
      fetch(`/branches?id=${id}`, { method:"DELETE" })
        .then(r=>r.json())
        .then(res=>{
          if(res.error) Swal.fire("Error", res.error, "error");
          else {
            Swal.fire("Eliminada","","success");
            branchTable.ajax.reload();
          }
        });
    }
  });
}
