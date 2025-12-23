import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';

export let tblTiposMovimientos;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaTipoMovimientos')) {
        tblTiposMovimientos = configurarTabla('#tblTiposMovimientos', "TipoMovimiento/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'aplicacion' },
            { 'data': 'efecto' },
            { 'data': 'acciones' }
        ], [], {
            searchInputId: 'customSearchMovimientos',
            searchBtnId: 'searchBtnMovimientos',
            clearSearchBtnId: 'clearSearchBtnMovimientos'
        });

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionTiposMovimiento);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblTiposMovimientos tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoTipoMovimiento = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarMovimiento(codigoTipoMovimiento);
            }
        });

        // Asignar evento al botón de acción cancelar
        const btnCancelar = document.getElementById('btn-cancelar');
        btnCancelar.addEventListener('click', btnCancelarM);

    }

})

/// Tipos De Movimientos
function btnCancelarM() {
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("frmTiposMovimientos").reset();
    // Limpia el valor del select
    const selectAplicacion = document.getElementById("codigoAplicacion");
    selectAplicacion.innerHTML = '<option value="">Seleccione...</option>'; // Opcional: Limpia las opciones dinámicas
    selectAplicacion.value = ""; // Establece el valor por defecto
}
function registrarOmodificarMovimiento(e, accion) {
    e.preventDefault();
    const url = base_url + "TipoMovimiento/" + accion;
    const frm = document.getElementById("frmTiposMovimientos");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Movimiento registrado" : "Movimiento modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            tblTiposMovimientos.ajax.reload();
            document.getElementById("btnAccion").innerHTML = "Registrar"
            document.getElementById("codigo").readOnly = false;
            // Limpia el valor del select
            const selectAplicacion = document.getElementById("codigoAplicacion");
            selectAplicacion.innerHTML = '<option value="">Seleccione...</option>'; // Opcional: Limpia las opciones dinámicas
            selectAplicacion.value = ""; // Establece el valor por defecto
            
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
function registrarMovimiento(e) {
    registrarOmodificarMovimiento(e, "registrar");
}
function modificarMovimiento(e) {
    registrarOmodificarMovimiento(e, "modificar");
}
function btnAccionTiposMovimiento(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarMovimiento(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarMovimiento(e);
    }
}
function btnEditarMovimiento(codigoTipoMovimiento) {
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "TipoMovimiento/editar/" + codigoTipoMovimiento;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("efecto").value = res.efecto;

        // Cargar el <select> con el ID del movimiento
        const selectAplicacion = document.getElementById("codigoAplicacion")
        selectAplicacion.innerHTML = '<option value="">Seleccione...</option>';

        const option = document.createElement("option");
        option.value = res.aplicacion;
        option.textContent = res.nombreAplicacion;
        option.selected = true;
        selectAplicacion.appendChild(option);
    })
}

$(document).ready(function () {
    $("#codigoAplicacion").select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "TipoMovimiento/searchAplicaciones", // Ruta de búsqueda en tu backend
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
                    results: data.map(aplicacion => ({
                        id: aplicacion.codigo,
                        text: aplicacion.nombre
                    }))
                };
            },
            cache: true
        }
    });
});


export { btnCancelarM, btnAccionTiposMovimiento, btnEditarMovimiento }