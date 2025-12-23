import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
let tblTiposDocumentos;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaTipoDocumentos')) {
        tblTiposDocumentos = configurarTabla('#tblTiposDocumentos', "TiposDocumentos/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchDocumentos',
            searchBtnId: 'searchBtnDocumentos',
            clearSearchBtnId: 'clearSearchBtnDocumentos'
        });

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccionDocumentos');
        btnAccion.addEventListener('click', btnAccionTiposDocumento);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblTiposDocumentos tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoTipoDocumento = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarDocumento(codigoTipoDocumento);
            }
        });

        // Asignar evento al botón de acción cancelar
        const btnCancelar = document.getElementById('btn-cancelar');
        btnCancelar.addEventListener('click', btnCancelarD);
    }
})

/// Tipos De Documentos
function btnCancelarD() {
    document.getElementById("btnAccionDocumentos").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("frmTiposDocumentos").reset();
}
function registrarOmodificarDocumento(e, accion) {
    e.preventDefault();
    const url = base_url + "TiposDocumentos/" + accion;
    const frm = document.getElementById("frmTiposDocumentos");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Documento registrado" : "Documento modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            tblTiposDocumentos.ajax.reload();
            document.getElementById("btnAccionDocumentos").innerHTML = "Registrar"
            document.getElementById("codigo").readOnly = false;
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
function registrarDocumento(e) {
    registrarOmodificarDocumento(e, "registrar");
}
function modificarDocumento(e) {
    registrarOmodificarDocumento(e, "modificar");
}
function btnAccionTiposDocumento(e) {
    var boton = document.getElementById("btnAccionDocumentos");
    if (boton.innerHTML == "Registrar") {
        registrarDocumento(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarDocumento(e);
    }
}
function btnEditarDocumento(codigoTipoDocumento) {
    document.getElementById("btnAccionDocumentos").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "TiposDocumentos/editar/" + codigoTipoDocumento;
    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
    })
}

export { btnCancelarD, btnAccionTiposDocumento, btnEditarDocumento }