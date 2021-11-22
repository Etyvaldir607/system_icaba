<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Obtiene la sucursal actual, en caso de que el servicio se requiera para actualizar
$id_almacen = (sizeof($params) > 0) ? $params[0] : 0;
// Verifica si es una peticion ajax
if (is_ajax()) {
    // Obtiene los alamacenes que no tienen asignación
	$almacenes = $db->query("
        SELECT
            id_almacen,
            almacen
        FROM
            inv_almacenes
        WHERE
            id_almacen != $id_almacen
            ORDER BY almacen
    ")->fetch();
	// Envia respuesta
	echo json_encode($almacenes);
} else {
	// Error 404
	require_once not_found();
	exit;
}