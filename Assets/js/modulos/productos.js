import { configurarTabla, enviarPeticion, formatearMoneda, formatearNumero } from "../utilidades/tablePeticion.js";
export let tblProductos;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaProductos')) {
        tblProductos = configurarTabla('#tblProductos', "Productos/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            {
                'data': 'costo',
                'render': function (data, type, row) {
                    return formatearNumero(data);
                }
            },
            {
                'data': 'precio',
                'render': function (data, type, row) {
                    return formatearNumero(data);
                }
            },
            { 'data': 'cantidad' },
            { 'data': 'proveedor' },
            { 'data': 'agrupacion' },
            { 'data': 'acciones' }
        ], [
            {
                extend: 'colvis',
                text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                postfixButtons: ['colvisRestore']
            },
        ], {
            searchInputId: 'customSearchProductos',
            searchBtnId: 'searchBtnProductos',
            clearSearchBtnId: 'clearSearchBtnProductos'
        });

        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmProducto);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionProducto);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblProductos tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoProducto = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarProducto(codigoProducto);
            }
        });

        // Aplicar formateo mientras el usuario escribe en los inputs
        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            });
        });
    }
})

// Inicio Productos
function frmProducto() {
    document.getElementById("title").innerHTML = "Agregar Producto"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("cantidad").readOnly = true;
    document.getElementById("frmProducto").reset();
    document.getElementById("codigo").value = "";
    $("#nuevoProducto").modal("show");
}
function registrarOmodificarProducto(e, accion) {
    e.preventDefault();
    const url = base_url + "Productos/" + accion;
    const frm = document.getElementById("frmProducto");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Producto registrado" : "Producto modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoProducto").modal("hide");
            tblProductos.ajax.reload();
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
function registrarProducto(e) {
    registrarOmodificarProducto(e, "registrar");
}
function modificarProducto(e) {
    registrarOmodificarProducto(e, "modificar");
}
function btnAccionProducto(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarProducto(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarProducto(e);
    }
}
function btnEditarProducto(codigoProducto) {
    document.getElementById("title").innerHTML = "Editar Producto";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    document.getElementById("cantidad").readOnly = true;
    const url = base_url + "Productos/editar/" + codigoProducto;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("cantidad").value = res.cantidad;

        const selectProveedor = document.getElementById("codigoProveedor");
        selectProveedor.innerHTML = '<option value="">Seleccione...</option>';
        const option = document.createElement("option");
        option.value = res.proveedor;
        option.textContent = res.nombreProveedor;
        option.selected = true;
        selectProveedor.appendChild(option);

        document.getElementById("precio").value = formatearNumero(res.precio);
        document.getElementById("costo").value = formatearNumero(res.costo);

        const selectAgrupacion = document.getElementById("codigoAgrupacion");
        selectAgrupacion.innerHTML = '</option value="">Seleccione...</option>';
        const optionAgrupacion = document.createElement("option");
        optionAgrupacion.value = res.agrupacion;
        optionAgrupacion.textContent = res.nombreAgrupacion;
        optionAgrupacion.selected = true;
        selectAgrupacion.appendChild(optionAgrupacion);
        $("#nuevoProducto").modal("show");
    })
}
$(document).ready(function () {
    // Aseguramos que la inicialización de Select2 se haga solo después de que el modal esté visible
    $('#nuevoProducto').on('shown.bs.modal', function () {
        // Inicializar select2 para Proveedor
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
            dropdownParent: $('#nuevoProducto') // Evitamos problemas de z-index
        });

        // Inicializar select2 para Agrupación
        $("#codigoAgrupacion").select2({
            placeholder: "Busque y seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "Productos/buscarAgrupaciones",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(agrupacion => ({
                            id: agrupacion.codigo,
                            text: agrupacion.nombre
                        }))
                    };
                },
                cache: true
            },
            dropdownParent: $('#nuevoProducto') // Asegura que no se corten los resultados
        });
    });

    $('#nuevoProducto').on('hidden.bs.modal', function () {
        $('#codigoProveedor').empty().val(null).trigger('change');
        $('#codigoAgrupacion').empty().val(null).trigger('change');
    });
    
});
export { frmProducto, btnAccionProducto, btnEditarProducto }