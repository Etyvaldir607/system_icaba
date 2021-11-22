<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Instancia el objeto
	
	// Obtiene los productos
	$unidades = $db->query("
        select u.id_unidad,u.unidad, u.tamanio
            from inv_unidades u 
            where u.tamanio > 1
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
