<?php
// Verifica si es una peticion ajax
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_rol']) ) {
		$id_rol = trim($_POST['id_rol']);
		

		$unidades = $db->query("SELECT *
						FROM inv_unidades
						ORDER BY tamanio
						")->fetch();

		foreach ($unidades as $nro => $unidad) { 
			
			$incremento = $db->query("SELECT *
									FROM inv_precios_roles
									WHERE unidad_id='".$unidad['id_unidad']."' AND rol_id='".$id_rol."'
									")->fetch_first();

			
			$idx=$unidad['id_unidad'];
			$idx=$_POST['incremento_'.$idx];
			$increm=(isset($idx) )? $idx : 0;

			if($incremento){
				$db->where('id_precio_rol', $incremento['id_precio_rol'])
				   ->update('inv_precios_roles', array('incremento' => $increm));
			}
			else{
				// Instancia el producto
				$datos = array(
					'rol_id' => $id_rol,
					'unidad_id' => $unidad['id_unidad'],
					'incremento' => $increm
				);		
				// Guarda la informacion
				$db->insert('inv_precios_roles', $datos);
			}						
		}

		// Redirecciona a la pagina principal
		redirect('?/incremento-precios/listar');
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>