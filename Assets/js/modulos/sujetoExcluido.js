import { formatearNumero, enviarPeticion, asignarEvento } from "../utilidades/tablePeticion.js";

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaSujetoExcluido')) {
    verificarContingenciaActiva();
        // opcion al seleccionar pago
        $("#selectTipoPago").prop("disabled", true).val(null).trigger("change").empty();
        $('#condicion').on('change', function () {
            $("#selectTipoPago").prop("disabled", false).val(null).trigger("change").empty();
        });
        $('#selectTipoPago').on('change', function () {
            let tipoPagoSeleccionado = $(this).find('option:selected').text().toLowerCase();

            $("#selectTipoPagoContainer").hide();
            $("#selectTipoPagoContainerCheque").hide();

            if (/(transferencia|Transferencia|TRANSFERENCIA)/i.test(tipoPagoSeleccionado)) {
                $("#selectTipoPagoContainer").show();  // Mostrar el contenedor de Proveedor
            } else if (/(cheque|Cheque|CHEQUE)/i.test(tipoPagoSeleccionado)) {
                $("#selectTipoPagoContainerCheque").show();  // Mostrar el contenedor de Proveedor
            }
        });

        // solo lectura el input de tipoDocumento
        document.getElementById("tipoDocumento").readOnly = true;
        document.getElementById("total").readOnly = true;
        document.getElementById("totalPagos").readOnly = true;

        // evento para cancelar la operacion, recetear los inputs
        asignarEvento('cancelarSE', 'click', cancelar);
        // evento para abrir el modal de agregar productos
        asignarEvento('nuevo', 'click', frmDetalle);
        // evento para seleccionar los productos y registrar en temporal
        asignarEvento('seleccionarProducto', 'click', seleccionarProductos);
        // evento para generar el dte
        asignarEvento('agregarFP', 'click', frmPagos);
        // agregar pagos
        asignarEvento('agregarForMa', 'click', agregarFormaPago);
        // generar dte
        asignarEvento('generarFSujeto', 'click', generarDte);

        document.getElementById('cantidadProducto').addEventListener('input', calcularCantidadTotal);
        document.getElementById('precioVenta').addEventListener('input', calcularCantidadTotal);
        document.getElementById('descuentoItem').addEventListener('input', calcularCantidadTotal);
        document.getElementById("montoPago").addEventListener("input", function () {
            const montoNuevo = parseFloat(this.value) || 0;
            const totalOriginal = parseFloat(document.getElementById("totalModal").dataset.totalOriginal || 0);
            const yaPagado = parseFloat(document.getElementById("totalRestante").dataset.pagadoActual || 0);

            const restanteFinal = Math.round((totalOriginal - yaPagado - montoNuevo) * 100) / 100;
            document.getElementById("totalRestante").value = formatearNumero(restanteFinal);

        });

        cargarDetalle();
        cargarDetallePagos();
    }
})
/// selects de las tablas
$(document).ready(function () {
    $('#selectCliente').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/buscarClientes", // Ruta de búsqueda en tu backend
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
    })

    $('#selectTipoMovimiento').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/buscarTipoMovimiento", // Ruta de búsqueda en tu backend
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

    $("#codigoProyecto").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/buscarProyecto", // Ruta de búsqueda en tu backend
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

    // tipo operacion
    $("#condicion").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/tipoOperacion", // Ruta de búsqueda en tu backend
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
                    results: data.map(oepracionT => ({
                        id: oepracionT.codigo,
                        text: oepracionT.nombre
                    }))
                };
            },
            cache: true
        }
    });
    $("#condicion").on("change", function (e) {
        const valor = $(this).val();
        if (!valor) {
            // Si se deselecciona (queda vacío)
            $("#selectTipoPago").prop("disabled", true).val(null).trigger("change").empty();
        }
    });

    $("#selectTipoPago").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/buscarTipoPago", // Ruta de búsqueda en tu backend
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
    $("#selectTipoPago").on("select2:select", function (e) {
        document.getElementById('referencia').value = '';
        $('#selectCuentaBancaria').empty().trigger('change');
        $('#selectBanco').empty().trigger('change');
        $('#selectCuentaBancaria').select2({
            placeholder: "Busque o seleccione",  // Mensaje de placeholder
            allowClear: true  // Permitir limpiar la selección
        });
    });

    // para seleccionar tipo de banco cuando se seleccione
    $("#selectBanco").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/buscarBanco", // Ruta de búsqueda en tu backend
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
                url: base_url + "SujetoExcluido/obtenerCuentaBancaria", // Ruta de búsqueda en tu backend
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
    $("#selectBanco").on("select2:unselect", function (e) {
        // Limpiar y resetear el select2 de Cuenta Bancaria
        $('#selectCuentaBancaria').empty().trigger('change');  // Limpiar las opciones
        $('#selectCuentaBancaria').select2({
            placeholder: "Busque o seleccione",  // Mensaje de placeholder
            allowClear: true  // Permitir limpiar la selección
        });
    });

    // selects para agregar productos
    $('#agregarProducto').on('shown.bs.modal', function () {
        // SELECT PRODUCTO EN MODAL
        $("#producto").select2({
            placeholder: "Busque y seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "DetalleTemporal/buscarProducto", // Ruta de búsqueda en tu backend
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
                        results: data.map(producto => ({
                            id: producto.codigo,
                            text: producto.nombre,
                            cantidad: producto.cantidad,
                            costo: producto.costo,
                            venta: producto.precio
                        }))
                    };
                },
                cache: true
            }
        });
        // para selecionar del select
        $("#producto").on("select2:select", function (e) {
            let data = e.params.data;

            // Extraemos los valores originales
            const costoOriginal = parseFloat(data.costo);
            const precioOriginal = parseFloat(data.venta);

            // Formateamos los valores solo para visualización
            const costo = formatearNumero(costoOriginal);
            const precio = formatearNumero(precioOriginal);

            $("#codigoProducto").val(data.id);
            $("#producto").val(data.text);
            $("#cantidadProducto").val(0);
            $("#precioCosto").val(costo);
            $("#precioVenta").val(precio);

            calcularCantidadTotal();
        });

        //para cancelar la seleccion
        $("#producto").on("select2:unselect", function (e) {
            $("#codigoProducto").val("");
            $("#producto").val("");
            $("#cantidadProducto").val("");
            $("#precioCosto").val("");
            $("#precioVenta").val("");
        });

        $('#agregarProducto').on('hidden.bs.modal', function () {
            $("#codigoProducto").val(null).trigger('change');
            $("#producto").val(null).trigger('change');
            $("#cantidadProducto").val(null).trigger('change');
            $("#precioCosto").val(null).trigger('change');
            $("#precioVenta").val(null).trigger('change');
        });
    });

    $('#unidadMedida').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/unidadDeMedida", // Ruta de búsqueda en tu backend
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
                    results: data.map(uniMedida => ({
                        id: uniMedida.codigo,
                        text: uniMedida.nombre
                    }))
                };
            },
            cache: true
        }
    })

    $('#tipoItemP').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "SujetoExcluido/tipoDeItem",
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
                    results: data.map(tItem => ({
                        id: tItem.codigo,
                        text: tItem.nombre
                    }))
                };
            },
            cache: true
        }
    })

});


