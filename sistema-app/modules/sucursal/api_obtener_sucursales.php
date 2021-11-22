<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Obtiene los sucursales
	$sucursales = $db->query("
		SELECT
			s.*,
			IFNULL(a.almacen, '') AS almacen
		FROM
			inv_sucursal s
		LEFT JOIN inv_almacen_sucursales asu ON asu.sucursal_id = s.id_sucursal 
		LEFT JOIN  inv_almacenes a ON a.id_almacen = asu.almacen_id
		GROUP BY s.id_sucursal
		ORDER BY s.sucursal
	")->fetch();
		
	// Envia respuesta
	echo json_encode([
		'sucursales'   => $sucursales,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}
