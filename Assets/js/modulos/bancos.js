import { configurarTabla, enviarPeticion, formatearMoneda, formatearNumero } from '../utilidades/tablePeticion.js';
let tblCuentas;
let tblBancosRegistrar;
let tblRegistros;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaBancos')) {
        tblCuentas = configurarTabla('#tblCuentas', "Bancos/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ],
            [
                {
                    extend: 'colvis',
                    text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                    postfixButtons: ['colvisRestore']
                }
            ], {
            searchInputId: 'customSearchBancos',
            searchBtnId: 'searchBtnBancos',
            clearSearchBtnId: 'clearSearchBtnBancos'
        });

        tblBancosRegistrar = configurarTabla('#tblBancosRegistrar', "Bancos/listarBancos", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ],
            [
                {
                    extend: 'colvis',
                    text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                    postfixButtons: ['colvisRestore']
                }
            ], {
            searchInputId: 'customSearchBancosRegistrar',
            searchBtnId: 'searchBtnBancosRegistrar',
            clearSearchBtnId: 'clearSearchBtnBancosRegistrar'
        });

        tblRegistros = configurarTabla('#tblRegistros', "Bancos/listarBancosRegistros", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'acciones' }
        ],
            [
                {
                    extend: 'colvis',
                    text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                    postfixButtons: ['colvisRestore']
                }
            ], {
            searchInputId: 'customSearchBancosRegistrar',
            searchBtnId: 'searchBtnBancosRegistrar',
            clearSearchBtnId: 'clearSearchBtnBancosRegistrar'
        });

        const btnNuevoRegistrar = document.getElementById('bancosRegistrar');
        btnNuevoRegistrar.addEventListener('click', frmBancosRegistrar);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccionB = document.getElementById('btnAccionBanco');
        btnAccionB.addEventListener('click', btnAccionBanco);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblBancosRegistrar tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoBanco = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarBanco(codigoBanco);
            }
        });


        const btnNuevo = document.getElementById('btnNuevo');
        btnNuevo.addEventListener('click', frmCuentas);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccionCuenta = document.getElementById('btnAccion');
        btnAccionCuenta.addEventListener('click', btnAccionCuentas);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblCuentas tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoCuenta = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarCuenta(codigoCuenta);
            }
        });

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblRegistros tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const verBanco = e.target.closest('.btn-editar').getAttribute('data-id');
                btnVerListado(verBanco);
            }
        });

        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            })
        })
    }
});

function frmCuentas() {
    document.getElementById("title").innerHTML = "Nueva Cuenta"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigoBanco").readOnly = true;
    $("#selectBanco").prop("disabled", false);
    document.getElementById("codigoCuentaBancaria").readOnly = false;
    document.getElementById("ingresos").readOnly = true;
    document.getElementById("salidas").readOnly = true;
    document.getElementById("saldo").readOnly = true;
    document.getElementById("frmCuenta").reset();
    $("#nuevaCuenta").modal("show");
}

function registrarOmodificarCuentas(e, accion) {
    e.preventDefault();
    const url = base_url + "Bancos/" + accion;
    const frm = document.getElementById("frmCuenta");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Cuenta registrada" : "Cuenta modificada",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevaCuenta").modal("hide");
            tblCuentas.ajax.reload();
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
    });
}

function registrarCuentas(e) {
    registrarOmodificarCuentas(e, "registrar");
}

function modificarCuentas(e) {
    registrarOmodificarCuentas(e, "modificar");
}

function btnAccionCuentas(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarCuentas(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarCuentas(e);
    }
}

function btnEditarCuenta(codigoCuenta) {
    document.getElementById("title").innerHTML = "Editar Banco";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigoBanco").readOnly = true;
    $("#selectBanco").prop("disabled", true);
    document.getElementById("codigoCuentaBancaria").readOnly = true;
    document.getElementById("ingresos").readOnly = true;
    document.getElementById("salidas").readOnly = true;
    document.getElementById("saldo").readOnly = true;
    const url = base_url + "Bancos/editar/" + codigoCuenta;

    enviarPeticion(url, "GET", null, (res) => {
        const SlectBanco = document.getElementById("selectBanco");
        SlectBanco.innerHTML = '<option value="">Seleccione...</option>';
        const option = document.createElement("option");
        option.value = res.codigo;
        option.textContent = res.banco;
        option.selected = true;
        SlectBanco.appendChild(option);
        document.getElementById("codigoBanco").value = res.codigo;
        document.getElementById("codigoCuentaBancaria").value = res.cuenta;
        document.getElementById("nombreCuentaBancaria").value = res.nombreCuenta;
        document.getElementById("saldoInicial").value = formatearNumero(res.saldoIni);
        document.getElementById("ingresos").value = formatearNumero(res.ingresos);
        document.getElementById("salidas").value = formatearNumero(res.salidas);
        document.getElementById("saldo").value = formatearNumero(res.saldo);
        $("#nuevaCuenta").modal("show");
    })
}