// calcular el total de productos seleccionados en el modal
function calcularCantidadTotal() {
    const cantidad = parseFloat(document.getElementById("cantidadProducto").value) || 0;
    const descuento = parseFloat(document.getElementById("descuentoItem").value) || 0;
    const precio = document.getElementById("precioVenta").value.replace(/[^0-9.-]+/g, "");
    const precioSinIva = Math.round((parseFloat(precio) / 1.13) * 100) / 100;
    let subtotalIva = precioSinIva * cantidad;
    let descuentoTotal = descuento * cantidad;

    let total = 0;

    total = Math.round(parseFloat(subtotalIva - descuentoTotal) * 100) / 100;

    document.getElementById("sub").value = formatearNumero(subtotalIva);
    document.getElementById("totalSeleccionado").value = formatearNumero(total);
}


// boton de cancelar
function cancelar() {

    document.getElementById('frmSujetoExcluido').reset();

    // Limpiar todos los selects
    $('#selectCliente').val(null).trigger('change');
    $('#selectTipoMovimiento').val(null).trigger('change');
    $('#codigoProyecto').val(null).trigger('change');
    $('#condicion').val(null).trigger('change');
    $('#selectTipoPago').val(null).trigger('change');
    $('#selectBanco').val(null).trigger('change');
    $('#selectCuentaBancaria').empty().trigger('change');
    $('#selectCuentaBancaria').select2({
        placeholder: "Busque o seleccione",  // Mensaje de placeholder
        allowClear: true  // Permitir limpiar la selección
    });

    // eliminar detalle de productos
    const url = base_url + "SujetoExcluido/vaciarDetalleProductos";
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res === 'ok') {
                cargarDetalle();
            }
        }
    };

    //eliminar detalle de pagos
    const urlP = base_url + "SujetoExcluido/vaciarDetallePagos";
    const httpP = new XMLHttpRequest();
    httpP.open("GET", urlP, true);
    httpP.send();
    httpP.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res === 'ok') {
                cargarDetallePagos();
            }
        }
    };
}

