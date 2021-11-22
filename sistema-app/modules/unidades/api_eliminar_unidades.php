<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Obtiene el id_unidad
$id_unidad = (sizeof($params) > 0) ? $params[0] : 0;

// ejecuta la validación
$verifica = check_asignaments($db, $id_unidad);
// recupera unidad solo si no existen transacciones realizadas
$unidad = ($verifica) ? null : $db->from('inv_unidades')->where('id_unidad', $id_unidad)->fetch_first(); 

// Verifica si el unidad existe
if ($unidad) {
	// Elimina el unidad
	$db->delete()->from('inv_unidades')->where('id_unidad', $id_unidad)->limit(1)->execute();

	// Verifica si fue el unidad eliminado
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
            "messagge" => "No se puede realizar la operación, verifique su conexión."
        ]);
    }
} else {
	// Error 404
	echo json_encode([
        "status" => 400, //status 100 informativo
        "title" => "Error",
        "type" => "warning",
        "messagge" => "No se puede realizar la operación, verifique que no ha realizado asignaciones con esta unidad."
    ]);
	exit;
}

//@etysoft funcion validar movimientos mediante la peticion requerida
function check_asignaments($db, $id_unidad){
    // obtiene el resultado de la consulta
    $unidad = $db->query("
        SELECT
            u.id_unidad
        FROM inv_unidades u
        JOIN inv_asignaciones a ON a.unidad_id = u.id_unidad
        WHERE
            u.id_unidad = $id_unidad
        GROUP BY u.id_unidad
    ")->fetch_first();
    $existe =($unidad) ? true: false;
    return $existe;
}