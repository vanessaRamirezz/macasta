import { configurarTabla, asignarEvento } from "../utilidades/tablePeticion.js";
let tblHistorialContin;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaContingencia')) {
        tblHistorialContin = configurarTabla('#tblHistorialContingencia', "HistorialContingencia/listar", [
            { 'data': 'nombreTipoDocumento' },
            { 'data': 'correlativo' },
            { 'data': 'codigo' },
            { 'data': 'cliente' },
            { 'data': 'incluido' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchHistorial',
            searchBtnId: 'searchBtnHistorial',
            clearSearchBtnId: 'clearSearchBtnHistorial'
        });
    }

    // Delegación de eventos para los botones de vista pdf
    document.querySelector('#tblHistorialContingencia tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const btn = e.target.closest('.btn-editar');
            const numControl = btn.getAttribute('data-id');
            generarPdfVista(numControl);
        }
    });

    document.querySelector('#tblHistorialContingencia tbody').addEventListener('click', function (e) {
        if (e.target.closest('.btn-json')) {
            const btn = e.target.closest('.btn-json');
            const numControlJS = btn.getAttribute('data-id');
            generarVistaJson(numControlJS);
        }
    });



    asignarEvento('generarEventoContingencia', 'click', generarContingencia);
    asignarEvento('generarLote', 'click', emitirLotes);
    asignarEvento('btnConsultar', 'click', consultar)
});

$(document).ready(function () {
    // Inicializamos Select2 pero sin datos aún
    $('#selectLote').select2({
        placeholder: "Seleccione fecha primero",
        allowClear: true,
        ajax: {
            url: base_url + "HistorialContingencia/consultarLote",
            dataType: "json",
            delay: 250,
            data: function (params) {
                const fecha = $('#fechaFiltro').val();
                if (!fecha) {
                    return false; // no envía nada si no hay fecha
                }
                return {
                    q: params.term || '',
                    fecha: fecha
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(lote => ({
                        id: lote.codigo,
                        text: lote.codigo // Mostrar código como texto
                    }))
                };
            },
            cache: true
        }
    });

    // Habilitar select cuando haya fecha
    $('#fechaFiltro').on('change', function () {
        if ($(this).val()) {
            $('#selectLote').prop('disabled', false);
            $('#selectLote').val(null).trigger('change'); // limpiar
        } else {
            $('#selectLote').prop('disabled', true);
            $('#selectLote').val(null).trigger('change');
        }
    });

    // Limpiar campos cada vez que se abre el modal
    $('#consultaLote').on('show.bs.modal', function () {
        $('#fechaFiltro').val('');
        $('#selectLote').prop('disabled', true).val(null).trigger('change');
    });
});




function generarPdfVista(numeroControl) {
    const ruta = base_url + 'HistorialContingencia/verPdfDte/' + numeroControl;
    window.open(ruta);
}

function generarVistaJson(numeroControl) {
    const ruta = base_url + 'HistorialContingencia/verJsonDte/' + numeroControl;
    window.open(ruta);
}

function generarContingencia(e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se generará el evento contingencia.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(base_url + 'HistorialContingencia/generar', {
                method: 'POST'
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        firmarLocalmente(res.dteJson, res.nit, res.passwordPri)
                    } else {
                        Swal.fire({
                            title: "Error al generar DTE",
                            icon: "error",
                            html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
                            confirmButtonText: 'Cerrar'
                        });
                    }
                });
        }
    });
}

function firmarLocalmente(dteJson, nit, passwordPrivada) {
    fetch("http://localhost:8113/firmardocumento/", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nit: nit,
            activo: true,
            passwordPri: passwordPrivada,
            dteJson: dteJson,
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === "OK" && data.body) {
                fetch(base_url + "HistorialContingencia/emitirFirmado", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        firmado: data.body,
                        dteJson: dteJson,
                    })
                })
                    .then(res => res.json())
                    .then(result => {
                        if (result.status === 'success') {
                            Swal.fire({
                                title: 'Documento emitido correctamente',
                                icon: 'success',
                                html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(result.emision, null, 2)}</pre>`,
                                confirmButtonText: 'Aceptar'
                            })
                            tblHistorialContin.ajax.reload();
                        } else {
                            Swal.fire("Error al emitir", JSON.stringify(result.message, null, 2), "error");
                        }
                    });

            } else {
                Swal.fire("Error en firma", JSON.stringify(data, null, 2), "error");
            }
        })
        .catch(err => {
            Swal.fire("Error de conexión con firmador", err.message, "error");
        });

}

function emitirLotes(e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Estás seguro?',
        text: "se enviarán todos los DTE en contingencia.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(base_url + 'HistorialContingencia/emitirLote', {
                method: 'POST'
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Lote emitido correctamente',
                            icon: 'success',
                            html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(res.emision, null, 2)}</pre>`,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            tblHistorialContin.ajax.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message || 'No se pudo enviar el lote', 'error');
                    }
                });
        }
    });
}

function consultar(e) {
    e.preventDefault();

    const fecha = document.getElementById('fechaFiltro').value;
    const lote = document.getElementById('selectLote').value;

    if (!fecha || !lote) {
        Swal.fire('Campos incompletos', 'Debe seleccionar fecha y lote.', 'warning');
        return;
    }

    fetch(base_url + 'HistorialContingencia/consultarLotes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            fecha: fecha,
            lote: lote
        })
    })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    title: 'Información de Lote',
                    icon: 'success',
                    html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto; user-select:text;">${JSON.stringify(res.consulta, null, 2)}</pre>`,
                    confirmButtonText: 'Aceptar',
                    width: '800px'
                }).then(() => {
                    tblHistorialContin.ajax.reload();
                });
            } else {
                Swal.fire('Error', res.message || 'No se pudo consultar el lote', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Ocurrió un problema al consultar.', 'error');
            console.error(err);
        });
}



export { generarPdfVista, generarVistaJson, generarContingencia, emitirLotes, consultar }