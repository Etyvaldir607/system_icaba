<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_egreso']) && isset($_POST['estado'])) {
		// Obtiene los datos del producto
		$id_egreso = trim($_POST['id_egreso']);
		$estado = trim($_POST['estado']);

		// Actualiza la informacion
		$db->where('id_egreso', $id_egreso)->update('inv_egresos', array('estado' => $estado));

		// Instancia el egreso
		$egreso = array(
			'id_egreso' => $id_egreso,
			'estado_anterior' => ($estado == 'A') ? 'V' : 'A',
			'estado_posterior' => $estado
		);
		
		// Envia respuesta
		echo json_encode($egreso);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>