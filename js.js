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
let listaInsumos = [];

// ✅ Ir a la sección de caja
document.getElementById("btnCaja").addEventListener("click", () => {
  window.location.href = "caja.html";
});

// 🔹 Cargar cajas
function cargarCajas() {
  fetch("listar_caja.php")
    .then(r => r.json())
    .then(data => {
      const selectCaja = document.getElementById("caja");
      const saldoCaja = document.getElementById("saldoCaja");
      selectCaja.innerHTML = "";

      if (!data || data.length === 0) {
        saldoCaja.textContent = "⚠️ No hay cajas registradas.";
        return;
      }

      data.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.idCaja;
        opt.textContent = `${c.nombre}`;
        opt.dataset.saldo = c.saldo;
        selectCaja.appendChild(opt);
      });

      const cajaActiva = localStorage.getItem("cajaActiva");
      if (cajaActiva) {
        const opcionGuardada = [...selectCaja.options].find(o => o.value === cajaActiva);
        if (opcionGuardada) {
          opcionGuardada.selected = true;
          const saldo = parseFloat(opcionGuardada.dataset.saldo);
          saldoCaja.textContent = `Saldo actual: $${saldo.toLocaleString("es-CO")}`;
        }
      } else {
        const primera = data[0];
        selectCaja.value = primera.idCaja;
        saldoCaja.textContent = `Saldo actual: $${parseFloat(primera.saldo).toLocaleString("es-CO")}`;
        localStorage.setItem("cajaActiva", primera.idCaja);
      }

      selectCaja.addEventListener("change", () => {
        const selectedOption = selectCaja.options[selectCaja.selectedIndex];
        const nombreCaja = selectedOption.textContent;
        const saldo = parseFloat(selectedOption.dataset.saldo);
        saldoCaja.textContent = `Saldo actual: $${saldo.toLocaleString("es-CO")}`;
        alert(`💰 Cambiaste a la caja "${nombreCaja}".\nSaldo disponible: $${saldo.toLocaleString("es-CO")}`);
        localStorage.setItem("cajaActiva", selectedOption.value);
      });
    })
    .catch(err => {
      console.error("Error al cargar cajas:", err);
      document.getElementById("saldoCaja").textContent = "❌ Error al cargar cajas.";
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

// 🔹 Cargar insumos
function cargarInsumos() {
  fetch("listar_insumo.php")
    .then(r => r.json())
    .then(data => {
      listaInsumos = data;
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

// 🔹 Autocompletar categoría
document.addEventListener("input", e => {
  if (e.target.id === "insumo") {
    const nombre = e.target.value.trim().toLowerCase();
    const encontrado = listaInsumos.find(i => i.insumo.toLowerCase() === nombre);
    const selectCat = document.getElementById("categoria");

    if (encontrado) {
      for (let opt of selectCat.options) {
        if (opt.value === encontrado.categoria) {
          opt.selected = true;
          break;
        }
      }
      selectCat.disabled = true;
    } else {
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
    })
    .catch(() => alert("❌ Error al registrar la categoría."));
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
  const editId = document.getElementById("formGasto").dataset.editId || "";

  if (!concepto || !monto || !medioPago || !idCaja)
    return alert("Completa todos los campos obligatorios.");

  const url = editId ? "actualizar_gasto.php" : "insertar_gasto.php";

  fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      id: editId,
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
        delete document.getElementById("formGasto").dataset.editId;
        document.getElementById("editMsg")?.remove();
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

      if (!Array.isArray(data) || data.length === 0) {
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
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.forEach(g => {
        const eliminado = g.eliminado == 1 || (g.observaciones && g.observaciones.includes("ELIMINADO"));
        const estilo = eliminado ? "style='color:red; text-decoration: line-through;'" : "";
        const estado = eliminado ? "❌ Eliminado" : "✅ Activo";

        tabla += `
          <tr ${estilo}>
            <td>${g.fecha}</td>
            <td>${g.concepto}</td>
            <td>$${parseFloat(g.montoTotal).toLocaleString("es-CO")}</td>
            <td>${g.medioPago}</td>
            <td>${g.caja}</td>
            <td>${g.observaciones || ''}</td>
            <td>${estado}</td>
            <td>
              ${eliminado
                ? "<small>(Sin acciones)</small>"
                : `
                  <button class="btn-editar" data-id="${g.idRegistroGasto}">✏️</button>
                  <button class="btn-eliminar" data-id="${g.idRegistroGasto}" data-monto="${g.montoTotal}">🗑️</button>
                `
              }
            </td>
          </tr>
        `;
      });

      tabla += "</tbody></table>";
      cont.innerHTML = tabla;
    })
    .catch(err => {
      console.error("Error cargando los gastos:", err);
      document.getElementById("listaGastos").innerHTML =
        "<p style='color:red'>Error al cargar los gastos.</p>";
    });
}

// 🔹 Editar / Eliminar
document.addEventListener("click", async (e) => {
  // 🗑️ Eliminar gasto
  if (e.target.classList.contains("btn-eliminar")) {
    const id = e.target.dataset.id?.trim();
    const monto = parseFloat(e.target.dataset.monto);

    if (!id || isNaN(Number(id))) {
      alert("⚠️ No se pudo obtener el ID del gasto para eliminar.");
      return;
    }

    const motivo = prompt("Ingrese el motivo de eliminación (dinero devuelto a caja):");
    if (!motivo || motivo.trim() === "") return alert("Debe ingresar un motivo.");
    if (confirm(`¿Seguro que desea eliminar el gasto #${id}? Se devolverán $${monto.toFixed(2)} a la caja.`)) {
      try {
        const res = await fetch("eliminar_gasto.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ id, motivo, monto })
        });

        const data = await res.json().catch(() => ({}));
        alert(data.message || "Gasto eliminado correctamente.");
        cargarGastos();
        cargarCajas();
      } catch (error) {
        console.error("Error eliminando gasto:", error);
        alert("❌ Error al intentar eliminar el gasto.");
      }
    }
  }

  // ✏️ Editar gasto
  if (e.target.classList.contains("btn-editar")) {
    let id = e.target.dataset.id;
    if (!id) {
      alert("⚠️ El ID del gasto no se encontró.");
      return;
    }

    id = id.replace(/[^0-9]/g, "");
    if (!id) {
      alert("⚠️ ID inválido del gasto.");
      return;
    }

    try {
      const url = `obtener_gasto.php?id=${id}`;
      console.log("📡 Solicitando:", url);

      const res = await fetch(url);
      if (!res.ok) throw new Error("Servidor no respondió correctamente.");

      const gasto = await res.json();
      if (!gasto || gasto.error) {
        alert(gasto?.error || "No se encontró el gasto especificado.");
        return;
      }

      document.getElementById("concepto").value = gasto.concepto;
      document.getElementById("monto").value = gasto.montoTotal;
      document.getElementById("medioPago").value = gasto.idMedioPago;
      document.getElementById("observaciones").value = gasto.observaciones || "";
      document.getElementById("caja").value = gasto.idCaja;

      const lista = document.getElementById("listaInsumos");
      lista.innerHTML = "";
      insumosGasto = [];

      if (gasto.insumos && Array.isArray(gasto.insumos)) {
        gasto.insumos.forEach(i => {
          insumosGasto.push({ nombre: i.insumo, categoria: i.categoria });
          const p = document.createElement("p");
          p.textContent = `🟢 ${i.insumo} (${i.categoria})`;
          lista.appendChild(p);
        });
      }

      const form = document.getElementById("formGasto");
      form.dataset.editId = id;

      document.getElementById("editMsg")?.remove();
      const msg = document.createElement("div");
      msg.id = "editMsg";
      msg.textContent = `🟡 Editando gasto #${id}`;
      msg.style.background = "#fff3cd";
      msg.style.color = "#856404";
      msg.style.padding = "10px";
      msg.style.margin = "10px 0";
      msg.style.borderRadius = "8px";
      document.querySelector(".form-card").prepend(msg);

      window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (error) {
      console.error("❌ Error cargando gasto:", error);
      alert("Error al obtener los datos del gasto.");
    }
  }
});
