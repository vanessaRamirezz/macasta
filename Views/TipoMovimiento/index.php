<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 mt-3 bg-secondary vistaTipoMovimientos">
    <li class="breadcrumb-item active text-white">TIPO MOVIMIENTO</li>
</ol>
<div class="card">
    <div class="card-body">
        <!-- Inputs Arriba -->
        <form method="POST" id="frmTiposMovimientos" class="bold-letra">
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="codigo">Codigo *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input id="codigo" class="form-control" type="text" name="codigo">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nombre">Nombre *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="nombre" class="form-control" type="text" name="nombre">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="codigoAplicacion">Aplicación</label>
                        <div class="input-group mb-2">
                            <select id="codigoAplicacion" class="form-control" name="codigoAplicacion">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="efecto">Efecto</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="efecto" class="form-control" type="text" name="efecto">
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn color-btn" type="button" id="btnAccion">Registrar</button>
            <button class="btn btnCancelar" type="button" id="btn-cancelar">Cancelar</button>
        </form>
        <!-- Card para la Tabla con el Buscador -->
        <div class="card mt-4">
            <div class="card-body">
                <!-- Buscador Dentro del Card -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchMovimientos" placeholder="Buscar por nombre o código..." class="form-control">
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
                <!-- Tabla -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="tblTiposMovimientos" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Aplicacion</th>
                                <th>Efecto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Fin del Card para la Tabla -->
    </div>
</div>

<ol class="breadcrumb mb-4 bg-secondary mt-3 vistaAplicaciones">
    <li class="breadcrumb-item active text-white">APLICACIONES</li>
</ol>
<div class="card">
    <div class="card-body">
        <form method="POST" id="frmAplicacion" class="bold-letra">
            <div class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="aplicacionCodigo">Codigo *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input id="aplicacionCodigo" class="form-control" type="text" name="aplicacionCodigo">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="nombreAplicacion">Nombre *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="nombreAplicacion" class="form-control" type="text" name="nombreAplicacion">
                        </div>
                    </div>
                    <div class="mb-2">
                        <button class="btn color-btn" type="button" id="btnAccionAp">Registrar</button>
                        <button class="btn btnCancelar" type="button" id="btn-cancelarA">Cancelar</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="card mt-4">

            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchAplicaciones" placeholder="Buscar por nombre o código..." class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnAplicaciones" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnAplicaciones" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="tblAplicaciones" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/tipoMovimientos.js"></script>
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/Aplicaciones.js"></script>
<?php include "Views/templates/footer.php"; ?>