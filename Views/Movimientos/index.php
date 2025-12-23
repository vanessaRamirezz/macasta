<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaMovimientos">
    <li class="breadcrumb-item active text-white">MOVIMIENTOS</li>
</ol>

<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#historial">Historial</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " data-toggle="tab" href="#registrar">Registrar</a>
    </li>
    <!-- <li class="nav-item">
        <a class="nav-link " data-toggle="tab" href="#recibos">Pagos Planilla</a>
    </li> -->
</ul>

<div class="tab-content mt-3">
    <!-- Pestaña de Historial -->
    <div class="tab-pane fade show active" id="historial" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="date" id="customSearchMovimientos" placeholder="Buscar por fecha" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnMovimientos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnMovimientos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tblMovimientos" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Transacción</th>
                                <th>Movimiento</th>
                                <th>Proveedor</th>
                                <th>Cliente</th>
                                <th>Empleado</th>
                                <th>Proyecto</th>
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

    <!-- Pestaña de Registrar -->
    <div class="tab-pane fade " id="registrar" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <form method="post" id="frmMovimientos">
                    <h5 class="mt-4">I. Datos</h5>
                    <hr>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="selectTipoMovimiento">Tipo de Movimiento *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="selectTipoMovimiento" name="selectTipoMovimiento">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numeroTransaccion">Número de Transacción *</label>
                                    <input type="text" class="form-control" id="numeroTransaccion" name="numeroTransaccion">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div id="selectProveedorContainer" style="display: none;" class="visibilidadProveedor">
                                <div class="card shadow-sm rounded-3">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Proveedor</h5>
                                    </div>
                                    <hr class="my-0">
                                    <div class="card-body">
                                        <label for="selectProveedor">Nombre</label>
                                        <div class="input-group mb-2">
                                            <select class="form-control" id="selectProveedor" name="selectProveedor">
                                                <!-- Opciones cargadas dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="selectClienteContainer" style="display: none;" class="visibilidadCliente">
                                <div class="card shadow-sm rounded-3">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Cliente</h5>
                                    </div>
                                    <hr class="my-0">
                                    <div class="card-body">
                                        <label for="selectCliente">Nombre</label>
                                        <div class="input-group mb-2">
                                            <select class="form-control" id="selectCliente" name="selectCliente">
                                                <!-- Opciones cargadas dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div id="selectPagoEmpleado" style="display: none;" class="selectPagoEmpleado">
                            <div class="card shadow-sm rounded-3">
                                <div class="card-header bg-light">
                                    <h6 class="fw-bold mb-0">Empleado</h5>
                                </div>
                                <hr class="my-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="selectTipoTransaccion">Categoría de Pago</label>
                                            <div class="input-group mb-2">
                                                <select class="form-control" name="selectTipoTransaccion" id="selectTipoTransaccion"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="selectCodigoEmpleado">Nombre</label>
                                            <div class="input-group mb-2">
                                                <select class="form-control" name="selectCodigoEmpleado" id="selectCodigoEmpleado"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numeroDocumento">Número de Documento *</label>
                                    <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monto">Monto *</label>
                                    <input type="text" class="form-control moneda" id="monto" name="monto">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="selectTipoDocumento">Tipo de Documento *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="selectTipoDocumento" name="selectTipoDocumento">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="codigoProyecto">Proyecto *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="selectTipoPago">Tipo de Pago *</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="selectTipoPago" name="selectTipoPago">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <label for="fecha">Fecha *</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn bg-info" id="registrarMovimiento">Registrar</button>
                    <button type="button" class="btn btnCancelar" id="cancelar">Cancelar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pestaña de recibos de pago
    <div class="tab-pane fade " id="recibos" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="date" id="customSearchRecibos" placeholder="Buscar por fecha" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnRecibos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnRecibos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tblRecibos" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Transacción</th>
                                <th>Movimiento</th>
                                <th>Empleado</th>
                                <th>Proyecto</th>
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
    </div> -->
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/movimientos.js"></script>
<?php include "Views/templates/footer.php"; ?>