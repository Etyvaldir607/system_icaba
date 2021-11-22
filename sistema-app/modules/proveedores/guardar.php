<?php
// Verifica la peticion post
if (is_post()) {
	// Verifica la existencia de datos
	if (isset($_POST['nombre_proveedor']) ) {
		// Obtiene los datos
		$id_proveedor 	= (isset($_POST['id_proveedor'])) ? clear($_POST['id_proveedor']) : 0;
		$nombre_proveedor = clear($_POST['nombre_proveedor']);
		
		// Instancia el cliente
		$proveedor = array(
			'nombre_proveedor'=> $nombre_proveedor,			
		);
		
		// Verifica si es creacion o modificacion
		if ($id_proveedor > 0) {
			// Modifica el cliente
			$db->where('id_proveedor', $id_proveedor)->update('inv_proveedores', $proveedor);
			
			// Crea la notificacion
			//set_notification('success', 'Modificación exitosa!', 'El registro se modificó satisfactoriamente.');
			
			// Redirecciona la pagina
			redirect('?/proveedores/listar');
		} else {
			// Crea el cliente
			$id_proveedor = $db->insert('inv_proveedores', $proveedor);

			// Crea la notificacion
			//set_notification('success', 'Creación exitosa!', 'El registro se creó satisfactoriamente.');
			
			// Redirecciona la pagina
			redirect('?/proveedores/listar');
		}
	} else {
		// Error 400
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}
?>