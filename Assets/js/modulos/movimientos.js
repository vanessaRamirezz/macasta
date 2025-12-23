import { formatearMoneda, configurarTabla } from "../utilidades/tablePeticion.js";
let tblMovimientos
let tblRecibos

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaMovimientos')) {
        tblMovimientos = configurarTabla('#tblMovimientos', "Movimientos/listar", [
            { 'data': 'transaccion' },
            { 'data': 'movimiento' },
            { 'data': 'proveedor' },
            { 'data': 'cliente' },
            { 'data': 'empleado' },
            { 'data': 'proyecto' },
            { 'data': 'fecha' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchMovimientos',
            searchBtnId: 'searchBtnMovimientos',
            clearSearchBtnId: 'clearSearchBtnMovimientos'
        });

        // tblRecibos = configurarTabla('#tblRecibos', "Movimientos/listarRecibos", [
        //     { 'data': 'transaccion' },
        //     { 'data': 'movimiento' },
        //     { 'data': 'empleado' },
        //     { 'data': 'proyecto' },
        //     { 'data': 'fecha' },
        //     { 'data': 'acciones' }
        // ], [], {
        //     searchInputId: 'customSearchRecibos',
        //     searchBtnId: 'searchBtnRecibos',
        //     clearSearchBtnId: 'clearSearchBtnRecibos'
        // });

        // Asignar evento al botón de acción (Registrar)
        const btnRegistrar = document.getElementById('registrarMovimiento');
        btnRegistrar.addEventListener('click', registrarMovimiento);

        $('#selectTipoMovimiento').on('change', function () {
            let tipoMovimientoSeleccionado = $(this).find('option:selected').text().toLowerCase();
        
            // Ocultar ambos select inicialmente
            $("#selectProveedorContainer").hide();
            $("#selectClienteContainer").hide();
            $("#selectPagoEmpleado").hide();
        
            // Limpiar los valores y deshabilitar ambos selects
            $("#selectProveedor").val(null).trigger("change").prop('disabled', true);
            $("#selectCliente").val(null).trigger("change").prop('disabled', true);
        
            // Si el tipo de movimiento contiene alguna variante de "proveedor"
            if (/(proveedor|proveedores|Proveedor|PROVEEDORES)/i.test(tipoMovimientoSeleccionado)) {
                $("#selectProveedorContainer").show();  // Mostrar el contenedor de Proveedor
                $("#selectProveedor").prop('disabled', false);  // Habilitar el select de proveedor
            }
            // Si el tipo de movimiento contiene alguna variante de "cliente"
            else if (/(cliente|clientes|Cliente|CLIENTES)/i.test(tipoMovimientoSeleccionado)) {
                $("#selectClienteContainer").show();  // Mostrar el contenedor de Cliente
                $("#selectCliente").prop('disabled', false);  // Habilitar el select de cliente
            }

            else if (/(planilla|Planilla)/i.test(tipoMovimientoSeleccionado)) {
                $("#selectPagoEmpleado").show();
                $("#selectCliente").prop('disabled', false);  // Habilitar el select de cliente
            }
        });

        $('#selectTipoPago').on('change', function () {
            let tipoPagoSeleccionado = $(this).find('option:selected').text().toLowerCase();

            $("#selectTipoPagoContainer").hide();

            if (/(transferencia|Transferencia|TRANSFERENCIA)/i.test(tipoPagoSeleccionado)) {
                $("#selectTipoPagoContainer").show();  // Mostrar el contenedor de Proveedor
            } else if (/(cheque|Cheque|CHEQUE)/i.test(tipoPagoSeleccionado)) {
                $("#selectTipoPagoContainer").show();  // Mostrar el contenedor de Proveedor
            }
        });
        

        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            })
        })

        const btnCancelar = document.getElementById('cancelar');
        btnCancelar.addEventListener('click', cancelar);



        // Delegación de eventos para los botones vista pdf
        document.querySelector('#tblMovimientos tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const documetoCodigo = e.target.closest('.btn-editar').getAttribute('data-id');
                generarPdfVista(documetoCodigo);
            }
        });
    }
});


