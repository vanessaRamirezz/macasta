<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaProductos">
    <li class="breadcrumb-item active text-white">PRODUCTOS</li>
</ol>
<button class="btn mb-4" type="button" id="btn-colorNuevo">Nuevo</button>
<div class="card bg-light mb-4">
    <div class="card-body">
        <!-- Agregar contenedor para alinear el buscador a la derecha y limitar su tamaño al 50% -->
        <div class="d-flex justify-content-end mb-3">
            <div class="input-group input-ancho">
                <input type="text" id="customSearchProductos" placeholder="Buscar por nombre o código..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="searchBtnProductos" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSearchBtnProductos" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-bordered" id="tblProductos" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Costo</th>
                        <th>Precio</th>
                        <th>Existencias</th>
                        <th>Proveedor</th>
                        <th>Agrupacion</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="nuevoProducto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="title">Agregar Producto</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="frmProducto" class="bold-letra">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="codigo">Codigo *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>

                                        <input id="codigo" class="form-control" type="text" name="codigo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="nombre">Nombre *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-box"></i></span>
                                    </div>
                                    <input id="nombre" class="form-control" type="text" name="nombre" require>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="cantidad">Existencias</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                    </div>
                                    <input id="cantidad" class="form-control" type="number" name="cantidad" min="0" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="codigoProveedor">Proveedor *</label>
                                <div class="input-group mb-2">
                                    <select id="codigoProveedor" class="form-control" name="codigoProveedor">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="costo">Costo</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input id="costo" class="form-control moneda" type="text" name="costo" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="precio">Precio</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input id="precio" class="form-control moneda" type="text" name="precio" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="codigoAgrupacion">Agrupación</label>
                            <div class="input-group mb-2">
                                <select id="codigoAgrupacion" class="form-control" name="codigoAgrupacion">
                                </select>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/productos.js"></script>
<?php include "Views/templates/footer.php"; ?>