import { formatearNumero, configurarTabla } from "../utilidades/tablePeticion.js";
let tblHistorial;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaCotizacion')) {
        tblHistorial = configurarTabla('#tblHistorial', "Cotizacion/listar", [
            { 'data': 'codigo' },
            { 'data': 'cliente' },
            { 'data': 'proyecto' },
            { 'data': 'fecha' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchHistorial',
            searchBtnId: 'searchBtnHistorial',
            clearSearchBtnId: 'clearSearchBtnHistorial'
        });
        const btnCancelar = document.getElementById('cancelar');
        btnCancelar.addEventListener('click', cancelar);

        const frmProducto = document.getElementById('nuevo');
        frmProducto.addEventListener('click', frmProductos);

        const seleccionar = document.getElementById('seleccionarProducto');
        seleccionar.addEventListener('click', seleccionarProductos);

        // Evento para calcular el total al escribir o usar las flechas
        document.getElementById('cantidadProducto').addEventListener('input', calcularCantidadTotal);
        document.getElementById('precioVenta').addEventListener('input', calcularCantidadTotal);
        cargarDetalle();

        const pdf = document.getElementById('generarCotizacion');
        pdf.addEventListener('click', cotizacion);

        document.querySelector('#tblHistorial tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoCotizacion = e.target.closest('.btn-editar').getAttribute('data-id');
                generarPdfVista(codigoCotizacion);
            }
        });

        // Vaciado de detalles al cambiar de página
        window.addEventListener('beforeunload', function () {
            cancelar(); // Llama a la función para vaciar los detalles
        });
    }
});

$(document).ready(function () {
    // SELECT CLIENTE 
    $("#codigoCliente").select2({
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
    $("#codigoCliente").on("select2:select", function () {
        eliminarDetallesAnteriores(); // Borra los detalles del cliente anterior en MySQL
    });



    // SELECT PROVEEDORES
    $("#codigoProveedor").select2({
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
});

function eliminarDetallesAnteriores() {
    const url = base_url + "Cotizacion/vaciarDetalleCotizacion";
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


function cancelar() {
    document.getElementById('frmCotizacion')
    document.getElementById('codigoCotizacion').value = '';
    $('#codigoCliente').val('').trigger('change');
    $('#codigoProyecto').val('').trigger('change');

    const url = base_url + "Cotizacion/vaciarDetalleCotizacion";
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

function frmProductos() {

    document.getElementById('codigoProducto').readOnly = true;
    document.getElementById('cantidadProducto').readOnly = false;
    document.getElementById('precioCosto').readOnly = true;
    document.getElementById('precioVenta').readOnly = true;
    document.getElementById("totalSeleccionado").value = '';

    let codigoCliente = document.getElementById('codigoCliente').value;
    if (!codigoCliente) {
        Swal.fire({
            position: "top-end",
            icon: "error",
            title: "Debe seleccionar un cliente para continuar",
            showConfirmButton: false,
            timer: 3000
        });
        return;
    } else {
        let inputCodigoCliente = document.getElementById('codigoClienteDetalle');
        if (!inputCodigoCliente) {
            inputCodigoCliente = document.createElement('input');
            inputCodigoCliente.type = 'hidden';
            inputCodigoCliente.id = 'codigoClienteDetalle';
            inputCodigoCliente.name = 'codigoClienteDetalle';
            document.getElementById('frmDetalle').appendChild(inputCodigoCliente);
        }
        inputCodigoCliente.value = codigoCliente;
        $('#agregarProducto').modal("show");
    }
}

function calcularCantidadTotal() {
    const cantidad = document.getElementById("cantidadProducto").value;
    const precio = document.getElementById("precioVenta").value.replace(/[^0-9.-]+/g, "");  // Eliminamos cualquier formato
    const total = parseFloat(precio) * parseInt(cantidad);
    document.getElementById("totalSeleccionado").value = formatearNumero(total);
}

function seleccionarProductos() {
    const url = base_url + "Cotizacion/seleccionar";
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
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }
    }
}

function cotizacion(e) {
    e.preventDefault();
    const url = base_url + "Cotizacion/registrarCotizacion";
    const frm = document.getElementById("frmCotizacion");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);

            const res = JSON.parse(this.responseText);
            if (res.status == 'si') {
                const codigoCotizacion = res.codigoCotizacion; // Usar el código corregido

                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Registrado",
                    showConfirmButton: false,
                    timer: 3000
                });
                // Abrir el reporte en una nueva pestaña
                const ruta = base_url + 'Cotizacion/cotizacionPdf/' + codigoCotizacion;
                window.open(ruta, '_blank');
                frm.reset();
                setTimeout(() => {
                    window.location.reload();
                }, 300);
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
    };
}

function generarPdfVista(codigoCotizacion) {
    const ruta = base_url + 'Cotizacion/cotizacionPdf/' + codigoCotizacion;
    window.open(ruta);
}

function cargarDetalle() {
    const url = base_url + "Cotizacion/listarDetalle";
    const codigoCliente = document.getElementById("codigoCliente").value; // Capturar el código del cliente
    const formData = new FormData();
    formData.append("codigoCliente", codigoCliente);

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(formData);

    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            let html = '';

            res.detalle.forEach(row => {
                html += `<tr>
                            <td>${row['id']}</td>
                            <td>${row['nombreProducto']}</td>
                            <td>${row['cantidad']}</td>
                            <td>${formatearNumero(row['precioVenta'])}</td>
                            <td>${formatearNumero(row['total'])}</td>
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

export { cancelar, frmProductos, seleccionarProductos, cotizacion, generarPdfVista, cargarDetalle }