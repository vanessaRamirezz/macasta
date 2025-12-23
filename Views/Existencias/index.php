<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaExistencias">
    <li class="breadcrumb-item active text-white">EXISTENCIAS</li>
</ol>

<div class="card">
    <div class="card-body">
        <form method="get" action="" id="frmStoks">
            <div class="row">
                <div class="col-md-10">
                    <label for="producto">Producto</label>
                    <div class="input-group mb-2">
                        <select class="form-control" id="producto" name="producto">
                        </select>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-md-6">
                    <label for="proyecto">Proyecto</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-code"></i></span>
                        </div>
                        <input class="form-control" type="text" name="proyecto" id="proyecto">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="cantidadProducto">Unidades disponibles</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                        </div>
                        <input class="form-control" type="number" name="cantidadProducto" id="cantidadProducto">
                    </div>
                </div>
            </div> -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">STOCKS</h5>
                    <div class="responsive">
                        <table class="table table-light" id="tblExistencias">
                            <thead class="thead-light">
                                <tr>
                                    <th>Proyecto</th>
                                    <th>Unidades Disponibles</th>
                                </tr>
                            </thead>
                            <tbody id="stockProductos">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/existencias.js"></script>
<?php include "Views/templates/footer.php"; ?>