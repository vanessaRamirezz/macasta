import { asignarEvento } from "../utilidades/tablePeticion.js";
$(document).ready(function () {
    $(".cliente").select2({
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

    //CLIENTE MOVIMIENTO
    $(".otraClase").select2({
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
    $(".otraClase").on("select2:select", function (e) {
        $('.codigoProveedor').empty().trigger('change');
        $('#codigoEmpleado').empty().trigger('change');
    });

    $(".codigoProyecto").select2({
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

    $(".codigoProveedor").select2({
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
    $(".codigoProveedor").on("select2:select", function (e) {
        $('.otraClase').empty().trigger('change');
        $('#codigoEmpleado').empty().trigger('change');
    });

    $("#codigoProducto").select2({
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
                    }))
                };
            },
            cache: true
        }
    });

    $('#tipoMovimiento').select2({
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

    $('#codigoEmpleado').select2({
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
    $("#codigoEmpleado").on("select2:select", function (e) {
        $('.otraClase').empty().trigger('change');
        $('.codigoProveedor').empty().trigger('change');
    });
    
    $("#tipoDocumento").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Reportes/buscarDte", // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            processResults: function (data) {
                // Mapea los resultados para adaptarlos al formato de Select2
                return {
                    results: data.map(dte => ({
                        id: dte.codigo,
                        text: dte.nombre
                    }))
                };
            },
            cache: true
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaReportes')) {
        asignarEvento('limpiar', 'click', limpiarProyectos)
        asignarEvento('limpiarCompras', 'click', limpiarCompras)
        asignarEvento('limpiarProductos', 'click', limpiarProducto)
        asignarEvento('limpiarMovimiento', 'click', limpiarMovimiento)
        asignarEvento('limpiarProveedores', 'click', limpiarProveedor)
        asignarEvento('limpiarClientes', 'click', limpiarCliente)
        asignarEvento('limpiarCotizaciones', 'click', limpiarCotizacion)
        asignarEvento('limpiarExistencias', 'click', limpiarExistencia)
        asignarEvento('limpiarFacturas', 'click', limpiarFacturas)
    }
})


function limpiarProyectos() {
    document.getElementById("frmReporteProyectos").reset();
    $('.cliente').val(null).trigger('change');
}

function limpiarCompras() {
    document.getElementById("frmReporteCompras").reset();
    $('#codigoProyecto').val(null).trigger('change');
}

function limpiarProducto() {
    document.getElementById("frmReporteProductos").reset();
    $('.codigoProveedor').val(null).trigger('change');
    $('.codigoProducto').val(null).trigger('change');
}

function limpiarMovimiento() {
    document.getElementById("frmReporteMovimientos").reset();
    $('.otraClase').val(null).trigger('change');
    $('.codigoProveedor').val(null).trigger('change');
    $('#codigoEmpleado').val(null).trigger('change');
    $('#tipoMovimiento').val(null).trigger('change');
}

function limpiarProveedor() {
    document.getElementById("frmReporteProveedores").reset();
}

function limpiarCliente() {
    document.getElementById("frmReporteClientes").reset();
}

function limpiarCotizacion() {
    document.getElementById("frmCotizaciones").reset();
    $('.cliente').val(null).trigger('change');
}

function limpiarExistencia() {
    document.getElementById("frmReporteExistencias").reset();
    $('.codigoProyecto').val(null).trigger('change');
}

function limpiarFacturas() {
    document.getElementById("frmReporteFacturas").reset();
    $('#tipoDocumento').val(null).trigger('change');
}

export { limpiarProyectos, limpiarCompras, limpiarProducto, limpiarMovimiento, limpiarProveedor, limpiarCliente, limpiarCotizacion, limpiarExistencia, limpiarFacturas };