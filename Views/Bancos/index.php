<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaBancos">
    <li class="breadcrumb-item active text-white">BANCOS</li>
</ol>
<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#registros">Registros</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#bancos">Bancos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#cuentas">Cuentas</a>
    </li>
</ul>
<div class="tab-content mt-3">
    <!-- Pestaña de Registros -->
    <div class="tab-pane fade show active" id="registros" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-bordered" id="tblRegistros" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Banco</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="verListado" class="modal fade" tabindex="-1" role="dialog" ria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="title">Nuevo Banco</h5>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" id="frmVerlistado" class="bold-letra">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="codigoBancoVer">Código Banco *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input id="codigoBancoVer" class="form-control" type="text" name="codigoBancoVer">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombreBancoVer">Nombre Banco *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                                        </div>
                                        <input id="nombreBancoVer" class="form-control" type="text" name="nombreBancoVer">
                                    </div>
                                </div>
                            </div>
                            <!-- Contenedor responsive para la tabla -->
                            <div class="responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Código Cuenta</th>
                                            <th>Nombre Cuenta</th>
                                            <th>Saldo Inicial</th>
                                            <th>Ingresos</th>
                                            <th>Salidas</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCuentasBody">
                                        <!-- Aquí se insertarán dinámicamente las cuentas -->
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pestaña de Bancos -->
    <div class="tab-pane fade show " id="bancos" role="tabpanel">
        <button class="btn mb-4 bg-info" type="button" id="bancosRegistrar">Nuevo</button>
        <div class="card  bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchBancosRegistrar" placeholder="Buscar por nombre o código..." class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnBancosRegistrar" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnBancosRegistrar" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tblBancosRegistrar" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Banco</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="nuevoBancoRegistrar" class="modal fade" tabindex="-1" role="dialog" ria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="title">Nuevo Banco</h5>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <h5 class="mt-4">I. Datos</h5>
                        <hr>
                        <form method="POST" id="frmBancoRegistrar" class="bold-letra">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="codigoBancoRegistrar">Código Banco *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input id="codigoBancoRegistrar" class="form-control" type="text" name="codigoBancoRegistrar">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombreBancoRegistrar">Nombre Banco *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                                        </div>
                                        <input id="nombreBancoRegistrar" class="form-control" type="text" name="nombreBancoRegistrar">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <button class="btn color-btn" type="button" id="btnAccionBanco">Registrar</button>
                                <button class="btn btnCancelar" type="button" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pestaña de Cuentas -->
    <div class="tab-pane fade" id="cuentas" role="tabpanel">
        <button class="btn mb-4 bg-info" type="button" id="btnNuevo">Nuevo</button>
        <div class="card  bg-light mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchBancos" placeholder="Buscar por nombre o código..." class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnBancos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnBancos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tblCuentas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Codigo Cuenta</th>
                                <th>Nombre Cuenta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="nuevaCuenta" class="modal fade" tabindex="-1" role="dialog" ria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="title">Nueva Cuenta</h5>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <h5 class="mt-4">I. Datos</h5>
                        <hr>
                        <form method="POST" id="frmCuenta" class="bold-letra">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="codigoBanco">Codigo Banco *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                                        </div>
                                        <input id="codigoBanco" class="form-control" type="text" name="codigoBanco">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="selectBanco">Banco *</label>
                                    <div class="input-group mb-2">
                                        <select class="form-control" name="selectBanco" id="selectBanco"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="codigoCuentaBancaria">Código Cuenta Bancaria *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input id="codigoCuentaBancaria" class="form-control" type="text" name="codigoCuentaBancaria">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombreCuentaBancaria">Nombre Cuenta Bancaria *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                                        </div>
                                        <input id="nombreCuentaBancaria" class="form-control" type="text" name="nombreCuentaBancaria">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="saldoInicial">Saldo Inicial *</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-arrow-up"></i></span>
                                        </div>
                                        <input id="saldoInicial" class="form-control moneda" type="text" name="saldoInicial" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="ingresos">Ingresos</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-arrow-up"></i></span>
                                        </div>
                                        <input id="ingresos" class="form-control moneda" type="text" name="ingresos" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="salidas">Salidas</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-arrow-down"></i></span>
                                        </div>
                                        <input id="salidas" class="form-control moneda" type="text" name="salidas" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="saldo">Saldo</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-arrow-down"></i></span>
                                        </div>
                                        <input id="saldo" class="form-control moneda" type="text" name="saldo" value="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <button class="btn color-btn" type="button" id="btnAccion">Registrar</button>
                                <button class="btn btnCancelar" type="button" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/bancos.js"></script>
<?php include "Views/templates/footer.php"; ?>