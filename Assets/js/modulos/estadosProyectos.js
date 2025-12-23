import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
let tblEstadoProyecto;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaEstadoProyectos')) {
        tblEstadoProyecto = configurarTabla('#tblEstadoProyecto', "EstadosProyectos/listar", [
            { 'data': 'codigo' },
            { 'data': 'estado' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchEstado',
            searchBtnId: 'searchBtnEstado',
            clearSearchBtnId: 'clearSearchBtnEstado'
        });

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionEstadoProyecto);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblEstadoProyecto tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoEstado = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarEstadoProyecto(codigoEstado);
            }
        });

        // Asignar evento al botón de acción cancelar
        const btnCancelar = document.getElementById('btn-cancelar');
        btnCancelar.addEventListener('click', btnCancelarEstado);
    }
});

/// Agrupaciones
function btnCancelarEstado() {
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigoEstadoProyecto").readOnly = false;
    document.getElementById("frmEstadoProyectos").reset();
}
function registrarOmodificarEstadoProyecto(e, accion) {
    e.preventDefault();
    const url = base_url + "EstadosProyectos/" + accion;
    const frm = document.getElementById("frmEstadoProyectos");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Estado registrado" : "Estado modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            tblEstadoProyecto.ajax.reload();
            document.getElementById("btnAccion").innerHTML = "Registrar"
            document.getElementById("codigoEstadoProyecto").readOnly = false;
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
function registrarEstadoProyecto(e) {
    registrarOmodificarEstadoProyecto(e, "registrar");
}
function modificarEstadoProyecto(e) {
    registrarOmodificarEstadoProyecto(e, "modificar");
}
function btnAccionEstadoProyecto(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarEstadoProyecto(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarEstadoProyecto(e);
    }
}
function btnEditarEstadoProyecto(codigoEstado) {
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigoEstadoProyecto").readOnly = true;
    const url = base_url + "EstadosProyectos/editar/" + codigoEstado;
    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigoEstadoProyecto").value = res.codigo;
        document.getElementById("nombreEstado").value = res.nombre;
    })
}

export { btnCancelarEstado, btnAccionEstadoProyecto, btnEditarEstadoProyecto}