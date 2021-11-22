<?php

// Obtiene el orden de compra
$fecha = (isset($params[0])) ? $params[0] : now();
$fecha_recibida = get_date_literal($fecha, $_institution['formato']);


$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('imprimir', $permisos);

if (!$fecha) {
		// Error 404
	require_once not_found();
	exit;
} elseif (!$permiso_ver) {
		// Error 401
	require_once bad_request();
	exit;
}
// Obtiene las ventas
$ventas = $db->query("SELECT * FROM inv_egresos e LEFT JOIN sys_empleados se ON e.empleado_id = se.id_empleado WHERE e.fecha_egreso = '$fecha' AND e.tipo='Venta' AND e.estado = 'V'  group by id_egreso")->fetch();
//Obtiene compras
$compras = $db->query("SELECT * FROM inv_ingresos i  LEFT JOIN sys_empleados se ON i.empleado_id = se.id_empleado WHERE i.fecha_ingreso = '$fecha' AND i.tipo='Compra'  group by id_ingreso")->fetch();

//cronograma de pagos
$cronogramas = $db->query("select se.*, c.periodo, cc.detalle, cc.monto, cc.fecha_pago, cc.id_cronograma_cuentas, cc.tipo_pago from cronograma c left join cronograma_cuentas cc on c.id_cronograma = cc.cronograma_id left join sys_empleados se on se.id_empleado = cc.empleado_id where cc.estado='1'  and cc.fecha_pago='$fecha' GROUP by c.id_cronograma")->fetch();
//COBRO DE VENTAS
$cobros = $db->query("select se.*, p.id_pago, p.movimiento_id, pd.tipo_pago, pd.fecha_pago, e.nro_factura, e.fecha_egreso,e.nombre_cliente,e.nit_ci, e.monto_total, e.tipo, ifnull(monto,0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id left join sys_empleados se ON se.id_empleado = pd.empleado_id LEFT JOIN inv_egresos e ON e.id_egreso=p.movimiento_id where p.tipo='Egreso' and pd.estado='1' and pd.fecha_pago='$fecha' and e.fecha_egreso!=pd.fecha_pago")->fetch(); 

//pagos compras
$pagos_compras = $db->query("select se.*, p.id_pago, p.movimiento_id, i.fecha_ingreso, i.fecha_ingreso, i.tipo, i.id_ingreso, i.nombre_proveedor, i.monto_total, pd.tipo_pago, ifnull(monto,0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id left join sys_empleados se ON se.id_empleado = pd.empleado_id LEFT JOIN inv_ingresos i ON i.id_ingreso=p.movimiento_id where p.tipo='Ingreso' and pd.estado='1' and pd.fecha_pago='$fecha' and i.fecha_ingreso!=pd.fecha_pago")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los ingresos
$ingresos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'i')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();

// Obtiene los egresos
$egresos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'e')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();

// Obtiene los gastos
$gastos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'g')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();


	// Define las variables
if (!$_user['nombres']) 
	$valor_empleado = 'USUARIO - ' . upper($_user['username']);
else
	$valor_empleado = $_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $user['materno'] ;


// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Define variables globales
define('DIRECCION', escape($_institution['pie_pagina']));
define('IMAGEN', escape($_institution['imagen_encabezado']));
define('ATENCION', 'Lun. a Vie. de 08:30 a 18:30 y Sáb. de 08:30 a 13:00');
define('PIE', escape($_institution['pie_pagina']));
define('TELEFONO', escape(str_replace(',', ', ', $_institution['telefono'])));

//SUCURSALES
/*define('SUCURSAL', escape(str_replace(',', ', ', substr(($_sucursal['sucursal']), 11))));
define('DIRECCION_SUCURSAL', escape($_sucursal['direccion']));
define('TELEFONO_SUCURSAL', escape(str_replace(',', ' - ',$_sucursal['telefono'])));
define('EMAIL_SUCURSAL', escape($_sucursal['correo']));
define('DESCRIPCION_SUCURSAL', escape($_sucursal['descripcion']));
define('IMAGEN_SUCURSAL', escape($_sucursal['imagen_encabezado']));
*/
define('EMPLEADO', escape($valor_empleado));
define('FECHA_RECIBIDA', escape($fecha_recibida));
define('IMAGEN_SUCURSAL', '');
// Operaciones con la imagen del header
list($ancho_header, $alto_header) = getimagesize(imgs . '/header.jpg');
$relacion = $alto_header / $ancho_header;
$ancho_header = 152;
$alto_header = round($ancho_header * $relacion);
define('ancho_header', $ancho_header);
define('alto_header', $alto_header);

