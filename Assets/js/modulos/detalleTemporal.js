import { formatearNumero } from "../utilidades/tablePeticion.js";
import { cargarDetalle } from "../modulos/compras.js";

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaCompras')) {
        const btnAgregarProducto = document.getElementById('nuevo');
        btnAgregarProducto.addEventListener('click', frmProductos);

        // Evento para calcular el total al escribir o usar las flechas
        document.getElementById('cantidadProducto').addEventListener('input', calcularCantidadTotal);
        document.getElementById('precioCosto').addEventListener('input', calcularCantidadTotal);

        const detalle = document.getElementById('registrarProducto');
        detalle.addEventListener('click', registrarDetalle);

        cargarDetalle();
    }
});

function frmProductos() {
    document.getElementById("codigoProducto").readOnly = true;
    $("#agregarProducto").modal("show");
}

function calcularCantidadTotal() {
    const cantidad = document.getElementById("cantidadProducto").value;
    const precio = document.getElementById("precioCosto").value.replace(/[^0-9.-]+/g, "");  // Eliminamos cualquier formato
    const total = parseFloat(precio) * parseInt(cantidad);
    document.getElementById("totalSeleccionado").value = formatearNumero(total);
}

function registrarDetalle() {

    const url = base_url + "DetalleTemporal/ingresar";
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

$(document).ready(function () {
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
            $("#cantidadProducto").val(data.cantidad);
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

export { frmProductos, calcularCantidadTotal, registrarDetalle }