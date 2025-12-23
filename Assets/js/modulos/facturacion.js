import { formatearNumero, enviarPeticion, asignarEvento } from "../utilidades/tablePeticion.js";
let advertenciaDocumentosMostrada = false;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaFacturacion')) {

        asignarEvento('cancelarFac', 'click', cancelar);


        $("#selectTipoPago").prop("disabled", true).val(null).trigger("change").empty();


        $('#condicion').on('change', function () {
            $("#selectTipoPago").prop("disabled", false).val(null).trigger("change").empty();
        });

        $('#selectTipoPago').on('change', function () {
            let tipoPagoSeleccionado = $(this).find('option:selected').text().toLowerCase();

            $("#selectTipoPagoContainer").hide();

            if (/(transferencia|Transferencia|TRANSFERENCIA)/i.test(tipoPagoSeleccionado)) {
                $("#selectTipoPagoContainer").show();  // Mostrar el contenedor de Proveedor
            } 
        });

        asignarEvento('nuevo', 'click', frmFacturacion);
        asignarEvento('seleccionarProducto', 'click', seleccionarProductos);
        asignarEvento('generarFacturacion', 'click', generarFactura);

        document.getElementById('cantidadProducto').addEventListener('input', calcularCantidadTotal);
        document.getElementById('precioVenta').addEventListener('input', calcularCantidadTotal);
        cargarDetalle();
        // $('#selectTipoDocumento').prop("disabled", true);

        // AGREGAR DTES INICIO
        document.getElementById('agregarDte').addEventListener('click', function () {
            // Obtener valores
            const selectTipoDocumento = document.getElementById("tipoDocumento");
            const tipoDocumentoValor = selectTipoDocumento.value;
            let tipoDocumentoTexto = '';
            if (selectTipoDocumento.selectedIndex !== -1) {
                tipoDocumentoTexto = selectTipoDocumento.options[selectTipoDocumento.selectedIndex].text;
            }

            const selectTipoGeneracion = document.getElementById("tipoGeneracion");
            const tipoGeneracionValor = selectTipoGeneracion.value;
            let tipoGeneracionTexto = '';
            if (selectTipoGeneracion.selectedIndex !== -1) {
                tipoGeneracionTexto = selectTipoGeneracion.options[selectTipoGeneracion.selectedIndex].text;
            }

            const numeroDoc = document.getElementById('numeroDoc').value;
            const fechaEmision = document.getElementById('fechaEmision').value;

            // Enviar al backend para validación con PHP
            fetch(base_url + "Facturacion/docRelacionados", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    tipoDocumento: tipoDocumentoValor,
                    tipoGeneracion: tipoGeneracionValor,
                    numeroDoc: numeroDoc,
                    fechaEmision: fechaEmision
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Crear nueva fila solo si el backend valida correctamente
                        const fila = `
                    <tr>
                        <td>
                            <input type="hidden" name="documentosRelacionados[tipoDocumento][]" value="${tipoDocumentoValor}">
                            ${tipoDocumentoTexto}
                        </td>
                        <td>
                            <input type="hidden" name="documentosRelacionados[tipoGeneracion][]" value="${tipoGeneracionValor}">
                            ${tipoGeneracionTexto}
                        </td>
                        <td>
                            <input type="hidden" name="documentosRelacionados[numeroDoc][]" value="${numeroDoc}">
                            ${numeroDoc}
                        </td>
                        <td>
                            <input type="hidden" name="documentosRelacionados[fechaEmision][]" value="${fechaEmision}">
                            ${fechaEmision}
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
                    </tr>
                    `;
                        document.querySelector("#tablaDoc tbody").insertAdjacentHTML("beforeend", fila);

                        // Limpiar campos
                        $('#tipoDocumento').val(null).trigger('change');
                        $('#tipoGeneracion').val(null).trigger('change');
                        document.getElementById("fechaEmision").value = "";
                        const input = document.getElementById('numeroDoc');
                        const label = document.getElementById('doc');
                        label.textContent = "";
                        input.value = '';
                        input.placeholder = "---";

                    } else {
                        // Si hay un error, muestra el mensaje
                        Swal.fire({
                            position: "top-end",
                            icon: "error",
                            title: data.message,
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // alert("Error: " + data.message); // Mostrar error desde PHP
                    }
                });
        });
        // Delegar evento para eliminar filas
        document.getElementById("tablaDoc").addEventListener("click", function (e) {
            if (e.target.classList.contains("eliminar-fila")) {
                const fila = e.target.closest("tr");
                const numeroDoc = fila.querySelector('input[name="documentosRelacionados[numeroDoc][]"]').value;

                // Eliminar la fila del DOM
                fila.remove();

                // Eliminar también del backend (sesión)
                fetch(base_url + "Facturacion/eliminarDocumentoRelacionado", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ numeroDoc })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire({
                                icon: "warning",
                                title: "Error al eliminar",
                                text: data.message || "No se pudo eliminar el documento de la sesión.",
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            cargarDetalle();
                        }
                    })
                    .catch(err => {
                        console.error("Error eliminando de sesión:", err);
                    });
            }
        });
        asignarEvento('limpiarDte', 'click', limpiarDte);
        // AGREGAR DTE FIN

        // SECCION DOCUMENTOS ASOCIADOS INICIO
        asignarEvento('limpiarDoc', 'click', LimpiarDocAS);
        $('#otrosAsociados').on('change', function () {
            let tipoDocAsociado = $(this).find('option:selected').text().toLowerCase();

            $('#selectDocumentoA').hide();
            $('#selectDocumentoATB').hide();
            $('#selectMedico').hide();
            $('#selectMedicoTB').hide();
            document.getElementById('identificacionDocumento').value = '';
            document.getElementById('descripcionDocumento').value = '';

            if (/(Emisor|Receptor)/i.test(tipoDocAsociado)) {
                $('#selectDocumentoA').show();
                $('#selectDocumentoATB').show();
            }

            else if (/(Médico)/i.test(tipoDocAsociado)) {
                $('#selectMedico').show();
                $('#selectMedicoTB').show();
            }
        })
        document.getElementById('agregarDoc').addEventListener('click', function () {
            // Obtener valores

            const codigo = document.getElementById('otrosAsociados').value;
            const identificacionDoc = document.getElementById('identificacionDocumento').value;
            const descripcionDoc = document.getElementById('descripcionDocumento').value;

            // Enviar al backend para validación con PHP
            fetch(base_url + "Facturacion/documentosASOC", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    codigo: codigo,
                    identificacionDoc: identificacionDoc,
                    descripcionDoc: descripcionDoc,
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Crear nueva fila solo si el backend valida correctamente
                        const fila = `
                    <tr>
                        <td>
                            <input type="hidden" name="documentoAsociado[codigo][]" value="${codigo}">
                            ${codigo}
                        </td>
                        <td>
                            <input type="hidden" name="documentoAsociado[identificacionDoc][]" value="${identificacionDoc}">
                            ${identificacionDoc}
                        </td>
                        <td>
                            <input type="hidden" name="documentoAsociado[descripcionDoc][]" value="${descripcionDoc}">
                            ${descripcionDoc}
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-filaDoc">Eliminar</button></td>
                    </tr>
                    `;
                        document.querySelector("#tablaER tbody").insertAdjacentHTML("beforeend", fila);

                        // Limpiar campos;

                        document.getElementById("identificacionDocumento").value = "";
                        document.getElementById("descripcionDocumento").value = "";

                    } else {
                        // Si hay un error, muestra el mensaje
                        Swal.fire({
                            position: "top-end",
                            icon: "error",
                            title: data.message,
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // alert("Error: " + data.message); // Mostrar error desde PHP
                    }
                });
        });
        document.getElementById("tablaER").addEventListener("click", function (e) {
            if (e.target.classList.contains("eliminar-filaDoc")) {
                e.target.closest("tr").remove();
            }
        });
        // SECCION DOCUMENTOS ASOCIADOS INICIO


        // AGREGAR DATOS MEDICO INICIO
        document.getElementById('agregarDocMedico').addEventListener('click', function () {
            // Obtener valores

            const codigo = document.getElementById('otrosAsociados').value;
            const tipoServicio = document.getElementById('tipoServicio').value;
            const nombreMedico = document.getElementById('nombreMedico').value;
            const tipoDocumentoAS = document.getElementById('tipoDocumentoAS').value;
            const nitMedico = document.getElementById('nitMedico').value;
            // console.log(codigo, tipoServicio, nombreMedico, tipoDocumentoAS, nitMedico);

            // Enviar al backend para validación con PHP
            fetch(base_url + "Facturacion/documentosMedico", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    codigo: codigo,
                    tipoServicio: tipoServicio,
                    nombreMedico: nombreMedico,
                    tipoDocumentoAS: tipoDocumentoAS,
                    nitMedico: nitMedico,
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Crear nueva fila solo si el backend valida correctamente
                        const fila = `
                    <tr>
                        <td>
                            <input type="hidden" name="documentoAsociado[codigo][]" value="${codigo}">
                            ${codigo}
                        </td>
                        <td>
                            <input type="hidden" name="documentoMedico[nombreMedico][]" value="${nombreMedico}">
                            ${nombreMedico}
                        </td>
                        <td>
                            <input type="hidden" name="documentoMedico[tipoServicio][]" value="${tipoServicio}">
                            ${tipoServicio}
                        </td>
                        <td>
                            <input type="hidden" name="documentoMedico[tipoDocumentoAS][]" value="${tipoDocumentoAS}">
                            ${tipoDocumentoAS}
                        </td>
                        <td>
                            <input type="hidden" name="documentoMedico[nitMedico][]" value="${nitMedico}">
                            ${nitMedico}
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-filaMed">Eliminar</button></td>
                    </tr>
                    `;
                        document.querySelector("#tablaMedico tbody").insertAdjacentHTML("beforeend", fila);

                        // Limpiar campos;
                        $('#tipoServicio').val(null).trigger('change');
                        document.getElementById('nombreMedico').value = ''
                        document.getElementById('tipoDocumentoAS').value = ''
                        document.getElementById('nitMedico').value = ''

                    } else {
                        // Si hay un error, muestra el mensaje
                        Swal.fire({
                            position: "top-end",
                            title: 'Error',
                            text: data.message,
                            showConfirmButton: true,
                        });
                        // alert("Error: " + data.message); // Mostrar error desde PHP
                    }
                });
        });
        document.getElementById("tablaMedico").addEventListener("click", function (e) {
            if (e.target.classList.contains("eliminar-filaMed")) {
                e.target.closest("tr").remove();
            }
        });
        asignarEvento('limpiarMed', 'click', limpiarMedico);
        //AGREGAR DATOS MEDICO FIN


        //AGREGAR VENTA TERCERO INICIO
        document.getElementById('validar').addEventListener('click', function () {
            // Obtener valores

            const nitTercero = document.getElementById('nit').value;
            const nombreTercero = document.getElementById('nombreTercero').value;

            // Enviar al backend para validación con PHP
            fetch(base_url + "Facturacion/ventaTercero", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    nitTercero: nitTercero,
                    nombreTercero: nombreTercero,
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Crear nueva fila solo si el backend valida correctamente
                        const fila = `
                    <tr>
                        <td>
                            <input type="hidden" name="ventaTercero[nitTercero][]" value="${nitTercero}">
                            ${nitTercero}
                        </td>
                        <td>
                            <input type="hidden" name="ventaTercero[nombreTercero][]" value="${nombreTercero}">
                            ${nombreTercero}
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-filaTercero">Eliminar</button></td>
                    </tr>
                    `;
                        document.querySelector("#tablaTercero tbody").insertAdjacentHTML("beforeend", fila);

                        // Limpiar campos;
                        document.getElementById('nit').value = ''
                        document.getElementById('nombreTercero').value = ''

                    } else {
                        // Si hay un error, muestra el mensaje
                        Swal.fire({
                            position: "top-end",
                            title: 'Error',
                            text: data.message,
                            showConfirmButton: true,
                        });
                        // alert("Error: " + data.message); // Mostrar error desde PHP
                    }
                });
        });
        document.getElementById("tablaTercero").addEventListener("click", function (e) {
            if (e.target.classList.contains("eliminar-filaTercero")) {
                e.target.closest("tr").remove();
            }
        });
        asignarEvento('limpiarTercero', 'click', limpiarVenta);
        //AGREGAR VENTA TERCERO FIN



        // codigo contingencia
        //boton abrir modal
        asignarEvento('btnContingenciaOpciones', 'click', mostrarContingencia);
        asignarEvento('cancelar', 'click', cancelarContingencia);
        asignarEvento('eventoEstadoA', 'click', registrarContingencia);
        asignarEvento('eventoEstadoD', 'click', modificarContingencia);
    }
    document.getElementById('descripcionNota').readOnly = true;
    $('#checkNotaCredito').on('change', function () {
        console.log("Evento detectado");
        const esNotaCredito = $(this).is(':checked');

        if (esNotaCredito) {
            document.getElementById('descripcionNota').readOnly = false;
            document.getElementById('descripcionNo').readOnly = true;
            document.getElementById('montoNo').readOnly = true;
        } else {
            document.getElementById('descripcionNota').readOnly = true;
            $('#selectTipoDocumento').val(null).trigger('change').empty().prop('disabled', false);
            $('#tipoDocumento').val(null).trigger('change').empty();
            document.getElementById('descripcionNo').readOnly = false;
            document.getElementById('montoNo').readOnly = false;
            cancelar();
        }
    });

});

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
                    q: params.term,
                    tipo: $("#checkNotaCredito").is(":checked") ? "conNrc" : "todos"
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
    $("#selectCliente").on("select2:select", function (e) {


        $('#selectTipoDocumento').val(null).trigger('change').empty();
        $('#tipoDocumento').val(null).trigger('change').empty();
        const clienteId = e.params.data.id;
        // console.log("ID del cliente seleccionado:", clienteId);
        // Hacés la petición para consultar si tiene NRC
        $.ajax({
            url: base_url + "Facturacion/validarNRCCliente",
            type: "POST",
            dataType: "json",
            data: { id: clienteId },
            success: function (response) {

                const esNotaCredito = $('#checkNotaCredito').is(':checked');
                if (esNotaCredito) {
                    if (response.nota_credito) {
                        setSelect2Option(
                            "#selectTipoDocumento",
                            response.nota_credito.codigoTipoDocumento,
                            response.nota_credito.nombreTipoDocumento
                        );
                    } else {
                        console.console.warn("No se recibio tipo 05 en la respuesta");
                    }
                    if (response.relacionadoN && Array.isArray(response.relacionadoN)) {
                        const select = $("#tipoDocumento");

                        // Agregar opción por defecto "Seleccione"
                        select.append(new Option("Seleccione", "", true, false));

                        // Agregar cada documento como opción
                        response.relacionadoN.forEach(item => {
                            const option = new Option(item.nombreTipoDocumento, item.codigoTipoDocumento, false, false);
                            select.append(option);
                        });
                    }
                } else {

                    if (response.tipo_documento) {
                        setSelect2Option(
                            "#selectTipoDocumento",
                            response.tipo_documento.codigoTipoDocumento,
                            response.tipo_documento.nombreTipoDocumento
                        );
                    }

                    if (response.dataTipoRelacionado && Array.isArray(response.dataTipoRelacionado)) {
                        const select = $("#tipoDocumento");

                        // Agregar opción por defecto "Seleccione"
                        select.append(new Option("Seleccione", "", true, false));

                        // Agregar cada documento como opción
                        response.dataTipoRelacionado.forEach(item => {
                            const option = new Option(item.nombreTipoDocumento, item.codigoTipoDocumento, false, false);
                            select.append(option);
                        });
                    }
                }

            },

            error: function () {
                console.error("Error al validar el NRC del cliente.");
            }
        });

        eliminarDetallesAnteriores(); // Borra los detalles del cliente anterior en MySQL
    });
    $("#selectCliente").on("select2:unselect", function (e) {
        $('#selectTipoDocumento').val(null).trigger('change').empty();
        $('#tipoDocumento').val(null).trigger('change').empty();
    });

    // SELECT TIPO DOCUMENTO
    $("#tipoDocumento").select2({
        placeholder: "Seleccione tipo de documento",
        allowClear: true
    });

    $("#tipoGeneracion").select2({
        placeholder: "Seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/tipoGeneracion", // Ruta de búsqueda en tu backend
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
                    results: data // Aquí 'data' ya contiene el formato esperado: [{id, text}]
                };
            },
            cache: true
        }
    });
    $("#tipoGeneracion").on("select2:select", function (e) {
        const tipoGeneracion = e.params.data.id;
        const input = document.getElementById('numeroDoc')
        const label = document.getElementById('doc')
        if (tipoGeneracion == 1) {
            label.textContent = "Número de Correlativo"
            input.placeholder = "Número Correlativo"
        } else if (tipoGeneracion == 2) {
            label.textContent = "Código de Generación"
            input.placeholder = "Código  Generación"
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
    $("#condicion").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/tipoOperacion", // Ruta de búsqueda en tu backend
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

    $("#otrosAsociados").select2({
        placeholder: "Seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/docAsociados", // Ruta de búsqueda en tu backend
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
                    results: data // Aquí 'data' ya contiene el formato esperado: [{id, text}]
                };
            },
            cache: true
        }
    });

    $("#tipoServicio").select2({
        placeholder: "Seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/tipoServicioMedico", // Ruta de búsqueda en tu backend
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
                    results: data // Aquí 'data' ya contiene el formato esperado: [{id, text}]
                };
            },
            cache: true
        }
    });

    $("#documentoRelacionado").select2({
        placeholder: "Seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/obtenerDocumentosRelacionados",
            dataType: "json",
            delay: 250,
            processResults: function (data) {
                return {
                    results: data // Ya en el formato correcto [{ id, text }]
                };
            },
            cache: true
        }
    });

    // $("#unidadMedida").select2({
    //     placeholder: "Seleccione",
    //     allowClear: true,
    //     ajax: {
    //         url: base_url + "Facturacion/unidadDeMedida",
    //         dataType: "json",
    //         delay: 250,
    //         processResults: function (data) {
    //             return {
    //                 results: data // Ya en el formato correcto [{ id, text }]
    //             };
    //         },
    //         cache: true
    //     }
    // });

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


    // Selects de contingenci    
    $.ajax({
        url: base_url + "Facturacion/modeloFacturacion",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data && data.codigo && data.nombre) {
                $("#modeloFacturacionTexto").val(data.nombre);
                $("#modeloFacturacionCodigo").val(data.codigo);
            } else {
                $("#modeloFacturacionTexto").val("Modelo no disponible");
            }
        },
        error: function () {
            $("#modeloFacturacionTexto").val("Error al cargar modelo");
        }
    });



    $.ajax({
        url: base_url + "Facturacion/tipoTransmision",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data && data.codigo && data.nombre) {
                $("#tipoTransmisionTexto").val(data.nombre);
                $("#tipoTransmisionCodigo").val(data.codigo);
            } else {
                $("#tipoTransmisionTexto").val("Modelo no disponible");
            }
        },
        error: function () {
            $("#tipoTransmisionTexto").val("Error al cargar modelo");
        }
    });

    $("#tipoContingencia").select2({
        placeholder: "Seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Facturacion/tipoContingencia", // Ruta de búsqueda en tu backend
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
                    results: data.map(tipoContingencia => ({
                        id: tipoContingencia.codigo,
                        text: tipoContingencia.nombre
                    }))
                };
            },
            cache: true
        }
    });
    $("#tipoContingencia").on("select2:select", function (e) {
        const tipo = e.params.data.id;
        if (tipo == 5) {
            document.getElementById('motivoContingencia').readOnly = false;
        } else {
            document.getElementById('motivoContingencia').readOnly = true;
        }
    });
});

