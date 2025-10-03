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



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restaurant Point Of Sale</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_path; ?>assets/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base_path; ?>assets/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $base_path; ?>assets/img/icons/favicon-16x16.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Icons -->
    <link href="<?php echo $base_path; ?>assets/vendor/nucleo/css/nucleo.css" rel="stylesheet">
    <link href="<?php echo $base_path; ?>assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    
    <!-- Argon CSS -->
    <link type="text/css" href="<?php echo $base_path; ?>assets/css/argon.css?v=1.0.0" rel="stylesheet">
    <script src="<?php echo $base_path; ?>assets/js/swal.js"></script>
    
    
    
    <!--Load Swal-->
    <?php if (isset($success)) { ?>
        <!--This code for injecting success alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $success; ?>", "success");
                },
                100);
        </script>
    <?php } ?>
    
    <?php if (isset($err)) { ?>
        <!--This code for injecting error alert-->
        <script>
            setTimeout(function() {
                    swal("Failed", "<?php echo $err; ?>", "error");
                },
                100);
        </script>
    <?php } ?>
    
    <?php if (isset($info)) { ?>
        <!--This code for injecting info alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $info; ?>", "info");
                },
                100);
        </script>
    <?php } ?>
    
    <script>
        function getCustomer(val) {
            $.ajax({
                type: "POST",
                url: "customer_ajax.php",
                data: 'custName=' + val,
                success: function(data) {
                    //alert(data);
                    $('#customerID').val(data);
                }
            });
        }
    </script>
</head>

