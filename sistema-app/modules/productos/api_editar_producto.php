<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        isset($_POST['id_producto']) &&
        isset($_POST['id_asignacion']) &&
        isset($_POST['codigo']) &&
        isset($_POST['codigo_barras']) &&
        isset($_POST['nombre']) &&
        isset($_POST['nombre_factura']) &&
        isset($_POST['cantidad_minima']) &&
        isset($_POST['categoria_id']) &&
        isset($_POST['ubicacion']) &&
        isset($_POST['descripcion']) &&
        isset($_POST['producto_regalo'])
    ) {
        // Obtiene los datos del producto
        $id_producto = trim($_POST['id_producto']);
        $id_asignacion = trim($_POST['id_asignacion']);
        $codigo = trim($_POST['codigo']);
        $codigo_barras = trim($_POST['codigo_barras']);
        $nombre = trim($_POST['nombre']);
        $nombre_factura = trim($_POST['nombre_factura']);
        $cantidad_minima = trim($_POST['cantidad_minima']);
        $precio_actual = trim($_POST['precio_actual']);
        $rango = trim($_POST['rango']);
        $unidad_id = trim($_POST['unidad_id']);
        $categoria_id = trim($_POST['categoria_id']);
        $ubicacion = trim($_POST['ubicacion']);
        $descripcion = trim($_POST['descripcion']);
        $producto_regalo = trim($_POST['producto_regalo']);

        // Instancia el producto
        $producto = array(
            'codigo' => $codigo,
            'codigo_barras' => 'CK' . $codigo_barras,
            'nombre' => $nombre,
            'nombre_factura' => $nombre_factura,
            'cantidad_minima' => $cantidad_minima,
            'ubicacion' => $ubicacion,
            'rango' => $rango,
            'precio_actual' => $precio_actual,
            'descripcion' => $descripcion,
            'bonificacion' => $producto_regalo,
            'unidad_id' => $unidad_id,
            'categoria_id' => $categoria_id,
            'fecha_registro' => date('Y-m-d'),
            'hora_registro' => date('H:i:s'),
            'imagen' =>  ''
        );

        // Guarda la informacion id producto
        $db->where('id_producto', $id_producto)->update('inv_productos', $producto);


        $asignacion = array(
            'unidad_id' => $unidad_id,
            'precio_actual' => $precio_actual,
        );

        $db->where('id_asignacion', $id_asignacion)->update('inv_asignaciones', $asignacion);
        
        //instacia de precio
        $precio = array(
            'precio' => $precio_actual,
            'fecha_registro' => date('Y-m-d'),
            'hora_registro' => date('H:i:s'),
            'producto_id' => $id_producto,
            'empleado_id' => $_user['persona_id'],
            'asignacion_id' => $id_asignacion,
        );
        // Crea el precio
        $db->insert('inv_precios', $precio);

        // Instancia la variable de notificacion
        $_SESSION[temporary] = array(
            'alert' => 'info',
            'title' => 'Edicion satisfactoria!',
            'message' => 'El registro se modifico correctamente.'
        );

        redirect('?/productos/ver/' . $id_producto);
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
