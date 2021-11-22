<?php
// Verifica la peticion post

if (is_post()) {
	// Verifica la existencia de datos
	
	if (isset($_POST['nombre_cliente']) && isset($_POST['nit_ci']) && isset($_POST['telefono']) && isset($_POST['direccion'])) {
		// Obtiene los datos
		$id_cliente 	= (isset($_POST['id_cliente'])) ? clear($_POST['id_cliente']) : 0;
		$nombre_cliente = clear($_POST['nombre_cliente']);
		$nit_ci 		= clear($_POST['nit_ci']);
		$telefono 		= clear($_POST['telefono']);
		$direccion 		= clear($_POST['direccion']);
		$categoria_id	= trim($_POST['categoria_id']);
		$crear	        = clear($_POST['crear']);
		$id_sucursal    = clear($_POST['id_sucursal']);
		
// 		if(!isset($categoria_id)){
// 		    $categoria_id = 1;
// 		}
		
		// Instancia el cliente
		$cliente = array(
			'nombre_cliente'=> $nombre_cliente,
			'nit_ci' 		=> $nit_ci,
			'telefono' 		=> $telefono,
			'escalafon' 	=> $direccion,
			'categoria_cliente_id' => $categoria_id
		);
		
		// Verifica si es creacion o modificacion
		if ($id_cliente > 0) {
			// Modifica el cliente
			$db->where('id_cliente', $id_cliente)->update('inv_clientes', $cliente);
			
			// Crea la notificacion
			//set_notification('success', 'Modificación exitosa!', 'El registro se modificó satisfactoriamente.');
			
			// Redirecciona la pagina
		    
		    if($crear == 'notas'){
			    redirect('?/notas/crear'.$id_sucursal);
		    }
		    if($crear == 'electronicas'){
			    redirect('?/electronicas/crear'.$id_sucursal);
		    }
		    if($crear == 'manuales'){
			    redirect('?/manuales/crear'.$id_sucursal);
		    }
		    if($crear == 'proformas'){
			    redirect('?/proformas/crear'.$id_sucursal);
		    }
		    if($crear==''){
		        redirect('?/clientes/listar'.$id_sucursal);
		    }
		} else {
			// Crea el cliente
			$id_cliente = $db->insert('inv_clientes', $cliente);
            // var_dump($id_cliente);die();
			// Crea la notificacion
			//set_notification('success', 'Creación exitosa!', 'El registro se creó satisfactoriamente.');
			
			// Redirecciona la pagina
			if($crear == 'notas'){
			    redirect('?/notas/crear/'.$id_sucursal);
		    }
		    if($crear == 'electronicas'){
			    redirect('?/electronicas/crear'.$id_sucursal);
		    }
		    if($crear == 'manuales'){
			    redirect('?/manuales/crear'.$id_sucursal);
		    }
		    if($crear == 'proformas'){
			    redirect('?/proformas/crear'.$id_sucursal);
		    }
		    if($crear==''){
		        redirect('?/clientes/listar'.$id_sucursal);
		    }
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