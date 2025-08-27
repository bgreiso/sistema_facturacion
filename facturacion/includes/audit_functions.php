<?php
/**
 * Función para registrar acciones en el sistema de auditoría
 * @param mysqli $conn Conexión a la base de datos
 * @param string $action_type Tipo de acción (CREATE, UPDATE, DELETE, LOGIN, LOGOUT, OTHER)
 * @param string $table_name Nombre de la tabla afectada
 * @param int|null $record_id ID del registro afectado
 * @param string|null $action_details Detalles de la acción
 * @return bool True si se registró correctamente, False en caso de error
 */
function logAction($conn, $action_type, $table_name, $record_id = null, $action_details = null) {
    // Verificar si hay una sesión activa y usuario logueado
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Limitar la longitud del user_agent si es necesario
    if (strlen($user_agent) > 255) {
        $user_agent = substr($user_agent, 0, 255);
    }
    
    $sql = "INSERT INTO audit_log (user_id, action_type, table_name, record_id, action_details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta de auditoría: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ississs", $user_id, $action_type, $table_name, $record_id, $action_details, $ip_address, $user_agent);
    $result = $stmt->execute();
    
    if ($result === false) {
        error_log("Error al ejecutar la consulta de auditoría: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

/**
 * Función para obtener el historial de auditoría
 * @param mysqli $conn Conexión a la base de datos
 * @param int $limit Límite de registros a obtener
 * @param int $offset Desplazamiento para paginación
 * @return array|false Array con los registros de auditoría o false en caso de error
 */
function getAuditLog($conn, $limit = 50, $offset = 0) {
    $sql = "SELECT a.*, u.username 
            FROM audit_log a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.action_timestamp DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta de auditoría: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $audit_log = [];
    while ($row = $result->fetch_assoc()) {
        $audit_log[] = $row;
    }
    
    $stmt->close();
    return $audit_log;
}

/**
 * Función para contar el total de registros de auditoría
 * @param mysqli $conn Conexión a la base de datos
 * @return int|false Número total de registros o false en caso de error
 */
function countAuditLog($conn) {
    $sql = "SELECT COUNT(*) as total FROM audit_log";
    $result = $conn->query($sql);
    
    if ($result === false) {
        error_log("Error al contar registros de auditoría: " . $conn->error);
        return false;
    }
    
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}
?>