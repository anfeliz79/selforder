let categoryTable;

$(document).ready(function () {
  categoryTable = $('#categoriesTable').DataTable({
    ajax: '/categories',
    columns: [
      { data: 'name' },
      {
        data: null,
        render: d => `
          <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(${d.id}, '${d.name}')">✏️</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${d.id})">🗑️</button>
        `
      }
    ]
  });
});

function showCategoryModal() {
  $('#categoryModalTitle').text('Nueva Categoría');
  $('#categoryId').val('');
  $('#categoryName').val('');
  new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function saveCategory() {
  const id = $('#categoryId').val();
  const name = $('#categoryName').val();

  if (!name) {
    Swal.fire("Error", "El nombre es obligatorio", "warning");
    return;
  }

  const payload = { id, name };

  fetch("/categories" + (id ? `?id=${id}` : ""), {
    method: id ? "PUT" : "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  }).then(r => r.json())
    .then(res => {
      if (res.error) {
        Swal.fire("Error", res.error, "error");
      } else {
        Swal.fire({ icon: "success", title: res.message, timer: 1500, showConfirmButton: false });
        bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
        categoryTable.ajax.reload();
      }
    });
}

function editCategory(id, name) {
  $('#categoryModalTitle').text('Editar Categoría');
  $('#categoryId').val(id);
  $('#categoryName').val(name);
  new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function deleteCategory(id) {
  Swal.fire({
    title: "¿Eliminar categoría?",
    text: "No podrás revertirlo",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
  }).then(res => {
    if (res.isConfirmed) {
      fetch(`/categories?id=${id}`, { method: "DELETE" })
        .then(r => r.json())
        .then(res => {
          if (res.error) Swal.fire("Error", res.error, "error");
          else {
            Swal.fire("Eliminada", "", "success");
            categoryTable.ajax.reload();
          }
        });
    }
  });
}
