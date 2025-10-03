<?php
// partials/_sidebar.php
ob_start();

// Determinar la ruta base
$current_path = $_SERVER['PHP_SELF'];
$folders = ['factura', 'delivery', 'auditoria', 'cocina','mesas','bar','usuario','hrm','customes','products','empresa','notificaciones'];
$is_in_subfolder = false;

foreach ($folders as $folder) {
    if (strpos($current_path, "/$folder/") !== false) {
        $is_in_subfolder = true;
        break;
    }
}

$base_path = $is_in_subfolder ? '../' : '';
?>



<!-- Argon Scripts -->
<script src="<?php echo $base_path; ?>assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?php echo $base_path; ?>assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_path; ?>assets/vendor/js-cookie/js.cookie.js"></script>
<script src="<?php echo $base_path; ?>assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?php echo $base_path; ?>assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>

<script src="<?php echo $base_path; ?>assets/vendor/chart.js/dist/Chart.min.js"></script>
<script src="<?php echo $base_path; ?>assets/vendor/chart.js/dist/Chart.extension.js"></script>

<script src="<?php echo $base_path; ?>assets/js/argon.js?v=1.2.0"></script>

<script>
    // Funciones globales útiles
    function formatCurrency(amount) {
        return 'RD$ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Auto-ocultar alertas después de 5 segundos
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
</script>