<?php

//echo json_encode($_POST);
//exit;

if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        isset($_POST['precio_actual']) &&
        isset($_POST['producto_id']) &&
        isset($_POST['unidad_id'])
    ) {
        //obtiene los datos para la tabla almacen_empleados
        $producto_id = $_POST['producto_id'];
        $unidad_id = $_POST['unidad_id'];
        $precio_actual = $_POST['precio_actual'];
        $tipo = 'secundario';
        $costo_actual = $_POST['precio_actual'];
        $tipo_entrada = 'principal';
        $visible = 's';
        // Instancia el almacen_empleado
        $asignacion = array(
            'producto_id' => $producto_id,
            'unidad_id' => $unidad_id,
            'precio_actual' => $precio_actual,
            'tipo' => $tipo,
            'costo_actual' => $costo_actual,
            'tipo_entrada' => $tipo_entrada,
            'visible' => $visible,
        );
        // Guarda la informacion e inserta
        $asignacion_id = $db->insert('inv_asignaciones', $asignacion);

        $precio = array(
            'precio' => $precio_actual,
            'fecha_registro' => date('Y-m-d'),
            'hora_registro' => date('H:i:s'),
            'producto_id' => $producto_id,
            'empleado_id' => $_user['persona_id'],
            'asignacion_id' => $asignacion_id,

        );
        $succes = $db->insert('inv_precios', $precio);

        if ($succes) {
            // Envia respuesta
            echo json_encode([
                "status" => 200, //status 100 informativo
                "title" => "Exito",
                "type" => "success",
                "messagge" => "Se asigno la unidad correctamente"
            ]);
        } else {
            // Envia respuesta
            echo json_encode([
                "status" => 500, //status 100 informativo
                "title" => "Error",
                "type" => "danger",
                "messagge" => "Ocurrio un problema en la transaccion verifica si los datos se guardaron parcialmente"
            ]);
        }
    } else {
        // Error 401
        echo 'error';
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
