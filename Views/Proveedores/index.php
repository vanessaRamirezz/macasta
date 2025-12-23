<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaProveedores">
    <li class="breadcrumb-item active text-white">PROVEEDORES</li>
</ol>
<button class="btn mb-4" type="button" id="btn-colorNuevo">Nuevo</button>
<div class="card bg-light mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <div class="input-group input-ancho">
                <input type="text" id="customSearchProveedores" placeholder="Buscar por nombre o código..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="searchBtnProveedores" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSearchBtnProveedores" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="tblProveedores" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Contacto</th>
                        <th>Limite Credito</th>
                        <th>Saldo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="nuevoProveedor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="title">Nuevo Proveedor</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="frmProveedor" class="bold-letra">
                    <div class="mb-3">
                        <label for="codigo">Codigo *</label>
                        <div class="input-group mb-2" style="max-width: 50%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input id="codigo" class="form-control" type="text" name="codigo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nombre">Nombre *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input id="nombre" class="form-control" type="text" name="nombre">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="contacto">Contacto</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input id="contacto" class="form-control" type="text" name="contacto">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="numeroTelefono">Numero de Telefono *</label>
                        <div class="input-group mb-2" style="max-width: 50%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            </div>
                            <input id="numeroTelefono" class="form-control" type="text" name="numeroTelefono">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="limiteCredito">Limite de Credito</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input id="limiteCredito" class="form-control moneda" type="text" name="limiteCredito" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="saldo">Saldo</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input id="saldo" class="form-control moneda" type="text" name="saldo" value="0.00">
                                </div>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/proveedores.js"></script>
<?php include "Views/templates/footer.php"; ?>