import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
let tblAgrupaciones;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaAgrupaciones')) {
        tblAgrupaciones = configurarTabla('#tblAgrupaciones', "Agrupaciones/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchAgrupaciones',
            searchBtnId: 'searchBtnAgrupaciones',
            clearSearchBtnId: 'clearSearchBtnAgrupaciones'
        });

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion2');
        btnAccion.addEventListener('click', btnAccionAgrupacion);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblAgrupaciones tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const agrupacionCodigo = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarAgrupacion(agrupacionCodigo);
            }
        });

        // Asignar evento al botón de acción cancelar
        const btnCancelar2 = document.getElementById('btn-cancelar2');
        btnCancelar2.addEventListener('click', btnCancelarAg);
    }
});
/// Agrupaciones
function btnCancelarAg() {
    document.getElementById("btnAccion2").innerHTML = "Registrar"
    document.getElementById("agrupacionCodigo").readOnly = false;
    document.getElementById("frmAgrupaciones").reset();
}
function registrarOmodificarAgrupacion(e, accion) {
    e.preventDefault();
    const url = base_url + "Agrupaciones/" + accion;
    const frm = document.getElementById("frmAgrupaciones");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Agrupación registrada" : "Agrupación modificada",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            tblAgrupaciones.ajax.reload();
            document.getElementById("btnAccion2").innerHTML = "Registrar"
            document.getElementById("agrupacionCodigo").readOnly = false;
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
function registrarAgrupacion(e) {
    registrarOmodificarAgrupacion(e, "registrar");
}
function modificarAgrupacion(e) {
    registrarOmodificarAgrupacion(e, "modificar");
}
function btnAccionAgrupacion(e) {
    var boton = document.getElementById("btnAccion2");
    if (boton.innerHTML == "Registrar") {
        registrarAgrupacion(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarAgrupacion(e);
    }
}
function btnEditarAgrupacion(codigoAgrupacion) {
    document.getElementById("btnAccion2").innerHTML = "Modificar";
    document.getElementById("agrupacionCodigo").readOnly = true;
    const url = base_url + "Agrupaciones/editar/" + codigoAgrupacion;
    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("agrupacionCodigo").value = res.codigo;
        document.getElementById("nombreAgrupacion").value = res.nombre;
    })
}
export { btnCancelarAg, btnAccionAgrupacion, btnEditarAgrupacion }