function setSelect2Option(selector, id, text) {
    // Crea una nueva opción si no existe
    let newOption = new Option(text, id, true, true);
    $(selector).append(newOption).trigger('change');
}


function eliminarDetallesAnteriores() {
    const url = base_url + "Facturacion/vaciarDetalleFacturacion";
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res === 'ok') {
                cargarDetalle(); // Recargar los detalles si fue exitoso
            }
        }
    };
}

function calcularCantidadTotal() {
    const cantidad = document.getElementById("cantidadProducto").value;
    const precio = document.getElementById("precioVenta").value.replace(/[^0-9.-]+/g, "");  // Eliminamos cualquier formato
    const total = parseFloat(precio) * cantidad;
    document.getElementById("totalSeleccionado").value = formatearNumero(total);
}

function cancelar() {

    document.getElementById('frmFacturacion').reset();

    // Limpiar todos los selects
    $('#selectTipoMovimiento').val(null).trigger('change');
    $('#selectProveedor').val(null).trigger('change');
    $('#selectCliente').val(null).trigger('change');
    $('#selectTipoDocumento').val(null).trigger('change');
    $('#selectTipoPago').val(null).trigger('change');
    $('#condicion').val(null).trigger('change');
    $('#codigoProyecto').val(null).trigger('change');
    $('#selectBanco').val(null).trigger('change');
    $('#otrosAsociados').val(null).trigger('change');
    $('#tablaMedico').val(null).trigger('change');
    recetearSelect();
    document.getElementById('tblTercero').innerHTML = '';
    document.getElementById('tblDoc').innerHTML = '';
    document.getElementById('tblDocM').innerHTML = '';
    document.getElementById('tblDocMedico').innerHTML = '';
    document.getElementById('numeroDoc').placeholder = '---';
    document.getElementById('doc').textContent = '';

    document.getElementById('descripcionNota').readOnly = true;
    $('#selectTipoDocumento').val(null).trigger('change').empty().prop('disabled', false);
    $('#tipoDocumento').val(null).trigger('change').empty();
    document.getElementById('descripcionNo').readOnly = false;
    document.getElementById('montoNo').readOnly = false;

    // Eliminar todos los documentos relacionados del backend (uno por uno)
    const filas = document.querySelectorAll("#tablaDoc tbody tr");
    filas.forEach(fila => {
        const numeroDocInput = fila.querySelector('input[name="documentosRelacionados[numeroDoc][]"]');
        if (numeroDocInput) {
            const numeroDoc = numeroDocInput.value;
            fetch(base_url + "Facturacion/eliminarDocumentoRelacionado", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ numeroDoc })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.warn("No se pudo eliminar documento relacionado:", numeroDoc);
                    }
                })
                .catch(err => {
                    console.error("Error eliminando de sesión:", err);
                });
        }
    });

    // Limpiar visualmente la tabla de documentos relacionados
    const tablaDoc = document.querySelector("#tablaDoc tbody");
    if (tablaDoc) {
        tablaDoc.innerHTML = "";
    }

    // Llamada AJAX para vaciar la tabla de compras en el backend
    const url = base_url + "Compras/vaciarDetalleCompra";
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res === 'ok') {
                cargarDetalle(); // Recargar los detalles si fue exitoso
            }
        }
    };
}



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

