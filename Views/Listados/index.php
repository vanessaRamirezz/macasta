<?php include "Views/templates/header.php"; ?>

<ol class="breadcrumb mb-4 bg-secondary vistaListado">
    <li class="breadcrumb-item active text-white">HISTORIAL</li>
</ol>


<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#facturas">Facturas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#comprobanteCreditoF">Comprobante de Credito Fiscal</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#notasCredito">Notas de Credito</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#sujetoExcluido">Factura de Sujeto Excluido</a>
    </li>
</ul>

<div class="tab-content mt-3">

    <div class="tab-pane fade show active" id="facturas" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchFe" placeholder="Buscar por nombre" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnFe" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnFe" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblFacturas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Numero Control</th>
                                <th>Codigo Generación</th>
                                <th>Fecha Generción</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Invalidar</th>
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

    <div class="tab-pane fade" id="comprobanteCreditoF" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchCcfe" placeholder="Buscar por nombre" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnCcfe" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnCcfe" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblComprobatesCF" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Numero Control</th>
                                <th>Codigo Generación</th>
                                <th>Fecha Generción</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Invalidar</th>
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

    <div class="tab-pane fade" id="notasCredito" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchNc" placeholder="Buscar por nombre" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnNc" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnNc" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblNotaCredito" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Numero Control</th>
                                <th>Codigo Generación</th>
                                <th>Fecha Generción</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Invalidar</th>
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

    <div class="tab-pane fade" id="sujetoExcluido" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchFse" placeholder="Buscar por nombre" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnFse" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnFse" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblSujetoExcluido" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Numero Control</th>
                                <th>Codigo Generación</th>
                                <th>Fecha Generción</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Invalidar</th>
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

    <div id="invalidarEvento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="title">Evento de Invalidación</h5>
                </div>
                <div class="modal-body">
                    <form method="POST" id="frmInvalidar" class="bold-letra">
                        <input type="hidden" id="idFacturaInvalidar" name="idFacturaInvalidar">

                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="codigoGen">Codigo Generación</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="codigoGen" type="text" name="codigoGen">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="selloRecepcion">Sello Recibido</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="selloRecepcion" type="text" name="selloRecepcion">
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="numeroControl">Numero Control</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="numeroControl" type="text" name="numeroControl">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="codigoGeneracionRemmplazo">Codigo generación que reemplaza este dte</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="codigoGeneracionRemmplazo" type="text" name="codigoGeneracionRemmplazo">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h5>Motivo</h5>
                            <hr>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="selectTipoI">Tipo de Invalidación</label>
                                        <div class="input-group mb-2">
                                            <select class="form-control" id="selectTipoI" name="selectTipoI">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="motInvalidacion">Motivo de invalidación</label>
                                        <div class="input-group mb-2">
                                            <textarea class="form-control" name="motInvalidacion" id="motInvalidacion"></textarea>
                                            <!-- <input type="text" class="form-control" id="codigoGeneracionRemmplazo" type="text" name="codigoGeneracionRemmplazo"> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn color-btn" type="button" id="btnGenerar">Generar</button>
                        <button class="btn btnCancelar" type="button" data-dismiss="modal" id="cancelarEv">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="enviarCorreoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="title">Enviar</h5>
                </div>
                <div class="modal-body">
                    <form method="POST" id="frmCorreo" class="bold-letra">
                        <div class="mb-3">
                            <input type="hidden" id="idFactura" name="idFactura">

                            <div class="col-md-8">
                                <label for="cliente">Cliente</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="cliente" type="text" name="cliente">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-8">
                                <label for="correoE">Correo</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="correoE" type="text" name="correoE">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-8">
                                <label for="control">Numero de Control</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="control" type="text" name="control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-8">
                                <label for="dte">Dte</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="dte" type="text" name="dte">
                                </div>
                            </div>
                        </div>
                        <button class="btn color-btn" type="button" id="enviarCorreo">Enviar</button>
                        <button class="btn btnCancelar" type="button" data-dismiss="modal" id="cancelarEnviar">Cancelar</button>
                    </form>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/listados.js"></script>
<?php include "Views/templates/footer.php"; ?>