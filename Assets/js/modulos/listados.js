import { configurarTabla, asignarEvento, enviarPeticion } from "../utilidades/tablePeticion.js";
let fe;
let ccf;
let nc;
let fse;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaListado')) {
        fe = configurarTabla('#tblFacturas', "Listados/listarFe", [
            { 'data': 'correlativo' },
            { 'data': 'codigo' },
            { 'data': 'fecha' },
            { 'data': 'cliente' },
            { 'data': 'estado' },
            { 'data': 'invalidar' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchFe',
            searchBtnId: 'searchBtnFe',
            clearSearchBtnId: 'clearSearchBtnFe'
        });

        ccf = configurarTabla('#tblComprobatesCF', "Listados/listarCcf", [
            { 'data': 'correlativo' },
            { 'data': 'codigo' },
            { 'data': 'fecha' },
            { 'data': 'cliente' },
            { 'data': 'estado' },
            { 'data': 'invalidar' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchCcfe',
            searchBtnId: 'searchBtnCcfe',
            clearSearchBtnId: 'clearSearchBtnCcfe'
        });

        nc = configurarTabla('#tblNotaCredito', "Listados/listarNc", [
            { 'data': 'correlativo' },
            { 'data': 'codigo' },
            { 'data': 'fecha' },
            { 'data': 'cliente' },
            { 'data': 'estado' },
            { 'data': 'invalidar' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchNc',
            searchBtnId: 'searchBtnNc',
            clearSearchBtnId: 'clearSearchBtnNc'
        });
        
        fse = configurarTabla('#tblSujetoExcluido', "Listados/listarFse", [
            { 'data': 'correlativo' },
            { 'data': 'codigo' },
            { 'data': 'fecha' },
            { 'data': 'cliente' },
            { 'data': 'estado' },
            { 'data': 'invalidar' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchFse',
            searchBtnId: 'searchBtnFse',
            clearSearchBtnId: 'clearSearchBtnFse'
        });
    }

    // Delegación de eventos para los botones de vista pdf
    document.querySelector('#tblFacturas tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const numControl = e.target.closest('.btn-editar').getAttribute('data-id');
            generarPdfVistaFe(numControl);
        }
    });
    document.querySelector('#tblFacturas tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-json')) {
            const numControlJS = e.target.closest('.btn-json').getAttribute('data-id');
            generarPdfVistaFeJson(numControlJS);
        }
    });
    document.querySelector('#tblFacturas tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-invalidar')) {
            const numControlIn = e.target.closest('.btn-invalidar').getAttribute('data-id');
            generarEventoInvalidar(numControlIn);
        }
    });
    document.querySelector('#tblFacturas tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-enviar-correo')) {
            const numControlEnviar = e.target.closest('.btn-enviar-correo').getAttribute('data-id');
            generarEventoCorreo(numControlEnviar);
        }
    });

    // Delegación de eventos para los botones de vista pdf ccf
    document.querySelector('#tblComprobatesCF tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const numControlC = e.target.closest('.btn-editar').getAttribute('data-id');
            generarPdfVistaCcf(numControlC);
        }
    });
    document.querySelector('#tblComprobatesCF tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-json')) {
            const numControlJSCcf = e.target.closest('.btn-json').getAttribute('data-id');
            generarPdfVistaCcfJson(numControlJSCcf);
        }
    });
    document.querySelector('#tblComprobatesCF tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-invalidar')) {
            const numControlInC = e.target.closest('.btn-invalidar').getAttribute('data-id');
            generarEventoInvalidar(numControlInC);
        }
    });
    document.querySelector('#tblComprobatesCF tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-enviar-correo')) {
            const numControlEnviarCcfe = e.target.closest('.btn-enviar-correo').getAttribute('data-id');
            generarEventoCorreo(numControlEnviarCcfe);
        }
    });


    // Delegación de eventos para los botones de vista pdf nc
    document.querySelector('#tblNotaCredito tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const numControlNc = e.target.closest('.btn-editar').getAttribute('data-id');
            generarPdfVistaNc(numControlNc);
        }
    });
    document.querySelector('#tblNotaCredito tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-json')) {
            const numControlJSNc = e.target.closest('.btn-json').getAttribute('data-id');
            generarPdfVistaNcJson(numControlJSNc);
        }
    });
    document.querySelector('#tblNotaCredito tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-invalidar')) {
            const numControlInNc = e.target.closest('.btn-invalidar').getAttribute('data-id');
            generarEventoInvalidar(numControlInNc);
        }
    });
    document.querySelector('#tblNotaCredito tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-enviar-correo')) {
            const numControlEnviarNc = e.target.closest('.btn-enviar-correo').getAttribute('data-id');
            generarEventoCorreo(numControlEnviarNc);
        }
    });
    
    // Delegación de eventos para los botones de vista pdf nc
    document.querySelector('#tblSujetoExcluido tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const numControlFse = e.target.closest('.btn-editar').getAttribute('data-id');
            generarPdfVistaFse(numControlFse);
        }
    });
    document.querySelector('#tblSujetoExcluido tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-json')) {
            const numControlJSFse = e.target.closest('.btn-json').getAttribute('data-id');
            generarPdfVistaFseJson(numControlJSFse);
        }
    });
    document.querySelector('#tblSujetoExcluido tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-invalidar')) {
            const numControlInFse = e.target.closest('.btn-invalidar').getAttribute('data-id');
            generarEventoInvalidar(numControlInFse);
        }
    });
    document.querySelector('#tblSujetoExcluido tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-enviar-correo')) {
            const numControlEnviarFse = e.target.closest('.btn-enviar-correo').getAttribute('data-id');
            generarEventoCorreo(numControlEnviarFse);
        }
    });
    

    asignarEvento('btnGenerar', 'click', enviarEvento)
    asignarEvento('cancelarEv', 'click', cancelarEvento)
    asignarEvento('enviarCorreo', 'click', enviarFacturaPorCorreo)
    asignarEvento('cancelarEnviar', 'click', cancelarCorreo)

    document.getElementById('codigoGen').readOnly = true;
    document.getElementById('selloRecepcion').readOnly = true;
    document.getElementById('numeroControl').readOnly = true;
    document.getElementById('codigoGeneracionRemmplazo').readOnly = true;
    document.getElementById('cliente').readOnly = true;
    document.getElementById('correoE').readOnly = true;
    document.getElementById('control').readOnly = true;
    document.getElementById('dte').readOnly = true;
});
$(document).ready(function () {
    $('#selectTipoI').select2({
        placeholder: "seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Listados/seleccionarTipoI", // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (data) {
                // Mapea los resultados para adaptarlos al formato de Select2
                return {
                    results: data.map(tipoInvalidar => ({
                        id: tipoInvalidar.codigo,
                        text: tipoInvalidar.nombre
                    }))
                };
            },
            cache: true
        }
    })
    $("#selectTipoI").on("select2:select", function (e) {
        const tipoAnular = e.params.data.id;

        if (tipoAnular === 1 || tipoAnular == 3) {
            document.getElementById('codigoGeneracionRemmplazo').readOnly = false;
        } else if (tipoAnular === 2)
            document.getElementById('codigoGeneracionRemmplazo').readOnly = true;
    });
    $("#selectTipoI").on("select2:unselect", function (e) {
        document.getElementById('codigoGeneracionRemmplazo').readOnly = true;
        document.getElementById('codigoGeneracionRemmplazo').value = '';
    });
})

