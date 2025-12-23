<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaAgrupaciones">
    <li class="breadcrumb-item active text-white">AGRUPACIONES</li>
</ol>

<div class="card">
    <div class="card-body">
        <form method="post" id="frmAgrupaciones" class="bold-letra">
            <div class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label for="agrupacionCodigo">Codigo *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input id="agrupacionCodigo" class="form-control" type="text" name="agrupacionCodigo">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="nombreAgrupacion">Nombre *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="nombreAgrupacion" class="form-control" type="text" name="nombreAgrupacion">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <button class="btn color-btn" type="button" id="btnAccion2">Registrar</button>
                        <button class="btn btnCancelar" type="button" id="btn-cancelar2">Cancelar</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-ancho">
                        <input type="text" id="customSearchAgrupaciones" placeholder="Buscar por nombre o código..." class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnAgrupaciones" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnAgrupaciones" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="tblAgrupaciones" width="100%" cellspacing="0">
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/agrupaciones.js"></script>
<?php include "Views/templates/footer.php"; ?>