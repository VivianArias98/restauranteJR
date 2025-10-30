document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('formGasto');
  const msg = document.getElementById('msg');
  const lista = document.getElementById('listaGastos');

  // 🔹 Mostrar mensajes de éxito o error
  function mostrarMensaje(text, isError = false) {
    msg.textContent = text;
    msg.style.color = isError ? '#b00020' : '#006400';
    setTimeout(() => { msg.textContent = ''; }, 4000);
  }

  // 🔹 Evitar inyección de HTML
  function escapeHtml(str) {
    return str.replace(/[&<>'"]/g, function(tag) {
      const chars = { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' };
      return chars[tag] || tag;
    });
  }

  // 🔹 Formato de miles (pesos colombianos)
  function formatoMiles(num) {
    return num.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // 🔹 Cargar las categorías dinámicamente
  async function cargarCategorias() {
    try {
      const res = await fetch('listar_categoria.php');
      const data = await res.json();

      const select = document.getElementById('categoria');
      select.innerHTML = '<option value="">Seleccione una categoría...</option>';

      if (data.success && data.data.length > 0) {
        data.data.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.idCategoria;
          opt.textContent = cat.nombre;
          select.appendChild(opt);
        });
      } else {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'No hay categorías registradas';
        select.appendChild(opt);
      }
    } catch (err) {
      console.error('Error cargando categorías:', err);
    }
  }

  // 🔹 Cargar los registros de gastos
  async function cargarRegistros() {
    try {
      const res = await fetch('listar_gastos.php?limit=100');
      const data = await res.json();

      if (!data.success) {
        lista.innerHTML = '<p>Error al cargar registros</p>';
        return;
      }

      const rows = data.data;
      if (rows.length === 0) {
        lista.innerHTML = '<p>No hay registros aún.</p>';
        return;
      }

      let html = `
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Concepto</th>
              <th>Insumo</th>
              <th>Categoría</th>
              <th>Monto</th>
              <th>Fecha</th>
              <th>Medio</th>
              <th>Administrador</th>
            </tr>
          </thead>
          <tbody>
      `;

      rows.forEach((r) => {
        const admin = r.nombres ? (r.nombres + ' ' + (r.apellidos || '')) : '';
        html += `
          <tr>
            <td>${r.idRegistroGasto}</td>
            <td>${escapeHtml(r.concepto)}</td>
            <td>${escapeHtml(r.insumo || '')}</td>
            <td>${escapeHtml(r.categoria || '')}</td>
            <td>$${formatoMiles(Number(r.montoTotal))}</td>
            <td>${r.fecha}</td>
            <td>${r.medio || ''}</td>
            <td>${escapeHtml(admin)}</td>
          </tr>`;
      });

      html += '</tbody></table>';
      lista.innerHTML = html;
    } catch (e) {
      lista.innerHTML = '<p>Error de conexión al cargar registros.</p>';
    }
  }

  // 🔹 Enviar formulario de gasto
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    try {
      const res = await fetch('insertar_gasto.php', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        mostrarMensaje('Gasto registrado correctamente');
        form.reset();
        cargarRegistros();
      } else {
        mostrarMensaje(data.message || 'Error al guardar', true);
      }
    } catch (error) {
      mostrarMensaje('Error al enviar los datos.', true);
    }
  });

  // 🟡 CONTROL DEL REGISTRO DE CATEGORÍA
  const btnNuevaCat = document.getElementById('btnNuevaCategoria');
  const boxCategoria = document.getElementById('nuevaCategoriaBox');
  const cancelarCategoria = document.getElementById('cancelarCategoria');
  const guardarCategoria = document.getElementById('guardarCategoria');

  if (btnNuevaCat) {
    btnNuevaCat.addEventListener('click', () => {
      boxCategoria.style.display = 'block';
      document.getElementById('nombreCategoria').focus();
    });
  }

  if (cancelarCategoria) {
    cancelarCategoria.addEventListener('click', () => {
      boxCategoria.style.display = 'none';
      document.getElementById('nombreCategoria').value = '';
    });
  }

  // Guardar categoría (real con PHP)
  if (guardarCategoria) {
    guardarCategoria.addEventListener('click', async () => {
      const nombre = document.getElementById('nombreCategoria').value.trim();
      if (!nombre) {
        alert('Ingrese un nombre para la categoría');
        return;
      }

      try {
        const formData = new FormData();
        formData.append('nombre', nombre);

        const res = await fetch('insertar_categoria.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          alert(`✅ ${data.message}`);
          boxCategoria.style.display = 'none';
          document.getElementById('nombreCategoria').value = '';

          // 🔹 Actualizar lista de categorías automáticamente
          await cargarCategorias();
        } else {
          alert(`⚠️ ${data.message}`);
        }
      } catch (err) {
        alert('Error al conectar con el servidor.');
      }
    });
  }

  // 🔹 Cargar categorías y registros al iniciar
  cargarCategorias();
  cargarRegistros();
});
