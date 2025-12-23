<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4 bg-secondary vistaReportes">
    <li class="breadcrumb-item active text-white">REPORTES</li>
</ol>

<ul class="nav nav-tabs pestania">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#proyectos">Proyectos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#compras">Compras</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#productos">Productos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#movimientos">Movimientos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#proveedores">Proveedores</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#clientes">Clientes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#cotizaciones">Cotizaciones</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#existencias">Existencias</a>
    </li>
        <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#facturas">Facturas</a>
    </li>
</ul>

<div class="tab-content mt-3">

    <!-- Vista de reporte de proyectos -->
    <div class="tab-pane fade show active" id="proyectos" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteProyectos" method="POST" target="_blank" id="frmReporteProyectos">
            <div class="container">

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="desde">Desde</label>
                                <input class="form-control" type="date" name="desde" id="desde" required>
                            </div>
                            <div class="col-lg-6">
                                <label for="hasta">Hasta</label>
                                <input class="form-control" type="date" name="hasta" id="hasta" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Entidad</h5>
                        <div class="row">
                            <div class="col-md-6 col-lg-6">
                                <label for="codigoCliente">Por Cliente</label>
                                <div class="input-group mb-2">
                                    <select class="form-control cliente" name="codigoCliente" id="codigoClientee">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfProyecto">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiar">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de compras -->
    <div class="tab-pane fade" id="compras" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteCompras" method="POST" target="_blank" id="frmReporteCompras">
            <div class="container">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="desde">Desde</label>
                                    <input class="form-control" type="date" name="desde" id="desde" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="hasta">Hasta</label>
                                    <input class="form-control" type="date" name="hasta" id="hasta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Entidad</h5>
                        <div class="row">
                            <div class="col-md-6 col-lg-5">
                                <label for="codigoProyecto"> Por Proyecto</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoProyecto" id="codigoProyecto" name="codigoProyecto">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfCompras">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarCompras">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de productos -->
    <div class="tab-pane fade" id="productos" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteProductos" method="POST" target="_blank" id="frmReporteProductos">
            <div class="container">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="desde">Desde</label>
                                    <input class="form-control" type="date" name="desde" id="desde" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="hasta">Hasta</label>
                                    <input class="form-control" type="date" name="hasta" id="hasta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Entidad</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="codigoProveedor">Por Proveedor</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoProveedor" name="codigoProveedor" id="codigoProveedorr">
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-lg-6">
                                <label for="codigoProducto">Por Producto</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoProducto" name="codigoProducto" id="codigoProducto">
                                    </select>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfProductos">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarProductos">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de movimientos -->
    <div class="tab-pane fade" id="movimientos" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteMovimientos" method="POST" target="_blank" id="frmReporteMovimientos">
            <div class="container">

                <!-- Filtros por Fecha -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtros por Fecha</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="desde">Desde</label>
                                <input class="form-control" type="date" name="desde" id="desde" required>
                            </div>
                            <div class="col-lg-6">
                                <label for="hasta">Hasta</label>
                                <input class="form-control" type="date" name="hasta" id="hasta" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros por Entidad -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtros por Entidad</h5>
                        <div class="row">
                            <div class="col-lg-4">
                                <label for="codigoCliente">Por Cliente</label>
                                <div class="input-group mb-2">
                                    <select class="form-control otraClase" name="codigoCliente" id="codigoCliente">
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="codigoProveedor">Por Proveedor</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoProveedor" name="codigoProveedor" id="codigoProveedor"></select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="codigoEmpleado">Por Empleado</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoEmpleado" name="codigoEmpleado" id="codigoEmpleado"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tipo de Movimiento -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tipo de Movimiento</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="tipoMovimiento">Tipo</label>
                                <div class="input-group mb-2">
                                    <select class="form-control tipoMovimiento" name="tipoMovimiento" id="tipoMovimiento"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfMovimientos">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarMovimiento">Limpiar</button>
                </div>
            </div>
        </form>

    </div>

    <!-- Vista de reporte de Proveedores -->
    <div class="tab-pane fade" id="proveedores" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteProveedores" method="POST" target="_blank" id="frmReporteProveedores">
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="desde">Desde</label>
                                    <input class="form-control" type="date" name="desde" id="desde" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="hasta">Hasta</label>
                                    <input class="form-control" type="date" name="hasta" id="hasta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfProveedores">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarProveedores">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de Clientes -->
    <div class="tab-pane fade" id="clientes" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteClientes" method="POST" target="_blank" id="frmReporteClientes">
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="desde">Desde</label>
                                    <input class="form-control" type="date" name="desde" id="desde" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="hasta">Hasta</label>
                                    <input class="form-control" type="date" name="hasta" id="hasta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfClientes">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarClientes">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de Cotiazciones -->
    <div class="tab-pane fade" id="cotizaciones" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteCotizaciones" method="POST" target="_blank" id="frmCotizaciones">
            <div class="container">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="desde">Desde</label>
                                    <input class="form-control" type="date" name="desde" id="desde" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="hasta">Hasta</label>
                                    <input class="form-control" type="date" name="hasta" id="hasta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Entidad</h5>
                        <div class="row">
                            <div class="col-md-6 col-lg-6">
                                <label for="codigo">Por Cliente</label>
                                <div class="input-group mb-2">
                                    <select class="form-control cliente" name="codigo" id="codigo">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfCotizaciones">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarCotizaciones">Limpiar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista de reporte de existencias -->
    <div class="tab-pane fade" id="existencias" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteExistencias" method="POST" target="_blank" id="frmReporteExistencias">
            <div class="container">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Entidad</h5>
                        <div class="row">
                            <div class="col-md-6 col-lg-5">
                                <label for="proyecto"> Por Proyecto</label>
                                <div class="input-group mb-2">
                                    <select class="form-control codigoProyecto" id="proyecto" name="proyecto">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfExistencias">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarExistencias">Limpiar</button>
                </div>
            </div>
        </form>
    </div>
    
     <!-- Vista de reporte de Facturas -->
    <div class="tab-pane fade" id="facturas" role="tabpanel">
        <form action="<?php echo base_url; ?>Reportes/reporteFacturas" method="POST" target="_blank" id="frmReporteFacturas">
            <div class="container">

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Fechas</h5>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="desde">Desde</label>
                                <input class="form-control" type="date" name="desde" id="desde" required>
                            </div>
                            <div class="col-lg-6">
                                <label for="hasta">Hasta</label>
                                <input class="form-control" type="date" name="hasta" id="hasta" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Filtro por Tipo Dte</h5>
                        <div class="row">
                            <div class="col-md-6 col-lg-6">
                                <label for="tipoDocumento">Tipo Documento</label>
                                <div class="input-group mb-2">
                                    <select class="form-control cliente" name="tipoDocumento" id="tipoDocumento">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-left mt-4">
                    <button type="submit" class="btn btn-danger" id="pdfFactura">Generar PDF</button>
                    <button type="button" class="btn btn-secondary" id="limpiarFacturas">Limpiar</button>
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
<script type="module" src="<?php echo base_url; ?>/Assets/js/modulos/reportes.js"></script>
<?php include "Views/templates/footer.php"; ?>