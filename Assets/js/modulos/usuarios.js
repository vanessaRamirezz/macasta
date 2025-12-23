import { configurarTabla, enviarPeticion } from '../utilidades/tablePeticion.js';
let tblUsuarios;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaUsuarios')) {
        tblUsuarios = configurarTabla('#tblUsuarios', "Usuarios/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'nivel' },
            { 'data': 'estado' },
            { 'data': 'acciones' }
        ], [
            {
                extend: 'colvis',
                text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                postfixButtons: ['colvisRestore']
            }
        ], {
            searchInputId: 'customSearchUsuarios',
            searchBtnId: 'searchBtnUsuarios',
            clearSearchBtnId: 'clearSearchBtnUsuarios'
        });

        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmUsuario)

        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionUsuario);

        document.querySelector('#tblUsuarios tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoUsuario = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarUsuario(codigoUsuario);
            }
        })

        document.querySelector('#tblUsuarios tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-eliminar')) {
                const codigoUsuario = e.target.closest('.btn-eliminar').getAttribute('data-id');
                btnEliminarUsuario(codigoUsuario);
            }
        })

        document.querySelector('#tblUsuarios tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-activar')) {
                const codigoUsuario = e.target.closest('.btn-activar').getAttribute('data-id');
                btnActivarUsuario(codigoUsuario);
            }
        })
    }
})
// Usuarios 
function frmUsuario() {
    document.getElementById("title").innerHTML = "Nuevo Usuario"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("claves").classList.remove("d-none");
    document.getElementById("frmUsuario").reset();
    document.getElementById("codigo").value = "";
    $('#tipoIdentificacion').val(null).trigger('change');
    $("#nuevoUsuario").modal("show");
}
function registrarOmodificarUsuario(e, accion) {
    e.preventDefault();
    const url = base_url + "Usuarios/" + accion;
    const frm = document.getElementById("frmUsuario");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Usuario registrado" : "Usuario modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoUsuario").modal("hide");
            tblUsuarios.ajax.reload();
            $('#tipoIdentificacion').val(null).trigger('change');
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
function registrarUsuario(e) {
    registrarOmodificarUsuario(e, "registrar");
}
function modificarUsuario(e) {
    registrarOmodificarUsuario(e, "modificar");
}
function btnAccionUsuario(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarUsuario(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarUsuario(e);
    }
}
function btnEditarUsuario(codigoUsuario) {
    document.getElementById("title").innerHTML = "Editar Usuario";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "Usuarios/editar/" + codigoUsuario;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("nivelSeguridad").value = res.nivel;
        document.getElementById("nombreCom").value = res.completo;

        const documentoSelect = document.getElementById("tipoIdentificacion");
        documentoSelect.innerHTML = '<option value="">Seleccione...</option>';
        const option = document.createElement("option");
        option.value = res.tipo;
        option.textContent = res.nombreIdentificacion;
        option.selected = true;
        documentoSelect.appendChild(option);

        document.getElementById('numeroDocumento').value = res.numero;

        document.getElementById("claves").classList.add("d-none");
        $("#nuevoUsuario").modal("show");
    })
}
function btnEliminarUsuario(codigoUsuario) {
    Swal.fire({
        title: "Estas seguro de eliminar?",
        text: "El usuario no se eliminara de forma permanente, solo cambiara a inactivo!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Usuarios/eliminar/" + codigoUsuario;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    if (res == "ok") {
                        Swal.fire({
                            title: "Mensaje!",
                            text: "Usuario Eliminado",
                            icon: "success"
                        });
                        tblUsuarios.ajax.reload();
                    } else {
                        Swal.fire({
                            title: "Deleted!",
                            text: res,
                            icon: "error"
                        });
                    }

                }
            }
        }
    });
}
function btnActivarUsuario(codigoUsuario) {
    Swal.fire({
        title: "Estas seguro de activar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Usuarios/activar/" + codigoUsuario;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    if (res == "ok") {
                        Swal.fire({
                            title: "Mensaje!",
                            text: "Usuario Activado",
                            icon: "success"
                        });
                        tblUsuarios.ajax.reload();
                    } else {
                        Swal.fire({
                            title: "Deleted!",
                            text: res,
                            icon: "error"
                        });
                    }

                }
            }
        }
    });
}

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
})
export { frmUsuario, btnAccionUsuario, btnEditarUsuario, btnEliminarUsuario, btnActivarUsuario }