// Operaciones con la imagen del footer
list($ancho_footer, $alto_footer) = getimagesize(imgs . '/footer.jpg');
$relacion = $alto_footer / $ancho_footer;
$ancho_footer = 612;
$alto_footer = round($ancho_footer * $relacion);
define('ancho_footer', $ancho_footer);
define('alto_footer', $alto_footer);
//$line = ;

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {

		$imagenn = (IMAGEN_SUCURSAL != '') ? institucion . '/' . IMAGEN_SUCURSAL : imgs . '/header.jpg' ;
		//$this->Image($imagenn, 30, 5, 140, 63);
		// Position at 15 mm from bottom
		$this->SetY(30);
		$this->SetX(185);
		$this->SetFont('helvetica', 'B', 25);
		$this->SetTextColor (0, 0, 0);
        // Page number
		$this->Cell(0, 0, 'REPORTE GENERAL DE CAJA', 0, false, 'R', 0, '', 0, false, 'T', 'M');
		// Position at 15 mm from bottom
		$this->SetY(55);
		$this->SetX(180);
		$this->SetFont('helvetica', 'B', 8);
		$this->SetTextColor (0, 0, 0);
        // Page number
		$this->Cell(0, 0, FECHA_RECIBIDA . ' ' . ' ' . EMPLEADO, 0, false, 'R', 0, '', 1, false, 'T', 'M');
		$this->Line(30, 67, 585, 67, array('width' => 1, 'color' => array(0, 0, 0)));

	}
	public function Footer() {
		//$this->Image(imgs . '/footer.jpg', 0, 698, ancho_footer, alto_footer);
		$this->SetY(-35);
		$this->SetX(0);
		$this->SetFont('helvetica', 'B', 7);
		$this->SetTextColor (0, 0, 0);
        // Page number
			//$this->Cell(0, 0, DIRECCION_SUCURSAL, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		// Position at 15 mm from bottom
		$this->SetY(-27);
		$this->SetX(0);
		$this->SetFont('helvetica', 'B', 7);
		$this->SetTextColor (0, 0, 0);
        // Page number
			//$this->Cell(0, 0, EMAIL_SUCURSAL, 0, false, 'C', 0, '', 1, false, 'T', 'M');
		// Position at 15 mm from bottom
		$this->SetY(-18);
		$this->SetX(0);
		$this->SetFont('helvetica', 'B', 6);
		$this->SetTextColor (0, 0, 0);
        // Page number
			//$this->Cell(0, 0, TELEFONO_SUCURSAL, 0, false, 'C', 0, '', 1, false, 'T', 'M');
		$this->Line(30, 755, 585, 755, array('width' => 1, 'color' => array(0, 0, 0)));
	}
}

// Instancia el documento PDF
$pdf = new MYPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(30, alto_header + 1, 30);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
	//$pdf->SetAutoPageBreak(true, alto_footer + 15);

// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('P');

// Adiciona la pagina
$pdf->AddPage();



