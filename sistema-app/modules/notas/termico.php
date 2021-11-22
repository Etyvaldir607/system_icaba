<?php
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
	if (isset($_POST['id']) ) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/controlcode-class/ControlCode.php';
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
		// Obtiene los datos de la nota
		
		$venta = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('id_egreso', $id_venta)->fetch_first();

		if (!$venta) {

			$detalles = $db->query("SELECT d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, u.tamanio
									FROM inv_egresos_detalles d
									LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
									LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
									LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
									LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
									WHERE d.egreso_id = '$id_venta'
									ORDER BY c.orden asc, codigo asc")->fetch();

			$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
			$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

			// Obtiene la dosificacion del periodo actual
			//$dosificacion = $db->from('inv_dosificaciones')->where('fecha_limite', $venta['fecha_limite'])->fetch_first();

			
			// Instancia la respuesta
				$respuesta = array(
					'papel_ancho' => 10,
					'papel_alto' => 25,
					'papel_limite' => 576,
					'empresa_nombre' => $_institution['nombre'],
					'empresa_sucursal' => 'SUCURSAL Nº 1',
					'empresa_direccion' => $_institution['direccion'],
					'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
					'empresa_ciudad' => 'EL ALTO - BOLIVIA',
					'empresa_actividad' => $_institution['razon_social'],
					'empresa_nit' => $_institution['nit'],
					'empresa_empleado' => ($_user['persona_id'] == 0) ? upper($_user['username']) : upper(trim($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno'])),
					'empresa_agradecimiento' => '¡Gracias por tu compra!',
					'factura_titulo' => 'NOTA DE REMISION',
					'factura_numero' => $venta['nro_factura'],
					'factura_autorizacion' => $venta['nro_autorizacion'],
					'factura_fecha' => date_decode($venta['fecha_egreso'], 'd/m/Y'),
					'factura_hora' => substr($venta['hora_egreso'], 0, 5),
					'factura_codigo' => $venta['codigo_control'],
					'factura_limite' => date_decode($venta['fecha_limite'], 'd/m/Y'),
					'factura_autenticidad' => '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"',
					'factura_leyenda' => 'Ley Nº 453: .',
					//'factura_leyenda' => 'Ley Nº 453: "' . $dosificacion['leyenda'] . '".',
					'cliente_nit' => $venta['nit_ci'],
					'cliente_nombre' => $venta['nombre_cliente'],
					'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
					'venta_cantidades' => $cantidades,
					'venta_detalles' => $nombres,
					'venta_precios' => $precios,
					'venta_subtotales' => $subtotales,
					'venta_total_numeral' => $venta['monto_total'],
					'venta_total_literal' => $monto_literal,
					'venta_total_decimal' => $monto_decimal . '/100',
					'venta_moneda' => $moneda,
					'importe_base' => '0',
					'importe_ice' => '0',
					'importe_venta' => '0',
					'importe_credito' => '0',
					'importe_descuento' => '0',
					'impresora' => $_terminal['impresora']
				);
				// Envia respuesta
				
				echo json_encode($respuesta);
			
		}else{
			// Envia respuesta
			echo 'error';
		}
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