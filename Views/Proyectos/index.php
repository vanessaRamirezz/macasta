<?php include "Views/templates/header.php"; ?>

<ol class="breadcrumb mb-4 bg-secondary vistaProyectos">
    <li class="breadcrumb-item active text-white">PROYECTOS</li>
</ol>

<button class="btn mb-4" type="button" id="btn-colorNuevo">Nuevo</button>
<div class="card bg-light mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <div class="input-group input-ancho">
                <input type="text" id="customSearchProyectos" placeholder="Buscar por nombre o código..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="searchBtnProyectos" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSearchBtnProyectos" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="tblProyectos" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Proyecto</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Cliente</th>
                        <th>Responsable</th>
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
<div id="nuevoProyecto" class="modal fade" tabindex="-1" role="dialog" ria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="title">Nuevo Proyecto</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <h5 class="mt-4">I. Datos</h5>
                <hr>
                <form method="POST" id="frmProyecto" class="bold-letra">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigoProyecto">Código *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input id="codigoProyecto" class="form-control" type="text" name="codigoProyecto">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="nombreProyecto">Nombre *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-project-diagram"></i></span>
                                </div>
                                <input id="nombreProyecto" class="form-control" type="text" name="nombreProyecto">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="codigoCliente">Cliente *</label>
                            <div class="input-group mb-2">
                                <select id="codigoCliente" class="form-control" name="codigoCliente">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="fechaInicio">Fecha Inicio *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input id="fechaInicio" class="form-control" type="date" name="fechaInicio">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="fechaFin">Fecha Fin *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                </div>
                                <input id="fechaFin" class="form-control" type="date" name="fechaFin">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="valorCotizado">Valor Cotizado *</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input id="valorCotizado" class="form-control  moneda" type="text" name="valorCotizado" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="valorRentabilidad">Rentabilidad</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                </div>
                                <input id="valorRentabilidad" class="form-control moneda" type="text" name="valorRentabilidad" value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="ingresos">Ingresos</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-arrow-up"></i></span>
                                </div>
                                <input id="ingresos" class="form-control moneda" type="text" name="ingresos" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="salidas">Salidas</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-arrow-down"></i></span>
                                </div>
                                <input id="salidas" class="form-control moneda" type="text" name="salidas" value="0.00">
                            </div>
                        </div>
                    </div>
                    <h5 class="mt-5">II. Responsable</h5>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigoResponsable">Codigo *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-minus"></i></span>
                                </div>
                                <input id="codigoResponsable" class="form-control" type="text" name="codigoResponsable">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="nombreResponsable">Nombre *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input id="nombreResponsable" class="form-control" type="text" name="nombreResponsable">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono">Telefono</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input id="telefono" class="form-control" type="text" name="telefono">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="flexCheckIndeterminate">
                                <label class="form-check-label" for="flexCheckIndeterminate">
                                    Existentes
                                </label>
                            </div>
                            <div id="visibilidad">
                                <div class="input-group mb-2">
                                    <select id="responsable" class="form-control" name="responsable">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--estadoProyecto -->
                    <h5 class="mt-5">III. Estado del Proyecto</h5>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="estado">Estado</label>
                            <div class="input-group mb-2">
                                <select id="estado" class="form-control" name="estado">
                                </select>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/proyectos.js"></script>
<?php include "Views/templates/footer.php"; ?>