// Estructura la tabla
$body = $body_ventas = $body_egresos = $body_compras = $body_gasto = '';
//cobros
$total_ingresos = $total_ingresos_banco = $total_total_ingresos = $total_cobros = $total_egresos = $total_total_venta = $total_egresos_banco = $total_gasto = 0;
if ($cobros || $ingresos) {
	if ($cobros) {		
		foreach ($cobros as $nro => $cobro) {
			if ($cobro['tipo_pago'] == 'Efectivo') 
				$total_ingresos = $total_ingresos + $cobro['subtotal'];
			else
				$total_ingresos_banco = $total_ingresos_banco + $cobro['subtotal'];
			$body .= '<tr>';
			$body .= '<td class="left-right" align="right">' . $cobro['nro_factura'] . '</td>';
			$body .= '<td class="left-right">' . date_decode($cobro['fecha_pago'], $_institution['formato']) . '</td>';
			$body .= '<td class="left-right">' . escape($cobro['nombre_cliente']) . '<br><font size="7">NIT : ' . escape($cobro['nit_ci']) .' - ' . escape($cobro['tipo']) . '</font></td>';
			$body .= '<td class="left-right" align="right">' . number_format($cobro['monto_total'], 2, '.', '') . '</td>';
			$body .= '<td class="left-right" align="right">' . number_format($cobro['subtotal'], 2, '.', '') . '</td>';
			$body .= '<td class="left-right" align="right">' . $cobro['tipo_pago'] . '</td>';
			$body .= '</tr>';
		}
	}
//ingresos
	foreach ($ingresos as $nro => $ingreso) {
		$total_ingresos = $total_ingresos + $ingreso['monto'];
		$body .= '<tr>';
		$body .= '<td class="left-right" align="right">' . $ingreso['nro_comprobante'] . '</td>';
		$body .= '<td class="left-right">' . date_decode($ingreso['fecha_movimiento'], $_institution['formato']) . '</td>';
		$body .= '<td class="left-right">' . escape($ingreso['concepto']) . '</td>';
		$body .= '<td class="left-right" align="right">' . number_format($ingreso['monto'], 2, '.', '') . '</td>';
		$body .= '<td class="left-right" align="right">' . number_format($ingreso['monto'], 2, '.', '') . '</td>';
		$body .= '<td class="left-right" align="rigth">Efectivo</td>';
		$body .= '</tr>';
	}
	$total_total_ingresos = (($total_ingresos_banco + $total_ingresos )) ? number_format(($total_ingresos_banco + $total_ingresos), 2, '.', ''): number_format(0, 2, '.', '');
	$total_ingresos = ($total_ingresos) ? number_format($total_ingresos, 2, '.', ''):  number_format(0, 2, '.', '');
	$total_ingresos_banco = ($total_ingresos_banco ) ? number_format($total_ingresos_banco, 2, '.', ''): number_format(0, 2, '.', '');
}else{
	$body .= '<tr>';
	$body .= '<td class="left-right" colspan="6">No existe Ingresos.</td>';
	$body .= '</tr>';			
	$total_ingresos = number_format(0, 2, '.', '');
	$total_ingresos_banco = number_format(0, 2, '.', '');
	$total_total_ingresos = number_format(0, 2, '.', '');
}

