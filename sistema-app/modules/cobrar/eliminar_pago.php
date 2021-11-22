<?php
/**
 * SimplePHP - Simple Framework PHP
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_ingreso
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nro'])){
		$id_ingreso = $_POST['nro'];

		// Obtiene el ingreso
		$ingreso = $db->select('*')
					  ->from('inv_pagos_detalles')
					  ->where('id_pago_detalle', $id_ingreso)
					  ->fetch_first();

		// Verifica si el ingreso existe
		if ($ingreso) {
			// Elimina el ingreso
			$db->delete()->from('inv_pagos_detalles')->where('id_pago_detalle', $id_ingreso)->limit(1)->execute();

			// Verifica si fue el ingreso eliminado
			if ($db->affected_rows) {
				// Instancia variable de notificacion
				echo "1";
			}
			else{
				echo "0";
			}
		} else {
			echo "2"; //NO EXISTE EL DATO EXTERNO
		}
	} else {
		echo "0";
	}
} else {
	echo "0";
}
?>