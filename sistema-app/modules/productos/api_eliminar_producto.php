<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Obtiene el id_producto
$id_producto = (sizeof($params) > 0) ? $params[0] : 0;

// ejecuta la validación
$verifica =  check_transaction($db, $id_producto);
// recupera producto solo si no existen transacciones realizadas
$producto = ($verifica) ? null : $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first(); 

// Verifica si el producto existe
if ($producto) {
    // Elimina las asignaciones del producto
    delete_asignaments($db, $id_producto);
	// Elimina el producto
	$db->delete()->from('inv_productos')->where('id_producto', $id_producto)->limit(1)->execute();

	// Verifica si fue el producto eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		echo json_encode([
            "status" => 200, //status 100 informativo
            "title" => "Exito",
            "type" => "success",
            "messagge" => "La eliminacion se ha realizado correctamente"
        ]);
	}else {
        // Envia respuesta
        echo json_encode([
            "status" => 500, //status 100 informativo
            "title" => "Error",
            "type" => "danger",
            "messagge" => "No se puede realizar la operación, verifique que no ha realizado transacciones con este producto."
        ]);
    }
} else {
	// Error 404
	echo json_encode([
        "status" => 400, //status 100 informativo
        "title" => "Error",
        "type" => "warning",
        "messagge" => "No se puede realizar la operación, verifique que no ha realizado transacciones con este producto."
    ]);
	exit;
}

//@etysoft funcion validar movimientos mediante la peticion requerida
function check_transaction($db, $id_producto){
    // obtiene el resultado de la consulta
    $producto_en_ingresos = $db->query("
        SELECT
            p.id_producto
        FROM inv_productos p
        JOIN inv_ingresos_detalles di ON di.producto_id = p.id_producto
        WHERE
            p.id_producto = $id_producto
        GROUP BY
            p.id_producto
    ")->fetch_first();

    $producto_en_egresos = $db->query("
        SELECT
            p.id_producto
        FROM inv_productos p
        JOIN inv_egresos_detalles de ON de.producto_id = p.id_producto
        WHERE
            p.id_producto = $id_producto
        GROUP BY
            p.id_producto
    ")->fetch_first();

    $producto_en_proformas = $db->query("
        SELECT
            p.id_producto
        FROM inv_productos p
        JOIN inv_proformas_detalles dp ON dp.producto_id = p.id_producto
        WHERE
            p.id_producto = $id_producto
        GROUP BY
            p.id_producto
    ")->fetch_first();

    $existe =($producto_en_ingresos || $producto_en_egresos || $producto_en_proformas) ? true: false;
    return $existe;
}

//@etysoft funcion eliminar asignaciones mediante la peticion requerida
function delete_asignaments($db, $id_producto){
    // obtiene el resultado de la consulta
    $arr_asignaciones = $db->query("
        SELECT
            a.id_asignacion
        FROM inv_productos p
        JOIN inv_asignaciones a ON a.producto_id = p.id_producto
        WHERE
            p.id_producto = $id_producto
    ")->fetch();
    foreach ($arr_asignaciones as $key => $asignacion) {
        $db->delete()->from('inv_asignaciones')->where('id_asignacion', $asignacion['id_asignacion'])->limit(1)->execute();
    }
}