//ventas
if ($ventas) {	
	foreach ($ventas as $nro => $venta) {
		//$total_venta = $total_venta + $venta['subtotal'];
		$body_ventas .= '<tr>';
		$body_ventas .= '<td class="left-right" align="right">' . $venta['nro_factura'] . '</td>';
		$body_ventas .= '<td class="left-right">' . date_decode($venta['fecha_egreso'], $_institution['formato']) . '</td>';
		$body_ventas .= '<td class="left-right">' . escape($venta['nombre_cliente']) . '<br><font size="7">NIT : ' . escape($venta['nit_ci']) . ' - '. escape($venta['tipo']) . '</font></td>';
		if ($venta['plan_de_pagos'] == 'si'){ 
			$body_ventas .= '<td class="left-right" align="right">' . number_format($venta['monto_total'], 2, '.', '') . '</td>';				
			$pagos = $db->query("select id_pago, movimiento_id, tipo_pago, ifnull(SUM(monto),0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id where p.movimiento_id = '". $venta['id_egreso'] . "' AND pd.estado='1' AND p.tipo='Egreso' GROUP by movimiento_id")->fetch_first();
			if ($pagos) {
				if ($pagos['tipo_pago'] == 'Efectivo') 
					$total_venta = $total_venta + $pagos['subtotal']; 							
				else
					$total_venta_banco = $total_venta_banco + $venta['monto_total'];
				$body_ventas .= '<td class="left-right" align="right">' . number_format($pagos['subtotal'], 2, '.', '') . '</td>';	
				$body_ventas .= '<td class="left-right" align="right">' . $pagos['tipo_pago'] . '</td>';					
			}else{
				$body_ventas .= '<td class="left-right" align="right">' . number_format(0, 2, '.', '') . '</td>';	
				$body_ventas .= '<td class="left-right" align="right"><i> SIN PAGO </i></td>';												
			}
		}else{
			if ($venta['tipo_de_pago'] == 'Efectivo') {
				$total_venta = $total_venta + $venta['monto_total'];
				$body_ventas .= '<td class="left-right" align="right">' . number_format($venta['monto_total'], 2, '.', '') . '</td>';
				$body_ventas .= '<td class="left-right" align="right">' . number_format($venta['monto_total'], 2, '.', '') . '</td>';
				$body_ventas .= '<td class="left-right" align="right">' . $venta['tipo_de_pago'] . '</td>';											
			}else{
				$total_venta_banco = $total_venta_banco + $venta['monto_total'];
				$body_ventas .= '<td class="left-right" align="right">' . number_format($venta['monto_total'], 2, '.', '') . '</td>';
				$body_ventas .= '<td class="left-right" align="right">' . number_format($venta['monto_total'], 2, '.', '') . '</td>';								
				$body_ventas .= '<td class="left-right" align="right">' . $venta['tipo_de_pago'] . '</td>';																	
			}
		}
		$body_ventas .= '</tr>';
	}
	$total_total_venta = (($total_venta_banco + $total_venta )) ? number_format(($total_venta_banco + $total_venta), 2, '.', ''): number_format(0, 2, '.', '');
	$total_venta = ($total_venta ) ? number_format($total_venta, 2, '.', ''): number_format(0, 2, '.', '');
	$total_venta_banco = ($total_venta_banco ) ? number_format($total_venta_banco, 2, '.', ''): number_format(0, 2, '.', '');
}else{
	$body_ventas .= '<tr>';
	$body_ventas .= '<td class="left-right" colspan="6">No existe Ventas.</td>';
	$body_ventas .= '</tr>';			
	$total_venta = number_format(0, 2, '.', '');
	$total_venta_banco = number_format(0, 2, '.', '');
	$total_total_venta = number_format(0, 2, '.', '');
}


//cronograma
if ($egresos || $pagos_compras || $cronogramas) {
	if ($cronogramas) {		
		foreach ($cronogramas as $nro => $cronograma) {
			if ($cronograma['tipo_pago'] == 'Efectivo') 
				$total_egresos = $total_egresos + $cronograma['monto'];
			else
				$total_egresos_banco = $total_egresos_banco + $cronograma['monto'];
			$body_egresos .= '<tr>';
			$body_egresos .= '<td class="left-right" align="right">' . $cronograma['id_cronograma_cuentas'] . '</td>';
			$body_egresos .= '<td class="left-right">' . date_decode($cronograma['fecha_pago'], $_institution['formato']) . '</td>';
			$body_egresos .= '<td class="left-right">' . escape($cronograma['detalle']) . '</td>';
			$body_egresos .= '<td class="left-right" align="right">' . number_format($cronograma['monto'], 2, '.', '') . '</td>';
			$body_egresos .= '<td class="left-right" align="right">' . number_format($cronograma['monto'], 2, '.', '') . '</td>';
			$body_egresos .= '<td class="left-right" align="right">' . $cronograma['tipo_pago'] . '</td>';
			$body_egresos .= '</tr>';
		}
	}

	if ($pagos_compras) {		
		foreach ($pagos_compras as $nro => $pagos_compra) {
			if ($pagos_compra['tipo_pago'] == 'Efectivo') 
				$total_egresos = $total_egresos + $pagos_compra['subtotal'];
			else
				$total_egresos_banco = $total_egresos_banco + $pagos_compra['subtotal'];
			$body_egresos .= '<tr>';
			$body_egresos .= '<td class="left-right" align="right">' . $pagos_compra['id_ingreso'] . '</td>';
			$body_egresos .= '<td class="left-right">' . date_decode($pagos_compra['fecha_ingreso'], $_institution['formato']) . '</td>';
			$body_egresos .= '<td class="left-right">' . escape($pagos_compra['nombre_proveedor']) . '  - <font size="7"> ' . escape($pagos_compra['tipo']) . '</font></td>';
			$body_egresos .= '<td class="left-right" align="right">' . number_format($pagos_compra['monto_total'], 2, '.', '') . '</td>';
			$body_egresos .= '<td class="left-right" align="right">' . number_format($pagos_compra['subtotal'], 2, '.', '') . '</td>';
			$body_egresos .= '<td class="left-right" align="right">' . $pagos_compra['tipo_pago'] . '</td>';
			$body_egresos .= '</tr>';
		}
	}
//egresos
	foreach ($egresos as $nro => $egreso) {
		$total_egresos = $total_egresos + $egreso['monto'];
		$body_egresos .= '<tr>';
		$body_egresos .= '<td class="left-right" align="right">' . $egreso['nro_comprobante'] . '</td>';
		$body_egresos .= '<td class="left-right">' . date_decode($egreso['fecha_movimiento'], $_institution['formato']) . '</td>';
		$body_egresos .= '<td class="left-right">' . escape($egreso['concepto']) . '</td>';
		$body_egresos .= '<td class="left-right" align="right">' . number_format($egreso['monto'], 2, '.', '') . '</td>';
		$body_egresos .= '<td class="left-right" align="right">' . number_format($egreso['monto'], 2, '.', '') . '</td>';
		$body_egresos .= '<td class="left-right" align="rigth">Efectivo</td>';
		$body_egresos .= '</tr>';
	}
	$total_total_egresos = (($total_egresos_banco + $total_egresos )) ? number_format(($total_egresos_banco + $total_egresos), 2, '.', ''): number_format(0, 2, '.', '');
	$total_egresos = ($total_egresos) ? number_format($total_egresos, 2, '.', ''):  number_format(0, 2, '.', '');;
	$total_egresos_banco = ($total_egresos_banco ) ? number_format($total_egresos_banco, 2, '.', ''): number_format(0, 2, '.', '');
}else{
	$body_egresos .= '<tr>';
	$body_egresos .= '<td class="left-right" colspan="6">No existe Egresos.</td>';
	$body_egresos .= '</tr>';			
	$total_egresos = number_format(0, 2, '.', '');
	$total_egresos_banco = number_format(0, 2, '.', '');
	$total_total_egresos = number_format(0, 2, '.', '');
}


