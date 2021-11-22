<?php

// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('ver', $permisos);

// Obtiene la proforma
$proforma = $db->select('p.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, e.telefono, pa.id_pago, ca.categoria')
				->from('inv_egresos p')
				->join('inv_almacenes a', 'p.almacen_id = a.id_almacen', 'left')
				->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
				->join('inv_clientes c', 'p.cliente_id = c.id_cliente', 'left')
				->join('inv_categorias_cliente ca', 'c.categoria_cliente_id = ca.id_categoria_cliente', 'left')
				->join('inv_pagos pa', 'pa.movimiento_id = p.id_egreso AND pa.tipo="Egreso"', 'left')
				->where('id_egreso', $id_proforma)
				->fetch_first();
				
$detallesCuota = $db->select('*, e.nombres, e.paterno, e.materno')
				    ->from('inv_pagos_detalles pd')
				    ->join('sys_empleados e', 'pd.empleado_id = e.id_empleado', 'left')
				    ->where('pd.pago_id', $proforma['id_pago'])
				    ->order_by('nro_cuota, fecha asc, fecha_pago asc')
				    ->fetch();

// $detallesCuota = $db->query("select")
// 				    ->fetch();
				    
// var_dump($detallesCuota);die();

// Verifica si existe el proforma
//if (!$proforma || $proforma['empleado_id'] != $_user['persona_id']) {
if (!$proforma) {
	// Error 404
	require_once not_found();
	exit;
} elseif (!$permiso_ver) {
	// Error 401
	require_once bad_request();
	exit;
}