// abrir modal para agregar productos
function frmDetalle() {
    document.getElementById('codigoProducto').readOnly = true;
    document.getElementById('cantidadProducto').readOnly = false;
    document.getElementById('descuentoItem').value = '';
    document.getElementById('sub').value = '';
    document.getElementById('precioCosto').readOnly = true;
    document.getElementById('precioVenta').readOnly = true;
    document.getElementById("totalSeleccionado").value = '';
    $('#unidadMedida').val(null).trigger('change');
    $('#tipoItemP').val(null).trigger('change');
    $('#agregarProducto').modal("show");
}


// generar dte
// function generarDte(e) {
//     e.preventDefault();

//     const url = base_url + "SujetoExcluido/firmarLocalmente";
//     const frm = document.getElementById("frmSujetoExcluido");
//     const http = new XMLHttpRequest();
//     http.open("POST", url, true);
//     http.send(new FormData(frm));
//     http.onreadystatechange = function () {
//         if (this.readyState == 4 && this.status == 200) {
//             const res = JSON.parse(this.responseText);
//             if (res.status === 'success') {
//                 firmarLocalmente(res.dteJson, res.nit)
//                     .then(data => {
//                         if (data.status === 'OK') {
//                             Swal.fire({
//                                 title: 'DTE firmado correctamente',
//                                 icon: 'success',
//                                 html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(data, null, 2)}</pre>`,
//                                 confirmButtonText: 'Aceptar'
//                             }).then(() => {
//                                 // cancelar(); // Descomenta si quieres limpiar el formulario
//                             });
//                         } else {
//                             Swal.fire({
//                                 title: 'Error al firmar el DTE',
//                                 icon: 'error',
//                                 html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(data, null, 2)}</pre>`,
//                                 confirmButtonText: 'Cerrar'
//                             });
//                         }
//                     })
//                     .catch(err => {
//                         Swal.fire({
//                             title: 'No se pudo conectar con el firmador local',
//                             icon: 'error',
//                             html: `<pre style="text-align:left; white-space:pre-wrap;">${err.message}</pre>`,
//                             confirmButtonText: 'Cerrar'
//                         });
//                     });

//             } else {
//                 Swal.fire({
//                     title: "Error al emitir DTE",
//                     icon: "error",
//                     html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
//                     confirmButtonText: 'Cerrar'
//                 });
//             }
//         }
//     };
// }

function generarDte(e) {
    e.preventDefault();

    const url = base_url + "SujetoExcluido/generar"; // Solo genera el JSON
    const frm = document.getElementById("frmSujetoExcluido");

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));

    http.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            const res = JSON.parse(this.responseText);
            if (res.status === 'success') {

                // Llamar al firmador local
                firmarLocalmente(res.dteJson, res.nit, res.passwordPri, res.movimiento, res.proyecto, res.correlativo);

            } else {
                Swal.fire({
                    title: "Error al generar DTE",
                    icon: "error",
                    html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
                    confirmButtonText: 'Cerrar'
                });
            }
        }
    };
}

function firmarLocalmente(dteJson, nit, passwordPrivada, movimiento, proyecto, correlativo) {
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
                // console.log("Enviando a emitirFirmado:", {
                //     firmado: data.body,
                //     dteJson: dteJson,
                //     tipoMovimiento: movimiento,
                //     codigoProyecto: proyecto,
                //     correlativoNumero: correlativo
                // });

                fetch(base_url + "SujetoExcluido/emitirFirmado", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        firmado: data.body,
                        dteJson: dteJson,
                        tipoMovimiento: movimiento,
                        codigoProyecto: proyecto,
                        correlativoNumero: correlativo
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
                            cancelar();
                        } else {
                            Swal.fire("Error al emitir", JSON.stringify(result.message, null, 2), "error");
                        }
                    });

            } else {
                Swal.fire("Error en firma", JSON.stringify(data, null, 2), "error");
            }
        })
        .catch(err => {
            // Liberar correlativo si falla la conexión al firmador
            fetch(base_url + "SujetoExcluido/liberarUltimoCorrelativo", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    tipoDte: dteJson.identificacion.tipoDte,
                    ambiente: dteJson.identificacion.ambiente
                })
            })
                .then(res => res.json())
                .then(resp => {
                    console.log("Liberación correlativo:", resp);
                })
                .catch(err2 => console.error("Error liberando correlativo:", err2));

            Swal.fire("Error de conexión con firmador", err.message, "error");
        });

}





