import { configurarTabla, enviarPeticion, formatearMoneda, formatearNumero, asignarEvento } from '../utilidades/tablePeticion.js';
let tblClientes;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaClientes')) {
        tblClientes = configurarTabla('#tblClientes', "Clientes/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'nrc' },
            { 'data': 'telefono' },
            { 'data': 'contacto' },
            {
                'data': 'creditoLimite',
                'render': function (data, type, row) {
                    return formatearNumero(data);
                }
            },
            {
                'data': 'saldo',
                'render': function (data, type, row) {
                    return formatearNumero(data);
                }
            },
            { 'data': 'acciones' }
        ],
            [
                {
                    extend: 'colvis',
                    text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                    postfixButtons: ['colvisRestore']
                }
            ], {
            searchInputId: 'customSearchClientes',
            searchBtnId: 'searchBtnClientes',
            clearSearchBtnId: 'clearSearchBtnClientes'
        });

        asignarEvento('btn-colorNuevo', 'click', frmCliente);
        asignarEvento('btnAccion', 'click', btnAccionCliente);


        // Delegación de eventos para los botones de editar
        document.querySelector('#tblClientes tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoCliente = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarCliente(codigoCliente);
            }
        });

        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            })
        })
    }
});
// Inicio Clientes
function frmCliente() {
    Swal.fire({
        title: 'Seleccione Tipo de Persona',
        html: `
            <select id="tipoPersonaSelect" class="form-control" style="width: 100%;">
                <option value="">Seleccione...</option>
            </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        didOpen: () => {
            $('#tipoPersonaSelect').select2({
                dropdownParent: $('.swal2-popup'),
                placeholder: 'Seleccione',
                allowClear: true,
                ajax: {
                    url: base_url + "Clientes/tipoPersona",
                    dataType: "json",
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(item => ({
                            id: item.codigo,
                            text: item.nombre
                        }))
                    }),
                    cache: true
                }
            });
        },
        preConfirm: () => {
            const tipoPersona = $('#tipoPersonaSelect').val();
            if (!tipoPersona) {
                Swal.showValidationMessage('Debe seleccionar un tipo de persona');
            }
            return tipoPersona;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const tipoSeleccionado = result.value;
            const frm = document.getElementById("frmCliente");

            frm.reset();
            document.getElementById("tipoPersona").value = tipoSeleccionado;
            document.getElementById("codigo").readOnly = false;
            document.getElementById("saldo").readOnly = true;
            $('#tipoIdentificacion').val(null).trigger('change');
            $('#departamento').val(null).trigger('change');
            // $('#municipio').val(null).trigger('change');
            $('#actividadEconomica').val(null).trigger('change');
            $('#municipio').empty().append('<option value=""></option>');
            $('#municipio').select2();
            if (tipoSeleccionado == 2) {
                document.getElementById("camposIdentificacion").style.display = "none";
                document.getElementById("camposTipoDocumento").style.display = "block";
                document.getElementById("gruponrc").style.display = "none";
            } else if (tipoSeleccionado == 1) {
                document.getElementById("camposIdentificacion").style.display = "block";
                document.getElementById("camposTipoDocumento").style.display = "none";
                document.getElementById("gruponrc").style.display = "block";

            }

            document.getElementById("title").innerHTML = "Nuevo Cliente";
            document.getElementById("btnAccion").innerHTML = "Registrar";
            document.getElementById("codigo").value = "";

            $("#nuevoCliente").modal("show");
        }
    });
}

function registrarOmodificarCliente(e, accion) {
    e.preventDefault();
    const url = base_url + "Clientes/" + accion;
    const frm = document.getElementById("frmCliente");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Cliente registrado" : "Cliente modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoCliente").modal("hide");
            tblClientes.ajax.reload();
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
function registrarCliente(e) {
    registrarOmodificarCliente(e, "registrar");
}
function modificarCliente(e) {
    registrarOmodificarCliente(e, "modificar");
}
function btnAccionCliente(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarCliente(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarCliente(e);
    }
}

function btnEditarCliente(codigoCliente) {

    document.getElementById("title").innerHTML = "Editar Cliente";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    document.getElementById("saldo").readOnly = true;
    const url = base_url + "Clientes/editar/" + codigoCliente;

    enviarPeticion(url, "GET", null, (res) => {

        const personaT = res.persona;
        if (personaT == 2) {
            document.getElementById("camposIdentificacion").style.display = "none";
            document.getElementById("camposTipoDocumento").style.display = "block";
        } else if (personaT == 1) {
            document.getElementById("camposIdentificacion").style.display = "block";
            document.getElementById("camposTipoDocumento").style.display = "none";
        }

        document.getElementById("tipoPersona").value = res.persona;
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("nrc").value = res.numeroRe;
        document.getElementById("numeroTelefono").value = res.telefono;
        document.getElementById("contacto").value = res.contacto;
        document.getElementById("limiteCredito").value = formatearNumero(res.creditoLimite);
        document.getElementById("saldo").value = formatearNumero(res.saldo);

        const documentoSelect = document.getElementById("tipoIdentificacion");
        documentoSelect.innerHTML = '<option value="">Seleccione...</option>';
        const option = document.createElement("option");
        option.value = res.identificacion;
        option.textContent = res.nombreIdentificacion;
        option.selected = true;
        documentoSelect.appendChild(option);

        document.getElementById('numeroDocumento').value = res.numeroIdentificacion;
        document.getElementById('nit').value = res.nit;
        document.getElementById('nombreComercial').value = res.comercial;

        const actividadEco = document.getElementById("actividadEconomica");
        actividadEco.innerHTML = '<option value="">Seleccione...</option>';
        const actividad = document.createElement("option");
        actividad.value = res.actividad;
        actividad.textContent = res.nombreActividad;
        actividad.selected = true;
        actividadEco.appendChild(actividad);

        const departamentoS = document.getElementById("departamento");
        departamentoS.innerHTML = '<option value="">Seleccione...</option>';
        const depa = document.createElement("option");
        depa.value = res.departamentoC;
        depa.textContent = res.nombreDepartamento;
        depa.selected = true;
        departamentoS.appendChild(depa);

        const municipioS = document.getElementById("municipio");
        municipioS.innerHTML = '<option value="">Seleccione...</option>';
        const muni = document.createElement("option");
        muni.value = res.municipioC;
        muni.textContent = res.nombreMunicipio;
        muni.selected = true;
        municipioS.appendChild(muni);

        document.getElementById('complemento').value = res.complement;
        document.getElementById('correo').value = res.email;

        $("#nuevoCliente").modal("show");
    })
}

//campos nuevos para clientes
$(document).ready(function () {
    $('#tipoIdentificacion').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Clientes/documentoIdentificacion", // Ruta de búsqueda en tu backend
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
                    results: data.map(identificacion => ({
                        id: identificacion.codigo,
                        text: identificacion.nombre
                    }))
                };
            },
            cache: true
        }
    })

    $('#departamento').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Clientes/departamentos", // Ruta de búsqueda en tu backend
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
                    results: data.map(departamentos => ({
                        id: departamentos.codigo,
                        text: departamentos.nombre
                    }))
                };
            },
            cache: true
        }
    })
    $('#departamento').on('select2:select', function (e) {
        const codigoDepartamento = e.params.data.id;

        if (codigoDepartamento) {
            $.ajax({
                url: base_url + 'Clientes/municipios',
                method: 'GET',
                data: { codigoDepartamento: codigoDepartamento },
                dataType: 'json',
                success: function (response) {
                    // Limpiar el select de municipios
                    $('#municipio').empty().append('<option value="">Seleccione una opción</option>');

                    // Agregar nuevas opciones
                    response.forEach(function (item) {
                        $('#municipio').append(new Option(item.valor, item.codigo));
                    });

                    // Volver a inicializar select2
                    $('#municipio').select2();
                },

                error: function (xhr, status, error) {
                    alert('Error al cargar los datos');
                }
            });
        } else {
            // Limpiar selectHijo si no se selecciona nada en selectPadre
            $('#municipio').empty().append('<option value="">Seleccione una opción</option>');
            $('#municipio').select2();
        }
    });
    $("#departamento").on("select2:unselect", function (e) {
        $('#municipio').empty().append('<option value=""></option>');
        $('#municipio').select2();
    });
    // $('#municipio').select2({
    //     placeholder: "Busque y seleccione",
    //     allowClear: true,
    //     ajax: {
    //         url: base_url + "Clientes/municipios", // Ruta de búsqueda en tu backend
    //         dataType: "json",
    //         delay: 250, // Espera para reducir solicitudes
    //         data: function (params) {
    //             return {
    //                 q: params.term // El término de búsqueda
    //             };
    //         },
    //         processResults: function (data) {
    //             // Mapea los resultados para adaptarlos al formato de Select2
    //             return {
    //                 results: data.map(municipio => ({
    //                     id: municipio.codigo,
    //                     text: municipio.nombre
    //                 }))
    //             };
    //         },
    //         cache: true
    //     }
    // })

    $('#actividadEconomica').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        ajax: {
            url: base_url + "Clientes/actividadEconomica", // Ruta de búsqueda en tu backend
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
                    results: data.map(actividad => ({
                        id: actividad.codigo,
                        text: actividad.nombre
                    }))
                };
            },
            cache: true
        }
    })
})
export { frmCliente, btnAccionCliente, btnEditarCliente };