<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (true) {
		// Obtiene los parametros
		$producto_id = (isset($params[0])) ? $params[0] : 0;
		// Obtiene el producto
		$asignacionn = $db->select('producto_id')
                          ->from('inv_asignaciones')
                          ->where('producto_id', $producto_id)
                          ->fetch_first();
                          
        if($asignacionn){
            $tipoo="secundario";
        }
        else{
            $tipoo="principal";
        }

			// Verifica la existencia de datos
			if (isset($_POST['unidad_id']) && isset($_POST['precio'])) {
                // Obtiene los datos
                $unidad_id = clear($_POST['unidad_id']);
                $precio = clear($_POST['precio']);
                $observacion = clear($_POST['observacion']);
                $precio = (is_numeric($precio)) ? $precio : 0;

                if(empty($_POST['id_asignacion'])){
                    //cuando no exista la asignacion
                    $asigna = array(
                        'producto_id' => $producto_id,
                        'unidad_id' => $unidad_id,
                        'precio_actual' => $precio,
                        'tipo'=>$tipoo,
                        'costo_actual' => "0",
                        'tipo_entrada'=>"principal"

                    );

                    // Obtiene la asignacion
                    $id_asignacion = $db->insert('inv_asignaciones', $asigna);

                    //instacia de precio
                    $instancia_precio = array(
                        'precio' => $precio,
                        'fecha_registro' => date('Y-m-d'),
                        'hora_registro' => date('H:i:s'),
                        'asignacion_id' => $id_asignacion,
                        'empleado_id' => $_user['persona_id']
                    );

                    // Crea el precio
                    $id_precio = $db->insert('inv_precios', $instancia_precio);

                    if($id_precio){
                        //mensaje
                        $_SESSION[temporary] = array(
                            'alert' => 'success',
                            'title' => 'Adición satisfactoria!',
                            'message' => 'El registro se guardó correctamente.'
                        );

                        // Redirecciona la pagina
                        redirect('?/precios/listar');
                    }
                }else{
                    //cuando si exista la asignacion
                    $id = $_POST['id_asignacion'];
                    $consulta = $db->query("SELECT *
                                            FROM inv_asignaciones  
                                            WHERE id_asignacion = $id")->fetch_first();
                    if(!empty($consulta)){
                        //echo 1;
                        $id_asignacion = $consulta['id_asignacion'];

                        //instacia de precio
                        $instancia_precio = array(
                            'precio' => $precio,
                            'fecha_registro' => date('Y-m-d'),
                            'hora_registro' => date('H:i:s'),
                            'asignacion_id' => $id_asignacion,
                            'empleado_id' => $_user['persona_id']
                        );

                        // Crea el precio
                        $id_precio = $db->insert('inv_precios', $instancia_precio);

                        $sonsulta_actualizacion = $db->query("UPDATE inv_asignaciones
                                                            SET precio_actual = $precio
                                                            WHERE id_asignacion = $id_asignacion")->execute();;

                        //mensaje
                        $_SESSION[temporary] = array(
                            'alert' => 'success',
                            'title' => 'Adición satisfactoria!',
                            'message' => 'El registro se guardó correctamente.'
                        );

                        // Redirecciona la pagina
                        redirect('?/precios/listar');

                    }else{
                        //echo 2;
                        //mensaje
                        $_SESSION[temporary] = array(
                            'alert' => 'danger',
                            'title' => 'Eliminación fallida!',
                            'message' => 'El registro no fue eliminado.'
                        );

                        // Redirecciona la pagina
                        redirect('?/precios/listar');
                    }                    
                }
                
			} else {
				// Error 400
				require_once bad_request();
				exit;
			}
		
	} else {
		// Redirecciona la pagina
		redirect(back());
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>