//agregar productos y registrarlos en temporal
function seleccionarProductos() {
    const url = base_url + "SujetoExcluido/seleccionar";
    const frm = document.getElementById("frmDetalle");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res == 'ok' || res == 'modificado') {
                frm.reset();
                cargarDetalle();
                $("#agregarProducto").modal("hide");
            } else {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: res,
                    showConfirmButton: true,
                });
            }
        }
    }
}

// cargar el detalle en la vista
function cargarDetalle() {
    const url = base_url + "SujetoExcluido/listarDetalle";
    const formData = new FormData();

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(formData);

    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            let html = '';

            res.detalle.forEach(row => {
                html += `<tr>
                            <td>${row['item']}</td>
                            <td>${row['valorItem']}</td>
                            <td>${row['cantidad']}</td>
                            <td>${row['nombre']}</td>
                            <td>${row['nombreProducto']}</td>
                            <td>${formatearNumero(row['precioSinIva'])}</td>
                            <td>${formatearNumero(row['descuentoItem'])}</td>
                            <td>${formatearNumero(row['total'])}</td>
                            <td>
                                <button class="btn" type="button" id="eliminar-${row['id']}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
            });

            document.getElementById("tblDetalle").innerHTML = html;
            document.getElementById("totalOperacion").value = formatearNumero(res.subTotal);
            document.getElementById('cantidadProducto').addEventListener('input', calcularCantidadTotal);


            // Guardamos el subtotal original de BD
            const subtotal = parseFloat(res.subTotal) || 0;
            const descuentoPorItem = parseFloat(res.totalDescuentoPorItem) || 0;

            // Mostrar el total inicial de descuentos
            const descuentoManualInicial = parseFloat(document.getElementById("montoDescuTotal").value) || 0;
            document.getElementById("totalDescuento").value = formatearNumero(descuentoPorItem + descuentoManualInicial);

            // Mostrar subtotal y total iniciales
            document.getElementById("subtotal").value = formatearNumero(subtotal);
            document.getElementById("totalOperacion").value = formatearNumero(subtotal);
            document.getElementById("total").value = formatearNumero(subtotal);

            // Función para recalcular el total
            function recalcularTotal() {
                const descuentoManual = parseFloat(document.getElementById("montoDescuTotal").value) || 0;
                const ivaRetenido = parseFloat(document.getElementById("ivaRetenido").value) || 0;
                const retencionRenta = parseFloat(document.getElementById("rentaRetenida").value) || 0;

                // Actualizar total de descuentos
                const totalDescuento = descuentoPorItem + descuentoManual;
                document.getElementById("totalDescuento").value = formatearNumero(totalDescuento);

                // Calcular nuevo subtotal
                const nuevoSubtotal = subtotal - descuentoManual;
                document.getElementById("subtotal").value = formatearNumero(nuevoSubtotal);

                // Calcular total final (subtotal - IVA retenido)
                const totalFinalConiva = nuevoSubtotal - ivaRetenido;
                document.getElementById("total").value = formatearNumero(totalFinalConiva);

                // Calcular total final (subtotal - retencion renta)
                const totalFinalConrenta = totalFinalConiva - retencionRenta;
                document.getElementById("total").value = formatearNumero(totalFinalConrenta);
            }

            // Escuchar cambios en los dos campos relevantes
            document.getElementById("montoDescuTotal").addEventListener("input", recalcularTotal);
            document.getElementById("ivaRetenido").addEventListener("input", recalcularTotal);
            document.getElementById("rentaRetenida").addEventListener("input", recalcularTotal);


            res.detalle.forEach(row => {
                const btnEliminar = document.getElementById(`eliminar-${row['id']}`);
                if (btnEliminar) {
                    btnEliminar.addEventListener('click', () => eliminarDetalle(row['id']));
                }
            });


            // Vaciado de detalles al cambiar de página
            window.addEventListener('beforeunload', function () {
                cancelar(); // Llama a la función para vaciar los detalles
            });
        }
    };
}


function eliminarDetalle(id) {
    const url = base_url + "SujetoExcluido/eliminarDetalle/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res == 'ok') {
                cargarDetalle();
            }
        }
    };
}

function actualizarDescuentoEnModal() {
    const totalDescuento = document.getElementById("total").value;
    document.getElementById("totalModal").value = totalDescuento;
    document.getElementById("totalRestante").value = totalDescuento;
}


function frmPagos() {
    actualizarDescuentoEnModal();

    const totalOriginal = parseFloat(document.getElementById("total").value.replace(/[^0-9.-]+/g, "")) || 0;
    const inputTotalModal = document.getElementById("totalModal");
    inputTotalModal.value = formatearNumero(totalOriginal);
    inputTotalModal.dataset.totalOriginal = totalOriginal;

    document.getElementById("totalModal").readOnly = true;
    document.getElementById("montoPago").value = '';
    document.getElementById("referencia").value = '';
    document.getElementById("totalRestante").readOnly = true;
    document.getElementById("totalRestante").value = formatearNumero(totalOriginal); // valor inicial

    $('#condicion').val(null).trigger('change');
    $('#selectTipoPago').val(null).trigger('change');
    $('#agregarFPagos').modal("show");

    cargarDetallePagos(); // aquí se actualizará el total pagado
}


function agregarFormaPago(e) {
    e.preventDefault();
    const url = base_url + "SujetoExcluido/seleccionarFp";
    const frm = document.getElementById("frmPagos");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res.status === 'success' && (res.res === 'ok' || res.res === 'modificado')) {
                frm.reset();
                $("#agregarFPagos").modal("hide");
                cargarDetallePagos();
            } else if (res.status === 'error') {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: res.message,
                    showConfirmButton: true,
                });
            } else {
                console.log("Respuesta inesperada:", res);
            }
        }
    }
}

function cargarDetallePagos() {
    const url = base_url + "SujetoExcluido/listarDetallePagos";
    const codigoCliente = document.getElementById("selectCliente").value; // Capturar el código del cliente
    const formData = new FormData();
    formData.append("selectCliente", codigoCliente);

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(formData);

    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            let html = '';
            let totalPagado = 0;

            res.detalle.forEach(row => {
                totalPagado += parseFloat(row['montoPago']);
                html += `<tr>
                            <td>${row['nombre']}</td>
                            <td>${row['nombrePago']}</td>
                            <td>${row['referencia']}</td>
                            <td>${formatearNumero(row['montoPago'])}</td>
                            <td>
                                <button class="btn" type="button" id="eliminarP-${row['id']}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
            });

            document.getElementById("tblPagos").innerHTML = html;
            document.getElementById("totalPagos").value = formatearNumero(res.totalPagos);


            const totalOriginal = parseFloat(document.getElementById("totalModal").dataset.totalOriginal || 0);
            const restante = totalOriginal - totalPagado;
            document.getElementById("totalRestante").value = formatearNumero(restante);

            // Guardar total pagado como atributo temporal
            document.getElementById("totalRestante").dataset.pagadoActual = totalPagado;

            res.detalle.forEach(row => {
                const btnEliminar = document.getElementById(`eliminarP-${row['id']}`);
                if (btnEliminar) {
                    btnEliminar.addEventListener('click', () => eliminarDetallePagos(row['id']));
                }
            });

            // Vaciado de detalles al cambiar de página
            window.addEventListener('beforeunload', function () {
                cancelar(); // Llama a la función para vaciar los detalles
            });
        }
    };
}

function eliminarDetallePagos(id) {
    const url = base_url + "SujetoExcluido/eliminarDetallePagos/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res == 'ok') {
                cargarDetallePagos();
            }
        }
    };
}

function verificarContingenciaActiva() {
    const url = base_url + "SujetoExcluido/obtenerContingencia";

    enviarPeticion(url, "GET", null, function (res) {
        if (res.activo === true) {
            document.querySelector('.container-fluid').classList.add('contingencia-activa');
        } else {
            document.querySelector('.container-fluid').classList.remove('contingencia-activa');
        }
    });
}
export { cancelar, frmDetalle, seleccionarProductos, cargarDetalle, generarDte, frmPagos, agregarFormaPago, cargarDetallePagos, verificarContingenciaActiva }