function registrarMovimiento(e) {
    e.preventDefault();
    const url = base_url + "Movimientos/registrar";
    const frm = document.getElementById("frmMovimientos");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res == 'si') {
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Registrado",
                    showConfirmButton: false,
                    timer: 3000
                });
                frm.reset();
                tblMovimientos.ajax.reload();
                setTimeout(() => {
                    window.location.reload();
                }, 300);
                $('#selectTipoMovimiento').val(null).trigger('change');
                $('#selectProveedor').val(null).trigger('change');
                $('#selectCliente').val(null).trigger('change');
                $('#selectTipoDocumento').val(null).trigger('change');
                $('#codigoProyecto').val(null).trigger('change');
                $('#selectBanco').val(null).trigger('change');
                $('#selectCuentaBancaria').val(null).trigger('change');
                resetearChecks();
                opcionesSelect();
            } else {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: res,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }
    }
}

function cancelar() {
    document.getElementById('frmMovimientos').reset();

    // Limpiar todos los selects
    $('#selectTipoMovimiento').val(null).trigger('change');
    $('#selectProveedor').val(null).trigger('change');
    $('#selectCliente').val(null).trigger('change');
    $('#selectTipoTransaccion').val(null).trigger('change');
    $('#selectCodigoEmpleado').val(null).trigger('change');
    $('#selectTipoDocumento').val(null).trigger('change');
    $('#selectTipoPago').val(null).trigger('change');
    $('#codigoProyecto').val(null).trigger('change');
    $('#selectBanco').val(null).trigger('change');
    recetearSelect();
}

// function resetearChecks() {
//     // Obtiene los checkboxes correctamente antes de usarlos
//     let checkboxProveedor = document.getElementById("flexCheckProveedor");
//     let checkboxCliente = document.getElementById("flexCheckCliente");

//     checkboxProveedor.addEventListener("change", function () {
//         $("#selectProveedor").prop("disabled", !this.checked).trigger("change");
//         $("#selectProveedor").val(null).trigger('change');
//     });

//     checkboxCliente.addEventListener("change", function () {
//         $("#selectCliente").prop("disabled", !this.checked).trigger("change");
//         $("#selectCliente").val(null).trigger('change');
//     });

//     resetearChecks();

//         // Aquí va el código de habilitar/deshabilitar los selects
//         let checkboxProveedor = document.getElementById("flexCheckProveedor");
//         let checkboxCliente = document.getElementById("flexCheckCliente");

//         // Si el checkboxProveedor está seleccionado
//         checkboxProveedor.addEventListener("change", function () {
//             if (checkboxProveedor.checked) {
//                 // Habilitar el select de Proveedor
//                 $("#selectProveedor").prop("disabled", false);
//                 // Deshabilitar el select de Cliente y desmarcar el checkboxCliente
//                 $("#selectCliente").prop("disabled", true).val(null).trigger("change").empty();
//                 checkboxCliente.checked = false;
//             }
//         });

//         // Si el checkboxCliente está seleccionado
//         checkboxCliente.addEventListener("change", function () {
//             if (checkboxCliente.checked) {
//                 // Habilitar el select de Cliente
//                 $("#selectCliente").prop("disabled", false);
//                 // Deshabilitar el select de Proveedor y desmarcar el checkboxProveedor
//                 $("#selectProveedor").prop("disabled", true).val(null).trigger("change").empty();
//                 checkboxProveedor.checked = false;
//             }
//         });
// }

