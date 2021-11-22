<?php
/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax
if (is_post()) {	
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_egreso']) && isset($_POST['nit_ci']) && isset($_POST['nombre_cliente'])) {
		// Obtiene los datos del producto
		$id_egreso = trim($_POST['id_egreso']);
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$telefono 		= trim($_POST['telefono']);
		$direccion 		= trim($_POST['direccion']);
		
		// Obtiene la venta modificada
		$venta = $db->from('inv_egresos')
					->where('id_egreso', $id_egreso)
					->fetch_first();

		// Verifica si existe la venta
		if ($venta) {
			// Instancia la venta
			$venta = array(
				'nit_ci' => $nit_ci,
				'telefono'=> $telefono,
				'direccion'	=> $direccion,
				'nombre_cliente' => strtoupper($nombre_cliente)
			);
			
			// Actualiza la informacion
			$db->where('id_egreso', $id_egreso)->update('inv_egresos', $venta);

			$res_client = $db->query("  SELECT id_cliente 
										FROM inv_clientes 
										WHERE 	nit_ci='$nit_ci' AND 
												telefono='$telefono' AND 
												escalafon='$direccion' AND 
												nombre_cliente='$nombre_cliente'")
							->fetch();

			if (!$res_client){
				$client = array(
					'nit_ci' 			=> $nit_ci,
					'telefono' 			=> $telefono,
					'escalafon' 		=> $direccion,
					'nombre_cliente' 	=> mb_strtoupper($nombre_cliente, 'UTF-8')
				);
				$db->insert('inv_clientes', $client);
			}

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);

			// Redirecciona a la pagina principal
			redirect('?/notas/ver/' . $id_egreso);
		} else {
			// Error 404
			require_once not_found();
			exit;
		}
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