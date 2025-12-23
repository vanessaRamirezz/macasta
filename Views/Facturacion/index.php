<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaFacturacion">
    <li class="breadcrumb-item active text-white">FACTURACIÓN</li>
</ol>

<!-- Activar contingencia -->
<div class="mb-3">
    <button type="button" class="btn btn-info" id="btnContingenciaOpciones">Contingencia</button>
</div>

<div id="contingencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-xl">
                <h5 class="modal-title" id="title">Evento Contingencia</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="frmContingencia">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="modeloFacturacion">Modelo de Facturación</label>
                                <div class="input-group mb-2">
                                    <input type="text" id="modeloFacturacionTexto" readonly class="form-control">
                                    <input type="hidden" id="modeloFacturacionCodigo" name="modeloFacturacionCodigo">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tipoTransmision">Tipo de Transmision</label>
                                <div class="input-group mb-2">
                                    <input type="text" id="tipoTransmisionTexto" readonly class="form-control">
                                    <input type="hidden" id="tipoTransmisionCodigo" name="tipoTransmisionCodigo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="tipoContingencia">Tipo de Contingencia</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="tipoContingencia" name="tipoContingencia">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="motivoContingencia">Motivo de Contingencia</label>
                                <div class="input-group mb-2">
                                    <textarea class="form-control" name="motivoContingencia" id="motivoContingencia"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning" id="eventoEstadoA">Activar</button>
                    <button type="button" class="btn btn-danger" id="cancelar">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="eventoEstadoD">Desactivar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class=" ">
    <form id="frmFacturacion" method="post">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="formTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="datos-tab" data-toggle="tab" href="#datos" role="tab">Datos Generales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="documentos-tab" data-toggle="tab" href="#documentos" role="tab">Documentos Relacionados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="asociados-tab" data-toggle="tab" href="#asociados" role="tab">Otros Documentos Asociados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="terceros-tab" data-toggle="tab" href="#terceros" role="tab">Venta a Cuenta de Terceros</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content border p-3 bg-light">
            <!-- TAB 1 -->
            <div class="tab-pane fade show active" id="datos" role="tabpanel">
                <di class="mb-3">
                    <div class="mb-3">
                        <input type="checkbox" id="checkNotaCredito"> Generar Nota de Crédito
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="selectCliente">Cliente</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="selectCliente" name="selectCliente">
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="selectTipoDocumento">Documento</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="selectTipoDocumento" name="selectTipoDocumento">
                                </select>
                            </div>
                        </div>
                    </div>
                </di>
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="selectTipoMovimiento">Tipo de Movimiento</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="selectTipoMovimiento" name="selectTipoMovimiento">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="codigoProyecto">Proyecto</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h4>Tipo Operación</h4>
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="condicion">Pago</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="condicion" name="condicion">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="selectTipoPago">Medio de Pago</label>
                            <div class="input-group mb-2">
                                <select class="form-control" id="selectTipoPago" name="selectTipoPago">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div id="selectTipoPagoContainer" style="display: none;" class="selectTipoPagoContainer">
                        <h5 class="mt-4">II. Banco</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="selectBanco">Banco </label>
                                <div class="input-group mb-2">
                                    <select class="form-control" name="selectBanco" id="selectBanco"></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="selectCuentaBancaria">Cuenta Bancaria </label>
                                <div class="input-group mb-2">
                                    <select class="form-control" name="selectCuentaBancaria" id="selectCuentaBancaria"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaEmi">Fecha</label>
                                <input type="date" class="form-control" id="fechaEmi" name="fechaEmi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horaEmi">Hora (Minutos y Segundos)</label>
                                <input type="text" class="form-control" id="horaEmi" name="horaEmi" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-info" id="nuevo">Agregar Productos</button>
                </div>
                <hr>
                <div class="responsive">
                    <table class="table table-bordered table-hover" id="tablaProductos">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Medida</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo</th>
                                <th>Total</th>
                                <th>Doc. Relacionado</th>
                                <th>No Afecto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tblDetalle">
                            <!-- Aquí se agregarán las filas de productos -->
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-4 ml-auto">
                        <div class="form-group">
                            <label for="subTotal" class="font-weight-bold">Sub-Total</label>
                            <input class="form-control" type="text" name="subTotal" id="subTotal" disabled>
                        </div>
                        <div class="form-group">
                            <label for="iva" class="font-weight-bold">IVA (13%)</label>
                            <input class="form-control" type="text" name="iva" id="iva" disabled>
                        </div>
                        <div class="form-group">
                            <label for="total" class="font-weight-bold">Total</label>
                            <input class="form-control" type="text" name="total" id="total" disabled>
                        </div>
                        <div class="form-group">
                            <label for="retenido" class="font-weight-bold">IVA Retenido</label>
                            <input class="form-control" type="number" name="retenido" id="retenido">
                            <button type="button" class="btn btn-success mt-2 btn-block" id="generarFacturacion">Generar Facturación</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btnCancelar" id="cancelarFac">Cancelar</button>
                <!-- <button type="button" class="btn bg-info" id="vistaPrevia">Vista Previa</button> -->
            </div>

            <!-- TAB 2 -->
            <div class="tab-pane fade" id="documentos" role="tabpanel">

                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="tipoDocumento">Tipo Documento</label>
                            <div class="input-group mb-2">
                                <select class="form-control" name="tipoDocumento" id="tipoDocumento"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipoGeneracion">Tipo Generación</label>
                            <div class="input-group mb-2">
                                <select class="form-control" name="tipoGeneracion" id="tipoGeneracion"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numeroDoc" id="doc"></label>
                        <input type="text" class="form-control" name="numeroDoc" id="numeroDoc" placeholder="---">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fechaEmision">Fecha</label>
                        <input type="date" class="form-control" name="fechaEmision" id="fechaEmision">
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-info" id="agregarDte">Agregar</button>
                    <button type="button" class="btn btn-danger" id="limpiarDte">Limpiar</button>
                </div>
                <div class="responsive">
                    <table class="table table-bordered table-hover" id="tablaDoc">
                        <thead>
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Tipo de Generación</th>
                                <th>Codigo de Generación/Número de Correlativo</th>
                                <th>Fecha de Generación</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tblDoc">
                            <!-- Aquí se agregarán las filas de productos -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3 -->
            <div class="tab-pane fade" id="asociados" role="tabpanel">

                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="otrosAsociados">Documento Asociado</label>
                            <div class="input-group mb-2">
                                <select class="form-control" name="otrosAsociados" id="otrosAsociados"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div id="selectDocumentoA" style="display: none;" class="visibilidadEmisorReceptor">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="identificacionDocumento">Identificación del documento</label>
                                    <textarea class="form-control" type="text" placeholder="Ej. Resoluciones, Licencias, Permisos, Contratos, Otros" name="identificacionDocumento" id="identificacionDocumento"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="descripcionDocumento">Descripción del documento</label>
                                    <textarea class="form-control" type="text" placeholder="Ej. Numero resolucion, fechas, numeros de contratos" name="descripcionDocumento" id="descripcionDocumento"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-info" id="agregarDoc">Agregar</button>
                            <button type="button" class="btn btn-danger" id="limpiarDoc">Limpiar</button>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div id="selectMedico" style="display: none;" class="visibilidadMedico">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="tipoServicio">Tipo Servicio</label>
                                    <div class="input-group mb-2">
                                        <select class="form-control" name="tipoServicio" id="tipoServicio"></select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombreMedico">Nombre</label>
                                    <input class="form-control" type="text" name="nombreMedico" id="nombreMedico" placeholder="nombre del medico">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="tipoDocumentoAS">Documento de médico no domiciliado</label>
                                    <input class="form-control" type="text" name="tipoDocumentoAS" id="tipoDocumentoAS" placeholder="detallar el documento con el que se identifica">
                                </div>
                                <div class="col-md-6">
                                    <label for="nitMedico">Si médico tiene NIT llene este campo</label>
                                    <input class="form-control" type="text" name="nitMedico" id="nitMedico" placeholder="Ej. 98475674686">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-info" id="agregarDocMedico">Agregar</button>
                            <button type="button" class="btn btn-danger" id="limpiarMed">Limpiar</button>
                        </div>
                    </div>
                </div>

                <div id="selectDocumentoATB" style="display: none;" class="visibilidadEmisorReceptor">
                    <div class="responsive">
                        <table class="table table-bordered table-hover" id="tablaER">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Detalle del documento</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tblDocM">
                                <!-- Aquí se agregarán las filas de productos -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="selectMedicoTB" style="display: none;" class="visibilidadEmisorReceptor">
                    <div class="responsive">
                        <table class="table table-bordered table-hover" id="tablaMedico">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Tipo de servicio</th>
                                    <th>Documento</th>
                                    <th>NIT</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tblDocMedico">
                                <!-- Aquí se agregarán las filas de productos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4 -->
            <div class="tab-pane fade" id="terceros" role="tabpanel">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nit">NIT/DUI HOMOLOGADO</label>
                            <input class="form-control" type="text" name="nit" id="nit">
                        </div>
                        <div class="col-md-6">
                            <label for="nombreTercero">Nombre, Denominación o razón social</label>
                            <input class="form-control" type="text" name="nombreTercero" id="nombreTercero">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-info" id="validar">Validar</button>
                    <button type="button" class="btn btn-danger" id="limpiarTercero">Limpiar</button>
                </div>
                <div class="responsive">
                    <table class="table table-bordered table-hover" id="tablaTercero">
                        <thead>
                            <tr>
                                <th>NIT</th>
                                <th>Nombre</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tblTercero">
                            <!-- Aquí se agregarán las filas de productos -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </form>
    <!-- Aca empieza el modal-->
    <div id="agregarProducto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-xl">
                    <h5 class="modal-title" id="title">Productos</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frmDetalle">
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="producto">Producto</label>
                                    <div class="input-group mb-2">
                                        <select class="form-control" id="producto" name="producto">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="codigoProducto">Codigo</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input class="form-control" type="text" name="codigoProducto" id="codigoProducto">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="cantidadProducto">Cantidad</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                        </div>
                                        <input class="form-control" type="number" name="cantidadProducto" id="cantidadProducto" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="unidadMedida">Unidad de Medida</label>
                                    <div class="input-group mb-2">
                                        <select class="form-control" id="unidadMedida" name="unidadMedida">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="precioCosto">Precio Costo</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        </div>
                                        <input class="form-control moneda" type="text" name="precioCosto" id="precioCosto">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="precioVenta">Precio Venta</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        </div>
                                        <input class="form-control moneda" type="text" name="precioVenta" id="precioVenta">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="totalSeleccionado" class="font-weight-bold">Total</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        </div>
                                        <input class="form-control moneda" type="text" name="totalSeleccionado" id="totalSeleccionado" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="documentoRelacionado">Documento Relacionado</label>
                                    <div class="input-group mb-2">
                                        <select class="form-control" id="documentoRelacionado" name="documentoRelacionado">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="descripcionNota">Descripción</label>
                                    <div class="input-group mb-2">
                                        <input class="form-control" type="text" name="descripcionNota" id="descripcionNota">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <hr>
                                <h5>Item No Afecto</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="descripcionNo">Descripción</label>
                                        <div class="input-group mb-2">
                                            <input class="form-control" type="text" name="descripcionNo" id="descripcionNo">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="montoNo">Monto</label>
                                        <div class="input-group mb-2">
                                            <input class="form-control moneda" type="number" name="montoNo" id="montoNo" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-info" id="seleccionarProducto">Agregar</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>


</main>
<footer class="py-4 bg-light mt-auto">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Gestor de proyectos <?php echo date("Y"); ?></div>
        </div>
    </div>
</footer>
</div>
</div>
<script>
    function actualizarHora() {
        let ahora = new Date();
        let horas = ahora.getHours().toString().padStart(2, '0');
        let minutos = ahora.getMinutes().toString().padStart(2, '0');
        let segundos = ahora.getSeconds().toString().padStart(2, '0');
        document.getElementById('horaEmi').value = `${horas}:${minutos}:${segundos}`;
    }

    setInterval(actualizarHora, 1000);
    actualizarHora();
</script>
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/facturacion.js"></script>
<?php include "Views/templates/footer.php"; ?>