//COMPRAS
if ($compras) {	
	foreach ($compras as $nro => $compra) {
		$body_compras .= '<tr>';
		$body_compras .= '<td class="left-right" align="right">' . $compra['id_ingreso'] . '</td>';
		$body_compras .= '<td class="left-right">' . date_decode($compra['fecha_ingreso'], $_institution['formato']) . '</td>';
		$body_compras .= '<td class="left-right">' . escape($compra['nombre_proveedor']) . '<br><font size="7">'. escape($compra['tipo']) . '</font></td>';
		if ($compra['plan_de_pagos'] == 'si'){ 
			$body_compras .= '<td class="left-right" align="right">' . number_format($compra['monto_total'], 2, '.', '') . '</td>';				
			$pagos = $db->query("select id_pago, movimiento_id, pd.tipo_pago, ifnull(SUM(monto),0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id where p.movimiento_id = '". $compra['id_ingreso'] . "' AND p.tipo='Ingreso' AND pd.estado='1' GROUP by movimiento_id")->fetch_first();
			if ($pagos) {
				if ($pagos['tipo_pago'] == 'Efectivo') 
					$total_compra = $total_compra + $pagos['subtotal']; 							
				else
						$total_compra_banco = $total_compra_banco + $compra['monto_total'];//revisar
					$body_compras .= '<td class="left-right" align="right">' . number_format($pagos['subtotal'], 2, '.', '') . '</td>';	
					$body_compras .= '<td class="left-right" align="right">' . $pagos['tipo_pago'] . '</td>';					
				}else{
					$body_compras .= '<td class="left-right" align="right">' . number_format(0, 2, '.', '') . '</td>';	
					$body_compras .= '<td class="left-right" align="right"><i> SIN PAGO </i></td>';												
				}
			}else{
				if ($compra['tipo_pago'] == 'Efectivo') {
					$total_compra = $total_compra + $compra['monto_total'];
					$body_compras .= '<td class="left-right" align="right">' . number_format($compra['monto_total'], 2, '.', '') . '</td>';
					$body_compras .= '<td class="left-right" align="right">' . number_format($compra['monto_total'], 2, '.', '') . '</td>';
					$body_compras .= '<td class="left-right" align="right">' . $compra['tipo_pago'] . '</td>';											
				}else{
					$total_venta_banco = $total_compra_banco + $compra['monto_total'];
					$body_compras .= '<td class="left-right" align="right">' . number_format($compra['monto_total'], 2, '.', '') . '</td>';
					$body_compras .= '<td class="left-right" align="right">' . number_format($compra['monto_total'], 2, '.', '') . '</td>';								
					$body_compras .= '<td class="left-right" align="right">' . $compra['tipo_pago'] . '</td>';																	
				}
			}
			$body_compras .= '</tr>';
		}
		$total_total_compra = (($total_compra_banco + $total_compra )) ? number_format(($total_compra_banco + $total_compra), 2, '.', ''): number_format(0, 2, '.', '');
		$total_compra = ($total_compra ) ? number_format($total_compra, 2, '.', ''): number_format(0, 2, '.', '');
		$total_compra_banco = ($total_compra_banco ) ? number_format($total_compra_banco, 2, '.', ''): number_format(0, 2, '.', '');
	}else{
		$body_compras .= '<tr>';
		$body_compras .= '<td class="left-right" colspan="6">No existe Compras.</td>';
		$body_compras .= '</tr>';			
		$total_compra = number_format(0, 2, '.', '');
		$total_compra_banco = number_format(0, 2, '.', '');
		$total_total_compra = number_format(0, 2, '.', '');
	}



