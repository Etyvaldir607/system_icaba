<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_asignacion
$id_asignacion = (sizeof($params) > 0) ? $params[0] : 0;

//obtiene todos los datos de la asignacion
$consulta = $db->query("SELECT * FROM inv_asignaciones WHERE id_asignacion = $id_asignacion")->fetch_first();

//rescatamos el id_producto
$id_producto = $consulta['producto_id'];

//buscamos a todos los productos con el id_producto
//$consulta_asignaciones = $db->query("SELECT * FROM inv_asignaciones WHERE producto_id = $id_producto ")->fetch();

$consulta_secundarios = $db->query("UPDATE inv_asignaciones SET tipo = 'secundario' WHERE producto_id = $id_producto AND id_asignacion != $id_asignacion ")->execute();

$consulta_secundarios = $db->query("UPDATE inv_asignaciones SET tipo = 'principal' WHERE producto_id = $id_producto AND id_asignacion = $id_asignacion ")->execute();

redirect('?/productos/listar');

?>