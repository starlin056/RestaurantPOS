
<?php
if (!function_exists('registrar_auditoria')) {
    function registrar_auditoria($accion, $descripcion = null)
    {
        global $mysqli;

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $usuario_id = $_SESSION['user_id'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONOCIDO';

        $sql = "INSERT INTO rpos_movimientos_log (usuario_id, accion, descripcion, ip_usuario, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssss", $usuario_id, $accion, $descripcion, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
}
