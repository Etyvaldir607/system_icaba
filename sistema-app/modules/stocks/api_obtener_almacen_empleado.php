<?php
/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */



// Obtiene el id_producto
$id_user_current = $_user['id_user'];
// $id_user_current = $_POST['id_user_current'];
// Verifica si es una peticion ajax
if (is_ajax()) {
    // @etysoft  obtiene el almacen asignado al usuario actual
    $almacenes = $db->query("
        SELECT
            a.id_almacen,
            a.almacen,
            s.id_sucursal,
            s.sucursal
        FROM inv_almacen_empleados ae
        JOIN inv_almacenes a ON a.id_almacen = ae.almacen_id
        JOIN inv_almacen_sucursales asu ON asu.almacen_id = a.id_almacen 
        JOIN inv_sucursal s ON s.id_sucursal = asu.sucursal_id
        JOIN sys_users u  ON u.persona_id = ae.empleado_id
        WHERE u.id_user = $id_user_current
    ")->fetch();
            
	// Envia respuesta
	echo json_encode($almacenes);
} else {
	// Error 404
	require_once not_found();
	exit;
}
