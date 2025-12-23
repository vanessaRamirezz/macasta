import { formatearMoneda } from "../utilidades/tablePeticion.js";

document.addEventListener("DOMContentLoaded", function () {


    document.querySelectorAll('.moneda').forEach(input => {
        input.addEventListener('input', function () {
            formatearMoneda(this);
        })
    })

    const btnCancelar = document.getElementById('cancelar');
    btnCancelar.addEventListener('click', cancelar);

    // Asignar evento al botón de acción (Registrar)
    const btnRegistrar = document.getElementById('generarSalida');
    btnRegistrar.addEventListener('click', resgistrarSalida);
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
});


function cancelar() {
    document.getElementById('frmSalidas').reset();
    $('#selectTipoMovimiento').val(null).trigger('change');
    $('#selectProveedor').val(null).trigger('change');
    $('#codigoProyecto').val(null).trigger('change');
}

function resgistrarSalida() {
    const url = base_url + "Salidas/registrar";
    const frm = document.getElementById("frmSalidas");
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
                $('#selectTipoMovimiento').val(null).trigger('change');
                $('#selectProveedor').val(null).trigger('change');
                $('#codigoProyecto').val(null).trigger('change');
                // const ruta = base_url + 'Compras/generarPdf/' + res.numeroDocumento;
                // window.open(ruta);
                // setTimeout(() => {
                //     window.location.reload();
                // }, 300);
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

export { cancelar, resgistrarSalida }