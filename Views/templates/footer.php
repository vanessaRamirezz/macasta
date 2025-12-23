<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Busca solo dentro de la sección de configuración
        const container = document.querySelector('#collapseLayouts');
        const activeLink = container.querySelector('.nav-link.active');
        
        if (activeLink) {
            activeLink.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });
</script>
<!-- Librerías comunes -->
<script src="<?php echo base_url; ?>/Assets/js/librerias/jquery-3.5.1.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/dataTables.bootstrap4.min.js"></script>
<script>
    const base_url = "<?php echo base_url; ?>";
</script>
<script src="<?php echo base_url; ?>/Assets/js/scripts.js"></script>
<!-- DataTables Buttons JS y dependencias -->
<script src="<?php echo base_url; ?>/Assets/js/librerias/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/buttons.bootstrap4.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/buttons.colVis.min.js"></script>

<script src="<?php echo base_url; ?>/Assets/js/librerias/select2.min.js"></script>
<script src="<?php echo base_url; ?>/Assets/js/librerias/es.min.js"></script>
</body>

</html>