nro<?php
/**
 * SimplePHP - Simple Framework PHP
 *
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	//var_dump($_POST);die;
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['cliente']) && isset($_POST['telefono']) && isset($_POST['direccion']) && isset($_POST['observacion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['almacen_id']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['monto_porcentaje']) && isset($_POST['valor_descuento']) ) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
		// Obtiene los datos de la nota
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$telefono = trim($_POST['telefono']);
		$direccion = trim($_POST['direccion']);
		//$descuento = trim($_POST['descuento']);
		$observacion = trim($_POST['observacion']);
		//$activo = trim($_POST['activo']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$asignaciones   = (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$monto_porcentaje = trim($_POST['monto_porcentaje']);
		$valor_descuento = trim($_POST['valor_descuento']);
		$almacen_id = trim($_POST['almacen_id']);
		$sucursal_id = trim($_POST['sucursal_id']);
		$tipo_pago = trim($_POST['tipo_pago']);

		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);
		var_dump($nro_factura); die();
		
		$nro_cuentas = trim($_POST['nro_cuentas']);
		$plan = trim($_POST['forma_pago']);
		$plan = ($plan=="2") ? "si" : "no";

		$id_empleado	= trim($_POST['usuario']);

		if($plan=="si"){
			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha']: array();
			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota']: array();
		}

		// Obtiene el numero de nota
		//$nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
		//$nro_factura = $nro_factura['nro_factura'];

		// Define la variable de subtotales
		$subtotales = array();

		// Obtiene la moneda
		$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
		$moneda = ($moneda) ? $moneda['moneda'] : '';

		// Obtiene los datos del monto total
		$conversor = new NumberToLetterConverter();
		$monto_textual = explode('.', $monto_porcentaje);
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

		// Instancia la nota
		$nota = array(
			'fecha_egreso' => date('Y-m-d'),
			'hora_egreso' => date('H:i:s'),
			'tipo' => 'Venta',
			'provisionado' => 'N',
			'descripcion' => 'Venta de productos con factura manual',
			
			'nro_factura' => $nro_factura,
			'nro_autorizacion' => $nro_autorizacion,
			'codigo_control' => '',
			'fecha_limite' => '0000-00-00',
			'monto_total' => $monto_total,
			
			'nit_ci' => $nit_ci,
			'telefono' => $telefono,
			'direccion' => $direccion,
			'descuento' => $valor_descuento,
			'observacion' => $observacion,
			
			//'activo' => $activo,
			'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
			'nro_registros' => $nro_registros,
			'dosificacion_id' => 0,
			'almacen_id' => $almacen_id,
			'sucursal_id' => $sucursal_id,
			
			'plan_de_pagos' => $plan,
			'tipo_de_pago' => $tipo_pago,
			'estado'=>'V',
			'cliente_id'  => $id_cliente,
			'empleado_id' => $id_empleado
		);
		//var_dump($nota);die;
		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $nota);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			// Forma el detalle
			/*if($descuentos[$nro] == ''){
			    $descuentos[$nro] = 0;
			}*/
			$detalle = array(
				'cantidad' => $cantidades[$nro],
				'precio' => $precios[$nro],
				'descuento' => "0",
				
				'asignacion_id' => $asignaciones[$nro],
				'producto_id' => $productos[$nro],
				'descripcion'=>"",
				'egreso_id' => $egreso_id
			);

			// Genera los subtotales
			$subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}

		if($plan=="si"){
			// Instancia el ingreso
			$ingresoPlan = array(
				'movimiento_id' => $egreso_id,
				'interes_pago' => "0",
				'tipo' => 'Egreso'
			);
			// Guarda la informacion del ingreso general
			$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
					
			$nro_cuota=0;
			for($nro2=0; $nro2<$nro_cuentas; $nro2++) {
				$fecha_format=(isset($fechas[$nro2]) and $fechas[$nro2] != '') ? $fechas[$nro2]: "00-00-0000";
				$vfecha=explode("-",$fecha_format);
				$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];
				
				$nro_cuota++;
				if($nro2==0){
					$detallePlan = array(
						'nro_cuota' => $nro_cuota,
						'pago_id' => $ingreso_id_plan,
						'fecha' => $fecha_format, 							
						'fecha_pago' => $fecha_format,

						'empleado_id'=> $id_empleado,
						'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
						'tipo_pago' => $tipo_pago,
						'estado'  => '1'
					);
				}else{
					$detallePlan = array(
						'nro_cuota' => $nro_cuota,
						'pago_id' => $ingreso_id_plan,
						'fecha' => $fecha_format, 	
						'fecha_pago' => '00-00-0000',
						
						'empleado_id'=> '0',
						'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
						'tipo_pago' => '',
						'estado'  => '0'
					);
				}
				// Guarda la informacion
				$db->insert('inv_pagos_detalles', $detallePlan);
			}
		}

		// Instancia la respuesta
		/*$respuesta = array(
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
			'nota_titulo' => 'N O T A   D E   R E M I S I Ó N',
			'nota_numero' => $nota['nro_factura'],
			'nota_fecha' => date_decode($nota['fecha_egreso'], 'd/m/Y'),
			'nota_hora' => substr($nota['hora_egreso'], 0, 5),
			'cliente_nit' => $nota['nit_ci'],
			'cliente_nombre' => $nota['nombre_cliente'],
			'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
			'venta_cantidades' => $cantidades,
			'venta_detalles' => $nombres,
			'venta_precios' => $precios,
			'venta_subtotales' => $subtotales,
			'venta_total_numeral' => $nota['monto_total'],
			'venta_total_literal' => $monto_literal,
			'venta_total_decimal' => $monto_decimal . '/100',
			'venta_moneda' => $moneda,
			'impresora' => $_terminal['impresora']
		);*/

		//Termico
		//echo json_encode($respuesta);
			
		//PDF
		//echo json_encode($respuesta);
		echo json_encode($egreso_id);
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