// codigoBanco
function frmBancosRegistrar() {
    document.getElementById("title").innerHTML = "Nuevo Banco"
    document.getElementById("btnAccionBanco").innerHTML = "Registrar"
    document.getElementById("codigoBancoRegistrar").readOnly = false;
    document.getElementById("frmBancoRegistrar").reset();
    document.getElementById("codigoBancoRegistrar").value = "";
    $("#nuevoBancoRegistrar").modal("show");
}

function registrarOmodificarBanco(e, accion) {
    e.preventDefault();
    const url = base_url + "Bancos/" + accion;
    const frm = document.getElementById("frmBancoRegistrar");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "ingresar" ? "Banco registrado" : "Banco modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoBancoRegistrar").modal("hide");
            tblBancosRegistrar.ajax.reload();
            tblCuentas.ajax.reload();
            tblRegistros.ajax.reload();
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
    });
}

function registrarBanco(e) {
    registrarOmodificarBanco(e, "ingresar");
}

function modificarBanco(e) {
    registrarOmodificarBanco(e, "actualizar");
}

function btnAccionBanco(e) {
    var boton = document.getElementById("btnAccionBanco");
    if (boton.innerHTML == "Registrar") {
        registrarBanco(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarBanco(e);
    }
}

function btnEditarBanco(codigoBanco) {
    document.getElementById("title").innerHTML = "Editar Banco";
    document.getElementById("btnAccionBanco").innerHTML = "Modificar";
    document.getElementById("codigoBancoRegistrar").readOnly = true;
    const url = base_url + "Bancos/editarBanco/" + codigoBanco;

    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigoBancoRegistrar").value = res.codigo;
        document.getElementById("nombreBancoRegistrar").value = res.nombre;
        $("#nuevoBancoRegistrar").modal("show");
    })
}

// Listado
function btnVerListado(codigoBancoVer) {
    document.getElementById("title").innerHTML = "Banco";
    document.getElementById("codigoBancoVer").readOnly = true;
    document.getElementById("nombreBancoVer").readOnly = true;

    const url = base_url + "Bancos/listadoCuentas/" + codigoBancoVer;

    enviarPeticion(url, "GET", null, (res) => {
        if (res.length > 0) {
            // Llenar los datos del banco
            document.getElementById("codigoBancoVer").value = res[0].codigoBanco;
            document.getElementById("nombreBancoVer").value = res[0].nombreBanco;

            // Limpiar la tabla antes de agregar nuevos datos
            let tablaBody = document.getElementById("tablaCuentasBody");
            tablaBody.innerHTML = "";

            // Recorrer el array de cuentas y agregar filas a la tabla
            res.forEach((cuenta) => {
                let row = `<tr>
                    <td>${cuenta.codigoCuenta}</td>
                    <td>${cuenta.nombreCuenta}</td>
                    <td>${formatearNumero(cuenta.saldoI)}</td>
                    <td>${formatearNumero(cuenta.ing)}</td>
                    <td>${formatearNumero(cuenta.sal)}</td>
                    <td>${formatearNumero(cuenta.sald)}</td>
                </tr>`;
                tablaBody.innerHTML += row;
            });

            // Mostrar el modal
            $("#verListado").modal("show");
        } else {
            alert("No hay cuentas bancarias asociadas a este banco.");
        }
    });
}


$(document).ready(function () {
    $('#nuevaCuenta').on('shown.bs.modal', function () {
        // selects de banco
        // Inicialización del select2 para Banco
        $("#selectBanco").select2({
            placeholder: "Busque y seleccione",
            allowClear: true,
            ajax: {
                url: base_url + "Movimientos/buscarBanco", // Ruta de búsqueda en tu backend
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
                        results: data.map(banco => ({
                            id: banco.codigo,
                            text: banco.nombre
                        }))
                    };
                },
                cache: true
            }
        });

        // Evento cuando se selecciona un responsable
        $("#selectBanco").on("select2:select", function (e) {
            let data = e.params.data;
            $("#codigoBanco").val(data.id);
        });
        // Evento cuando se deselecciona un responsable
        $("#selectBanco").on("select2:unselect", function (e) {
            // Limpiar los inputs asociados
            $("#codigoBanco").val(""); // Limpiar código
        });
    })
    $('#nuevaCuenta').on('hidden.bs.modal', function () {
        $('#selectBanco').val(null).trigger('change').empty();
    });
})

export { frmBancosRegistrar, btnAccionBanco, btnEditarBanco, frmCuentas, btnAccionCuentas, btnEditarCuenta, btnVerListado };