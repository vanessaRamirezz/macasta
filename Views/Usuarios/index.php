<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaUsuarios">
    <li class="breadcrumb-item active text-white">USUARIOS</li>
</ol>
<button class="btn mb-4" type="button" id="btn-colorNuevo">Nuevo</button>

<div class="card  bg-light mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <div class="input-group input-ancho">
                <input type="text" id="customSearchUsuarios" placeholder="Buscar por nombre o código..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="searchBtnUsuarios" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSearchBtnUsuarios" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="tblUsuarios" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Usuario</th>
                        <th>Nivel Seguridad</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>



<div id="nuevoUsuario" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="title">Nuevo Usuario</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="frmUsuario" class="bold-letra">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="codigo">Codigo *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="codigo" type="text" name="codigo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="nombre">Usuario *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="nombre" type="text" name="nombre" placeholder="Ej. empleado1">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label for="nombreCom">Nombre Completo *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="nombreCom" type="text" name="nombreCom">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="tipoIdentificacion">Tipo Documento Identificación</label>
                                <div class="input-group mb-2">
                                    <select class="form-control" id="tipoIdentificacion" name="tipoIdentificacion">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="numeroDocumento">Numero Documento</label>
                                <div class="input-group mb-2">
                                    <input id="numeroDocumento" class="form-control" type="text" name="numeroDocumento">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row" id="claves">
                            <div class="col-md-6">
                                <label for="clave">Clave *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="clave" type="text" name="clave">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmar">Confirmar Clave</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="confirmar" type="text" name="confirmar">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nivelSeguridad">Nivel Seguridad *</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="text" class="form-control" id="nivelSeguridad" type="text" name="nivelSeguridad">
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/usuarios.js"></script>
<?php include "Views/templates/footer.php"; ?>