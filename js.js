document.addEventListener("DOMContentLoaded", () => {
  cargarCajas();
  cargarCategorias();
  cargarGastos();

  document.getElementById("btnGuardar").addEventListener("click", guardarGasto);
  document.getElementById("btnNuevaCategoria").addEventListener("click", mostrarNuevaCategoria);
  document.getElementById("cancelarCategoria").addEventListener("click", ocultarNuevaCategoria);
  document.getElementById("guardarCategoria").addEventListener("click", guardarCategoria);
  document.getElementById("btnVolver").addEventListener("click", () => window.location.href = "home.html");
});

let insumosGasto = [];

// 🔹 Cargar cajas y mostrar saldo
function cargarCajas() {
  fetch("listar_caja.php")
    .then(r => r.json())
    .then(data => {
      const selectCaja = document.getElementById("caja");
      const saldoCaja = document.getElementById("saldoCaja");
      selectCaja.innerHTML = "";

      data.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.idCaja;
        opt.textContent = c.nombre;
        selectCaja.appendChild(opt);
      });

      selectCaja.addEventListener("change", () => {
        const caja = data.find(x => x.idCaja == selectCaja.value);
        saldoCaja.textContent = `Saldo actual: $${parseFloat(caja.saldo).toFixed(2)}`;
      });

      // Mostrar saldo inicial
      if (data.length > 0) {
        const caja = data[0];
        selectCaja.value = caja.idCaja;
        saldoCaja.textContent = `Saldo actual: $${parseFloat(caja.saldo).toFixed(2)}`;
      }
    });
}

// 🔹 Cargar categorías
function cargarCategorias() {
  fetch("listar_categoria.php")
    .then(r => r.json())
    .then(data => {
      const select = document.getElementById("categoria");
      select.innerHTML = `<option value="">Seleccione una categoría...</option>`;
      data.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.nombre;
        opt.textContent = c.nombre;
        select.appendChild(opt);
      });
    });
}

// 🔹 Mostrar formulario para nueva categoría
function mostrarNuevaCategoria() {
  document.getElementById("nuevaCategoriaBox").style.display = "block";
}

// 🔹 Ocultar formulario de nueva categoría
function ocultarNuevaCategoria() {
  document.getElementById("nuevaCategoriaBox").style.display = "none";
}

// 🔹 Guardar nueva categoría
function guardarCategoria() {
  const nombre = document.getElementById("nombreCategoria").value.trim();
  if (!nombre) return alert("Escribe un nombre para la categoría.");

  fetch("insertar_categoria.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "nombre=" + encodeURIComponent(nombre)
  })
  .then(r => r.text())
  .then(msg => {
    alert(msg);
    cargarCategorias();
    ocultarNuevaCategoria();
  });
}

// 🔹 Añadir varios insumos
const btnAgregarInsumo = document.createElement("button");
btnAgregarInsumo.textContent = "➕ Agregar insumo";
btnAgregarInsumo.className = "btn btn-secundario";
document.querySelector(".insumo-section").appendChild(btnAgregarInsumo);

const listaInsumos = document.createElement("div");
listaInsumos.id = "listaInsumos";
document.querySelector(".insumo-section").appendChild(listaInsumos);

btnAgregarInsumo.addEventListener("click", () => {
  const nombre = document.getElementById("insumo").value.trim();
  const categoria = document.getElementById("categoria").value;
  if (!nombre || !categoria) return alert("Completa el nombre del insumo y selecciona una categoría.");

  insumosGasto.push({ nombre, categoria });
  const item = document.createElement("p");
  item.textContent = `🟡 ${nombre} (${categoria})`;
  listaInsumos.appendChild(item);

  document.getElementById("insumo").value = "";
});

// 🔹 Guardar gasto
function guardarGasto(e) {
  e.preventDefault();

  const concepto = document.getElementById("concepto").value.trim();
  const monto = parseFloat(document.getElementById("monto").value);
  const medioPago = document.getElementById("medioPago").value;
  const observaciones = document.getElementById("observaciones").value.trim();
  const idCaja = document.getElementById("caja").value;

  if (!concepto || !monto || !medioPago || !idCaja) return alert("Completa todos los campos obligatorios.");

  fetch("insertar_gasto.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      concepto, monto, medioPago, observaciones, idCaja,
      insumos: JSON.stringify(insumosGasto)
    })
  })
  .then(r => r.text())
  .then(msg => {
    alert(msg);
    insumosGasto = [];
    document.getElementById("formGasto").reset();
    listaInsumos.innerHTML = "";
    cargarGastos();
    cargarCajas();
  });
}
function cargarGastos() {
  fetch("listar_gastos.php")
    .then(res => {
      if (!res.ok) throw new Error("Error al obtener los gastos");
      return res.json();
    })
    .then(data => {
      const cont = document.getElementById("listaGastos");
      cont.innerHTML = "";

      if (!data || data.length === 0) {
        cont.innerHTML = "<p>No hay gastos registrados todavía.</p>";
        return;
      }

      // Crear tabla
      let tabla = `
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Monto</th>
              <th>Medio de Pago</th>
              <th>Caja</th>
              <th>Observaciones</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.forEach(g => {
        tabla += `
          <tr>
            <td>${g.fecha}</td>
            <td>${g.concepto}</td>
            <td>$${parseFloat(g.montoTotal).toLocaleString("es-CO")}</td>
            <td>${g.medioPago}</td>
            <td>${g.caja}</td>
            <td>${g.observaciones ? g.observaciones : ""}</td>
          </tr>
        `;
      });

      tabla += "</tbody></table>";
      cont.innerHTML = tabla;
    })
    .catch(err => {
      console.error("Error cargando los gastos:", err);
      document.getElementById("listaGastos").innerHTML = "<p style='color:red'>Error al cargar los gastos.</p>";
    });
}

