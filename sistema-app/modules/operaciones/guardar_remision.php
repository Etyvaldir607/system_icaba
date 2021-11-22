<?php
/**
 * SimplePHP - Simple Framework PHP
 *
 * @package  SimplePHP
 * @author   Checkcode2.1
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	// var_dump($_POST);
	// var_dump('--------------');
	//var_dump($_POST['nit_ci'].' '.$_POST['nombre_cliente'].' Producto: '.$_POST['productos'].' Nombre_producto: '.$_POST['nombres'].' Cantidades: '.$_POST['cantidades'].' '.$_POST['precios'].' '.$_POST['almacen_id'].' '.$_POST['nro_registros'].' '.$_POST['monto_total']);
	if (isset($_POST['nit_ci']) && 	
		isset($_POST['nombre_cliente']) && 
		isset($_POST['precios']) && 
		isset($_POST['monto_total']) //&& isset($_POST['cliente_id'])
		
	) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
		// Obtiene los datos de la nota
		$nit_ci 		= trim($_POST['nit_ci']);
// 		$cliente_id_aux = trim($_POST['cliente_id']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$telefono 		= trim($_POST['telefono']);
		$direccion 		= trim($_POST['direccion']);
		$observacion 	= trim($_POST['observacion']);
		$productos 		= (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$asignaciones   = (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$nombres 		= (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades 	= (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios 		= (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$nro_registros 	= trim($_POST['nro_registros']);
		$monto_total 	= trim($_POST['monto_total']);
		$id_proforma 	= trim($_POST['proforma_id']);
        $valor_descuento = trim($_POST['valor_descuento']);
		$almacen_id 	= trim($_POST['almacen_id']);
		$sucursal_id	= trim($_POST['sucursal_id']);
		$tipo_pago 		= trim($_POST['tipo_pago']);
		$plan 				= (isset($_POST['forma_pago']))?trim($_POST['forma_pago']):'';
		$plan 				= ($plan=="2") ? "si" : "no";
        
        // foreach($productos as $nro => $proc){
        //     var_dump('>'.$productos[$nro].'<');
        // }
        // die();
		$id_pago 	= (isset($_POST['id_pago'])) ? $_POST['id_pago'] : 0;
		$nro_cuentas = trim($_POST['nro_cuentas']);			

		if ($plan == "si") {
			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha']: array();
			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota']: array();
		}


		if ($valor_descuento == '') {
		    $valor_descuento = 0;
		}
		
		// Obtiene el numero de nota
		$nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura 
									from inv_egresos 
									where tipo = 'Venta' AND provisionado = 'S' AND almacen_id = '$almacen_id'
									")->fetch_first();

		if(isset($nro_factura['nro_factura'])){
            $nro_factura_aux = $nro_factura['nro_factura'];
        }else{
            $nro_factura_aux = 1;
        }
// 		$nro_factura = $nro_factura['nro_factura'];

		
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
		

        $res_client = $db->query("SELECT id_cliente 
									FROM inv_clientes 
									WHERE nit_ci='$nit_ci' AND telefono='$telefono' AND escalafon='$direccion' AND nombre_cliente='$nombre_cliente'")->fetch_first();
		// Instancia la nota
		$nota = array(
			'fecha_egreso' 		=> date('Y-m-d'),
			'hora_egreso'		=> date('H:i:s'),
			'tipo' 				=> 'Venta',
			'provisionado' 		=> 'S',
			'descripcion' 		=> 'Orden de compra',
			'nro_factura' 		=> $nro_factura_aux,
			'nro_autorizacion'	=> '',
			'codigo_control' 	=> '',
			'fecha_limite' 		=> '0000-00-00',
			'monto_total' 		=> $monto_total,
			'nit_ci' 			=> $nit_ci,
			'telefono' 			=> $telefono,
			'direccion' 		=> $direccion,
			'descuento' 		=> $valor_descuento,
			'observacion' 		=> $observacion,
			'nombre_cliente' 	=> mb_strtoupper($nombre_cliente, 'UTF-8'),
			'nro_registros' 	=> $nro_registros,
			'dosificacion_id' 	=> 0,
			'almacen_id' 		=> $almacen_id,
			'sucursal_id' 		=> $sucursal_id,
			'tipo_de_pago' 		=> 'Efectivo',
			'empleado_id' 		=> $_user['persona_id'],
			'cliente_id'        => $res_client['id_cliente'],
			'plan_de_pagos' 	=> $plan,
			// 'convertido'		=> 'proforma'
		);
		
		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $nota);
		
	    $res_client = $db->query("SELECT id_cliente 
									FROM inv_clientes 
									WHERE nit_ci='$nit_ci' AND telefono='$telefono' AND escalafon='$direccion' AND nombre_cliente='$nombre_cliente'")->fetch();
	
		if(!$res_client){
			$client = array(
				'nit_ci' => $nit_ci,
				'telefono' => $telefono,
				'escalafon' => $direccion,
				'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8')
			);
			$db->insert('inv_clientes', $client);
		}

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			
			$lamismavariable = $db->query("SELECT * FROM inv_asignaciones q
                                     LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
                                     WHERE tipo = 'principal' and producto_id='".$productos[$nro]."'")->fetch_first();
			$unidad[$nro] = $lamismavariable["unidad"];
			$nombres[$nro]=$nombres[$nro]." (".$unidad[$nro].")";
			$asig = $lamismavariable["id_asignacion"];
			
			// Forma el detalle
			$detalle = array(
				'cantidad' 		=> $cantidades[$nro],
				'precio' 		=> $precios[$nro],
				'asignacion_id' => $asignaciones[$nro],
				'producto_id' 	=> $productos[$nro],
				'egreso_id' 	=> $egreso_id
			);

			// Genera los subtotales
			$subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}
        
                if($id_pago==0){
					$ingresoPlan = array(
						'movimiento_id' => $egreso_id,
						'interes_pago' 	=> "0",
						'tipo' 			=> 'Egreso'
					);
					$id_pago = $db->insert('inv_pagos', $ingresoPlan);
				}

				$queryy="";
				$nro_cuota=0;
				for($nro2=0; $nro2<$nro_cuentas; $nro2++) {
					$fecha_format=(isset($fechas[$nro2]) and $fechas[$nro2] != '') ? $fechas[$nro2]: "00-00-0000";
				// 	var_dump($fechas[$nro2]);
					$vfecha=explode("-",$fecha_format);
					
					$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];
					$nro_cuota++;

					$id_pago_detalle_x=(isset($id_pago_detalle[$nro2]))? $id_pago_detalle[$nro2]:0;
					
					$queryx = $db->query("	SELECT *
											FROM inv_pagos_detalles 
											WHERE id_pago_detalle = '$id_pago_detalle_x'")
									->fetch_first();

					$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";

					if($queryx){
						$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format,
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
							);
						$condicion = array('id_pago_detalle' => $id_pago_detalle_x);

						$db->where($condicion)->update('inv_pagos_detalles', $detallePlan);
					}
					else{
						if ($nro2 == 0){
							$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format,
								'fecha_pago'	=> $fecha_format,
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
								'tipo_pago' 	=> $tipo_pago,
								'empleado_id'	=> $_user['persona_id'],
								'estado'  		=> '1'
							);
						}else{
							$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format, 	
								'fecha_pago' 	=> '00-00-0000',
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
								'tipo_pago' 	=> '',
								'empleado_id' 	=> '0',
								'estado'  		=> '0'
							);
						}
						// Guarda la informacion
						$id_pago_detalle_x=$db->insert('inv_pagos_detalles', $detallePlan);
						$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";
					}
				}

				$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";

				$db->query("	DELETE FROM inv_pagos_detalles  
								WHERE pago_id = '$id_pago' ".$queryy)->execute();

				
				$forma_de_pago="FORMA DE PAGO: Pago por cuotas";
        
        $proforma = array(
			'convertido' => 'nota',
		);		
		// Actualiza la informacion
		$condicion = array('id_proforma' => $id_proforma);
		$db->where($condicion)->update('inv_proformas', $proforma);
		
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
			'nota_titulo' => 'N O T A   D E   R E M I S I Ó N',
			'nota_numero' => $nota['nro_factura'],
			'nota_fecha' => date_decode($nota['fecha_egreso'], 'd/m/Y'),
			'nota_hora' => substr($nota['hora_egreso'], 0, 5),
			'cliente_nit' => $nota['nit_ci'],
			'cliente_nombre' => $nota['nombre_cliente'],
			//'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'IMPORTE TOTAL'),
			'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'IMPORTE TOTAL','DESCUENTO','IMPORTE TOTAL CON DESCUENTO'),
			'venta_cantidades' => $cantidades,
			'venta_detalles' => $nombres,
			'venta_precios' => $precios,
			'venta_subtotales' => $subtotales,
			'venta_total_numeral' => $nota['monto_total'],
			'venta_total_literal' => $monto_literal,
			'venta_total_decimal' => $monto_decimal . '/100',
			'venta_moneda' => $moneda,
			'impresora' => $_terminal['impresora'],
			'forma_de_pago' =>'Efectivo',
			'venta_valor_descuento' => $valor_descuento,
			'venta_total_descuento' => $monto_total
		);
    
		//Termico
		//echo json_encode($respuesta);
		
		//PDF
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