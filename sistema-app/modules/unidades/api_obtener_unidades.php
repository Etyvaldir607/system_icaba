<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Obtiene loas unidades registradas
	$unidades = $db->query("
		select
			u.*
		from
			inv_unidades u
		order by u.unidad
	")->fetch();
		
	// Envia respuesta
	echo json_encode([
		'unidades'   => $unidades,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}