function generarFactura(e) {
    e.preventDefault();

    //Swal.fire({
      //title: 'Emitiendo DTE...',
       //text: 'Por favor espera un momento',
        //allowOutsideClick: false,
        //didOpen: () => {
          //  Swal.showLoading();
       // }
    //});

    const url = base_url + "Facturacion/generar";
    const frm = document.getElementById("frmFacturacion");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
        console.log(this.responseText);
            const res = JSON.parse(this.responseText);
            if (res.status === 'success') {
                // Mostrar primero los datos de emisión en SweetAlert
                Swal.fire({
                    title: 'Documento emitido correctamente',
                    icon: 'success',
                    html: `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow:auto;">${JSON.stringify(res.emision, null, 2)}</pre>`,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Abrir PDF de la factura
                    const formFactura = document.createElement('form');
                    formFactura.method = 'POST';
                    formFactura.action = base_url + 'Facturacion/vistaPreviaPDF';
                    formFactura.target = '_blank';

                    const inputFactura = document.createElement('input');
                    inputFactura.type = 'hidden';
                    inputFactura.name = 'data';
                    inputFactura.value = JSON.stringify(res.data);
                    formFactura.appendChild(inputFactura);
                    document.body.appendChild(formFactura);
                    formFactura.submit();
                    document.body.removeChild(formFactura);
                    cancelar();
                    
                    // // Abrir PDF de los datos técnicos (debug)
                    // const jsonDebug = res.data; // El JSON completo directamente


                    // fetch(base_url + 'Facturacion/vistaDebugJSON', {
                    //     method: 'POST',
                    //     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    //     body: 'jsonDebug=' + encodeURIComponent(JSON.stringify(jsonDebug))
                    // })
                    //     .then(response => response.blob())
                    //     .then(blob => {
                    //         const url = URL.createObjectURL(blob);
                    //         window.open(url, '_blank');
                    //     });
                });
            }
            else {
                Swal.fire({
                    title: "Error al emitir DTE",
                    icon: "error",
                    html: `<pre style="text-align:left; white-space:pre-wrap;">${JSON.stringify(res.message, null, 2)}</pre>`,
                    confirmButtonText: 'Cerrar'
                });

            }
        }
    };
}