//gastos
	$total_gasto_banco = 0;
	if ($gastos) {
		foreach ($gastos as $nro => $gasto) {
			$total_gasto = $total_gasto + $gasto['monto'];
			$body_gasto .= '<tr>';
			$body_gasto .= '<td class="left-right" align="right">' . $gasto['nro_comprobante'] . '</td>';
			$body_gasto .= '<td class="left-right">' . date_decode($gasto['fecha_movimiento'], $_institution['formato']) . '</td>';
			$body_gasto .= '<td class="left-right">' . escape($gasto['concepto']) . '</td>';
			$body_gasto .= '<td class="left-right" align="right">' . number_format($gasto['monto'], 2, '.', '') . '</td>';
			$body_gasto .= '<td class="left-right" align="right">' . number_format($gasto['monto'], 2, '.', '') . '</td>';
			$body_gasto .= '<td class="left-right" align="rigth">Efectivo</td>';
			$body_gasto .= '</tr>';
		}
		$total_total_gasto = (($total_gasto_banco + $total_gasto )) ? number_format(($total_gasto_banco + $total_gasto), 2, '.', ''): number_format(0, 2, '.', '');
		$total_gasto = ($total_gasto) ? number_format($total_gasto, 2, '.', ''):  number_format(0, 2, '.', '');;
		$total_gasto_banco = ($total_gasto_banco ) ? number_format($total_gasto_banco, 2, '.', ''): number_format(0, 2, '.', '');
	}else{
		$body_gasto .= '<tr>';
		$body_gasto .= '<td class="left-right" colspan="6">No existe Gastos.</td>';
		$body_gasto .= '</tr>';			
		$total_gasto = number_format(0, 2, '.', '');
		$total_gasto_banco = number_format(0, 2, '.', '');
		$total_total_gasto = number_format(0, 2, '.', '');
	}



// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 11);

// Salto de linea
	$pdf->Ln(1);
// Titulo del documento
	$pdf->Cell(0, 10, 'INGRESOS ', 0, true, 'L', false, '', 0, false, 'T', 'M');

// Salto de linea
	$pdf->Ln(3);

	$valor_moneda = $moneda;
	$total = 0;

// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 7);

