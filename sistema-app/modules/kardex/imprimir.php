<?php

// Obtiene el id_egreso
$id_almacen = (isset($params[0])) ? $params[0] : 0;
$id_producto = (isset($params[1])) ? $params[1] : 0;

//busca todos los movimientos del producto
$movimientos = $db->query("select m.*, ifnull(concat(e.nombres, ' ', e.paterno, ' ', e.materno), '') as empleado 
							from (
								select i.id_ingreso as id_movimiento, d.id_detalle, i.fecha_ingreso as fecha_movimiento, i.hora_ingreso as hora_movimiento, i.descripcion, d.cantidad, d.costo as monto, d.asignacion_id as asignacion_id, 'i' as tipo, i.empleado_id, i.almacen_id , '' as estado
								from inv_ingresos_detalles d 
								Left join inv_ingresos i on d.ingreso_id = i.id_ingreso 
								where d.producto_id = $id_producto  
									
								union 
								
								select e.id_egreso as id_movimiento, d.id_detalle, e.fecha_egreso as fecha_movimiento, e.hora_egreso as hora_movimiento, e.descripcion, d.cantidad, d.precio as monto, d.asignacion_id as asignacion_id, 'e' as tipo, e.empleado_id, e.almacen_id, e.estado 
								from inv_egresos_detalles d 
								left join inv_egresos e on d.egreso_id = e.id_egreso 
								where d.producto_id = $id_producto  
									
							) m 
							left join sys_empleados e on m.empleado_id = e.id_empleado 
							where m.almacen_id = $id_almacen 
							order by m.fecha_movimiento asc, m.hora_movimiento asc")->fetch();

if (!$movimientos) {
	// Error 404
	require_once not_found();
	exit;
}

//busca los precios del producto
$consulta_asignaciones = $db->query("SELECT a.id_asignacion, u.unidad, a.precio_actual
															 FROM inv_asignaciones a
															 LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
															 WHERE a.producto_id = $id_producto")->fetch();

// Obtiene el almacen
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

$codigo=$producto['codigo'];
$nombre=$producto['nombre'];
$almacen_nombre=escape($almacen['almacen']); 
$direccion=escape($almacen['direccion']); 
$principal=($almacen['principal'] == 'S') ? 'Si' : 'No'; 
$precio='';
foreach ($consulta_asignaciones as $key => $asignaciones) {
	$precio = $precio . $asignaciones['unidad'] . ": " . $asignaciones['precio_actual'] ." ";
}

//$principal=$almacen['principal']; 

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Instancia el documento pdf
$pdf = new TCPDF('L', 'pt', 'LETTER', true, 'UTF-8', false);
//pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Define variables globales
define('DIRECCION', escape($_institution['pie_pagina']));
define('IMAGEN', escape($_institution['imagen_encabezado']));
define('ATENCION', 'Lun. a Vie. de 08:30 a 18:30 y Sáb. de 08:30 a 13:00');
define('PIE', escape($_institution['pie_pagina']));
define('TELEFONO', escape(str_replace(',', ', ', $_institution['telefono'])));
//define('TELEFONO', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Define tamanos y fuentes
$font_name_main = 'roboto';
$font_name_data = 'roboto';
$font_size_main = 8;
$font_size_data = 7;

// Obtiene el ancho de la pagina
$width_page = $pdf->GetPageWidth();

// Define los margenes
$margin_left = $margin_right = 30;
$margin_top = 30;
//$margin_left2 = $margin_right = 60;
$margin_bottom = 0;
// Define las cabeceras
$margin_header = 30;
$margin_header = 60;
$margin_footer2 = 30;
$margin_footer = 1;

// Define el ancho de la pagina sin margenes
$width_page = $width_page - $margin_left - $margin_right;
// Define el ancho de la pagina sin margenes
$width_page2 = $margin_left + $margin_right ;

// Asigna margenes
$pdf->SetMargins($margin_left, $margin_top, $margin_right);
$pdf->SetAutoPageBreak(true, $margin_bottom);

// Elimina las cabeceras
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);


// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('L');

// Adiciona la pagina
$pdf->AddPage();


// imagem de agua
// nivel de opacidad
//$pdf->SetAlpha(0.1);
// poner la imagen de agua
//$pdf->Image(IMGS . '/image-agua.png', 170, 320, 300, 100, '', '', '', true, 72);



// Define las variables
$width_image = 145;
$height_image = 50;
$rows = 9;
$padding = 5;
$height_cell = ($height_image - $padding) / $rows;
$width_table = ($width_page * (1 - ($width_image / $width_page))) - $padding; //19CM //230
//$width_table = ($width_page * (1 - ($width_image / $width_page))) - $padding;
//$width_table2= ($width_image + $padding + 900 + 900 + 900 );
//$width_table3= ($padding);
$pru = (30);
$pdf->SetAlpha(1);
// Define el margen interior de las celdas
$pdf->setCellPaddings($padding, $padding, $padding, $padding);

// Primera sección
$pdf->SetTextColor(48, 48, 48);
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->SetXY($margin_left , $margin_top);
$pdf->Ln($padding+2);
// Cuarta sección
$pdf->Ln($padding-45);

// Segunda sección
$pdf->SetTextColor(52, 73, 94);
$pdf->SetFont($font_name_data, '', 12);

$pdf->Cell($width_page * 1.1, $height_cell * 12.5, ' KARDEX VALORADO ' ,  0, 0, 'C', 0, '', 1, true, 'T', 'M');

$pdf->SetFont($font_name_data, 'B', 8);
$pdf->Ln($padding+13);





$pdf->Ln($padding+29);


// Tercera sección
$pdf->SetTextColor(48, 48, 48);


$pdf->SetFont($font_name_data, 'B', $font_size_data);

$pdf->Ln($padding);
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page* 0.30, $height_cell * 0.1, 'CODIGO: ', 0, 0, 'R', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page* 0.25, $height_cell * 0.1, '', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page * 0.10, $height_cell * 0.1, 'ALMACEN:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding);
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page * 0.30, $height_cell * 1.2 , 'PRODUCTO:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page* 0.25, $height_cell * 0.1, '', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page * 0.10, $height_cell * 1.2, 'DIRECCIÓN:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding);
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page * 0.30, $height_cell * 2.3 , 'PRECIOS:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page* 0.25, $height_cell * 0.1, '', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_page * 0.10, $height_cell * 2.3, 'PRINCIPAL:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding-5);
$pdf->SetFont($font_name_data, '', $font_size_data);
//$pdf->Cell($width_page *0.80, $height_cell * 1.30,  $fecha , 0, 0, 'C', 0, '', 1, true, 'T', 'M');
//$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.30, $height_cell * -3.5,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page*0.10, $height_cell * -3.5,  $codigo, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.65, $height_cell * -3.5,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 1.35, $height_cell * -3.5, $almacen_nombre, 0, 0, 'L', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.30, $height_cell * -0.7,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page*0.50, $height_cell * -0.7, $nombre, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.65, $height_cell * -0.7,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 1.35, $height_cell * -0.7, $direccion, 0, 0, 'L', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.30, $height_cell * 2.3,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.25, $height_cell * 2.3, $precio, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.65, $height_cell * 2.3,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 1.35, $height_cell * 2.3, $principal, 0, 0, 'L', 0, '', 1, true, 'T', 'M');