function frmFacturacion() {
    document.getElementById('codigoProducto').readOnly = true;
    document.getElementById('cantidadProducto').readOnly = false;
    document.getElementById('precioCosto').readOnly = true;
    document.getElementById('precioVenta').readOnly = true;
    document.getElementById("totalSeleccionado").value = '';
    $('#documentoRelacionado').val(null).trigger('change');
    $('#unidadMedida').val(null).trigger('change');

    const filasDocumentos = document.querySelectorAll("#tblDoc tr");
    let codigoCliente = document.getElementById('selectCliente').value;
    let tipoDocumento = document.getElementById('selectTipoDocumento').value;

    if (!codigoCliente) {
        Swal.fire({
            position: "top-end",
            icon: "error",
            title: "Importante",
            text: "Seleccione un cliente para continuar",
            showConfirmButton: true,
        });
        return;
    }

    // Validación estricta para tipo 05
    if (tipoDocumento === '05' && filasDocumentos.length === 0) {
        Swal.fire({
            title: "Importante!",
            text: "Debe agregar al menos un Documento Relacionado antes de continuar",
            icon: "warning",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Agregar",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        }).then(() => {
            document.querySelector('#documentos-tab').click();
        });
        return; // Importante: detener la ejecución aquí
    }

    // Advertencia opcional para los demás documentos
    if (filasDocumentos.length === 0 && !advertenciaDocumentosMostrada) {
        advertenciaDocumentosMostrada = true;
        Swal.fire({
            title: "Importante!",
            text: "Si agregará Documentos Relacionados debe hacerlo antes de continuar",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Agregar",
            cancelButtonText: "No Agregar"
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('#documentos-tab').click();
            } else {
                mostrarModalAgregarProducto(codigoCliente);
            }
        });
    } else {
        mostrarModalAgregarProducto(codigoCliente);
    }
}