$(document).ready(function () {
    // SELECT TIPO MOVIMIENTO
    $('#selectTipoMovimiento').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarTipoMovimiento", // Ruta de búsqueda en tu backend
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
                    results: data.map(tipoMovimiento => ({
                        id: tipoMovimiento.codigo,
                        text: tipoMovimiento.nombre
                    }))
                };
            },
            cache: true
        }
    })

    // SELECT TIPO TRANSACCION
    $('#selectTipoTransaccion').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarTransaccionEmpleado", // Ruta de búsqueda en tu backend
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
                    results: data.map(tipoTransaccion => ({
                        id: tipoTransaccion.codigo,
                        text: tipoTransaccion.nombre
                    }))
                };
            },
            cache: true
        }
    })

    //BUSCAR EMPLEADO
    $('#selectCodigoEmpleado').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarEmpleado", // Ruta de búsqueda en tu backend
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
                    results: data.map(empleado => ({
                        id: empleado.codigo,
                        text: empleado.nombre
                    }))
                };
            },
            cache: true
        }
    })

    // SELECT PROVEEDORES
    $("#selectProveedor").select2({
        placeholder: "Busque y seleccione",
        allowClear: true, // Permitimos limpiar la selección
        ajax: {
            url: base_url + "Productos/buscarProveedores",
            dataType: "json",
            delay: 250, // Para reducir el número de peticiones
            data: function (params) {
                return {
                    q: params.term // Enviamos el término de búsqueda al backend
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(proveedores => ({
                        id: proveedores.codigo, // Usamos el id de proveedor
                        text: proveedores.nombre // Usamos el nombre como texto visible
                    }))
                };
            },
            cache: true // Habilitamos el cache para evitar múltiples peticiones para los mismos datos
        },
    });

    // SELECT CLIENTE 
    $("#selectCliente").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Proyectos/buscarClientes", // Ruta de búsqueda en tu backend
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
                    results: data.map(cliente => ({
                        id: cliente.codigo,
                        text: cliente.nombre
                    }))
                };
            },
            cache: true
        }
    });

    // SELECT TIPO DOCUMENTO
    $("#selectTipoDocumento").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarTipoDocumento", // Ruta de búsqueda en tu backend
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
                    results: data.map(tipoDocumento => ({
                        id: tipoDocumento.codigo,
                        text: tipoDocumento.nombre
                    }))
                };
            },
            cache: true
        }
    });

    // SELECT PROYECTO
    $("#codigoProyecto").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Compras/buscarProyecto", // Ruta de búsqueda en tu backend
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
                    results: data.map(proyecto => ({
                        id: proyecto.codigo,
                        text: proyecto.nombre
                    }))
                };
            },
            cache: true
        }
    });

    // SELECT TIPO DE PAGO
    $("#selectTipoPago").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarTipoPago", // Ruta de búsqueda en tu backend
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
                    results: data.map(tipoPago => ({
                        id: tipoPago.codigo,
                        text: tipoPago.nombre
                    }))
                };
            },
            cache: true
        }
    });

    // Inicialización del select2 para Banco
    $("#selectBanco").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Movimientos/buscarBanco", // Ruta de búsqueda en tu backend
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
                    results: data.map(banco => ({
                        id: banco.codigo,
                        text: banco.nombre
                    }))
                };
            },
            cache: true
        }
    });

    // Evento para cuando se selecciona un banco
    $("#selectBanco").on("select2:select", function (e) {
        let codigoBanco = e.params.data.id;
        // Inicializar el select2 de Cuenta Bancaria con el código del banco seleccionado
        $("#selectCuentaBancaria").select2({
            placeholder: "Busque o seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "Movimientos/obtenerCuentaBancaria", // Ruta de búsqueda en tu backend
                dataType: "json",
                delay: 250, // Espera para reducir solicitudes
                data: function (params) {
                    return {
                        q: codigoBanco // El término de búsqueda
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(cuentaBancaria => ({
                            id: cuentaBancaria.codigo,
                            text: cuentaBancaria.nombre
                        }))
                    };
                },
                cache: false
            }
        });
    });

    // Evento para cuando se deselecciona un banco
    $("#selectBanco").on("select2:unselect", function (e) {
        // Limpiar y resetear el select2 de Cuenta Bancaria
        $('#selectCuentaBancaria').empty().trigger('change');  // Limpiar las opciones
        $('#selectCuentaBancaria').select2({
            placeholder: "Busque o seleccione",  // Mensaje de placeholder
            allowClear: true  // Permitir limpiar la selección
        });
    });


});

function recetearSelect() {
    $(document).ready(function () {

        // Limpiar y resetear el select2 de Cuenta Bancaria
        $('#selectCuentaBancaria').empty().trigger('change');  // Limpiar las opciones
        $('#selectCuentaBancaria').select2({
            placeholder: "Busque o seleccione",  // Mensaje de placeholder
            allowClear: true  // Permitir limpiar la selección
        });

    });
}

function generarPdfVista(numeroDocumento) {
    const ruta = base_url + 'Movimientos/generarPdf/' + numeroDocumento;
    window.open(ruta);
}

export { cancelar, registrarMovimiento, recetearSelect, generarPdfVista }