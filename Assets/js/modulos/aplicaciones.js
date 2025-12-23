import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
import { tblTiposMovimientos } from './tipoMovimientos.js';
let tblAplicaciones;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaAplicaciones')) {
        tblAplicaciones = configurarTabla('#tblAplicaciones', "Aplicaciones/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchAplicaciones',
            searchBtnId: 'searchBtnAplicaciones',
            clearSearchBtnId: 'clearSearchBtnAplicaciones'
        });
        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccionAp');
        btnAccion.addEventListener('click', btnAccionAplicacion);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblAplicaciones tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const aplicacionCodigo = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarAplicacion(aplicacionCodigo);
            }
        });

        // Asignar evento al botón de acción cancelar
        const btnCancelar = document.getElementById('btn-cancelarA');
        btnCancelar.addEventListener('click', btnCancelarAp);
    }
})

/// Aplicaciones
function btnCancelarAp() {
    document.getElementById("btnAccionAp").innerHTML = "Registrar"
    document.getElementById("aplicacionCodigo").readOnly = false;
    document.getElementById("frmAplicacion").reset();
}
function registrarOmodificarAplicacion(e, accion) {
    e.preventDefault();
    const url = base_url + "Aplicaciones/" + accion;
    const frm = document.getElementById("frmAplicacion");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Aplicación registrada" : "Aplicación modificada",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            tblAplicaciones.ajax.reload();
            tblTiposMovimientos.ajax.reload();
            document.getElementById("btnAccionAp").innerHTML = "Registrar"
            document.getElementById("aplicacionCodigo").readOnly = false;
        } else {
            Swal.fire({
                position: "top-end",
                icon: "error",
                title: res,
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
}
function registrarAplicacion(e) {
    registrarOmodificarAplicacion(e, "registrar");
}
function modificarAplicacion(e) {
    registrarOmodificarAplicacion(e, "modificar");
}
function btnAccionAplicacion(e) {
    var boton = document.getElementById("btnAccionAp");
    if (boton.innerHTML == "Registrar") {
        registrarAplicacion(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarAplicacion(e);
    }
}
function btnEditarAplicacion(aplicacionCodigo) {
    document.getElementById("btnAccionAp").innerHTML = "Modificar";
    document.getElementById("aplicacionCodigo").readOnly = true;
    const url = base_url + "Aplicaciones/editar/" + aplicacionCodigo;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("aplicacionCodigo").value = res.codigo;
        document.getElementById("nombreAplicacion").value = res.nombre;
    });
}

export { btnCancelarAp, btnAccionAplicacion, btnEditarAplicacion }