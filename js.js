document.addEventListener("DOMContentLoaded", () => {
  cargarCajas();
  cargarCategorias();
  cargarInsumos();
  cargarGastos();

  document.getElementById("btnGuardar").addEventListener("click", guardarGasto);
  document.getElementById("btnNuevaCategoria").addEventListener("click", mostrarNuevaCategoria);
  document.getElementById("cancelarCategoria").addEventListener("click", ocultarNuevaCategoria);
  document.getElementById("guardarCategoria").addEventListener("click", guardarCategoria);
  document.getElementById("btnVolver").addEventListener("click", () => window.location.href = "home.html");
});

let insumosGasto = [];
let listaInsumos = []; // 🔸 guardará todos los insumos con sus categorías

// 🔹 Cargar cajas
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

// 🔹 Cargar insumos (autocompletar)
function cargarInsumos() {
  fetch("listar_insumo.php")
    .then(r => r.json())
    .then(data => {
      listaInsumos = data; // guardar para búsqueda
      const datalist = document.getElementById("listaInsumosSugeridos");
      datalist.innerHTML = "";
      data.forEach(i => {
        const opt = document.createElement("option");
        opt.value = i.insumo;
        opt.textContent = `${i.insumo} (${i.categoria})`;
        datalist.appendChild(opt);
      });
    })
    .catch(err => console.error("Error cargando insumos:", err));
}

// 🔹 Autocompletar categoría al escribir un insumo
document.addEventListener("input", e => {
  if (e.target.id === "insumo") {
    const nombre = e.target.value.trim().toLowerCase();
    const encontrado = listaInsumos.find(i => i.insumo.toLowerCase() === nombre);
    const selectCat = document.getElementById("categoria");

    if (encontrado) {
      // Si el insumo existe → selecciona automáticamente su categoría
      for (let opt of selectCat.options) {
        if (opt.value === encontrado.categoria) {
          opt.selected = true;
          break;
        }
      }
      selectCat.disabled = true;
    } else {
      // Si no existe → habilita para elegir categoría manualmente
      selectCat.disabled = false;
      selectCat.value = "";
    }
  }
});

// 🔹 Mostrar / ocultar categoría
function mostrarNuevaCategoria() {
  document.getElementById("nuevaCategoriaBox").style.display = "block";
}
function ocultarNuevaCategoria() {
  document.getElementById("nuevaCategoriaBox").style.display = "none";
}

// 🔹 Guardar categoría
function guardarCategoria() {
  const nombre = document.getElementById("nombreCategoria").value.trim();
  if (!nombre) return alert("Escribe un nombre para la categoría.");

  fetch("insertar_categoria.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "nombre=" + encodeURIComponent(nombre)
  })
    .then(r => r.json())
    .then(data => {
      alert(data.message || "Categoría registrada correctamente.");
      if (data.success) {
        cargarCategorias();
        ocultarNuevaCategoria();
      }
    });
}

// 🔹 Crear botón para agregar insumo
const btnAgregarInsumo = document.createElement("button");
btnAgregarInsumo.textContent = "➕ Agregar insumo";
btnAgregarInsumo.className = "btn btn-secundario";
document.querySelector(".insumo-section").appendChild(btnAgregarInsumo);

const lista = document.createElement("div");
lista.id = "listaInsumos";
document.querySelector(".insumo-section").appendChild(lista);

// 🔹 Agregar insumo
btnAgregarInsumo.addEventListener("click", () => {
  const nombre = document.getElementById("insumo").value.trim();
  const categoria = document.getElementById("categoria").value;

  if (!nombre) return alert("Escribe o selecciona un insumo.");
  if (!categoria) return alert("Selecciona una categoría válida.");

  insumosGasto.push({ nombre, categoria });

  const item = document.createElement("p");
  item.textContent = `🟢 ${nombre} (${categoria})`;
  lista.appendChild(item);

  document.getElementById("insumo").value = "";
  document.getElementById("categoria").disabled = false;
  document.getElementById("categoria").value = "";
});

// 🔹 Guardar gasto
function guardarGasto(e) {
  e.preventDefault();

  const concepto = document.getElementById("concepto").value.trim();
  const monto = parseFloat(document.getElementById("monto").value);
  const medioPago = document.getElementById("medioPago").value;
  const observaciones = document.getElementById("observaciones").value.trim();
  const idCaja = document.getElementById("caja").value;

  if (!concepto || !monto || !medioPago || !idCaja)
    return alert("Completa todos los campos obligatorios.");

  fetch("insertar_gasto.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      concepto, monto, medioPago, observaciones, idCaja,
      insumos: JSON.stringify(insumosGasto)
    })
  })
    .then(r => r.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        insumosGasto = [];
        document.getElementById("formGasto").reset();
        lista.innerHTML = "";
        cargarInsumos();
        cargarGastos();
        cargarCajas();
      }
    })
    .catch(err => {
      console.error("Error al guardar gasto:", err);
      alert("❌ Error al guardar el gasto.");
    });
}

// 🔹 Cargar lista de gastos
function cargarGastos() {
  fetch("listar_gastos.php")
    .then(res => res.json())
    .then(data => {
      const cont = document.getElementById("listaGastos");
      cont.innerHTML = "";

      if (!data || data.length === 0) {
        cont.innerHTML = "<p>No hay gastos registrados todavía.</p>";
        return;
      }

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
            <td>${g.observaciones || ""}</td>
          </tr>
        `;
      });

      tabla += "</tbody></table>";
      cont.innerHTML = tabla;
    });
}
