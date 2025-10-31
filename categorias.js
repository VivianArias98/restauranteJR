
document.addEventListener("DOMContentLoaded", () => {
  const lista = document.getElementById("listaCategorias");
  const form = document.getElementById("formCategoria");
  const msg = document.getElementById("msg");

  const modal = document.getElementById("modalEditar");
  const editarId = document.getElementById("editarId");
  const editarNombre = document.getElementById("editarNombre");
  const editarDescripcion = document.getElementById("editarDescripcion");

  const btnGuardarCambios = document.getElementById("guardarCambios");
  const btnCerrarModal = document.getElementById("cerrarModal");

  // 🔹 Función para cargar categorías
  function cargarCategorias() {
    fetch("listar_categoria.php")
      .then(res => res.json())
      .then(data => {
        console.log("📦 Datos recibidos desde listar_categoria.php:", data);

        if (!Array.isArray(data) || data.length === 0) {
          lista.innerHTML = "<p style='text-align:center;'>No hay categorías registradas.</p>";
          return;
        }

        let html = `
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
        `;

        data.forEach(cat => {
          // Sanitizar valores para evitar errores en HTML
          const id = cat.idCategoria ?? "";
          const nombre = (cat.nombre ?? "").replace(/'/g, "&#39;");
          const descripcion = (cat.descripcion ?? "").replace(/'/g, "&#39;");

          html += `
            <tr>
              <td>${id}</td>
              <td>${nombre}</td>
              <td>${descripcion}</td>
              <td>
                <button class="btn btn-guardar" onclick="editarCategoria(${id}, '${nombre}', '${descripcion}')">Editar</button>
                <button class="btn btn-cancelar" onclick="eliminarCategoria(${id})">Eliminar</button>
              </td>
            </tr>
          `;
        });

        html += `</tbody></table>`;
        lista.innerHTML = html;
      })
      .catch(err => {
        console.error("❌ Error al cargar categorías:", err);
        lista.innerHTML = "<p style='color:red;'>Error al cargar categorías.</p>";
      });
  }

  // 🔹 Cargar categorías al iniciar
  cargarCategorias();

  // 🔹 Insertar nueva categoría
  form.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(form);

    fetch("insertar_categoria.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        console.log("💾 Respuesta al insertar:", data);

        msg.textContent = data.message;
        msg.style.color = data.success ? "green" : "red";

        if (data.success) {
          form.reset();
          setTimeout(() => {
            msg.textContent = "";
          }, 2000);

          // 🔁 Recargar lista inmediatamente
          cargarCategorias();
        }
      })
      .catch(err => {
        console.error("❌ Error al insertar:", err);
        msg.textContent = "Error al registrar la categoría.";
        msg.style.color = "red";
      });
  });

  // 🔹 Editar categoría
  window.editarCategoria = function (id, nombre, descripcion) {
    modal.style.display = "flex";
    editarId.value = id;
    editarNombre.value = nombre;
    editarDescripcion.value = descripcion;
  };

  // 🔹 Guardar cambios en edición
  btnGuardarCambios.addEventListener("click", () => {
    const formData = new FormData();
    formData.append("id", editarId.value);
    formData.append("nombre", editarNombre.value);
    formData.append("descripcion", editarDescripcion.value);

    fetch("actualizar_categoria.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        modal.style.display = "none";
        cargarCategorias();
      })
      .catch(err => console.error("❌ Error al actualizar:", err));
  });

  btnCerrarModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // 🔹 Eliminar categoría
  window.eliminarCategoria = function (id) {
    if (confirm("¿Seguro que deseas eliminar esta categoría?")) {
      const formData = new FormData();
      formData.append("id", id);

      fetch("eliminar_categoria.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          alert(data.message);
          cargarCategorias();
        })
        .catch(err => console.error("❌ Error al eliminar:", err));
    }
  };
});
