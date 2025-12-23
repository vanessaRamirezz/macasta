<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaCompras">
    <li class="breadcrumb-item active text-white">COMPRAS</li>
</ol>

<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#historial">Historial</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#registrar">Registrar</a>
    </li>
</ul>

<div class="tab-content mt-3">
    <!-- Pestaña de Historial -->
    <div class="tab-pane fade show active" id="historial" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="date" id="customSearchHistorial" placeholder="Buscar por fecha" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnHistorial" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnHistorial" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tblHistorial" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Documento FE</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña de Registro -->
    <div class="tab-pane fade" id="registrar" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <form method="post" id="frmCompras">
                    <input type="hidden" id="codigoCliente" name="codigoCliente">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="numeroDocumento">Número de Documento *</label>
                                <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" required>
                            </div>
                            <div class="col-md-6">
                                <label for="numeroDocumentoFe">Número de Documento Fe *</label>
                                <input type="text" class="form-control" id="numeroDocumentoFe" name="numeroDocumentoFe">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="codigoTipoMovimiento">Tipo de Movimiento *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="codigoTipoMovimiento" name="codigoTipoMovimiento">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="codigoProveedor">Proveedor *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="codigoProveedor" name="codigoProveedor">
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <label for="totalCompra">Monto Total de la Compra *</label>
                                <input type="text" class="form-control moneda" id="totalCompra" name="totalCompra" step="0.01" disabled>
                            </div> -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="codigoProyecto">Proyecto *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="fechaCompra">Fecha de Compra *</label>
                                <input type="date" class="form-control" id="fechaCompra" name="fechaCompra">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="observacion">Observación:</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb3">
                        <button type="button" class="btn btn-info" id="nuevo">Agregar Productos</button>
                    </div>
                    <hr>
                    <h4>Productos Comprados</h4>
                    <div class="responsive">
                        <table class="table table-bordered table-hover" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo</th>
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
                                <label for="total" class="font-weight-bold">Total</label>
                                <input class="form-control" type="text" name="total" id="total" disabled>
                                <button type="button" class="btn btn-success mt-2 btn-block" id="generarCompra">Registrar Compra</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btnCancelar" id="cancelar">Cancelar</button>
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
                                            <div class="col-md-4">
                                                <label for="precioCosto">Precio Costo</label>
                                                <div class="input-group mb-2">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                    </div>
                                                    <input class="form-control moneda" type="text" name="precioCosto" id="precioCosto">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="precioVenta">Precio Venta</label>
                                                <div class="input-group mb-2">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                    </div>
                                                    <input class="form-control moneda" type="text" name="precioVenta" id="precioVenta">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
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
                                        <button type="button" class="btn btn-info" id="registrarProducto">Agregar</button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/compras.js"></script>
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/detalleTemporal.js"></script>
<?php include "Views/templates/footer.php"; ?>