function generarPdfVistaFe(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfFe/' + numeroControl;
    window.open(ruta);
}
function generarPdfVistaFeJson(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfFeJSON/' + numeroControl;
    window.open(ruta);
}

function generarPdfVistaCcf(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfCcf/' + numeroControl;
    window.open(ruta);
}
function generarPdfVistaCcfJson(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfCcfJSON/' + numeroControl;
    window.open(ruta);
}

function generarPdfVistaNc(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfNc/' + numeroControl;
    window.open(ruta);
}
function generarPdfVistaNcJson(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfNcfJSON/' + numeroControl;
    window.open(ruta);
}

function generarPdfVistaFse(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfFse/' + numeroControl;
    window.open(ruta);
}
function generarPdfVistaFseJson(numeroControl) {
    const ruta = base_url + 'Listados/generarPdfFseJSON/' + numeroControl;
    window.open(ruta);
}


// evento de invalidacion
function generarEventoInvalidar(numeroControl) {
    const url = base_url + "Listados/generarEv/" + numeroControl;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("numeroControl").value = res.numeroControl;
        document.getElementById("codigoGen").value = res.codigoGeneracion;
        document.getElementById("selloRecepcion").value = res.selloRecepcion;
        document.getElementById("idFacturaInvalidar").value = res.id;
        $("#invalidarEvento").modal({
            backdrop: 'static',
            keyboard: false
        });


    })
}