function mostrarModalAgregarProducto(codigoCliente) {
    let inputCodigoCliente = document.getElementById('codigoClienteDetalle');
    if (!inputCodigoCliente) {
        inputCodigoCliente = document.createElement('input');
        inputCodigoCliente.type = 'hidden';
        inputCodigoCliente.id = 'codigoClienteDetalle';
        inputCodigoCliente.name = 'codigoClienteDetalle';
        document.getElementById('frmDetalle').appendChild(inputCodigoCliente);
    }
    inputCodigoCliente.value = codigoCliente;
    validarDocExistente();
    $('#agregarProducto').modal("show");
}

function seleccionarProductos() {
    const url = base_url + "Facturacion/seleccionar";
    const frm = document.getElementById("frmDetalle");
    const tipoDte = document.getElementById("selectTipoDocumento").value; // Obtener el tipo de documento

    const formData = new FormData(frm);
    formData.append("tipoDte", tipoDte); // Lo agregas manualmente al FormData

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(formData);

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


function cargarDetalle() {
    const url = base_url + "Facturacion/listarDetalle";
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

            res.detalle.forEach(row => {
                html += `<tr>
                            <td>${row['item']}</td>
                            <td>${row['nombre']}</td>
                            <td>${row['nombreProducto']}</td>
                            <td>${row['cantidad']}</td>
                            <td>${formatearNumero(row['precioVenta'])}</td>
                            <td>${formatearNumero(row['total'])}</td>
                            <td>${row['documentoRelacionado']}</td>
                            <td>${row['descripcionN']}</td>
                            <td>
                                <button class="btn" type="button" id="eliminar-${row['id']}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
            });

            document.getElementById("tblDetalle").innerHTML = html;
            document.getElementById("subTotal").value = formatearNumero(res.subTotal);
            document.getElementById("iva").value = formatearNumero(res.ivaRetenido);
            document.getElementById("total").value = formatearNumero(res.total);

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
    const url = base_url + "Compras/eliminarDetalle/" + id;
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

// DOCUMETNO RELACIONADO 
function limpiarDte() {
    $('#tipoDocumento').val(null).trigger('change');
    $('#tipoGeneracion').val(null).trigger('change');
    document.getElementById('fechaEmision').value = '';
    const input = document.getElementById('numeroDoc');
    const label = document.getElementById('doc');
    label.textContent = "";
    input.value = '';
    input.placeholder = "---";
    // document.getElementById('tblDoc').innerHTML = '';
}

function LimpiarDocAS() {
    //$('#otrosAsociados').val(null).trigger('change');
    document.getElementById('identificacionDocumento').value = '';
    document.getElementById('descripcionDocumento').value = '';
    //     document.getElementById('tblDocM').innerHTML = '';
}

function limpiarMedico() {
    $('#tipoServicio').val(null).trigger('change');
    document.getElementById('nombreMedico').value = ''
    document.getElementById('tipoDocumentoAS').value = ''
    document.getElementById('nitMedico').value = ''
    //     document.getElementById('tblDocMedico').innerHTML = '';
}

function validarDocExistente() {
    const filas = document.querySelectorAll('#tablaDoc tbody tr');
    if (filas.length > 0) {
        document.getElementById('documentoRelacionado').disabled = false;
    } else {
        document.getElementById('documentoRelacionado').disabled = true;
    }
}


function limpiarVenta() {
    document.getElementById('nit').value = '';
    document.getElementById('nombreTercero').value = '';
}

function generarPdfVista() {
    const ruta = base_url + 'Facturacion/generarPDF/';
    window.open(ruta);
}


// codigo de contingencia
function mostrarContingencia() {
    const url = base_url + "Facturacion/editarContingenciaBtn";
    enviarPeticion(url, "GET", null, (res) => {
        if (res && res.tipoContin) {
            // Insertar manualmente el valor en Select2
            const newOption = new Option(res.valor, res.tipoContin, true, true);
            $('#tipoContingencia').append(newOption).trigger('change');
            $('#tipoContingencia').prop('disabled', true);

            document.getElementById("motivoContingencia").value = res.motivo;
            document.getElementById("motivoContingencia").readOnly = true;

            $('#contingencia').modal({
                backdrop: 'static',
                keyboard: false
            });
        } else {
            document.getElementById('motivoContingencia').readOnly = true;
            $('#contingencia').modal({
                backdrop: 'static',
                keyboard: false
            });
        }
    });
}



function cancelarContingencia() {
    document.getElementById('motivoContingencia').value = '';
    $('#tipoContingencia').val(null).trigger('change');
    $('#contingencia').modal("hide");
}

function registrarContingencia(e) {
    e.preventDefault();
    const url = base_url + "Facturacion/registrarContingencia";
    const frm = document.getElementById("frmContingencia");

    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Contingencia activada",
                showConfirmButton: false,
                timer: 3000
            });
            $("#contingencia").modal("hide");
            document.querySelector('.container-fluid').classList.add('contingencia-activa');
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

function modificarContingencia(e) {
    e.preventDefault();
    const url = base_url + "Facturacion/modificarContingencia";
    const frm = document.getElementById("frmContingencia");

    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Contingencia desactivada",
                showConfirmButton: false,
                timer: 3000
            });
            document.querySelector('.container-fluid').classList.remove('contingencia-activa');
            $("#contingencia").modal("hide");
            $('#tipoContingencia').prop('disabled', false);
            document.getElementById('motivoContingencia').value = '';
            $('#tipoContingencia').val(null).trigger('change');
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



export { cancelar, recetearSelect, frmFacturacion, seleccionarProductos, cargarDetalle, generarFactura, limpiarDte, LimpiarDocAS, limpiarMedico, limpiarVenta, generarPdfVista, mostrarContingencia, cancelarContingencia, registrarContingencia, modificarContingencia }