<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (
		isset($_POST['id_sucursal']) && 
		isset($_POST['sucursal']) && $_POST['sucursal'] != null &&
		isset($_POST['direccion']) && $_POST['direccion'] != null &&
		isset($_POST['telefono']) && 
		isset($_POST['almacen']) && $_POST['almacen'] != null &&
		isset($_POST['descripcion'])
	) {
		// Obtiene los datos del almacén
		$id_sucursal = trim($_POST['id_sucursal']);
		$sucursal = trim($_POST['sucursal']);
		$direccion = trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);
		$almacen = trim($_POST['almacen']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia el almacén
		$sucursal = array(
			'sucursal' => $sucursal,
			'direccion' => $direccion,
			'telefono' => $telefono,
			'descripcion' => $descripcion
		);

		// Guarda la informacion
		$id_sucursal = $db->insert('inv_sucursal', $sucursal);
		add_new_asignament($db, $id_sucursal, $almacen);
		// Instancia la variable de notificacion
		$respuesta = array(
			'status' => 200,
			'alert' => 'success',
			'title' => '¡Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);
	
		echo json_encode($respuesta);
	} else {
		// Instancia la variable de notificacion
		$respuesta = array(
			'status' => 202,
			'alert' => 'danger',
			'title' => '¡Algo salio mal!',
			'message' => 'No se logro procesar la solicitud.'
		);
		echo json_encode($respuesta);
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

//@etysoft funcion agregar nueva asignacion mediante la peticion requerida
function add_new_asignament($db, $id_sucursal, $id_almacen){
    $asignacion = array(
        'sucursal_id' => $id_sucursal,
        'almacen_id' => $id_almacen
    );
    $id_sucursal = $db->insert('inv_almacen_sucursales', $asignacion);
}

//@etysoft funcion eliminar asignaciones mediante la peticion requerida
function delete_asignament($db, $id_sucursal){
    // obtiene el resultado de la consulta
    $arr_asignaciones = $db->query("
        SELECT
            asu.id
        FROM
            inv_sucursal s
        JOIN inv_almacen_sucursales asu ON asu.sucursal_id = s.id_sucursal 
        JOIN inv_almacenes a ON a.id_almacen = asu.almacen_id
        WHERE
            s.id_sucursal = $id_sucursal
    ")->fetch();
    foreach ($arr_asignaciones as $key => $asignacion) {
        $db->delete()->from('inv_almacen_sucursales')->where('id', $asignacion['id'])->limit(1)->execute();
    }
}

?>