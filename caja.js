document.addEventListener("DOMContentLoaded", () => {
  cargarCajas();
  document.getElementById("formIngreso").addEventListener("submit", registrarIngreso);
  document.getElementById("btnVolver").addEventListener("click", () => window.location.href = "html.html");
});

// 🔹 Cargar lista de cajas
function cargarCajas() {
  fetch("listar_cajas.php")
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById("selectCaja");
      const info = document.getElementById("infoCaja");
      select.innerHTML = "";

      if (!data || data.length === 0) {
        info.innerHTML = "<p style='color:red'>No hay cajas registradas.</p>";
        return;
      }

      // Llenar select
      data.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.idCaja;
        opt.textContent = `${c.nombre} (${c.estado})`;
        opt.dataset.saldo = c.saldo;
        select.appendChild(opt);
      });

      // Mostrar primera caja activa
      const activa = data.find(c => c.estado === "activa") || data[0];
      select.value = activa.idCaja;
      mostrarCaja(activa);

      // Evento cambio de caja
      select.addEventListener("change", () => {
        const seleccionada = data.find(c => c.idCaja == select.value);
        if (seleccionada) {
          mostrarCaja(seleccionada);
          alert(`💰 Cambiaste a la caja "${seleccionada.nombre}". Estado: ${seleccionada.estado.toUpperCase()}`);
        }
      });
    });
}

// 🔹 Mostrar datos de la caja
function mostrarCaja(caja) {
  const info = document.getElementById("infoCaja");
  info.innerHTML = `
    <h3>${caja.nombre}</h3>
    <p><strong>Estado:</strong> ${caja.estado}</p>
    <p><strong>Saldo actual:</strong> $${parseFloat(caja.saldo).toLocaleString("es-CO")}</p>
  `;
  cargarIngresos(caja.idCaja);
}

// 🔹 Registrar ingreso
function registrarIngreso(e) {
  e.preventDefault();
  const monto = parseFloat(document.getElementById("montoIngreso").value);
  const descripcion = document.getElementById("descripcionIngreso").value.trim();
  const idCaja = document.getElementById("selectCaja").value;

  if (!monto || monto <= 0) {
    alert("Ingrese un monto válido.");
    return;
  }

  fetch("registrar_ingreso.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ monto, descripcion, idCaja })
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        document.getElementById("formIngreso").reset();
        cargarCajas();
      }
    })
    .catch(err => console.error("Error:", err));
}

// 🔹 Cargar historial de ingresos
function cargarIngresos(idCaja) {
  fetch(`listar_ingresos.php?idCaja=${idCaja}`)
    .then(res => res.json())
    .then(data => {
      const cont = document.getElementById("listaIngresos");
      cont.innerHTML = "";

      if (!data || data.length === 0) {
        cont.innerHTML = "<p>No hay ingresos registrados en esta caja.</p>";
        return;
      }

      let tabla = `
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Descripción</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.forEach(i => {
        tabla += `
          <tr>
            <td>${i.fecha}</td>
            <td>${i.descripcion || "—"}</td>
            <td>$${parseFloat(i.monto).toLocaleString("es-CO")}</td>
          </tr>
        `;
      });

      tabla += "</tbody></table>";
      cont.innerHTML = tabla;
    });
}