// Obtiene los detalles
$detalles = $db->select('d.*, p.codigo, p.nombre, p.descripcion, u.unidad')
				->from('inv_egresos_detalles d')
				->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				->join('inv_asignaciones a', 'd.asignacion_id = a.id_asignacion', 'left')
				->join('inv_unidades u', 'a.unidad_id = u.id_unidad', 'left')
				->where('d.egreso_id', $id_proforma)
				->order_by('id_detalle asc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene datos generales
$telefono = str_replace(',', ' / ', escape($_institution['telefono']));
$correo = escape($_institution['correo']);

// Define datos de la proforma
$nro_proforma = escape($proforma['nro_factura']);
$fecha = upper(get_date_literal($proforma['fecha_egreso']));
$nombre_cliente = escape($proforma['nombre_cliente']);
$nit_ci = escape($proforma['nit_ci']);
$atencion = escape($proforma['descripcion']);
//$validez = date_decode(add_day($proforma['fecha_egreso'], intval($proforma['validez'])), $_institution['formato']);
$observacion = trim(escape($proforma['observacion']));
$monto_total = escape($proforma['monto_total']);
$total = 0;

// Datos del vendedor
$nombre_empleado = trim($proforma['nombres'] . ' ' . $proforma['paterno'] . ' ' . $proforma['materno']);
$nombre_empleado = ($nombre_empleado != '') ? $nombre_empleado : upper('ninguno');
$telefono_empleado = trim(str_replace(',', ' / ', escape($proforma['telefono'])));
$telefono_empleado = ($telefono_empleado != '') ? $telefono_empleado : upper('ninguno');
$categoria = escape($proforma['categoria']);
$almacen = upper($proforma['almacen']);

// Importa la libreria para generar el reporte
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Instancia el documento pdf
$pdf = new TCPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator($_institution['propietario']);
$pdf->SetAuthor($_institution['propietario']);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Define tamanos y fuentes
$font_name_main = 'roboto';
$font_name_data = 'roboto';
$font_size_main = 10;
$font_size_data = 8;

// Obtiene el ancho de la pagina
$width_page = $pdf->GetPageWidth();

// Define los margenes
$margin_left  = 30;
$margin_top  = 30;
$margin_right = 30;
$margin_bottom = 30;

// Define las cabeceras
$margin_header = 30;
$margin_footer = 30;

// Define el ancho de la pagina sin margenes
$width_page = $width_page - $margin_left - $margin_right;

// Asigna margenes
$pdf->SetMargins($margin_left, $margin_top, $margin_right);
$pdf->SetAutoPageBreak(true, $margin_bottom);

// Elimina las cabeceras
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('P');

// Adiciona la pagina
$pdf->AddPage();

$red=$_institution['color_r1'];
$green=$_institution['color_g1'];
$blue=$_institution['color_b1'];

$pdf->SetAlpha(0.5);
$pdf->SetFillColor(0, 0, 0);
//$pdf->RoundedRect(50, 140, 60, 60,'','1111', 'DF');
$pdf->Polygon(array(0,0,100,0,0,138,0,53), 'DF', 1, array(125, 125, 125), false);

// set alpha to semi-transparency
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(34, 84, 160);
$pdf->SetDrawColor(0, 0, 127);

//$pdf->RoundedRect(50, 140, 60, 60,'','1111', 'DF');
$pdf->Polygon(array(70,0,140,0,0,203,0,103), 'DF', 1, array($red,$green,$blue), false);
// set alpha to semi-transparency

//$pdf->RoundedRect(0, 17, 612, 85, $padding, '0000','DF');
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(222, 203, 120); 
//$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(222, 203, 120)));
$pdf->Polygon(array(0,45,425,45,405,93,0,93), 'DF', 1, array(225, 225, 225), false);

//$pdf->RoundedRect(0, 20, 612, 85, $padding, '0000','DF');
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(146, 25, 25); 
//$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(146, 25, 25)));
$pdf->Polygon(array(0,25,259,25,200,108,0,108), 'DF', 1, array(190, 190, 190), false);
//$pdf->RoundedRect(0, 17, 612, 85, $padding, '0000','DF');
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(222, 203, 120); 
//$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(222, 203, 120)));
$pdf->Polygon(array(0,128,350,128,330,173,0,173), 'DF', 1, array(225, 225, 225), false);

//$pdf->RoundedRect(0, 20, 612, 85, $padding, '0000','DF');
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(146, 25, 25); 
//$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(146, 25, 25)));
$pdf->Polygon(array(0,119,115,119,75,178,0,178), 'DF', 1, array(180, 180, 180), false);

//$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(34, 84, 160)));
//$pdf->RoundedRect(231, 0, 200, 110, $padding, '0000','DF');



$pdf->SetAlpha(0.5);
$pdf->SetFillColor(0, 0, 0);
//$pdf->RoundedRect(50, 140, 60, 60,'','1111', 'DF');
//imprime el adorno del final de la hoja
// $pdf->Polygon(array(540,792,570,792,598,732,569,732), 'DF', 1, array(125, 125, 125), false);

// set alpha to semi-transparency
$pdf->SetAlpha(0.5);
$pdf->SetFillColor(34, 84, 160);
$pdf->SetDrawColor(0, 0, 127);
//$pdf->RoundedRect(50, 140, 60, 60,'','1111', 'DF');
//imprime el adorno del final de la hoja
// $pdf->Polygon(array(618,650,550,792,618,792), 'DF', 1, array($red,$green,$blue), false);
// set alpha to semi-transparency






$pdf->SetAlpha(1);




// Define las variables
$width_image = 145;
$height_image = 145;
$rows = 9;
$padding = 5;
$height_cell = ($height_image - $padding) / $rows;
$width_table = ($width_page * (1 - ($width_image / $width_page))) - $padding;

// Define el margen interior de las celdas
$pdf->setCellPaddings($padding, $padding, $padding, $padding);

// Primera sección
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->SetXY($margin_left, $margin_top);
$pdf->Cell($width_table * 0.5, $height_cell, $_institution['nombre'], 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetTextColor(125, 125, 125);
$pdf->Cell($width_table * 0.5, $height_cell, 'Venta de herramientas en general', 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_table * 0.5, $height_cell, $_institution['horario_atencion_para_notas'], 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_table * 0.5, $height_cell, 'Telefonos: ' . $telefono, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($width_table * 0.5, $height_cell, 'Síguenos en Facebook: ' . 'ICABA Importaciones', 0, 1, 'L', 0, '', 1, true, 'T', 'M');

// Segunda sección
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($margin_left + ($width_table * 0.5), $margin_top);
$pdf->Cell($width_table * 0.5, $height_cell * 1, '', '', 1, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetTextColor(0,0,0);
$pdf->SetFont($font_name_data, '', 28);
$pdf->SetXY($margin_left + ($width_table * 0.5), $margin_top + $height_cell);
$pdf->Cell($width_table * 0.5, $height_cell * 2, 'NOTA DE ENTREGA', '', 1, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_main);
$pdf->SetXY($margin_left + ($width_table * 0.5), $margin_top + ($height_cell * 3));
$pdf->Cell($width_table * 0.5, $height_cell, 'Nro. ' . $nro_proforma, '', 1, 'C', 0, '', 1, true, 'T', 'M');
$pdf->SetXY($margin_left + ($width_table * 0.5), $margin_top + ($height_cell * 4));
$pdf->Cell($width_table * 0.5, $height_cell, '', '', 1, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding);

// Tercera sección
$pdf->SetTextColor(48, 48, 48);
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table, $height_cell,'                                                  '. $fecha, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.12, $height_cell, 'SEÑOR(ES):', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $nombre_cliente, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.12, $height_cell, 'NIT / CI:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $nit_ci, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.12, $height_cell, 'ALMACEN:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $almacen, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.15, $height_cell, 'TEL. VENDEDOR:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $telefono_empleado, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
//$pdf->Cell($width_table * 0.12, $height_cell, 'ATENCIÓN:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
//$pdf->SetFont($font_name_data, '', $font_size_data);
//$pdf->Cell($width_table * 0.38, $height_cell, $atencion, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
//$pdf->SetFont($font_name_data, 'B', $font_size_data);
//$pdf->Cell($width_table * 0.12, $height_cell, '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
//$pdf->SetFont($font_name_data, '', $font_size_data);
//$pdf->Cell($width_table * 0.38, $height_cell, '', 0, 1, 'L', 0, '', 1, true, 'T', 'M');
//$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.12, $height_cell, 'VENDEDOR:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $nombre_empleado, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, 'B', $font_size_data);
$pdf->Cell($width_table * 0.12, $height_cell, 'ATENCIÓN:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->SetFont($font_name_data, '', $font_size_data);
$pdf->Cell($width_table * 0.38, $height_cell, $atencion, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding);

// Cuarta sección
$pdf->SetTextColor(52, 73, 94);
$pdf->SetFont($font_name_data, 'B', $font_size_main);
$pdf->Cell($width_page * 0.13, $height_cell * 1.5, 'CÓDIGO', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.39, $height_cell * 1.5, 'DESCRIPCIÓN', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.12, $height_cell * 1.5, 'CANTIDAD', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.12, $height_cell * 1.5, 'UNIDAD', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.12, $height_cell * 1.5, 'P. UNITARIO', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.12, $height_cell * 1.5, 'SUBTOTAL', 0, 1, 'C', 0, '', 1, true, 'T', 'M');

// Imprime la imagen
//$imagen = (IMAGEN != '') ? institucion . '/' . escape($_institution['imagen_encabezado']) : imgs . '/empty.jpg' ;
//$pdf->Image($imagen, $margin_left + $width_table + $padding, $margin_top, 130, 70, 'jpg', '', 'T', false, false, '', false, false, 0, false, false, false);

// Define el estilo de los bordes
$border = array(
	'width' => 1,
	'cap' => 'butt',
	'join' => 'miter',
	'dash' => 0,
	'color' => array(52, 73, 94)
);

// Imprime los bordes
$pdf->SetLineStyle($border);
//$pdf->RoundedRect($margin_left, $margin_top, $width_table, $height_cell * 5, $padding, '1111');
//$pdf->RoundedRect($margin_left, $margin_top + ($height_cell * 5) + $padding, $width_table, $height_cell * 4, $padding, '1111');
$pdf->RoundedRect($margin_left, $margin_top + ($height_cell * 9) + ($padding * 2), $width_page, $height_cell * 1.5, $padding, '1111');

// Define el color de las lineas
$pdf->SetTextColor(48, 48, 48);

// Titulo del documento
$pdf->SetXY($margin_left, $margin_top + ($height_cell * 9) + ($padding * 3) + ($height_cell * 1.5));

// Estructura la tabla
$body = '';
foreach ($detalles as $nro => $detalle) {
	$cantidad = escape($detalle['cantidad']);
	$precio = escape($detalle['precio']);
	$descuento = escape($detalle['descuento']);
	$unidad = escape($detalle['unidad']);
	$importe = $cantidad * $precio;
	$total = $total + $importe;
	$body .= '<tr>';
	$body .= '<td width="13%" align="center">' . escape($detalle['codigo']) . '</td>';
	$body .= '<td width="39%" align="center"><b>' . escape($detalle['nombre']) . '</b></td>';
	$body .= '<td width="12%" align="right">' . $cantidad . '</td>';
	$body .= '<td width="12%" align="right">' . $unidad . '</td>';
	$body .= '<td width="12%" align="right">' . number_format($precio, 2, '.', ',') . '</td>';
	$body .= '<td width="12%" align="right"><b>' . number_format($importe, 2, '.', ',') . '</b></td>';
	$body .= '</tr>';
}
$total = number_format($total, 1, '.', '');

// Obtiene los datos del monto total
$conversor = new NumberToLetterConverter();
$monto_textual = explode('.', $total);
$monto_numeral = $monto_textual[0];
$monto_decimal = $monto_textual[1];
$monto_literal = upper($conversor->to_word($monto_numeral));

// Formatea la tabla en caso de tabla vacia
$body = ($body == '') ? '<tr><td colspan="5" align="center">Este egreso no tiene detalle, es muy importante que todos las egresos cuenten con un detalle de venta.</td></tr>' : $body;

// Formateamos la tabla
$tabla = '<style>
table { margin: 0px; }
th { background-color: #eee; font-weight: bold; }
td { border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; }
</style>
<table cellpadding="' . $padding . '">' . $body . '</table>';

// Asigna la fuente
$pdf->SetFont($font_name_data, '', $font_size_data);




// Imprime la tabla
$pdf->writeHTML($tabla, true, false, false, false, '');


// Cuarta sección
$pdf->SetTextColor(52, 73, 94);
$pdf->SetFont($font_name_data, 'B', $font_size_main);

//muestra descuento en caso de existir un descuento
if($proforma['descuento'] != 0){
    // Obtiene la posicion vertical final
    $final = $pdf->getY() - 18;
    
    // Asigna la posicion final
    $pdf->SetXY($margin_left, $final + $padding);
    
    $pdf->Ln($padding);
    
    $pdf->Cell($width_page * 0.76, $height_cell * 1, 'IMPORTE SIN DESCUENTO ' . $moneda . ' / ' . $monto_literal . ' ' . $monto_decimal . '/100', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.24, $height_cell * 1, number_format($total, 1, '.', ','), 0, 1, 'R', 0, '', 1, true, 'T', 'M');

    // Obtiene los datos del descuento
    $descuento = ($total * $proforma['descuento']) / 100 ;
    $descuento_total = $total - $descuento;
											
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $descuento);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = upper($conversor->to_word($monto_numeral));

    $pdf->Cell($width_page * 0.76, $height_cell * 1, 'DESCUENTO DEL '.escape(number_format($proforma['descuento']), 0) . " % " . $moneda . ' / ' . $monto_literal . ' ' . $monto_decimal . '/100', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.24, $height_cell * 1, number_format($descuento, 1, '.', ','), 0, 1, 'R', 0, '', 1, true, 'T', 'M');
    
    // Obtiene los datos del monto total con descuento
    
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $descuento_total);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = upper($conversor->to_word($monto_numeral));
    
    $pdf->Cell($width_page * 0.76, $height_cell * 1, 'IMPORTE TOTAL ' . $moneda . ' / ' . $monto_literal . ' ' . $monto_decimal . '/100', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.24, $height_cell * 1, number_format($descuento_total, 1, '.', ','), 0, 1, 'R', 0, '', 1, true, 'T', 'M');
    
    $pdf->Ln($padding);
    $pdf->RoundedRect($margin_left, $final + $padding, $width_page, $height_cell * 3.7, $padding, '1111');
}else{
    // Obtiene la posicion vertical final
    $final = $pdf->getY() - 18;
    
    // Asigna la posicion final
    $pdf->SetXY($margin_left, $final + $padding);
    
    $pdf->Cell($width_page * 0.76, $height_cell * 1.5, 'IMPORTE TOTAL ' . $moneda . ' / ' . $monto_literal . ' ' . $monto_decimal . '/100', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.24, $height_cell * 1.5, number_format($total, 1, '.', ','), 0, 1, 'R', 0, '', 1, true, 'T', 'M');
    $pdf->RoundedRect($margin_left, $final + $padding, $width_page, $height_cell * 1.5, $padding, '1111');
}

// Asigna la fuente y color
$pdf->SetFont($font_name_data, '', $font_size_main);
$pdf->SetTextColor(48, 48, 48);

// Salto de linea
$pdf->Ln($padding);


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (escape($proforma['plan_de_pagos'])=="si"){
    
    $pdf->Ln($padding);
    $pdf->SetFont($font_name_data, 'B', $font_size_data);
    $pdf->Cell($width_table * 0.2, $height_cell * 1.3, 'DETALLE DE CUOTAS:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    
    $pdf->Ln($padding);
    $pdf->Ln($padding);
    // $final = $pdf->getY() - 60;
    $pdf->SetTextColor(52, 73, 94);
    $pdf->SetFont($font_name_data, 'B', $font_size_main);
    $pdf->Cell($width_page * 0.15, $height_cell * 2.5, 'Descripción', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.15, $height_cell * 2.5, 'Fecha programada', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.15, $height_cell * 2.5, 'Fecha de pago', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.12, $height_cell * 2.5, 'Tipo de pago', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.09, $height_cell * 2.5, 'Monto', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.13, $height_cell * 2.5, 'Estado', 0, 0, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Cell($width_page * 0.20, $height_cell * 2.5, 'Cobrador', 0, 1, 'C', 0, '', 1, true, 'T', 'M');
    $pdf->Ln($padding);
    // Define el estilo de los bordes
    $border = array(
        'width' => 1,
        'cap' => 'butt',
        'join' => 'miter',
        'dash' => 0,
        'color' => array(52, 73, 94)
    );
    $final = $pdf->getY() - 41;
    
    // Imprime los bordes
    $pdf->SetLineStyle($border);
    //$pdf->RoundedRect($margin_left, $margin_top, $width_table, $height_cell * 5, $padding, '1111');
    //$pdf->RoundedRect($margin_left, $margin_top + ($height_cell * 5) + $padding, $width_table, $height_cell * 4, $padding, '1111');
    $pdf->RoundedRect($margin_left, $final + $padding, $width_page, $height_cell * 1.5, $padding, '1111');

    // Define el color de las lineas
    $pdf->SetTextColor(48, 48, 48);

    // Titulo del documento
    $final = $pdf->getY() - 13;
    $pdf->SetXY($margin_left, $final + $padding, $width_page, $height_cell * 1.5, $padding, '1111');//9,3,1.5//TABLA
    
                            
    // Estructura la tabla
    $total_pendiente = 0;
    $body_plan_pagos = '';
    foreach ($detallesCuota as $nro => $detalle) {
        
        $fecha_programada = date_decode($detalle['fecha'], $_institution['formato']);
        $fecha_pago = date_decode($detalle['fecha_pago'], $_institution['formato']);
        $fecha_pago = $fecha_pago != '00-00-0000' ? $fecha_pago : '-';
        $tipo = $detalle['tipo_pago'] ? $detalle['tipo_pago'] : '-';
        $monto_pago = number_format($detalle['monto'], 2, '.', '');
        $cobrador = $detalle['paterno'] ? escape($detalle['paterno']." ".$detalle['materno']." ".$detalle['nombres']) : '-';
        $nro_cuota = ($nro+1) == 1 ? "Cuota Inicial " : "Cuota # ".($nro+1);
        if($detalle['estado']==0){
            $estado = 'Pendiente';
            $total_pendiente = $total_pendiente + $monto_pago;
        }else{
            $estado = 'Cancelado';
        }
        
        $body_plan_pagos .= '<tr>';
        $body_plan_pagos .= '<td width="15%" align="center"><b>' . $nro_cuota . '</b></td>';
        $body_plan_pagos .= '<td width="15%" align="center">' . $fecha_programada . '</td>';
        $body_plan_pagos .= '<td width="15%" align="center">' . $fecha_pago . '</td>';
        // $body_plan_pagos .= '<td width="12%" align="right">' . escape(date_decode($detalle['fecha_pago'], $_institution['formato']) . '</td>';
        $body_plan_pagos .= '<td width="12%" align="center">' . $tipo . '</td>';
        $body_plan_pagos .= '<td width="10%" align="center">' . $monto_pago . '</td>';
        $body_plan_pagos .= '<td width="12%" align="center"><b>' . $estado . '</b></td>';
        $body_plan_pagos .= '<td width="21%" align="center"><b>' . $cobrador . '</b></td>';
        $body_plan_pagos .= '</tr>';
    }

    // Formatea la tabla en caso de tabla vacia
    $body_plan_pagos = ($body_plan_pagos == '') ? '<tr><td colspan="5" align="center">Este egreso no tiene detalle, es muy importante que todos las egresos cuenten con un detalle de venta.</td></tr>' : $body_plan_pagos;

    // Formateamos la tabla
    $tabla = '<style>
    table { margin: 0px; }
    th { background-color: #eee; font-weight: bold; }
    td { border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; }
    </style>
    <table cellpadding="' . $padding. '">' . $body_plan_pagos . '</table>';

    // Asigna la fuente
    $pdf->SetFont($font_name_data, '', $font_size_data);

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $total_pendiente);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = upper($conversor->to_word($monto_numeral));

    // Imprime la tabla
    $pdf->writeHTML($tabla, true, false, false, false, '');
    
    $pdf->SetTextColor(52, 73, 94);
    $pdf->SetFont($font_name_data, 'B', $font_size_main);
    if($total_pendiente!=0){
        $pdf->Cell($width_page * 0.76, $height_cell * 0, 'DEUDA PENDIENTE ' . $moneda . ' / ' . $monto_literal . ' ' . $monto_decimal . '/100', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    }else{
        $pdf->Cell($width_page * 0.76, $height_cell * 0, 'DEUDA SALDADA', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    }
    $pdf->Cell($width_page * 0.24, $height_cell * 0, number_format($total_pendiente, 1, '.', ','), 0, 1, 'R', 0, '', 1, true, 'T', 'M');
    //IMPRIME ÚLTIMO CUADRO (DEUDA)
    $final = $pdf->getY() - 17;
    $pdf->RoundedRect($margin_left, $final + $padding, $width_page, $height_cell * 1.5, $padding, '1111');
    
    // Asigna la fuente y color
    $pdf->SetFont($font_name_data, '', $font_size_main);
    $pdf->SetTextColor(48, 48, 48);

}


// Verifica si existe una observacion
if ($observacion != '') {
    $pdf->Ln($padding);
    $pdf->Ln($padding);
    // Obtiene la posicion vertical final
    $final = $pdf->getY() - 5;

    
    // Asigna la posicion final
    $pdf->SetXY($margin_left, $final + $padding);
    
	// Imprime la tabla
	$pdf->writeHTML('<table><tr><td><br><br><u><b>OBSERVACIÓN:</b></u><br></td></tr><tr><td align="justify">' . $observacion . '</td></tr></table>', true, false, false, false, '');
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Imprime el footer
//$pdf->writeHTML('<img src="' . imgs . '/footer.jpg" width="' . $width_page . '">', true, false, false, false, '');

// Genera el nombre del archivo
$nombre = 'proforma_' . $id_proforma . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
