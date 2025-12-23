<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaResponsables">
    <li class="breadcrumb-item active text-white">RESPONSABLES</li>
</ol>

<button class="btn mb-4" type="button" id="btn-colorNuevo">Nuevo</button>

<div class="card  bg-light mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <div class="input-group input-ancho">
                <input type="text" id="customSearchResponsables" placeholder="Buscar por nombre o código..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="searchBtnResponsables" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSearchBtnResponsables" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="tblResponsables" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="nuevoResponsable" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="title">Nuevo Responsable</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="frmResponsable" class="bold-letra">
                    <div class="mb-3">
                        <label for="codigo">Codigo *</label>
                        <div class="input-group mb-2" style="max-width: 50%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input type="text" class="form-control" id="codigo" type="text" name="codigo">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre">Nombre *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="nombre" type="text" name="nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono">Telefono</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="text" class="form-control" id="telefono" type="text" name="telefono">
                            </div>
                        </div>
                    </div>

                    <button class="btn color-btn" type="button" id="btnAccion">Registrar</button>
                    <button class="btn btnCancelar" type="button" data-dismiss="modal">Cancelar</button>
                </form>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/responsables.js"></script>
<?php include "Views/templates/footer.php"; ?>