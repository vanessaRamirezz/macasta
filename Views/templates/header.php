<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Panel Administración</title>
    <link href="<?php echo base_url; ?>/Assets/css/styles.css" rel="stylesheet" />
    <link href="<?php echo base_url; ?>/Assets/css/librerias/select2.min.css" rel="stylesheet" />
    <link href="<?php echo base_url; ?>/Assets/css/librerias/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="<?php echo base_url; ?>/Assets/css/librerias/buttons.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="<?php echo base_url; ?>/Assets/js/librerias/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php
    $currentPage = basename($_SERVER['REQUEST_URI']);
    $configPages = ["TiposDocumentos", "TipoMovimiento", "EstadosProyectos", "Responsables", "Agrupaciones", "Usuarios", "Bancos", "Empleados"];
    $collapseClass = in_array($currentPage, $configPages) ? "show" : "";

    $currentPageInventario = basename($_SERVER['REQUEST_URI']);
    $configPagesInventario = ["Productos", "Existencias"];
    $collapseClassInventario = in_array($currentPageInventario, $configPagesInventario) ? "show" : "";

    // al dar clic mantener abierto el collapsed
    $currentPageFacturacion = basename($_SERVER['REQUEST_URI']);
    $configPagesFacturacion = ["Facturacion", "HistorialContingencia", "SujetoExcluido", "Listados"];
    $collapseClassFacturacion = in_array($currentPageFacturacion, $configPagesFacturacion) ? "show" : "";

    $claseContingencia = isset($data['contingenciaActiva']) && $data['contingenciaActiva'] ? 'contingencia-activa' : '';

    ?>

    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-white">
        <a class="navbar-brand" href="<?php echo base_url; ?>Inicio">Gestor de proyectos</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        <div class="input-group"></div>
        <!-- Navbar-->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="<?php echo base_url; ?>Usuarios/salir">Cerrar Sessión</a>
                </div>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-primary" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- Sección para mostrar el usuario -->
                        <div class="user-profile text-center">
                            <div class="sb-nav-link-icon mb-2">
                                <img class="img-fluid" src="<?php echo base_url; ?>/Assets/img/logo.jpg" alt="Logo">
                            </div>
                            <span class="admin-name"><?php echo $_SESSION['nombreUsuario']; ?></span>
                        </div>
                        <a class="nav-link" href="<?php echo base_url; ?>Inicio">
                            <div class="sb-nav-link-icon"><i class="fas fa-home mr-2"></i></div>
                            Inicio
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Proveedores">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck"></i></div>
                            Proveedores
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Clientes">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Clientes
                        </a>
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLayoutsInventario" aria-expanded="false" aria-controls="collapseLayoutsInventario">
                            <div class="sb-nav-link-icon"><i class="fas fa-solid fa-warehouse"></i></i></div>
                            Inventario
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?php echo $collapseClassInventario; ?>" id="collapseLayoutsInventario" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageInventario == 'Productos') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Productos">Productos</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageInventario == 'Existencias') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Existencias">Existencias</a>
                            </nav>
                        </div>
                        <a class="nav-link" href="<?php echo base_url; ?>Proyectos">
                            <div class="sb-nav-link-icon"><i class="fas fa-project-diagram"></i></div>
                            Proyectos
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Compras">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i> </div>
                            Compras
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Movimientos">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            Movimientos
                        </a>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLayoutsFacturacion" aria-expanded="false" aria-controls="collapseLayoutsFacturacion">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice"></i></i></div>
                            Facturación
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?php echo $collapseClassFacturacion; ?>" id="collapseLayoutsFacturacion" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageFacturacion == 'Facturacion') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Facturacion">Emitir</a>
                            </nav>
                                                        <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageFacturacion == 'SujetoExcluido') ? 'active' : ''; ?>" href="<?php echo base_url; ?>SujetoExcluido">Factura Sujeto Excluido</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageFacturacion == 'HistorialContingencia') ? 'active' : ''; ?>" href="<?php echo base_url; ?>HistorialContingencia">Contingencia</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPageFacturacion == 'Listados') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Listados">Historial</a>
                            </nav>
                        </div>
                        <a class="nav-link" href="<?php echo base_url; ?>Cotizacion">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            Cotización
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Reportes">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-pdf"></i></div>
                            Reportes
                        </a>
                        <div class="sb-sidenav-menu-heading">Opciones</div>
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></i></div>
                            Configuraciónes
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?php echo $collapseClass; ?>" id="collapseLayouts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'TiposDocumentos') ? 'active' : ''; ?>" href="<?php echo base_url; ?>TiposDocumentos">Tipo Documento</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'TipoMovimiento') ? 'active' : ''; ?>" href="<?php echo base_url; ?>TipoMovimiento">Tipo Movimiento</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'EstadosProyectos') ? 'active' : ''; ?>" href="<?php echo base_url; ?>EstadosProyectos">Estados Proyectos</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'Bancos') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Bancos">Bancos</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'Empleados') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Empleados">Empleados</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'Agrupaciones') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Agrupaciones">Agrupaciones</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'Responsables') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Responsables">Responsables</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?php echo ($currentPage == 'Usuarios') ? 'active' : ''; ?>" href="<?php echo base_url; ?>Usuarios">Usuarios</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer">

                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid mt-2 <?php echo $claseContingencia; ?>">