// Formateamos la tabla
$tabla = <<<EOD
<style>
th {
background-color: #eee;
font-weight: bold;
}
.left-right {
border-left: 1px solid #444;
border-right: 1px solid #444;
}
.none {
border: 1px solid #fff;
}
.all {
border: 1px solid #444;
}
</style>
<table cellpadding="5">
<tr>
<th width="14%" class="all" align="right" rowspan = "2">Nº COMPROBANTE</th>
<th width="10%" class="all" align="left" rowspan = "2">FECHA</th>
<th width="36%" class="all" align="left" rowspan = "2">DETALLE</th>
<th width="12%" class="all" align="right" rowspan = "2">TOTAL CONCEPTO</th>
<th width="29%" class="all" align="center" colspan="2">TOTAL PAGADO</th>
</tr>
<tr>
<td class="all" align="center">MONTO</td>
<td class="all" align="center">TIPO PAGO</td>
</tr>
$body
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL EFECTIVO $valor_moneda</th>
<th class="all" align="right">$total_ingresos</th>
<th class="all" align="right">Efectivo</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL ENTIDAD FINANCIERA $valor_moneda</th>
<th class="all" align="right">$total_ingresos_banco</th>
<th class="all" align="right">Entidad Financiera</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">TOTAL DE TOTALES $valor_moneda</th>
<th class="all" align="right">$total_total_ingresos</th>
<th class="all" align="right">Monto Total</th>
</tr>
</table>
EOD;

// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');

	/*	VENTAS*/
// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 11);

// Titulo del documento
	$pdf->Cell(0, 10, 'VENTAS', 0, true, 'L', false, '', 0, false, 'T', 'M');
// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 7);
	// Salto de linea
	$pdf->Ln(3);

// Formateamos la tabla
$tabla_ventas = <<<EOD
<style>
th {
background-color: #eee;
font-weight: bold;
}
.left-right {
border-left: 1px solid #444;
border-right: 1px solid #444;
}
.none {
border: 1px solid #fff;
}
.all {
border: 1px solid #444;
}
</style>
<table cellpadding="5">
<tr>
<th width="14%" class="all" align="right"  rowspan = "2">Nº COMPROBANTE</th>
<th width="10%" class="all" align="left"  rowspan = "2">FECHA</th>
<th width="36%" class="all" align="left"  rowspan = "2">DETALLE</th>
<th width="12%" class="all" align="right"  rowspan = "2">TOTAL VENTA</th>
<th width="29%" class="all" align="center" colspan="2">TOTAL PAGADO</th>
</tr>
<tr>
<td class="all" align="center">MONTO</td>
<td class="all" align="center">TIPO PAGO</td>
</tr>
$body_ventas
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL EFECTIVO $valor_moneda</th>
<th class="all" align="right">$total_venta</th>
<th class="all" align="right">Efectivo</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL ENTIDAD FINANCIERA $valor_moneda</th>
<th class="all" align="right">$total_venta_banco</th>
<th class="all" align="right">Entidad Financiera</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">TOTAL DE TOTALES $valor_moneda</th>
<th class="all" align="right">$total_total_venta</th>
<th class="all" align="right">Monto Total</th>
</tr>
</table>
EOD;

// Imprime la tabla
	$pdf->writeHTML($tabla_ventas, true, false, false, false, '');

	/*	VENTAS*/
// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 11);

// Titulo del documento
	$pdf->Cell(0, 10, 'EGRESOS', 0, true, 'L', false, '', 0, false, 'T', 'M');
// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 7);
	// Salto de linea
	$pdf->Ln(3);

// Formateamos la tabla
$tabla_egresos = <<<EOD
<style>
th {
background-color: #eee;
font-weight: bold;
}
.left-right {
border-left: 1px solid #444;
border-right: 1px solid #444;
}
.none {
border: 1px solid #fff;
}
.all {
border: 1px solid #444;
}
</style>
<table cellpadding="5">
<tr>
<th width="14%" class="all" align="right"  rowspan = "2">Nº COMPROBANTE</th>
<th width="10%" class="all" align="left"  rowspan = "2">FECHA</th>
<th width="36%" class="all" align="left"  rowspan = "2">DETALLE</th>
<th width="12%" class="all" align="right"  rowspan = "2">TOTAL VENTA</th>
<th width="29%" class="all" align="center" colspan="2">TOTAL PAGADO</th>
</tr>
<tr>
<td class="all" align="center">MONTO</td>
<td class="all" align="center">TIPO PAGO</td>
</tr>
$body_egresos
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL EFECTIVO $valor_moneda</th>
<th class="all" align="right">$total_egresos</th>
<th class="all" align="right">Efectivo</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL ENTIDAD FINANCIERA $valor_moneda</th>
<th class="all" align="right">$total_egresos_banco</th>
<th class="all" align="right">Entidad Financiera</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">TOTAL DE TOTALES $valor_moneda</th>
<th class="all" align="right">$total_total_egresos</th>
<th class="all" align="right">Monto Total</th>
</tr>
</table>
EOD;

