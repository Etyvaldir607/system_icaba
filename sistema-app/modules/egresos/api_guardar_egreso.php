<?php
// Verifica si es una peticion
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (
        isset($_POST['almacen_id']) &&

        isset($_POST['tipo']) && 
		isset($_POST['descripcion']) && 
		isset($_POST['usuario']) && 
		isset($_POST['responsable']) &&

        isset($_POST['nro_registros']) && 
		isset($_POST['monto_total'])&&

		isset($_POST['arr_id_producto']) && 
		isset($_POST['arr_nombre_producto']) && 
        isset($_POST['arr_asignacion_id']) &&
        isset($_POST['arr_cantidad']) &&
        isset($_POST['arr_cantidad_unidad']) &&
		isset($_POST['arr_precio'])
	) {
        // Obtiene los datos en formato correcto
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // agregando validacion de stock actual del producto por unidad
		$arr_producto_id_val=(isset($_POST['arr_id_producto'])) ? $_POST['arr_id_producto'] : array();
		$arr_asignacion_id_val = (isset($_POST['arr_asignacion_id'])) ? $_POST['arr_asignacion_id']: array();
		$arr_cantidades_val = (isset($_POST['arr_cantidad'])) ? $_POST['arr_cantidad'] : array();
        $arr_cantidades_unidad_val=(isset($_POST['arr_cantidad_unidad'])) ? $_POST['arr_cantidad_unidad'] : array();
		$id_almacen_val = trim($_POST['almacen_id']);

        // @etysoft verificamos el stock actual, en caso de error de validacion retorna una respuesta
        $validar = validate_stock( $db, $arr_producto_id_val, $arr_asignacion_id_val, $arr_cantidades_unidad_val, $id_almacen_val);
        if($validar['control'] > 0){
            echo json_encode([
                "status" => 400, //status 201 creacion
                "title" => "¡Error de transacción!",
                "productos" => $validar['agotados'],
                "type" => "warning", //info danger
                "icon" => "glyphicon glyphicon-alert", //"glyphicon glyphicon-info-sign",
                "message" => 'El stock es insuficiente verifica y vuelve a intentarlo'
            ]);
            exit;
        }

        $control = $validar['control'];
		$agotados = $validar['agotados'];


        // pregunta si hay algun producto con stock limitado
		if ($control < 1){

            // Obtiene los datos de la venta
            // $id_egreso 			= isset($_POST['id_egreso']) ? trim($_POST['id_egreso']) : 0;
            $almacen_id 		= trim($_POST['almacen_id']);
            
            $tipo 				= trim($_POST['tipo']);
            $descripcion		= trim($_POST['descripcion']);
            $empleado 			= trim($_POST['usuario']);
            $responsable 		= trim($_POST['responsable']);
            $conductor 			= trim($_POST['conductor']);

            $nro_registros 		= trim($_POST['nro_registros']);
            $monto_total 		= trim($_POST['monto_total']);

            $arr_id_producto 		= (isset($_POST['arr_id_producto'])) ? $_POST['arr_id_producto']: array();
            $arr_nombre_producto	= (isset($_POST['arr_nombre_producto'])) ? $_POST['arr_nombre_producto']: array();
            $arr_asignacion_id  	= (isset($_POST['arr_asignacion_id'])) ? $_POST['arr_asignacion_id']: array();
            $arr_cantidad 		    = (isset($_POST['arr_cantidad'])) ? $_POST['arr_cantidad']: array();
            $arr_cantidad_unidad    = (isset($_POST['arr_cantidad_unidad'])) ? $_POST['arr_cantidad_unidad']: array();
            $arr_precio		        = (isset($_POST['arr_precio'])) ? $_POST['arr_precio']: array();


            // verifica si es un egreso de tipo 'Traspaso'
            if ($tipo == "Traspaso") {
                $almacen_destino_id = trim($_POST['almacen_destino_id']);
                $nombre_almacen_destino = $db->query("select almacen from inv_almacenes where id_almacen = $almacen_destino_id ")->fetch_first()['almacen'];

                // Instancia el egreso por traspaso
                $egreso = array(
                    'fecha_egreso' 		=> date('Y-m-d'),
                    'hora_egreso' 		=> date('H:i:s'),
                    'tipo' 				=> $tipo,
                    'provisionado' 		=> 'N',
                    'descripcion' 		=> $descripcion,
                    'nro_factura' 		=> 0,
                    'nro_autorizacion' 	=> 0,
                    'codigo_control' 	=> '',
                    'fecha_limite' 		=> '0000-00-00',
                    'monto_total' 		=> $monto_total,
                    'nombre_cliente' 	=> $nombre_almacen_destino,
                    'nit_ci' 			=> 0,
                    'plan_de_pagos'     => 'no',
                    'nro_registros' 	=> $nro_registros,
                    'dosificacion_id' 	=> 0,
                    'almacen_id' 		=> $almacen_id,
                    'empleado_id' 		=> $empleado,
                    'responsable_id' 	=> $responsable,
                    'conductor_id'		=> $conductor,
                    'cliente_id'		=> $almacen_destino_id 
                );

                // Guarda la informacion
                $egreso_id = $db->insert('inv_egresos', $egreso);
                
                // Recorre los productos
                foreach ($arr_id_producto as $nro => $elemento) {
                    // Forma el detalle
                    $detalle = array(
                        'cantidad' => $arr_cantidad[$nro],
                        'precio' => $arr_precio[$nro],
                        'asignacion_id' => $arr_asignacion_id [$nro],
                        'descuento' => 0,
                        'producto_id' => $arr_id_producto[$nro],
                        'egreso_id' => $egreso_id
                    );
                    // Guarda la informacion
                    $db->insert('inv_egresos_detalles', $detalle);
                }


                $ingreso = array(
                    'fecha_ingreso' 		=> date('Y-m-d'),
                    'hora_ingreso' 			=> date('H:i:s'),
                    'tipo' 					=> $tipo,
                    'descripcion' 			=> $descripcion,
                    'monto_total' 			=> $monto_total,
                    'nombre_proveedor' 		=> $almacen_id,
                    'proveedor_id'          => $egreso_id,
                    'nro_registros' 		=> $nro_registros,
                    'almacen_id' 			=> $almacen_destino_id,
                    'plan_de_pagos' 		=> 'no',
                    'empleado_id' 			=> $empleado,
                    'responsable_id'		=> $responsable,
                );

                // Guarda la informacion
                $ingreso_id = $db->insert('inv_ingresos', $ingreso);
                
                foreach ($arr_id_producto as $nro => $elemento) {
                    // Forma el detalle
                    $detalle = array(
                        'cantidad' => (isset($arr_cantidad[$nro])) ? $arr_cantidad[$nro]: 0,
                        'costo' => (isset($arr_precio[$nro])) ? $arr_precio[$nro]: 0,
                        'producto_id' => $arr_id_producto[$nro],
                        'asignacion_id' => $arr_asignacion_id [$nro],				
                        'ingreso_id' => $ingreso_id
                    );
                    // Guarda la informacion
                    $db->insert('inv_ingresos_detalles', $detalle);
                }

                /** existe un parametro recibido, pero sin visualizar - consultar funcionalidad */ 
                $des_reserva = trim($_POST['des_reserva']);
                if ($des_reserva) {
                    $egreso_id_transitorio = $egreso_id;
                    $reserva = 1;
                } else {
                    $egreso_id_transitorio = 0;
                    $reserva = 0;
                }
                $datos = array(
                    'des_transitorio'       => $des_reserva,
                    'egreso_id_transitorio' => $egreso_id_transitorio,
                    'transitorio'=>$reserva
                );
                // Guarda la informacion
                $condicion = array('id_ingreso' => $ingreso_id);
                $db->where($condicion)->update('inv_ingresos', $datos);	
                /** existe un parametro recibido, pero sin visualizar - consultar funcionalidad */ 
            

            }else{
                // Instancia el egreso por baja
                $egreso = array(
                    'fecha_egreso' 		=> date('Y-m-d'),
                    'hora_egreso' 		=> date('H:i:s'),
                    'tipo' 				=> $tipo,
                    'provisionado' 		=> 'N',
                    'descripcion' 		=> $descripcion,
                    'nro_factura' 		=> 0,
                    'nro_autorizacion' 	=> 0,
                    'codigo_control' 	=> '',
                    'fecha_limite' 		=> '0000-00-00',
                    'monto_total' 		=> $monto_total,
                    'nombre_cliente' 	=> '',
                    'nit_ci' 			=> 0,
                    'plan_de_pagos'     => 'no',
                    'nro_registros' 	=> $nro_registros,
                    'dosificacion_id' 	=> 0,
                    'almacen_id' 		=> $almacen_id,
                    'empleado_id' 		=> $empleado,
                    'responsable_id' 	=> $responsable,
                    'conductor_id'		=> $conductor,
                    'cliente_id'		=> 0 
                );

                // Guarda la informacion
                $egreso_id = $db->insert('inv_egresos', $egreso);
                
                // Recorre los productos
                foreach ($arr_id_producto as $nro => $elemento) {
                    // Forma el detalle
                    $detalle = array(
                        'cantidad' => $arr_cantidad[$nro],
                        'precio' => $arr_precio[$nro],
                        'asignacion_id' => $arr_asignacion_id [$nro],
                        'descuento' => 0,
                        'producto_id' => $arr_id_producto[$nro],
                        'egreso_id' => $egreso_id
                    );
                    // Guarda la informacion
                    $db->insert('inv_egresos_detalles', $detalle);
                }
            }
            echo json_encode([
                "status" => 201, //status 100 informativo
                "title" => "¡Exito!",
                "type" => "success",
                "icon" => "glyphicon glyphicon-ok",
                "messagge" => "Se ha realizado el registro del egreso correctamente."
            ]);
            exit;
        }
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
}  else {
    // Error 404
    require_once not_found();
    exit;
}




