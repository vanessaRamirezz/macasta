import { configurarTabla, enviarPeticion, formatearMoneda,formatearNumero} from '../utilidades/tablePeticion.js';
let tblEmpleados;
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('.vistaEmpleados')) {
        tblEmpleados = configurarTabla('#tblEmpleados', "Empleados/listar", [
            { 'data': 'codigo' },
            { 'data': 'nombre' },
            { 'data': 'telefono' },
            { 'data': 'acciones' }
        ],
            [
                {
                    extend: 'colvis',
                    text: '<span class="badge  badge-info"><i class="fas fa-columns"></i></span>',
                    postfixButtons: ['colvisRestore']
                }
            ], {
            searchInputId: 'customSearchEmpleados',
            searchBtnId: 'searchBtnEmpleados',
            clearSearchBtnId: 'clearSearchBtnEmpleados'
        });

        // Asignar el evento al botón "Nuevo"
        const btnNuevo = document.getElementById('btn-colorNuevo');
        btnNuevo.addEventListener('click', frmEmpleado);

        // Asignar evento al botón de acción (Registrar/Modificar)
        const btnAccion = document.getElementById('btnAccion');
        btnAccion.addEventListener('click', btnAccionEmpleado);

        // Delegación de eventos para los botones de editar
        document.querySelector('#tblEmpleados tbody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-editar')) {
                const codigoEmpleado = e.target.closest('.btn-editar').getAttribute('data-id');
                btnEditarEmpleado(codigoEmpleado);
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
function frmEmpleado() {
    document.getElementById("title").innerHTML = "Nuevo Empleado"
    document.getElementById("btnAccion").innerHTML = "Registrar"
    document.getElementById("codigo").readOnly = false;
    document.getElementById("frmEmpleados").reset();
    document.getElementById("codigo").value = "";
    $("#nuevoEmpleado").modal("show");
}
function registrarOmodificarEmpleado(e, accion) {
    e.preventDefault();
    const url = base_url + "Empleados/" + accion;
    const frm = document.getElementById("frmEmpleados");
    enviarPeticion(url, "POST", frm, function (res) {
        if (res == "si" || res == "modificado") {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: accion === "registrar" ? "Empleado registrado" : "Empleado modificado",
                showConfirmButton: false,
                timer: 3000
            });
            frm.reset();
            $("#nuevoEmpleado").modal("hide");
            tblEmpleados.ajax.reload();
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
function registrarEmpleado(e) {
    registrarOmodificarEmpleado(e, "registrar");
}
function modificarEmpleado(e) {
    registrarOmodificarEmpleado(e, "modificar");
}
function btnAccionEmpleado(e) {
    var boton = document.getElementById("btnAccion");
    if (boton.innerHTML == "Registrar") {
        registrarEmpleado(e);
    } else if (boton.innerHTML == "Modificar") {
        modificarEmpleado(e);
    }
}
function btnEditarEmpleado(codigoEmpleado) {
    document.getElementById("title").innerHTML = "Editar Empleado";
    document.getElementById("btnAccion").innerHTML = "Modificar";
    document.getElementById("codigo").readOnly = true;
    const url = base_url + "Empleados/editar/" + codigoEmpleado;
    
    enviarPeticion(url, "GET", null, (res) => {
        document.getElementById("codigo").value = res.codigo;
        document.getElementById("nombre").value = res.nombre;
        document.getElementById("numeroTelefono").value = res.telefono;
        $("#nuevoEmpleado").modal("show");
    })
}

export { frmEmpleado, btnAccionEmpleado, btnEditarEmpleado }