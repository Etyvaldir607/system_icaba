<?php

if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        isset($_POST['almacen_id']) && 
        isset($_POST['empleado_id'])
    ) 
    {
        //obtiene los datos para la tabla almacen_empleados
        $almacen_id = $_POST['almacen_id'];
        $empleado_id = $_POST['empleado_id'];

        $empleado_almacen= $db->query("
            SELECT 
                count(ae.id) as existe,
                e.id_empleado
            FROM
                inv_almacen_empleados ae 
            JOIN sys_empleados e ON e.id_empleado = ae.empleado_id
            JOIN sys_users u ON u.persona_id = e.id_empleado
            JOIN sys_roles r ON r.id_rol = u.rol_id AND r.id_rol !=1 AND r.id_rol !=2
            WHERE
                ae.almacen_id = $almacen_id
        ")->fetch_first();
        $update = $empleado_almacen['existe'];
        $old_empleado_id = $empleado_almacen['id_empleado'];

        // Instancia el almacen_empleado
        $almacen_empleado = array(
            'almacen_id' => $almacen_id,
            'empleado_id' => $empleado_id
        );

        if($update){
            $condicion = array('empleado_id' => $old_empleado_id);
            // Actualiza la informacion
            $db->where($condicion)->update('inv_almacen_empleados', $almacen_empleado);
            // Envia respuesta
            echo json_encode([
                "status" => 201, //status 201 creacion
                "title" => "Update", 
                "type" => "info", 
                "messagge" => "Se actualizo correctamente el empleado"
            ]);
        }else{
            // Guarda la informacion e inserta
            $db->insert('inv_almacen_empleados', $almacen_empleado);
                // Envia respuesta
            echo json_encode([
                "status" => 200, //status 100 informativo
                "title" => "Exito", 
                "type" => "success", 
                "messagge" => "Se asigno el almacen correctamente"
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

