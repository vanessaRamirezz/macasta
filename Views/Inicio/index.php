<?php include "Views/templates/header.php"; ?>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">PAGINA PRINCIPAL</li>
</ol>

<div class="row">
    <div class="col-xl-3 col-md-6 mt-3">
        <div class="card">
            <div class="card-body d-flex">
                Clientes
                <i class="fas fa-user fa-2x ml-auto"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?php echo base_url; ?>Clientes">Ver Listado</a>
                <span><?php echo $data['clientes']['total']?></span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mt-3">
        <div class="card">
            <div class="card-body d-flex">
                Proveedores
                <i class="fas fa-truck fa-2x ml-auto"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?php echo base_url; ?>Proveedores">Ver Listado</a>
                <span><?php echo $data['proveedores']['total']?></span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mt-3">
        <div class="card">
            <div class="card-body d-flex">
                Proyectos
                <i class="fas fa-project-diagram fa-2x ml-auto"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?php echo base_url; ?>Proyectos">Ver Listado</a>
                <span><?php echo $data['proyectos']['total']?></span>
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
<?php include "Views/templates/footer.php"; ?>