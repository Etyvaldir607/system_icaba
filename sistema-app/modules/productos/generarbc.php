<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Define las cabeceras
header('Content-Type: application/json');
// Verifica la peticion ajax
if (is_ajax()) {

	// Define las variables
	$codigo_pais = '777';
	$codigo_empresa = '10';

	// Instancia el objeto
	$codigo_producto = $db->query("select ifnull((max(id_producto)), 1) as codigo from inv_productos")->fetch_first();
	$codigo_producto = $codigo_producto['codigo'];

	// Verifica la capacidad
	if ($codigo_producto < 10000) {
		// Rellena con ceros
		$codigo_producto = str_pad($codigo_producto, 4, '0', STR_PAD_LEFT);

		// Obtiene el codigo de verifiacion
		$digitos = str_split($codigo_pais . $codigo_empresa . $codigo_producto);

		// Define variables
		$pares = 0;
		$impares = 0;

		// Recorre los digitos
		foreach ($digitos as $nro => $digito) {
			if ($nro % 2 == 0) {
				$impares = $impares + $digito;
			} else {
				$pares = $pares + $digito;
			}
		}

		// Obtiene el digito de verificacion
		$digito_verificacion = ($impares * 3) + $pares;
		$digito_verificacion = intval(substr($digito_verificacion, -1));
		$digito_verificacion = ($digito_verificacion == 0) ? 0 : 10 - $digito_verificacion;

		$random = mt_rand(5, 1000000);
		$nuevo = $random %1000;
		if ($nuevo<100) {
			if ($nuevo>10) {
				$nuevo = '0'.$nuevo;
			}
			else{
				$nuevo = '00'.$nuevo;
			}
		}

		// Obtiene el codigo de barras
		$codigo_barras = $codigo_pais . $codigo_empresa . $codigo_producto . $nuevo;


		// Devuelve los resultados
		echo json_encode(array('codigo' => $codigo_barras));
	} else {
		// Devuelve los resultados
		echo json_encode('n');
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>