// Imprime la tabla
	$pdf->writeHTML($tabla_egresos, true, false, false, false, '');


	/*	VENTAS*/
// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 11);
// Titulo del documento
	$pdf->Cell(0, 10, 'COMPRAS', 0, true, 'L', false, '', 0, false, 'T', 'M');
// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 7);
	// Salto de linea
	$pdf->Ln(3);

// Formateamos la tabla
$tabla_egresos = <<<EOD
<style>
th {
background-color: #eee;
font-weight: bold;
}
.left-right {
border-left: 1px solid #444;
border-right: 1px solid #444;
}
.none {
border: 1px solid #fff;
}
.all {
border: 1px solid #444;
}
</style>
<table cellpadding="5">
<tr>
<th width="14%" class="all" align="right"  rowspan = "2">Nº COMPROBANTE</th>
<th width="10%" class="all" align="left"  rowspan = "2">FECHA</th>
<th width="36%" class="all" align="left"  rowspan = "2">DETALLE</th>
<th width="12%" class="all" align="right"  rowspan = "2">TOTAL VENTA</th>
<th width="29%" class="all" align="center" colspan="2">TOTAL PAGADO</th>
</tr>
<tr>
<td class="all" align="center">MONTO</td>
<td class="all" align="center">TIPO PAGO</td>
</tr>
$body_compras
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL EFECTIVO $valor_moneda</th>
<th class="all" align="right">$total_compra</th>
<th class="all" align="right">Efectivo</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL ENTIDAD FINANCIERA $valor_moneda</th>
<th class="all" align="right">$total_compra_banco</th>
<th class="all" align="right">Entidad Financiera</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">TOTAL DE TOTALES $valor_moneda</th>
<th class="all" align="right">$total_total_compra</th>
<th class="all" align="right">Monto Total</th>
</tr>
</table>
EOD;

// Imprime la tabla
	$pdf->writeHTML($tabla_egresos, true, false, false, false, '');


	/*	VENTAS*/
// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 11);

// Titulo del documento
	$pdf->Cell(0, 10, 'GASTOS', 0, true, 'L', false, '', 0, false, 'T', 'M');
// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 7);
	// Salto de linea
	$pdf->Ln(3);

// Formateamos la tabla
$tabla_gasto = <<<EOD
<style>
th {
background-color: #eee;
font-weight: bold;
}
.left-right {
border-left: 1px solid #444;
border-right: 1px solid #444;
}
.none {
border: 1px solid #fff;
}
.all {
border: 1px solid #444;
}
</style>
<table cellpadding="5">
<tr>
<th width="14%" class="all" align="right"  rowspan = "2">Nº COMPROBANTE</th>
<th width="10%" class="all" align="left"  rowspan = "2">FECHA</th>
<th width="36%" class="all" align="left"  rowspan = "2">DETALLE</th>
<th width="12%" class="all" align="right"  rowspan = "2">TOTAL VENTA</th>
<th width="29%" class="all" align="center" colspan="2">TOTAL PAGADO</th>
</tr>
<tr>
<td class="all" align="center">MONTO</td>
<td class="all" align="center">TIPO PAGO</td>
</tr>
$body_gasto
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL EFECTIVO $valor_moneda</th>
<th class="all" align="right">$total_gasto</th>
<th class="all" align="right">Efectivo</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">IMPORTE TOTAL ENTIDAD FINANCIERA $valor_moneda</th>
<th class="all" align="right">$total_gasto_banco</th>
<th class="all" align="right">Entidad Financiera</th>
</tr>
<tr>
<th class="all" align="rigth" colspan="4">TOTAL DE TOTALES $valor_moneda</th>
<th class="all" align="right">$total_total_gasto</th>
<th class="all" align="right">Monto Total</th>
</tr>
</table>
EOD;

// Imprime la tabla
	$pdf->writeHTML($tabla_gasto, true, false, false, false, '');	

// Genera el nombre del archivo
	$nombre = 'cierre_de_caja' . $fecha . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
	$pdf->Output($nombre, 'I');

	?>
