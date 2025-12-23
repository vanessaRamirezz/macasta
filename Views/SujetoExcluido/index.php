<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaSujetoExcluido">
    <li class="breadcrumb-item active text-white">FACTURA SUJETO EXCLUIDO</li>
</ol>

<div class="sujeto">
    <form id="frmSujetoExcluido" method="post">
        <div class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="selectCliente">Cliente</label>
                    <div class="input-group mb-2">
                        <select class="form-control" id="selectCliente" name="selectCliente">
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fechaEmi">Fecha</label>
                        <input type="date" class="form-control" id="fechaEmi" name="fechaEmi">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="horaEmi">Hora</label>
                        <input type="text" class="form-control" id="horaEmi" name="horaEmi" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="tipoDocumento">Tipo Documento</label>
                    <div class="input-group mb-2">
                        <input class="form-control" type="text" id="tipoDocumento" name="tipoDocumento" value="Factura Sujeto Excluido">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="selectTipoMovimiento">Tipo de Movimiento</label>
                    <div class="input-group mb-2">
                        <select class="form-control" id="selectTipoMovimiento" name="selectTipoMovimiento">
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <label for="codigoProyecto">Proyecto</label>
                    <div class="input-group mb-2">
                        <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-info" id="nuevo">Agregar Productos</button>
        </div>
        <div class="responsive">
            <table class="table table-sm table-bordered table-hover" id="tablaProductos">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Medida</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Descuentos</th>
                        <th>Total</th>
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
                    <label for="totalOperacion" class="font-weight-bold small">Total Operación</label>
                    <input class="form-control form-control-sm" type="text" name="totalOperacion" id="totalOperacion" disabled>
                </div>
                <div class="form-group">
                    <label for="montoDescuTotal" class="font-weight-bold small">Monto global de Descuento, Bonificación, Rebajas y otros al total de operaciones.</label>
                    <input class="form-control form-control-sm" type="number" name="montoDescuTotal" id="montoDescuTotal" min="0">
                </div>
                <div class="form-group">
                    <label for="totalDescuento" class="font-weight-bold small">Total del monto de Descuento, Bonificación, Rebajas</label>
                    <input class="form-control form-control-sm" type="number" name="totalDescuento" id="totalDescuento" min="0" disabled>
                </div>
                <div class="form-group">
                    <label for="subtotal" class="font-weight-bold small">Sub-Total</label>
                    <input class="form-control form-control-sm" type="text" name="subtotal" id="subtotal" disabled>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="form-group mb-1">
                            <label for="ivaRetenido" class="font-weight-bold small">IVA Retenido</label>
                            <input class="form-control form-control-sm" type="number" name="ivaRetenido" id="ivaRetenido" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-1">
                            <label for="rentaRetenida" class="font-weight-bold small"> Retención Renta</label>
                            <input class="form-control form-control-sm" type="number" name="rentaRetenida" id="rentaRetenida" min="0">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="total" class="font-weight-bold small">Total a Pagar</label>
                    <input class="form-control form-control-sm" type="text" name="total" id="total">
                </div>
            </div>
        </div>
        <hr>
        <h5>Tipo Operación</h5>
        <hr>
        <div class="mb-3">
            <button type="button" class="btn btn-info" id="agregarFP">Formas de pago</button>
        </div>
        <div class="responsive">
            <table class="table table-sm table-bordered table-hover" id="tablaPagos">
                <thead>
                    <tr>
                        <th>Condición Operación</th>
                        <th>Medio de pago</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tblPagos">
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-4 ml-auto">
                <div class="form-group">
                    <label for="totalPagos" class="font-weight-bold small">Total</label>
                    <input class="form-control form-control-sm" type="text" name="totalPagos" id="totalPagos">
                </div>
            </div>
        </div>
        <hr>
        <div class="mb-3">
            <div class="row">
                <div class="col-md-12">
                    <label for="observacion">Observaciónes:</label>
                    <textarea class="form-control" id="observacion" name="observacion" rows="3" maxlength="3000"></textarea>
                </div>
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-between">
            <button type="button" class="btn btnCancelar px-4 py-2" id="cancelarSE">Cancelar</button>
            <button type="button" class="btn btn-success px-4 py-2" id="generarFSujeto">Generar</button>
        </div>
    </form>
</div>

<!-- aca modal de formas de pago -->
<div id="agregarFPagos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-xl">
                <h5 class="modal-title" id="title">Agregar Formas de pagos</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmPagos">
                <div class="modal-body">
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
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="totalModal" class="font-weight-bold">Total a Pagar</label>
                                    <input class="form-control" type="text" name="totalModal" id="totalModal">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="montoPago">Monto (aplica cuando sean n medios de pago)</label>
                                    <input class="form-control" type="number" name="montoPago" id="montoPago" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="totalRestante" class="font-weight-bold">Total Restante</label>
                                    <input class="form-control" type="text" id="totalRestante" name="totalRestante">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div id="selectTipoPagoContainerCheque" style="display: none;" class="selectTipoPagoContainer">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="referencia">Referencia</label>
                                    <input type="text" id="referencia" name="referencia" class="form-control" placeholder="ej. 788453405">
                                </div>
                            </div>
                        </div>
                        <div id="selectTipoPagoContainer" style="display: none;" class="selectTipoPagoContainer">
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
                    <button type="button" class="btn btn-info" id="agregarForMa">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                            <div class="col-md-4">
                                <label for="unidadMedida">Unidad de Medida</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="unidadMedida" name="unidadMedida">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="tipoItemP">Tipo de Item</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="tipoItemP" name="tipoItemP">
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
                                <label for="descuentoItem">Descuento por ítem</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input class="form-control" type="number" name="descuentoItem" id="descuentoItem" min="0">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>Sub-Total reflejado sin iva y Total aplicando descuento si aplica</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="sub" class="font-weight-bold">Sub-Total</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input class="form-control moneda" type="text" name="sub" id="sub" disabled>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/sujetoExcluido.js"></script>
<?php include "Views/templates/footer.php"; ?>