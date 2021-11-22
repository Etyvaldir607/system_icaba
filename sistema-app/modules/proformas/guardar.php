<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	//var_dump($_POST);die;
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['atencion']) && isset($_POST['validez']) && isset($_POST['observacion']) && isset($_POST['telefono']) && isset($_POST['direccion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la proforma
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$atencion = trim($_POST['atencion']);
		$validez = trim($_POST['validez']);
		$observacion = trim($_POST['observacion']);
		$telefono = trim($_POST['telefono']);
		$direccion = trim($_POST['direccion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$asignaciones   = (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		//$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$almacen_id = trim($_POST['almacen_id']);

		// Convierte a mayusculas
		$nombre_cliente = upper($nombre_cliente);
		$atencion = upper($atencion);
		$observacion = upper($observacion);

		// Obtiene el numero de la proforma
		$nro_proforma = $db->query("select ifnull(max(nro_proforma), 0) + 1 as nro_proforma from inv_proformas")->fetch_first();
		$nro_proforma = $nro_proforma['nro_proforma'];

		// Define la variable de subtotales
		$subtotales = array();

		// Obtiene la moneda
		$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
		$moneda = ($moneda) ? $moneda['moneda'] : '';

		// Obtiene los datos del monto total
		$conversor = new NumberToLetterConverter();
		$monto_textual = explode('.', $monto_total);
		$monto_numeral = $monto_textual[0];
		$monto_decimal = $monto_textual[1];
		$monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

		/*****************************************/
		$res_client = $db->query("SELECT id_cliente 
									FROM inv_clientes 
									WHERE UPPER(nit_ci)=UPPER('$nit_ci') AND telefono='$telefono' AND escalafon='$direccion' AND UPPER(nombre_cliente)=UPPER('$nombre_cliente')")
						 ->fetch_first();

		if (!$res_client) {
			$client = array(
				'nit_ci' 			=> $nit_ci,
				'telefono' 			=> $telefono,
				'escalafon' 		=> $direccion,
				'nombre_cliente' 	=> mb_strtoupper($nombre_cliente, 'UTF-8')
			);
			$id_cliente=$db->insert('inv_clientes', $client);
		}else{
			$id_cliente=$res_client['id_cliente'];
		}

		// Instancia la proforma
		$proforma = array(
			'fecha_proforma' => date('Y-m-d'),
			'hora_proforma' => date('H:i:s'),
			'descripcion' => $atencion,
			'nro_proforma' => $nro_proforma,
			'monto_total' => $monto_total,
			'nombre_cliente' => $nombre_cliente,
			'nit_ci' => $nit_ci,
			'nro_registros' => $nro_registros,
			'validez' => $validez,
			'observacion' => $observacion,
			'telefono' => $telefono,
			'direccion' => $direccion,
			'almacen_id' => $almacen_id,
			'cliente_id'  => $id_cliente,
			'empleado_id' => $_user['persona_id']
		);

		// Guarda la informacion
		$proforma_id = $db->insert('inv_proformas', $proforma);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			// Forma el detalle
			/*if($descuentos[$nro]==''){
			    $descuentos[$nro]=0;
			}*/
			$detalle = array(
				'cantidad' => $cantidades[$nro],
				'precio' => $precios[$nro],
				'descuento' => '0',
				'asignacion_id' => $asignaciones[$nro],
				'producto_id' => $productos[$nro],
				'proforma_id' => $proforma_id
			);

			// Genera los subtotales
			$subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

			$lamismavariable = $db->query("SELECT * FROM inv_asignaciones q
                                     LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
                                     WHERE id_asignacion='".$asignaciones[$nro]."'")->fetch_first();
			$unidad[$nro] = $lamismavariable["unidad"];
			$nombres[$nro]=$nombres[$nro]." (".$unidad[$nro].")";
			
			// Guarda la informacion
			$db->insert('inv_proformas_detalles', $detalle);
		}

		// Instancia la respuesta
		$respuesta = array(
			'papel_ancho' => 10,
			'papel_alto' => 30,
			'papel_limite' => 576,
			'empresa_nombre' => $_institution['nombre'],
			'empresa_sucursal' => 'SUCURSAL Nº 1',
			'empresa_direccion' => $_institution['direccion'],
			'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
			'empresa_ciudad' => 'LA PAZ - BOLIVIA',
			'empresa_actividad' => $_institution['razon_social'],
			'empresa_nit' => $_institution['nit'],
			'proforma_titulo' => 'P  R  O  F  O  R  M  A',
			'proforma_numero' => $proforma['nro_proforma'],
			'proforma_fecha' => date_decode($proforma['fecha_proforma'], 'd/m/Y'),
			'proforma_hora' => substr($proforma['hora_proforma'], 0, 5),
			'cliente_nit' => $proforma['nit_ci'],
			'cliente_nombre' => $proforma['nombre_cliente'],
			'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
			'venta_cantidades' => $cantidades,
			'venta_detalles' => $nombres,
			'venta_precios' => $precios,
			'venta_subtotales' => $subtotales,
			'venta_total_numeral' => $proforma['monto_total'],
			'venta_total_literal' => $monto_literal,
			'venta_total_decimal' => $monto_decimal . '/100',
			'venta_moneda' => $moneda,
			'impresora' => $_terminal['impresora']
		);

			//Termico
			//echo json_encode($respuesta);
			
			//PDF
			echo json_encode($proforma_id);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>