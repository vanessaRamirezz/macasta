<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaTipoDocumentos">
    <li class="breadcrumb-item active text-white">TIPOS DE DOCUMENTOS</li>
</ol>
<div class="card">
    <div class="card-body">
        <!-- Inputs Arriba -->
        <form method="POST" id="frmTiposDocumentos" class="bold-letra">
            <div class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="codigo">Codigo *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input id="codigo" class="form-control" type="text" name="codigo">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="nombre">Nombre *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="nombre" class="form-control" type="text" name="nombre">
                        </div>
                    </div>
                    <div class="mb-2">
                        <button class="btn color-btn" type="button" id="btnAccionDocumentos">Registrar</button>
                        <button class="btn btnCancelar" type="button" id="btn-cancelar">Cancelar</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- Card para la Tabla con el Buscador -->
        <div class="card mt-4">
            <div class="card-body">
                <!-- Buscador Dentro del Card -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchDocumentos" placeholder="Buscar por nombre o código..." class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnDocumentos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnDocumentos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Tabla -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="tblTiposDocumentos" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/tipoDocumentos.js"></script>
<?php include "Views/templates/footer.php"; ?>