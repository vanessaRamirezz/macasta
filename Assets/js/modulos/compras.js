import { formatearNumero, configurarTabla } from "../utilidades/tablePeticion.js";
import { calcularCantidadTotal } from "./detalleTemporal.js";
let tblHistorial;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaCompras')) {
        tblHistorial = configurarTabla('#tblHistorial', "Compras/listar", [
            { 'data': 'codigo' },
            { 'data': 'fe' },
            { 'data': 'proveedor' },
            { 'data': 'fecha' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchHistorial',
            searchBtnId: 'searchBtnHistorial',
            clearSearchBtnId: 'clearSearchBtnHistorial'
        });

        const registrarCompras = document.getElementById('generarCompra');
        registrarCompras.addEventListener('click', registrarCompra);

        // Asignar evento al botón de acción cancelar
        const btnCancelar = document.getElementById('cancelar');
        btnCancelar.addEventListener('click', Cancelar);

        // Delegación de eventos para los botones de vista pdf
        document.querySelector('#tblHistorial tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const documetoCodigo = e.target.closest('.btn-editar').getAttribute('data-id');
                generarPdfVista(documetoCodigo);
            }
        });
    }
});

$(document).ready(function () {
    // SELECT TIPO MOVIMIENTO
    $('#codigoTipoMovimiento').select2({
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


function cargarDetalle() {
    const url = base_url + "Compras/listarDetalle";
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            let html = '';
            res.detalle.forEach(row => {
                html += `<tr>
                            <td>${row['item']}</td>
                            <td>${row['nombreProducto']}</td>
                            <td >${row['cantidad']}</td>
                            <td>${formatearNumero(row['costoProducto'])}</td>
                            <td>${formatearNumero(row['total'])}</td>
                            <td>
                                <button class="btn" type="button" id="eliminar-${row['id']}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
            });
            document.getElementById("tblDetalle").innerHTML = html;
            const totalInput = document.getElementById("total");
            if (totalInput) {
                let total = res.totalPagar.totalPagar;

                // Verificar si es un número válido antes de formatear
                if (!isNaN(total) && total !== null && total !== undefined && total !== '') {
                    totalInput.value = formatearNumero(total);
                } else {
                    totalInput.value = ''; // Si no hay dato, dejar vacío
                }
            }

            // Agregar evento a los botones después de cargar el detalle
            res.detalle.forEach(row => {
                const btnEliminar = document.getElementById(`eliminar-${row['id']}`);
                if (btnEliminar) {
                    btnEliminar.addEventListener('click', () => eliminarDetalle(row['id']));
                }
            });

            // Vaciado de detalles al cambiar de página
            window.addEventListener('beforeunload', function () {
                Cancelar(); // Llama a la función para vaciar los detalles
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

function Cancelar() {
    document.getElementById('frmCompras').reset();

    // Vaciar los campos seleccionados de los select2
    $('#codigoTipoMovimiento').val(null).trigger('change');
    $('#codigoProveedor').val(null).trigger('change');
    $('#codigoProyecto').val(null).trigger('change');

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

function registrarCompra() {
    const totalInput = document.getElementById("total");
    const totalCompra = parseFloat(totalInput.value) || 0; // Obtener el total de la compra
    const codigoProveedor = document.getElementById("codigoProveedor").value; // Obtener el código del proveedor

    // Obtener el límite del proveedor antes de registrar la compra
    fetch(base_url + "Compras/limiteProveedor/" + codigoProveedor)
        .then(response => response.json())
        .then(data => {
            if (!codigoProveedor) {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: "Debe seleccionar un proveedor para continuar",
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            } else {
                const limiteCredito = parseFloat(data.limiteCreditoProveedor) || 0;
                if (totalCompra > limiteCredito) {
                    // Mostrar advertencia y esperar confirmación antes de proceder
                    Swal.fire({
                        title: "Límite excedido",
                        text: `El total de la compra supera el límite del proveedor`,
                        icon: "warning",
                        showCancelButton: false,
                        confirmButtonText: "Entendido"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Si el usuario acepta, registrar la compra
                            realizarRegistroCompra();
                        }
                    });
                } else {
                    // Si no excede el límite, registrar directamente la compra
                    realizarRegistroCompra();
                }
            }
        })
        .catch(error => console.error("Error obteniendo el límite del proveedor:", error));
}

// Función para registrar la compra
function realizarRegistroCompra() {
    const url = base_url + "Compras/registrar";
    const frm = document.getElementById("frmCompras");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));

    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);

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
                tblHistorial.ajax.reload();
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



function generarPdfVista(numeroDocumento) {
    const ruta = base_url + 'Compras/generarPdf/' + numeroDocumento;
    window.open(ruta);
}


export { cargarDetalle, eliminarDetalle, registrarCompra, Cancelar, generarPdfVista }