// Imprime la imagen
$imagen = (IMAGEN != '') ? institucion . '/' . escape($_institution['imagen_encabezado']) : imgs . '/empty.jpg' ;
$pdf->Image($imagen, $margin_left , '40', '160', '40', 'jpg', '', 'T', false, false, '', false, false, 0, false, false, false);

// Define el estilo de los bordes
$border = array(
	'width' => 1,

	'cap' => 'butt',
	'join' => 'miter',
	'dash' => 0,
	'color' => array(52, 73, 94)
);

// Define el color de las lineas
$pdf->SetTextColor(48, 48, 48);

// Titulo del documento
$pdf->SetXY($margin_left, $margin_top + ($height_cell * 9) + ($padding * 3) + ($height_cell * 1.5));

// Estructura la tabla

$body = '';
$body .= '<thead><tr>';
$body .= '<th class="none" width="4%" align="center" rowspan="2">#</th>';
$body .= '<th class="none" width="8%" align="center" rowspan="2">FECHA</th>';
$body .= '<th class="none" width="15%" align="center" rowspan="2">DESCRIPCION</th>';		
$body .= '<th class="none" width="23%" align="center" colspan="4">ENTRADAS</th>';
$body .= '<th class="none" width="23%" align="center" colspan="4">SALIDAS</th>';
$body .= '<th class="none" width="15%" align="center" colspan="2">SALDOS</th>';
$body .= '<th class="none" width="10%" align="center" rowspan="2">EMPLEADO</th>';
$body .= '</tr>';

