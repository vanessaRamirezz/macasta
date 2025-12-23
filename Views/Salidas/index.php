<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaCompras">
    <li class="breadcrumb-item active text-white">SALIDAS</li>
</ol>

<div class="card">
    <div class="card-body">
        <form method="post" id="frmSalidas">
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label for="numeroDocumento">Número de Documento *</label>
                        <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" required>
                    </div>
                    <div class="col-md-6">
                        <label for="numeroDocumentoFe">Número de Documento Fe *</label>
                        <input type="text" class="form-control" id="numeroDocumentoFe" name="numeroDocumentoFe">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label for="selectTipoMovimiento">Tipo de Movimiento *</label>
                        <div class="input-group mb-2">
                            <select class="form-control" id="selectTipoMovimiento" name="selectTipoMovimiento">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="selectProveedor">Proveedor *</label>
                        <div class="input-group mb-2">
                            <select class="form-control" id="selectProveedor" name="selectProveedor">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label for="codigoProyecto">Proyecto *</label>
                        <div class="input-group mb-2">
                            <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="fechaCompra">Fecha de Salida *</label>
                        <input type="date" class="form-control" id="fechaCompra" name="fechaCompra">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <label for="observacion">Observación:</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 ml-auto">
                    <div class="form-group">
                        <button type="button" class="btn btn-success mt-2 btn-block" id="generarSalida">Registrar Salida</button>
                    </div>
                </div>
            </div>
        </form>
        <button type="button" class="btn btnCancelar" id="cancelar">Cancelar</button>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/salidas.js"></script>
<?php include "Views/templates/footer.php"; ?>