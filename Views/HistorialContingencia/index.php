<?php include "Views/templates/header.php"; ?>

<ol class="breadcrumb mb-4 bg-secondary vistaContingencia">
    <li class="breadcrumb-item active text-white">DTE GENERADOS EN CONTINGENCIA</li>
</ol>


<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#facturas">Dtes en contingencia</a>
    </li>
</ul>

<div class="tab-content mt-3">

    <div class="mb-3">
        <!-- Botón para abrir el modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#consultaLote">
            Consultar Lote
        </button>

        <!-- Modal -->
        <div class="modal fade" id="consultaLote" tabindex="-1" role="dialog" aria-labelledby="consultaLoteLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!-- Encabezado -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="consultaLoteLabel">Consultar Lotes</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Cuerpo -->
                    <div class="modal-body">
                        <form id="frmLote">
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="fechaFiltro">Fecha de procesamiento</label>
                                        <div class="input-group mb-2">
                                            <input type="date" class="form-control" id="fechaFiltro" name="fechaFiltro">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="selectLote">Busque y seleccione</label>
                                        <div class="input-group mb-2">
                                            <select class="form-control" id="selectLote" name="selectLote" disabled></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Pie -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnConsultar" class="btn btn-primary">Consultar</button>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <div class="tab-pane fade show active" id="facturas" role="tabpanel">
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-success mt-2 w-100" id="generarEventoContingencia">
                                Generar evento contingencia
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-danger mt-2 w-100" id="generarLote">
                                Emitir Lote
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" id="tblHistorialContingencia" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tipo Dte</th>
                                <th>Numero Control</th>
                                <th>Codigo Generación</th>
                                <th>Cliente</th>
                                <th>Incluido en evento</th>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/historialContingencia.js"></script>
<?php include "Views/templates/footer.php"; ?>