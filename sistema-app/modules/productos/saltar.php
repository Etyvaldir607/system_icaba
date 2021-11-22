<?php

// Obtiene los parametros
$tipo = (isset($params[0])) ? $params[0] : 0;
$id_producto = (isset($params[1])) ? $params[1] : 0;

// Obtiene el producto
$producto =  $db->select('id_producto')
				->from('inv_productos')
				->where('id_producto', $id_producto)->fetch_first();

// Verifica si existen el producto
if ($producto) {
	// Verifica si es antes o despues
	if ($tipo == 'antes') {
		$id_productox = $db->query("	SELECT id_producto 
									from inv_productos 
									where nombre < (	SELECT nombre 
														FROM inv_productos 
														WHERE id_producto='$id_producto'
													)
									Order by nombre DESC
									")->fetch_first();

		if($id_productox){
			$id_producto = $id_productox['id_producto'];
		}
	} else {
		$id_productox = $db->query("	SELECT id_producto 
									from inv_productos 
									where nombre > (	SELECT nombre 
														FROM inv_productos 
														WHERE id_producto='$id_producto'
													)
									Order by nombre ASC
									")->fetch_first();

		if($id_productox){
			$id_producto = $id_productox['id_producto'];
		}
	}

	// Redirecciona la pagina
	redirect('?/productos/ver/' . $id_producto);
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>