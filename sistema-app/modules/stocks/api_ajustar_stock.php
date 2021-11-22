<?php
if (is_post()) {
    if (
        isset($_POST['id_almacen']) &&
        isset($_POST['id_producto']) &&
        isset($_POST['stock_actual']) &&
        isset($_POST['stock_nuevo'])
    ) {
        // Obtiene los parametros
        $id_almacen 	= (isset($_POST['id_almacen'])) ? $_POST['id_almacen'] : 0;
        $id_producto 	= (isset($_POST['id_producto'])) ? $_POST['id_producto'] : 0;
        $stock_actual 	= $_POST['stock_actual'];
        $stock_nuevo 	= $_POST['stock_nuevo']; 

        // Obtiene la asignación
        $id_asignacion = $db->query("SELECT id_asignacion FROM inv_asignaciones WHERE tipo = 'principal' AND producto_id = $id_producto")->fetch_first()["id_asignacion"];

        if ( $stock_nuevo-$stock_actual != 0 ){
            if ($stock_nuevo > $stock_actual){
                $cantidad = $stock_nuevo-$stock_actual;
                $ingreso_id = generate_ingreso($id_almacen, $_user);
                generate_detalle_ingreso($cantidad, $id_asignacion, $id_producto, $ingreso_id, $id_almacen);
            } else {
                $cantidad = $stock_actual-$stock_nuevo;
                $egreso_id = generate_egreso($id_almacen, $_user);
                generate_detalle_egreso($cantidad, $id_asignacion, $id_producto, $egreso_id, $id_almacen);
            }

            // Envia respuesta
            echo json_encode([
                "status" => 201, //status 201 creacion
                "title" => "¡Ajuste exitoso!",
                "type" => "success", //info  warning
                "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
                "message" => "Se ha creado un registro temporal, para confirmar (Click en Restablecer todos los Almacenes)."
            ]);

        } else {
            // Envia respuesta
            echo json_encode([
                "status" => 400, //status 201 creacion
                "title" => "¡Ajuste exitoso!",
                "type" => "warning", //info  warning
                "icon" => "glyphicon glyphicon-info", //"glyphicon glyphicon-info-sign",
                "message" => "Se ha creado un registro temporal, para confirmar (Click en Restablecer todos los Almacenes)."
            ]);
        }

    }else{
        // Error 400
        require_once bad_request();
        exit; 
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}


function generate_ingreso($id_almacen){
    global $db, $_user;          
    $ingreso = array(
        'fecha_ingreso' 	=> date('Y-m-d'),
        'hora_ingreso' 		=> date('H:i:s'),
        'tipo' 				=> 'Ajuste',
        'descripcion' 		=> 'Ingreso por ajuste de inventario',
        'monto_total' 		=> 0,
        
        'nombre_proveedor' 	=> '',
        'nro_registros' 	=> 1,
        'almacen_id' 		=> $id_almacen,
        'plan_de_pagos' 	=> 'no',
        'empleado_id' 		=> $_user['persona_id'],
        
        'responsable_id'	=> $_user['persona_id'],
        'proveedor_id' 		=> 0
    );

    // Guarda la informacion
    $ingreso_id = $db->insert('inv_ingresos', $ingreso);

    return $ingreso_id;
}

function generate_detalle_ingreso($cantidad, $id_asignacion, $id_producto, $ingreso_id, $id_almacen){
    global $db; 
    $detalle = array(
        'cantidad' 		=> $cantidad,
        'costo' 		=> 0,
        'asignacion_id' => $id_asignacion,
        'producto_id' 	=> $id_producto,
        'ingreso_id' 	=> $ingreso_id
    );

    // Guarda la informacion
    $db->insert('inv_ingresos_detalles', $detalle);
    
    $ajustar = array(
        'producto_id'   => $id_producto,
        'almacen_id'    => $id_almacen
    );

    $db->insert('tmp_ajustar', $ajustar);
}



function generate_egreso($id_almacen){
    global $db, $_user; 
    // var_dump($stock_actual.' - '.$stock_nuevo.' = '.$cantidad);die();
    $egreso = array(
        'fecha_egreso' 		=> date('Y-m-d'),
        'hora_egreso' 		=> date('H:i:s'),
        'tipo' 				=> 'Ajuste',
        'provisionado' 		=> 'N',
        'descripcion' 		=> 'Egreso por ajuste de inventario',
        
        'nro_factura' 		=> 0,
        'nro_autorizacion' 	=> 0,
        'codigo_control' 	=> '',
        'fecha_limite' 		=> '0000-00-00',
        'monto_total' 		=> 0,
        
        'nombre_cliente' 	=> '',
        'nit_ci' 			=> 0,
        'nro_registros' 	=> 1,
        'dosificacion_id' 	=> 0,
        'almacen_id' 		=> $id_almacen,
        
        'empleado_id' 		=> $_user['persona_id'],
        'plan_de_pagos' 	=> 'no',
        'telefono' 			=> '',
        'direccion' 		=> '',
        'observacion' 		=> '',
        
        'descuento' 		=> 0,
        'estado' 			=> 'V',
        'tipo_de_pago' 		=> '',
        'sucursal_id' 		=> '0',
        'responsable_id' 	=> $_user['persona_id'],
        
        'conductor_id'		=> 0,
        'cliente_id'		=> 0 
    );

    // Guarda la informacion
    $egreso_id = $db->insert('inv_egresos', $egreso);

    return $egreso_id;
}

function generate_detalle_egreso($cantidad, $id_asignacion, $id_producto, $egreso_id, $id_almacen){
    global $db; 
    $detalle = array(
        'cantidad' 		=> $cantidad,
        'precio' 		=> 0,
        'asignacion_id' => $id_asignacion,
        'descuento' 	=> 0,
        'producto_id' 	=> $id_producto,
        'egreso_id' 	=> $egreso_id
    );

    // Guarda la informacion
    $db->insert('inv_egresos_detalles', $detalle);
    
    $ajustar = array(
        'producto_id'   =>$id_producto,
        'almacen_id'    => $id_almacen
    );
    $db->insert('tmp_ajustar', $ajustar);

}