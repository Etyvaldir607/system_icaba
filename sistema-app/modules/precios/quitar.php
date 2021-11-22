<?php
// Obtiene los parametros
$id_asignacion = (isset($params[0])) ? $params[0] : 0;

// Obtiene la asignacion
$asignacion = $db->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->fetch_first();
// Verifica si la asignacion existe
if ($asignacion) {

	$egreso = $db->from('inv_egresos_detalles')
				 ->where('asignacion_id', $id_asignacion)
				 ->fetch_first();

	$ingreso = $db->from('inv_egresos_detalles')
				  ->where('asignacion_id', $id_asignacion)
				  ->fetch_first();

	if (!($egreso || $ingreso)){
		
		// Elimina la asignacion
		$db->delete()->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->execute();
		
		// Verifica la eliminacion
		if ($db->affected_rows) {
			// Elimina los dependientes
			$db->delete()->from('inv_precios')->where('asignacion_id', $id_asignacion)->execute();

			// Guarda el proceso
			/*$db->insert('sys_procesos', array(
				'fecha_proceso' => date('Y-m-d H:i:s'),
				'proceso' => 'd',
				'nivel' => 'h',
				'detalle' => 'Se eliminó la unidad con identificador número ' . $asignacion['unidad_id'] . ' del producto con identificador número ' . $asignacion['producto_id'] . '.',
				'direccion' => $_location,
				'usuario_id' => $_user['id_user']
			));*/
			
			//crea la notificacion
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Eliminación exitosa!',
                'message' => 'El registro fue eliminado satisfactoriamente.'
            );
			// Redirecciona la pagina
            redirect('?/precios/listar');
		} else {
			//crea la notificacion
            $_SESSION[temporary] = array(
                'alert' => 'danger',
                'title' => 'Eliminación fallida!',
                'message' => 'El registro no fue eliminado.'
            );
			// Redirecciona la pagina
            redirect('?/precios/listar');
		}
	}
	else{
		$db->where('id_asignacion', $id_asignacion)->update('inv_asignaciones', array('visible' => "n"));
		//crea la notificacion

        $_SESSION[temporary] = array(
            'alert' => 'success',
            'title' => 'Eliminación exitosa!',
            'message' => 'El registro fue eliminado satisfactoriamente.'
        );
		// Redirecciona la pagina
        redirect('?/precios/listar');				
	}
	redirect(back());
} else {
	// Error 400
	require_once bad_request();
	exit;
}
?>