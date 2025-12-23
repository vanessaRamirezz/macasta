import { configurarTabla, enviarPeticion, formatearMoneda, formatearNumero } from "../utilidades/tablePeticion.js";
export let tblProyectos;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaProyectos')) {
        tblProyectos = configurarTabla('#tblProyectos', "Proyectos/listar", [
            { 'data': 'codigo' },
            { 'data': 'proyecto' },
            { 'data': 'inicio' },
            { 'data': 'fin' },
            { 'data': 'cliente' },
            { 'data': 'responsable' },
            { 'data': 'estado' },
            { 'data': 'acciones' }
        ], [
            {
                extend: 'colvis',
                text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                postfixButtons: ['colvisRestore']
            },
        ], {
            searchInputId: 'customSearchProyectos',
            searchBtnId: 'searchBtnProyectos',
            clearSearchBtnId: 'clearSearchBtnProyectos'
        });

        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmProyecto);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionProyecto);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblProyectos tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoProyecto = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarProyecto(codigoProyecto);
            }
        });

        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            })
        })



        checkbox.addEventListener("change", function () {
            $("#responsable").prop("disabled", !this.checked).trigger("change");
        });


        resetModal();
    }

})
let checkbox = document.getElementById("flexCheckIndeterminate");

function resetModal() {
    checkbox.checked = false;
    $("#responsable").prop("disabled", true).val(null).trigger("change").empty();
}
function frmProyecto() {
    document.getElementById("title").innerHTML = "Agregar Proyecto"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigoProyecto").readOnly = false;
    document.getElementById("ingresos").readOnly = true;
    document.getElementById("salidas").readOnly = true;
    document.getElementById("responsable").readOnly = false;
    document.getElementById("frmProyecto").reset();
    document.getElementById("codigoProyecto").value = "";
    $("#codigoCliente").val(null).trigger("change").empty();
    $("#estado").val(null).trigger("change").empty();
    $("#nuevoProyecto").modal("show");
    resetModal();
}

function registrarOmodificarProyecto(e, accion) {
    e.preventDefault();
    const url = base_url + "Proyectos/" + accion;
    const frm = document.getElementById("frmProyecto");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Proyecto registrado" : "Proyecto modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoProyecto").modal("hide");
            tblProyectos.ajax.reload();
        } else {
            Swal.fire({
                position: "top-end",
                icon: "error",
                title: res,
                showConfirmButton: false,
                timer: 3000
            });
            console.log(res);

        }
    });
}
function registrarProyecto(e) {
    registrarOmodificarProyecto(e, "registrar");
}
function modificarProyecto(e) {
    registrarOmodificarProyecto(e, "modificar");
}
function btnAccionProyecto(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarProyecto(e);
    } else if (boton.innerHTML == "Actualizar") {
        modificarProyecto(e);
    }
}
function btnEditarProyecto(codigoProyecto) {
    document.getElementById("title").innerHTML = "Actualizar Proyecto";
    document.getElementById("btnAccion").innerHTML = "Actualizar";
    document.getElementById("codigoProyecto").readOnly = true;
    document.getElementById("ingresos").readOnly = true;
    document.getElementById("salidas").readOnly = true;
    const url = base_url + "Proyectos/editar/" + codigoProyecto;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigoProyecto").value = res.codigo;
        document.getElementById("nombreProyecto").value = res.proyecto;
        document.getElementById("fechaInicio").value = res.inicio;
        document.getElementById("fechaFin").value = res.fin;

        const selectCliente = document.getElementById("codigoCliente");
        selectCliente.innerHTML = '<option value="">Seleccione...</option>';
        const option = document.createElement("option");
        option.value = res.cliente;
        option.textContent = res.nombreCliente;
        option.selected = true;
        selectCliente.appendChild(option);

        document.getElementById("valorCotizado").value = formatearNumero(res.valorCotizado);
        document.getElementById("ingresos").value = formatearNumero(res.ingresos);
        document.getElementById("salidas").value = formatearNumero(res.salidas);

        // Limpiar los inputs relacionados con el responsable antes de asignar los nuevos valores
        $("#codigoResponsable").val("");
        $("#nombreResponsable").val("");
        $("#telefono").val("");
        
        document.getElementById("codigoResponsable").value = res.responsable;
        document.getElementById("nombreResponsable").value = res.nombreResponsable;
        document.getElementById("telefono").value = res.telefono;

        
        // en caso sea seleccionado desde el select
        $("#responsable").append(new Option(res.nombreResponsable, res.responsable, res.numero, true, true)).trigger('change');


        document.getElementById("valorRentabilidad").value = formatearNumero(res.rentabilidad);

        const selectEstado = document.getElementById("estado");
        selectEstado.innerHTML = '<option value="">Seleccione...</option>';
        const optionEstado = document.createElement("option");
        optionEstado.value = res.estado;
        optionEstado.textContent = res.nombreEstado;
        optionEstado.selected = true;
        selectEstado.appendChild(optionEstado);

        $("#nuevoProyecto").modal("show");
        resetModal();
    })
}


$(document).ready(function () {

    $('#nuevoProyecto').on('shown.bs.modal', function () {

        //select2 responsables
        $("#responsable").select2({
            placeholder: "Busque y seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "Proyectos/buscarResponsables", // Ruta de búsqueda en tu backend
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
                        results: data.map(responsables => ({
                            id: responsables.codigo,
                            text: responsables.nombre,
                            numero: responsables.numero
                        }))
                    };
                },
                cache: true
            }
        });
        // Evento cuando se selecciona un responsable
        $("#responsable").on("select2:select", function (e) {
            let data = e.params.data; // Obtener datos seleccionados
            $("#codigoResponsable").val(data.id); // Asignar código
            $("#nombreResponsable").val(data.text); // Asignar nombre
            $("#telefono").val(data.numero); // Asignar teléfono
        });

        // Evento cuando se deselecciona un responsable
        $("#responsable").on("select2:unselect", function (e) {
            // Limpiar los inputs asociados
            $("#codigoResponsable").val(""); // Limpiar código
            $("#nombreResponsable").val(""); // Limpiar nombre
            $("#telefono").val(""); // Limpiar teléfono
        });

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

        // select2 estados
        $("#estado").select2({
            placeholder: "Busque y seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "Proyectos/buscarEstados", // Ruta de búsqueda en tu backend
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
                        results: data.map(estados => ({
                            id: estados.codigo,
                            text: estados.nombre
                        }))
                    };
                },
                cache: true
            }
        });
    });
    $('#nuevoProyecto').on('hidden.bs.modal', function () {
        $('#estado').val(null).trigger('change');
        $('#responsable').val(null).trigger('change');
        $('#codigoCliente').val(null).trigger('change');
    });
});

export { frmProyecto, btnAccionProyecto, btnEditarProyecto }