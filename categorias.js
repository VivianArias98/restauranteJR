document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formCategoria');
  const msg = document.getElementById('msg');
  const lista = document.getElementById('listaCategorias');
  const inputNombre = document.getElementById('nombreCategoria');

  // 🔹 Mostrar mensajes
  function mostrarMensaje(text, isError = false) {
    msg.textContent = text;
    msg.style.color = isError ? '#b00020' : '#006400';
    setTimeout(() => { msg.textContent = ''; }, 4000);
  }

  // 🔹 Cargar categorías
  async function cargarCategorias() {
    try {
      const res = await fetch('listar_categoria.php');
      const data = await res.json();

      if (!data.success || data.data.length === 0) {
        lista.innerHTML = '<p>No hay categorías registradas.</p>';
        return;
      }

      let html = `
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.data.forEach(cat => {
        html += `
          <tr>
            <td>${cat.idCategoria}</td>
            <td>
              <input type="text" value="${cat.nombre}" id="cat-${cat.idCategoria}" class="input-editable">
            </td>
            <td>
              <button class="btn btn-guardar" onclick="actualizarCategoria(${cat.idCategoria})">Modificar</button>
              <button class="btn btn-cancelar" onclick="eliminarCategoria(${cat.idCategoria})">Eliminar</button>
            </td>
          </tr>
        `;
      });

      html += '</tbody></table>';
      lista.innerHTML = html;
    } catch (err) {
      lista.innerHTML = '<p>Error al cargar categorías.</p>';
      console.error(err);
    }
  }

  // 🔹 Guardar nueva categoría
  form.addEventListener('submit', async e => {
    e.preventDefault();

    const formData = new FormData(form);
    try {
      const res = await fetch('insertar_categoria.php', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        mostrarMensaje('✅ ' + data.message);
        form.reset();
        cargarCategorias();
      } else {
        mostrarMensaje('⚠️ ' + data.message, true);
      }
    } catch {
      mostrarMensaje('Error al conectar con el servidor.', true);
    }
  });

  // 🔹 Función global para actualizar
  window.actualizarCategoria = async (id) => {
    const nuevoNombre = document.getElementById(`cat-${id}`).value.trim();
    if (!nuevoNombre) {
      alert('El nombre no puede estar vacío.');
      return;
    }

    const formData = new FormData();
    formData.append('idCategoria', id);
    formData.append('nombre', nuevoNombre);

    try {
      const res = await fetch('actualizar_categoria.php', { method: 'POST', body: formData });
      const data = await res.json();
      alert(data.message);
      if (data.success) cargarCategorias();
    } catch {
      alert('Error al actualizar la categoría.');
    }
  };

  // 🔹 Función global para eliminar
  window.eliminarCategoria = async (id) => {
    if (!confirm('¿Seguro que deseas eliminar esta categoría?')) return;

    const formData = new FormData();
    formData.append('idCategoria', id);

    try {
      const res = await fetch('eliminar_categoria.php', { method: 'POST', body: formData });
      const data = await res.json();
      alert(data.message);
      if (data.success) cargarCategorias();
    } catch {
      alert('Error al eliminar la categoría.');
    }
  };

  // Cargar categorías al iniciar
  cargarCategorias();
});
