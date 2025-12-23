import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
let tblResponsables;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaResponsables')) {
        tblResponsables = configurarTabla('#tblResponsables', "Responsables/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'numero' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchResponsables',
            searchBtnId: 'searchBtnResponsables',
            clearSearchBtnId: 'clearSearchBtnResponsables'
        });

        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmResponsable);

        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionResponsable);

        document.querySelector('#tblResponsables tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigo = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarResponsable(codigo);
            }
        })
    }
});


function frmResponsable() {
    document.getElementById("title").innerHTML = "Agregar Responsable";
    document.getElementById("btnAccion").innerHTML = "Registrar";
    document.getElementById("codigo").readOnly = false;
    document.getElementById("frmResponsable").reset();
    $("#nuevoResponsable").modal("show");
}

function registrarOmodificarResponsable(e, accion) {
    e.preventDefault();
    const url = base_url + "Responsables/" + accion;
    const frm = document.getElementById("frmResponsable");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Responsable registrado" : "Responsable modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoResponsable").modal("hide");
            tblResponsables.ajax.reload();
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
function registrarResponsable(e) {
    registrarOmodificarResponsable(e, "registrar");
}
function modificarResponsable(e) {
    registrarOmodificarResponsable(e, "modificar");
}
function btnAccionResponsable(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarResponsable(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarResponsable(e);
    }
}
function btnEditarResponsable(codigoResponsable) {
    document.getElementById("title").innerHTML = "Editar Responsable";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "Responsables/editar/" + codigoResponsable;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("telefono").value = res.telefono;
        $("#nuevoResponsable").modal("show");
    })
}

export { frmResponsable, btnAccionResponsable, btnEditarResponsable } 