$body .= '<tr>';
$body .= '<th class="none" width="5%" align="center" colspan="3">Cant.</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">Unidad</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">Costo</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">TOTAL</th>';

$body .= '<th class="none" width="5%" align="center" colspan="3">Cant.</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">Unidad</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">Costo</th>';
$body .= '<th class="none" width="6%" align="center" colspan="3">TOTAL</th>';

$body .= '<th class="none" width="7%" align="center" colspan="3">CANTIDAD</th>';
$body .= '<th class="none" width="8%" align="center" colspan="3">TOTAL</th>';
$body .= '</tr></thead>';

$saldo_cantidad = 0; 
$saldo_costo = 0;
$ingresos = array(); 
				
foreach ($movimientos as $nro => $movimiento) { 
	if ($movimiento['tipo'] == 'i') {
		$descripcion=(escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén': escape($movimiento['descripcion']);

		$body .= '<tbody><tr>';
		$body .= '<td class="none" width="4%" align="center">' . escape($nro + 1) . '</td>';
		$body .= '<td class="none" width="8%" align="center">' . escape($movimiento['fecha_movimiento']) .' '.escape($movimiento['hora_movimiento']).'</td>';
		$body .= '<td class="none" width="15%" align="justify">' . $descripcion . '</td>';
		
		$body .= '<td class="none" width="5%" align="right" style="background-color:#d0e9c6;">' . escape($movimiento['cantidad']) . '</td>';
		$body .= '<td class="none" width="6%" align="right" style="background-color:#d0e9c6; padding:0;">';
								
			$asignacion_entrada = $movimiento['asignacion_id'];
			$unidad_compra = $db->query("SELECT a.id_asignacion, u.unidad, u.tamanio
										 FROM inv_asignaciones a 
										 LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
										 WHERE a.id_asignacion = $asignacion_entrada")->fetch_first();
			$tamanio = number_format($unidad_compra['tamanio'], 0);
			if($tamanio == 0){
				$tamanio = 1;
			}
			$saldo_cantidad = $saldo_cantidad + ($movimiento['cantidad'] * $tamanio);
			$saldo_costo = $saldo_costo + ($movimiento['cantidad'] * $movimiento['monto']);

			array_push($ingresos, array('cantidad' => ($movimiento['cantidad']*$tamanio), 'costo' => ($movimiento['monto']/$tamanio) ) ); 		

		$body .= escape($unidad_compra['unidad']) . '</td>';
		$body .= '<td class="none" width="6%" align="right" style="background-color:#d0e9c6;">' . escape($movimiento['monto']) . '</td>';
		$body .= '<td class="none" width="6%" align="right" style="background-color:#d0e9c6;">' . number_format(($movimiento['cantidad'] * $movimiento['monto']), 2, '.', '') . '</td>';
		
		$body .= '<td class="none" width="5%" align="right"></td>';
		$body .= '<td class="none" width="6%" align="right"></td>';
		$body .= '<td class="none" width="6%" align="right"></td>';
		$body .= '<td class="none" width="6%" align="right"></td>';
		$body .= '<td class="none" width="7%" align="right" style="background-color:#c4e3f3">' . $saldo_cantidad . '</td>';
		$body .= '<td class="none" width="8%" align="right" style="background-color:#c4e3f3">' . number_format($movimiento['cantidad'] * $movimiento['monto'], 2, '.', '') . '</td>';
		$body .= '<td class="none" width="10%" align="right">' . escape($movimiento['empleado']) . '</td>';
		$body .= '</tr>';
	}else{
	$ciclo = true;

	$asignacion_entrada = $movimiento['asignacion_id'];							
	$unidad_venta = $db->query("SELECT a.id_asignacion, u.unidad, u.tamanio, a.producto_id
										 FROM inv_asignaciones a 
										 LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
										 WHERE a.id_asignacion = $asignacion_entrada")->fetch_first();
	
	$unidad_simple = $db->query("SELECT u.unidad
										 FROM inv_asignaciones a 
										 LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
										 WHERE tamanio=1 AND a.producto_id = '".$unidad_venta['producto_id']."'")->fetch_first();
	if(!$unidad_simple){
		$unidad_simple['unidad']="Unidad";
	}

	$tamanio = number_format($unidad_venta['tamanio'], 0);
	if($tamanio == 0){
		$tamanio = 1;
	}

	$cantidad_BD=$movimiento['cantidad']*$tamanio;

	do {
			$ingreso = array_shift($ingresos);
									
			//$saldo_costo = $saldo_costo - ($movimiento['cantidad'] * $movimiento['monto']); 
			
			if ($ingreso['cantidad'] >= $cantidad_BD ){
				$ingreso['cantidad'] = $ingreso['cantidad'] - $cantidad_BD;
				if ($ingreso['cantidad'] > 0) {
					array_unshift($ingresos, $ingreso);
				}
				
				$ciclo = false;
				$saldo_cantidad = $saldo_cantidad - $cantidad_BD;
				$saldo_costo = $saldo_costo - ($cantidad_BD * $ingreso['costo']);
				
				$body .= '<tr>';
				$body .= '<td class="none" width="4%" align="center">' . escape($nro + 1) . '</td>';
				$body .= '<td class="none" width="8%" align="center">' . escape($movimiento['fecha_movimiento']) .' '.escape($movimiento['hora_movimiento']).'</td>';
				$body .= '<td class="none" width="15%" align="justify">' . $descripcion . '</td>';
				
				$body .= '<td class="none" width="5%" align="right"></td>';
				$body .= '<td class="none" width="6%" align="right"></td>';
				$body .= '<td class="none" width="6%" align="right"></td>';
				$body .= '<td class="none" width="6%" align="right"></td>';
				$body .= '<td class="none" width="5%" align="right" style="background-color:#ebcccc">' . escape($cantidad_BD) . '</td>';
				$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">';
				$body .= escape($unidad_simple['unidad']) . '</td>';
				$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">' . escape($ingreso['costo']) . '</td>';
				$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">'.number_format( ($cantidad_BD*$movimiento['monto']), 2, '.', '') . '</td>';
				
				$body .= '<td class="none" width="7%" align="right" style="background-color:#c4e3f3">' . $saldo_cantidad . '</td>';
				$body .= '<td class="none" width="8%" align="right" style="background-color:#c4e3f3">' . number_format($saldo_costo, 2, '.', '') . '</td>';
				$body .= '<td class="none" width="10%" align="right">' . escape($movimiento['empleado']) . '</td>';
				$body .= '</tr>';		
			} else {
				if($ingreso['cantidad']!=0){
					$saldo_cantidad = $saldo_cantidad - $ingreso['cantidad'];
					$saldo_costo = $saldo_costo - ($ingreso['cantidad'] * $ingreso['costo']); 
					
					$body .= '<tr>';
					$body .= '<td class="none" width="4%" align="center">' . escape($nro + 1) . '</td>';
					$body .= '<td class="none" width="8%" align="center">' . escape($movimiento['fecha_movimiento']) .' '.escape($movimiento['hora_movimiento']).'</td>';
					$body .= '<td class="none" width="15%" align="justify">' . $descripcion . '</td>';
					
					$body .= '<td class="none" width="5%" align="right"></td>';
					$body .= '<td class="none" width="6%" align="right"></td>';
					$body .= '<td class="none" width="6%" align="right"></td>';
					$body .= '<td class="none" width="6%" align="right"></td>';
					$body .= '<td class="none" width="5%" align="right" style="background-color:#ebcccc">' . escape($ingreso['cantidad']) . '</td>';
					$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">';
					$body .= escape($unidad_simple['unidad']) . '</td>';
					$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">' . escape($ingreso['costo']) . '</td>';
					$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">' . number_format(($ingreso['cantidad'] * $ingreso['costo']), 2, '.', '') . '</td>';
					
					$body .= '<td class="none" width="7%" align="right" style="background-color:#c4e3f3">' . $saldo_cantidad . '</td>';
					$body .= '<td class="none" width="8%" align="right" style="background-color:#c4e3f3">' . number_format($saldo_costo, 2, '.', '') . '</td>';
					$body .= '<td class="none" width="10%" align="right">' . escape($movimiento['empleado']) . '</td>';
					$body .= '</tr>';		
						
					$cantidad_BD = $cantidad_BD - $ingreso['cantidad'];
				}else{
					if(count($ingresos)==0){								
						$saldo_cantidad = $saldo_cantidad - $cantidad_BD;
						
						$body .= '<tr>';
						$body .= '<td class="none" width="4%" align="center">' . escape($nro + 1) . '</td>';
						$body .= '<td class="none" width="8%" align="center">' . escape($movimiento['fecha_movimiento']) .' '.escape($movimiento['hora_movimiento']).'</td>';
						$body .= '<td class="none" width="15%" align="justify">' . $descripcion . '</td>';
						
						$body .= '<td class="none" width="5%" align="right"></td>';
						$body .= '<td class="none" width="6%" align="right"></td>';
						$body .= '<td class="none" width="6%" align="right"></td>';
						$body .= '<td class="none" width="6%" align="right"></td>';
						$body .= '<td class="none" width="5%" align="right" style="background-color:#ebcccc">' . escape($saldo_cantidad) . '</td>';
						$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">';
						$body .= escape($unidad_simple['unidad']) . '</td>';
						$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">0.00</td>';
						$body .= '<td class="none" width="6%" align="right" style="background-color:#ebcccc">0.00</td>';
						
						$body .= '<td class="none" width="7%" align="right" style="background-color:#c4e3f3">' . $saldo_cantidad . '</td>';
						$body .= '<td class="none" width="8%" align="right" style="background-color:#c4e3f3">0.00</td>';
						$body .= '<td class="none" width="10%" align="right">' . escape($movimiento['empleado']) . '</td>';
						$body .= '</tr>';		

						$movimiento['cantidad'] = $cantidad_BD - $ingreso['cantidad'];
						$ciclo=false;
					}else{
						//jhf
					}
				}
			}
		} while ($ciclo); 
					
	} 
}
$total = number_format($total, 2, '.', '');

// Formatea la tabla en caso de tabla vacia
$body = ($body == '') ? '<tr><td colspan="5" align="center">Este egreso no tiene detalle, es muy importante que todos las egresos cuenten con un detalle de venta.</td></tr>' : $body;

// Formateamos la tabla
$tabla = '<style>
	th {
		background-color: #eee;
		font-weight: bold;
	}
	.left-right {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	.none {
		border: 1px solid #000;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
<table cellpadding="' . $padding . '">' . $body . '</table>';

// Asigna la fuente
$pdf->SetFont($font_name_data, '', $font_size_data);

// Imprime la tabla
$pdf->writeHTML($tabla, true, false, false, false, '');

// Obtiene la posicion vertical final
$final = $pdf->getY() - 18;

// Asigna la posicion final
$pdf->SetXY($margin_left , $final + $padding);

// Cuarta sección
$pdf->SetTextColor(52, 73, 94);
$pdf->SetFont($font_name_data, 'B', $font_size_main);

// Asigna la fuente y color
$pdf->SetFont($font_name_data, '', $font_size_main);
$pdf->SetTextColor(48, 48, 48);

// Salto de linea
$pdf->Ln($padding);

// Genera el nombre del archivo
$nombre = 'kardex_valorado_' . $id_egreso . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
