<?php

//echo json_encode($_POST);
//exit;

if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        isset($_POST['asignacion_id']) &&
        isset($_POST['nuevo_precio'])
    ) {
        //obtiene los datos para la tabla almacen_empleados
        $asignacion_id = $_POST['asignacion_id'];
        $nuevo_precio = $_POST['nuevo_precio'];

        $asignacion = $db->query("select a.* from inv_asignaciones a where a.id_asignacion = $asignacion_id")->fetch_first();
        //echo json_encode($asignacion);
        //exit;

        // Instancia el almacen_empleado
        $update_asignacion = array(
            'precio_actual' => $nuevo_precio
        );


        // Actualiza la informacion
        $db->where('id_asignacion', $asignacion_id)->update('inv_asignaciones', $update_asignacion);
        
        $producto_id = intval($asignacion['producto_id']);
        $precio = array(
            'precio' => $nuevo_precio,
            'fecha_registro' => date('Y-m-d'),
            'hora_registro' => date('H:i:s'),
            'producto_id' => $producto_id,
            'empleado_id' => $_user['persona_id'],
            'asignacion_id' => $asignacion_id,
        );

        $db->insert('inv_precios', $precio);

        // Envia respuesta
        echo json_encode([
            "status" => 200, //status 201 creacion
            "title" => "Update",
            "type" => "info",
            "messagge" => "Se actualizo correctamente el precio de la unidad"
        ]);
    } else {
        // Error 401
        echo 'error';
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
