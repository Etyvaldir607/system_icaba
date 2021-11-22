<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Obtiene la sucursal actual, en caso de que el servicio se requiera para actualizar
$id_sucursal = (sizeof($params) > 0) ? $params[0] : 0;
// Verifica si es una peticion ajax
if (is_ajax()) {
    // Obtiene los alamacenes que no tienen asignación
	$almacenes = $db->query("
        SELECT
        s.id_sucursal,
        a.id_almacen,
        a.almacen
        FROM
            inv_almacenes a
        LEFT JOIN inv_almacen_sucursales asu ON asu.almacen_id = a.id_almacen
        LEFT JOIN inv_sucursal s ON s.id_sucursal = asu.sucursal_id 
        
        WHERE
            asu.almacen_id IS NULL OR s.id_sucursal = $id_sucursal
        ORDER BY a.almacen
    ")->fetch();
	// Envia respuesta
	echo json_encode($almacenes);
} else {
	// Error 404
	require_once not_found();
	exit;
}