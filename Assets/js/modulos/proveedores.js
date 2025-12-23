import { configurarTabla, enviarPeticion, formatearMoneda,formatearNumero} from '../utilidades/tablePeticion.js';
let tblProveedores;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaProveedores')) {
        tblProveedores = configurarTabla('#tblProveedores', "Proveedores/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
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
            searchInputId: 'customSearchProveedores',
            searchBtnId: 'searchBtnProveedores',
            clearSearchBtnId: 'clearSearchBtnProveedores'
        });

        // Asignar el evento al botón "Nuevo"
        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmProveedor);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionProveedor);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblProveedores tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoProveedor = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarProveedor(codigoProveedor);
            }
        });

        document.querySelectorAll('.moneda').forEach(input => {
            input.addEventListener('input', function () {
                formatearMoneda(this);
            })
        })
        
    }
});

// Inicio Proveedores
function frmProveedor() {
    document.getElementById("title").innerHTML = "Nuevo Proveedor"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("saldo").readOnly = true;
    document.getElementById("frmProveedor").reset();
    document.getElementById("codigo").value = "";
    $("#nuevoProveedor").modal("show");
}
function registrarOmodificarProveedor(e, accion) {
    e.preventDefault();
    const url = base_url + "Proveedores/" + accion;
    const frm = document.getElementById("frmProveedor");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Proveedor registrado" : "Proveedor modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoProveedor").modal("hide");
            tblProveedores.ajax.reload();
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
function registrarProveedor(e) {
    registrarOmodificarProveedor(e, "registrar");
}
function modificarProveedor(e) {
    registrarOmodificarProveedor(e, "modificar");
}
function btnAccionProveedor(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarProveedor(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarProveedor(e);
    }
}
function btnEditarProveedor(codigoProveedor) {
    document.getElementById("title").innerHTML = "Editar Proveedor";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("saldo").readOnly = true;
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "Proveedores/editar/" + codigoProveedor;
    
    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("numeroTelefono").value = res.telefono;
        document.getElementById("contacto").value = res.contacto;
        document.getElementById("limiteCredito").value = formatearNumero(res.creditoLimite);
        document.getElementById("saldo").value = formatearNumero(res.saldo);
        $("#nuevoProveedor").modal("show");
    })
}

export { frmProveedor, btnAccionProveedor, btnEditarProveedor }