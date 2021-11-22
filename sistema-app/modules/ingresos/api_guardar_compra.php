<?php
//echo json_encode($_POST);
//exit;

//check_costo_actual($db,$_user);


// Verifica si es una peticion post
if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        isset($_POST['almacen_id']) &&

        isset($_POST['des_transitorio']) &&
        isset($_POST['descripcion']) &&
        isset($_POST['monto_total_confirmation']) &&
        isset($_POST['nombre_proveedor']) &&
        isset($_POST['nro_registros']) &&
        isset($_POST['plan_de_pagos']) &&
        isset($_POST['proveedor_id']) &&
        isset($_POST['responsable_id']) &&

        isset($_POST['arr_producto_id']) &&
        isset($_POST['arr_asignacion_id']) &&
        isset($_POST['arr_cantidad']) &&
        isset($_POST['arr_costo'])
        
    ) {

    
        // obtiene los datos para la tabla ingresos
        $fecha_ingreso          = date('Y-m-d');
        $hora_ingreso           = date('H:i:s');
        $tipo                   = 'Compra';
        $descripcion            = trim($_POST['descripcion']);
        $monto_total            = trim($_POST['monto_total_confirmation']);
        $nombre_proveedor       = trim($_POST['nombre_proveedor']);
        $nro_registros          = trim($_POST['nro_registros']);
        $almacen_id             = trim($_POST['almacen_id']);
        $empleado_id            = $_user['persona_id']; // usuario sesion actual
        $plan_de_pagos          = trim($_POST['plan_de_pagos']);
        $responsable_id         = trim($_POST['responsable_id']); // para quien se realizo la compra
        $proveedor_id           = trim($_POST['proveedor_id']);
        $des_transitorio        = trim($_POST['des_transitorio']);
        $transitorio            = isset($_POST['transitorio']) ?  trim($_POST['transitorio']) : '0';
        $user_cambiotransitorio = 0;
        $egreso_id_transitorio  = 0;


         // Instancia el ingreso
        $ingreso = array(
            'fecha_ingreso'             => $fecha_ingreso,
            'hora_ingreso'              => $hora_ingreso,
            'tipo'                      => $tipo,
            'descripcion'               => $descripcion,
            'monto_total'               => $monto_total,
            'nombre_proveedor'          => $nombre_proveedor,
            'nro_registros'             => $nro_registros,
            'almacen_id'                => $almacen_id,
            'empleado_id'               => $empleado_id,
            'plan_de_pagos'             => $plan_de_pagos,
            'responsable_id'            => $responsable_id,
            'proveedor_id'              => $proveedor_id,
            'des_transitorio'           => $des_transitorio,
            'transitorio'               => $transitorio,
            'user_cambiotransitorio'    => $user_cambiotransitorio,
            'egreso_id_transitorio'     => $egreso_id_transitorio,
        );

         // Guarda la informacion del ingreso y obtiene el id_ingreso
        $id_ingreso = $db->insert('inv_ingresos', $ingreso);

         // obtiene los datos para la tabla ingresos_detalles
        $ingreso_id             = isset($id_ingreso) ?  $id_ingreso : 0;
        $arr_producto_id        = $_POST['arr_producto_id'] ;
        $arr_asignacion_id      = $_POST['arr_asignacion_id'] ;
        $arr_cantidad           = $_POST['arr_cantidad'] ;
        $arr_costo              = $_POST['arr_costo'] ;

        // recoore los productos del array para ingresos_detalles
        foreach ($arr_producto_id as $nro => $elemento) {
            // Forma el detalle
            $ingreso_detalle = array(
                'ingreso_id'    => $ingreso_id,
                'producto_id'   => $arr_producto_id[$nro],
                'asignacion_id' => $arr_asignacion_id[$nro],
                'cantidad'      => $arr_cantidad[$nro],
                'costo'         => $arr_costo[$nro], 
            );
            // Guarda la informacion
            $db->insert('inv_ingresos_detalles', $ingreso_detalle);
        }

        //verifica los precios de entrada y crea uno nuevo si el costo es diferente
        check_costo_actual($db,$_user);

        // actualizamos el costo actual de la asignacion del producto
        foreach ($arr_asignacion_id as $nro => $elemento) {

            //obtiene los datos para la actualizacion del costo actual de la asignacion del producto
            $id_asignacion           = $arr_asignacion_id[$nro];
            $costo_actual            =  $arr_costo[$nro];
            //instacia el costo actual para inv_asignaciones
            $asignacion = array('costo_actual' => $costo_actual );
            // actualiza el costo actual
            $db->where('id_asignacion', $id_asignacion)->update('inv_asignaciones', $asignacion);
        }

        //si solo el plan de pagos es si
        if(
            $plan_de_pagos == 'si' &&
            isset($_POST['arr_nro_cuota']) &&
            isset($_POST['arr_fecha_pago']) &&
            isset($_POST['arr_monto'])
        )
        {
            // obtiene los datos para la tabla ingresos
            $movimiento_id  = $ingreso_id;
            $interes_pago   = 0;
            $tipo           = 'Ingreso';
            // Instancia el pago
            $pago = array(
                'movimiento_id' => $movimiento_id,
                'interes_pago'  => $interes_pago,
                'tipo'          => $tipo,
            );
            $id_pago = $db->insert('inv_pagos', $pago);

            //obtiene los datos para la tabla pagos detalles
            $pago_id            = isset($id_pago)?  $id_pago : 0;
            $fecha              = date('Y-m-d');
            $arr_nro_cuota      = $_POST['arr_nro_cuota'] ;
            $arr_fecha_pago     = $_POST['arr_fecha_pago'] ;
            $arr_monto          = $_POST['arr_monto'] ;
            $estado             = 1;
            $tipo_pago          = 'Efectivo';
            $empleado_id        = $empleado_id;
            // recoore los productos del array para ingresos_detalles
            foreach ($arr_nro_cuota as $nro => $elemento) {
                // Forma el detalle
                $pago_detalle = array(
                    'pago_id'       => $id_pago,
                    'fecha'         => $fecha,
                    'nro_cuota'     => $arr_nro_cuota[$nro],
                    'fecha_pago'    => $arr_fecha_pago[$nro],
                    'monto'         => $arr_monto[$nro],
                    'estado'        => $estado, 
                    'tipo_pago'     => $tipo_pago, 
                    'empleado_id'   => $empleado_id, 
                );
                // Guarda la informacion
                $db->insert('inv_pagos_detalles', $pago_detalle);
            }
            // Envia respuesta
            echo json_encode([
                "status" => 201, //status 201 creacion
                "title" => "Exito !",
                "type" => "success", //info  warning
                "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
                "messagge" => "Los datos de la compra y planes de pagos se han registrado correctamente"
            ]);
            exit;

        }

        // Envia respuesta
        echo json_encode([
            "status" => 201, //status 201 creacion
            "title" => "Exito !",
            "type" => "success", //info  warning
            "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
            "messagge" => "Los datos de la compra se han registrado correctamente"
        ]);

    
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








function check_costo_actual($db,$_user)
{	
    $arr_asignacion_id      = $_POST['arr_asignacion_id'] ;
    $arr_costo              = $_POST['arr_costo'] ;

    $str_id_asignacion =	implode(',',$arr_asignacion_id);

    //fusionamos las asignaciones_id con los costos de cada uno
    foreach ($arr_asignacion_id as $nro => $elemento) {

        $costos_post [] = array(
			'id_asignacion' => $arr_asignacion_id[$nro],
			'costo' => $arr_costo[$nro],
		);
    }
    //obtiene los productos con el stock actual
	$costos_actuales = $db->query("
        select
            a.id_asignacion,
            a.producto_id,
            a.costo_actual
        from
            inv_asignaciones a
        where
            a.id_asignacion in ($str_id_asignacion)
    ")->fetch();

    foreach ($costos_actuales as $i => $elemento) {

		foreach ($costos_post as $j => $el) {
			// verifica el id asignacion sea igual al del id asignacion enviado
			if($costos_actuales[$i]['id_asignacion'] == $costos_post[$j]['id_asignacion'] )
			{	//si el costo actual es distinto se crea un nnuevo precio entrada
				if( $costos_actuales[$i]['costo_actual'] !== $costos_post[$j]['costo'] ){
                    
					//instacia de precio
                    $precio_entrada = array(
                        'costo' 		=> $costos_post[$j]['costo'],
                        'fecha_registro'=> date('Y-m-d'),
                        'hora_registro' => date('H:i:s'),
                        'asignacion_id' => $costos_post[$j]['id_asignacion'],
                        'empleado_id' 	=> $_user['persona_id'], // usuario sesion actual
                    );
                    // Crea el precio entrada
                    $db->insert('inv_precios_entrada', $precio_entrada);
				}
			}
		}
	}

    //echo json_encode($costos_actuales);
    //exit;

}