function cancelarEvento() {
    document.getElementById('frmInvalidar').reset();
    $('#selectTipoI').val(null).trigger('change').empty().prop('disabled', false);
    document.getElementById('codigoGeneracionRemmplazo').readOnly = true;
}

function enviarEvento(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Emitiendo DTE...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = base_url + "Listados/generarInvalidacion";
    const frm = document.getElementById("frmInvalidar");

    enviarPeticion(url, "POST", frm, function (res) {
        if (res.status === "success") {
            Swal.fire({
                title: 'Documento emitido e Invalidado Correctamente',
                icon: 'success',
                html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(res.emision, null, 2)}</pre>`,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                frm.reset();
                $("#invalidarEvento").modal("hide");
                $('#selectTipoI').val(null).trigger('change').empty().prop('disabled', false);
                document.getElementById('codigoGeneracionRemmplazo').readOnly = true;
                fe.ajax.reload();
                ccf.ajax.reload();
                nc.ajax.reload();
            });
        } else {
            Swal.fire({
                title: "Error al invalidar DTE",
                icon: "error",
                html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
                confirmButtonText: 'Cerrar'
            });

        }
    });
}

//enviar por correo

function generarEventoCorreo(numeroControl) {
    const url = base_url + "Listados/generarEnviar/" + numeroControl;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("cliente").value = res.cliente;
        document.getElementById("correoE").value = res.correo;
        document.getElementById("control").value = res.numeroControl;
        document.getElementById("dte").value = res.tipoDte;
        document.getElementById("idFactura").value = res.id;
        $("#enviarCorreoModal").modal({
            backdrop: 'static',
            keyboard: false
        });


    })
}

function cancelarCorreo() {
    document.getElementById('frmCorreo').reset();
}

function enviarFacturaPorCorreo(e) {
    e.preventDefault(); // importante

    Swal.fire({
        title: 'Enviando Archivos',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = base_url + "Listados/enviarFacturaPorCorreo";
    const frm = document.getElementById("frmCorreo");

    enviarPeticion(url, "POST", frm, function (res) {
        if (res.status === "success") {
            Swal.fire({
                title: 'Documentos enviados',
                icon: 'success',
                html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(res.message, null, 2)}</pre>`,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                $("#enviarCorreoModal").modal('hide');
                frm.reset();
            });
        } else {
            Swal.fire({
                title: "Error al enviar",
                icon: "error",
                html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
                confirmButtonText: 'Cerrar'
            });
        }
    });
}




export { generarPdfVistaFe, generarPdfVistaFeJson, generarPdfVistaCcf, generarPdfVistaCcfJson, generarPdfVistaNc, generarPdfVistaNcJson, generarEventoInvalidar, enviarEvento, cancelarEvento, generarEventoCorreo, enviarFacturaPorCorreo, generarPdfVistaFse, generarPdfVistaFseJson }