// @etysoft validar el stock para la venta
function validate_stock( $db, Array $productos, Array $asignacion, Array $sum_cantidades, $almacen_id ){
    // agregando validacion de stock actual del producto por unidad
    $productos_val= $productos;
    $cantidades_val = $sum_cantidades;
    $id_almacen_val = $almacen_id;
    $control = 0;
    $agotados= [];
    // Recorre los productos
    foreach ($productos_val as $nro => $elemento) {
        $id_producto_val = $productos_val[$nro];
        $stock_val = $db->query("
            SELECT
                p.id_producto,
                p.nombre,
                p.codigo,
                I.unidad,
                IFNULL((IFNULL(I.cantidad_ingresos, 0) - IFNULL(E.cantidad_egresos, 0)), 0 ) AS stock
            FROM
                inv_productos p
                LEFT JOIN (
                    SELECT
                        u.unidad,
                        d.producto_id,
                        SUM(d.cantidad * u.tamanio) AS cantidad_ingresos
                    FROM
                        inv_ingresos_detalles d
                        LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                        LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                    WHERE
                        i.almacen_id = $almacen_id 
                    GROUP BY
                        d.producto_id
                ) I ON I.producto_id = p.id_producto
                LEFT JOIN (
                    SELECT
                        d.producto_id,
                        SUM(d.cantidad * u.tamanio) as cantidad_egresos
                    FROM
                        inv_egresos_detalles d
                        LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id AND e.estado='V'
                        LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                    WHERE
                        e.almacen_id = $id_almacen_val
                    GROUP BY
                        d.producto_id
                ) E ON E.producto_id = p.id_producto
                WHERE
            p.id_producto = $id_producto_val		
        ")->fetch_first();

        if($stock_val['stock'] < $cantidades_val[$nro] ){
            $agotados[$control]= array(
                'codigo'=>$stock_val['codigo'],
                'stock'=>(int)($stock_val['stock']),
                'solicitado' => $cantidades_val[$nro],
                'unidad'=>$stock_val['unidad'],
                'nombre'=>$stock_val['nombre']
            );
            $control++;
        }
    }
    return array('control'=>$control, 'agotados' =>$agotados);
}
?>