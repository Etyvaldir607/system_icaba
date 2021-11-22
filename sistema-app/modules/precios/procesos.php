<?php

	$boton = $_POST['boton'];

	if($boton == "listar_unidades"){
		$producto_id = $_POST['producto_id'];
		//var_dump($producto_id);die;
		$unidades = $db->query("SELECT u.id_unidad, u.unidad 
								FROM inv_unidades u 
								WHERE u.id_unidad NOT IN (SELECT a.unidad_id 
														  FROM inv_asignaciones a 
														  WHERE a.producto_id = $producto_id AND a.visible='s')")->fetch();
		echo json_encode($unidades);
	}

	if($boton == "buscar_unidad"){
		$asignacion_id = $_POST['asignacion_id'];
		$asignacion = $db->query("SELECT u.id_unidad, u.unidad
								FROM inv_asignaciones a
								INNER JOIN inv_unidades u  ON u.id_unidad = a.unidad_id
								WHERE id_asignacion = $asignacion_id AND a.visible='s' ")->fetch_first();
		/*$unidad_id = $asignacion['unidad_id'];

		$unidad = $db->query("SELECT *
								FROM inv_unidades 
								WHERE id_unidad = $unidad_id")->fetch_first();*/
		echo json_encode($asignacion);
	}
?>