<?php
// config/funciones_comprobantes.php

/**
 * Obtener el próximo número de comprobante fiscal
 */
function obtenerProximoComprobante($tipo_comprobante) {
    global $mysqli;
    
    $query = "SELECT * FROM rpos_secuenciales_comprobantes 
              WHERE tipo_comprobante = ? AND estado = 'Activo' 
              AND secuencial_actual < secuencial_final";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $tipo_comprobante);
    $stmt->execute();
    $comprobante = $stmt->get_result()->fetch_object();
    $stmt->close();
    
    if (!$comprobante) {
        return false; // No hay comprobantes disponibles
    }
    
    return $comprobante->prefijo . str_pad($comprobante->secuencial_actual + 1, 8, '0', STR_PAD_LEFT);
}

/**
 * Actualizar el secuencial después de usar un comprobante
 */
function actualizarSecuencialComprobante($tipo_comprobante) {
    global $mysqli;
    
    $query = "UPDATE rpos_secuenciales_comprobantes 
              SET secuencial_actual = secuencial_actual + 1 
              WHERE tipo_comprobante = ? AND estado = 'Activo' 
              AND secuencial_actual < secuencial_final";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $tipo_comprobante);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Verificar estado de los comprobantes y notificar si están por agotarse
 */
function verificarEstadoComprobantes() {
    global $mysqli;
    
    $query = "SELECT * FROM rpos_secuenciales_comprobantes WHERE estado = 'Activo'";
    $comprobantes = $mysqli->query($query);
    
    $alertas = [];
    
    while ($comp = $comprobantes->fetch_object()) {
        $disponibles = $comp->secuencial_final - $comp->secuencial_actual;
        $porcentaje = ($disponibles / $comp->secuencial_final) * 100;
        
        if ($disponibles <= 0) {
            // Marcar como agotado
            $query_update = "UPDATE rpos_secuenciales_comprobantes SET estado = 'Agotado' WHERE id = ?";
            $stmt = $mysqli->prepare($query_update);
            $stmt->bind_param('i', $comp->id);
            $stmt->execute();
            $stmt->close();
            
            $alertas[] = "⚠️ Los comprobantes {$comp->tipo_comprobante} se han agotado.";
        } elseif ($disponibles < 50) {
            $alertas[] = "🔔 Los comprobantes {$comp->tipo_comprobante} están por agotarse. Quedan {$disponibles}.";
        } elseif ($disponibles < 100) {
            $alertas[] = "💡 Los comprobantes {$comp->tipo_comprobante} tienen pocas unidades. Quedan {$disponibles}.";
        }
    }
    
    return $alertas;
}