<?php

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0; 

if ($id_egreso == 0) { 
	// Obtiene las egresos
	$egresos = $db->select('p.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
					->from('inv_egresos p')
					->join('inv_almacenes a', 'p.almacen_id = a.id_almacen', 'left')
					->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
					->where('p.empleado_id', $_user['persona_id'])
					->order_by('p.fecha_egreso desc, p.hora_egreso desc')
					->fetch();
} else {
	// Obtiene los permisos
	$permisos = explode(',', permits);  
	
	// Almacena los permisos en variables
	$permiso_ver = in_array('ver', $permisos);
	
	// Obtiene la egreso
	$egreso = $db->select('p.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
				  ->from('inv_egresos p')
				  ->join('inv_almacenes a', 'p.almacen_id = a.id_almacen', 'left')
				  ->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
				  ->where('id_egreso', $id_egreso)
				  ->fetch_first();
	
	// Verifica si existe el egreso
	if (!$egreso || $egreso['empleado_id'] != $_user['persona_id']) {
		// Error 404
		require_once not_found();
		exit;
	} elseif (!$permiso_ver) {
		// Error 401
		require_once bad_request();
		exit;
	}

	// Obtiene los detalles
	$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')
				   ->from('inv_egresos_detalles d')
				   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				   ->where('d.egreso_id', $id_egreso)
				   ->order_by('id_detalle asc')
				   ->fetch();
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';
require_once libraries . '/tcpdf/tcpdf_barcodes_2d.php';
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Define variables globales
define('direccion', escape($_institution['pie_pagina']));
define('imagen', escape($_institution['imagen_encabezado']));
define('atencion', 'Lun. a Vie. de 08:30 a 18:30 y Sáb. de 08:30 a 13:00');
define('pie', escape($_institution['pie_pagina']));
define('telefono', escape(str_replace(',', ', ', $_institution['telefono'])));
//define('telefono', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
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
$pdf->SetMargins(30, 20, 30);

// Elimina las cabeceras
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);


// ------------------------------------------------------------

if ($id_egreso == 0) {
} else {
	// Documento individual --------------------------------------------------
	
	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);
	

	// Titulo del documento
	//$pdf->Cell(0, 10, 'FACTURA', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	//$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);
	
	// Define las variables
	$valor_fecha = escape(date_decode($egreso['fecha_egreso'], $_institution['formato']) . ' ' . $egreso['hora_egreso']);
	$valor_nombre_cliente = escape($egreso['nombre_cliente']);
	$valor_nit_ci = escape($egreso['nit_ci']);
	$valor_nro_egreso = escape($egreso['nro_factura']);
	$valor_monto_total = escape($egreso['monto_total']);
	$valor_nro_registros = escape($egreso['nro_factura']);
	$valor_almacen = escape($egreso['almacen']);
	$valor_empleado = escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']);
	// $valor_descuento_global = escape($egreso['descuento_bs']);
    // $valor_descuento_porcentaje = (isset($egreso['descuento_porcentaje'])) ? clear($egreso['descuento_porcentaje']) : '';
	$valor_moneda = $moneda;
	$total = 0;

	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

	// Estructura la tabla
	$body = '';$total_con_descuento=0;
	foreach ($detalles as $nro => $detalle) {
		$cantidad = escape($detalle['cantidad']);
		$precio = escape($detalle['precio']);
		$descuento = escape($detalle['descuento']);
		$importe = $cantidad * $precio;
		$total = $total + $importe;


		$body .= '<tr>';
		$body .= '<td class="left-right" align="right">' . ($nro + 1) . '</td>';
		$body .= '<td class="left-right" align="right">' . escape($detalle['codigo']) . '</td>';
		$body .= '<td class="left-right" >' . escape($detalle['nombre_factura']) . '</td>';
		$body .= '<td class="left-right" align="center">' . $cantidad . '</td>';
		$body .= '<td class="left-right" align="center">' . $precio . '</td>';
		$body .= '<td class="left-right" align="center">' . number_format($importe, 2, '.', '') . '</td>';
		$body .= '</tr>';
	}
	
	$valor_total = number_format($total, 2, '.', '');
	$total_con_descuento=$valor_total-$valor_descuento_global;
	$valor_total_con_descuento = number_format($total_con_descuento, 2, '.', '');
	$body = ($body == '') ? '<tr><td colspan="6" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos las egresos cuenten con un detalle de venta.</td></tr>' : $body;

	// Define la fecha de hoy
	$hoy = date('Y-m-d');

	// Obtiene la dosificacion del periodo actual
	$dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();

	$valor_logo = (imagen != '') ? institucion . '/' . imagen : imgs . '/empty.jpg';
	$valor_empresa = $_institution['nombre'];
	$valor_direccion = $_institution['direccion'];
	$valor_telefono = $_institution['telefono'];
	$valor_pie = $_institution['pie_pagina'];
	$valor_razon = $_institution['razon_social']; 


	$valor_nit_empresa = $_institution['nit'];
	$valor_autorizacion = $egreso['nro_autorizacion'];
	$valor_codigo = $egreso['codigo_control'];
	$valor_numero = $egreso['nro_factura'];
	$valor_limite = date_decode($egreso['fecha_limite'], $_institution['formato']);

	$valor_leyenda = $dosificacion['leyenda'];

	$valor_solo_fecha = date_decode($egreso['fecha_egreso'], 'd/m/Y');

	// Gereramos el codigo de seguridad QR
	$factura_qr = $valor_nit_empresa . '|' . $valor_numero . '|' . $valor_autorizacion . '|' . $valor_solo_fecha . '|' . $valor_total . '|' . $valor_total_con_descuento . '|' . $valor_codigo . '|' . $valor_nit_ci . '|0.00|0.00|0.00|0.00';
	
	// Instancia el objeto QR
	$objeto = new TCPDF2DBarcode($factura_qr, 'QRCODE,L');

	// Obtiene la imagen QR en modo cadena
	$imagen = $objeto->getBarcodePngData(4, 4, array(30, 30, 30));

	// Crea la imagen a partir de la cadena
	$imagen = imagecreatefromstring($imagen);

	imagejpeg($imagen, storage . '/qr.jpg', 100);

	$qr_imagen = storage . '/qr.jpg';

	// Obtiene los datos del monto total
	$conversor = new NumberToLetterConverter();
	$monto_textual = explode('.', $valor_total);
	//$monto_textual = explode('.', $valor_total_con_descuento);
	$monto_numeral = $monto_textual[0];
	$monto_decimal = $monto_textual[1];
	$monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

	$monto_escrito = $monto_literal . ' ' . $monto_decimal . '/100';

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
		height: 15px;
	}
	.all {
		border: 1px solid #444;
	}
	.padddding {
		border: 0px solid #fff;
		height: 0px;
	}
	</style>
	<table cellpadding="1">
		<tr>
			<td width="22%" class="none" align="left" rowspan="4">
				<img src="$valor_logo" width="120">
			</td>
			<td width="28%" class="none" align="left">$valor_empresa</td>
			<td width="35%" color ="#ffffff" class="none" align="right"><b>NIT:</b></td>
			<td width="15%" color ="#ffffff" class="none" align="right">$valor_nit_empresa</td>
		</tr>
		<tr>
			<td class="none" align="left">$valor_direccion</td>
			<td class="none" color ="#ffffff" align="right"><b>NRO. FACTURA:</b></td>
			<td class="none" color ="#ffffff" align="right">$valor_numero</td>
		</tr>
		<tr>
			<td class="none" align="left">Teléfono: $valor_telefono</td>
			<td class="none" color ="#ffffff" align="right"><b>NRO. AUTORIZACIÓN:</b></td>
			<td class="none" color ="#ffffff" align="right">$valor_autorizacion</td>
		</tr>
		<tr>
			<td class="none" align="left">$valor_pie</td>
			<td class="none" color ="#ffffff" align="right" colspan="2"></td>
		</tr>
		<tr>
			<td class="padddding" align="left"></td>
			<td class="padddding" color ="#ffffff" align="right" colspan="2"></td>
		</tr>
	</table>


EOD;
	// Formateamos la tabla
	$tabla1 = <<<EOD
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
		height: 15px;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
	<h1 align="">FACTURA</h1>
	<table cellpadding="1">
		<tr>
			<td width="20%" class="none"><b>FECHA Y HORA:</b></td>
			<td width="80%" class="none">$valor_fecha</td> 
		</tr>
		<tr>
			<td class="none"><b>SEÑOR(ES):</b></td>
			<td class="none">$valor_nombre_cliente</td>
		</tr>
		<tr>
			<td class="none"><b>NIT / CI:</b></td>
			<td class="none">$valor_nit_ci</td>
		</tr>
	</table>
	<br>
	<table cellpadding="5">
		<tr>
			<th width="5%" class="all" align="center">#</th>
			<th width="12%" class="all" align="center">CÓDIGO</th>
			<th width="47%" class="all" align="center">NOMBRE</th>
			<th width="12%" color="#ffffff" class="all" align="center">CANTIDAD</th>
			<th width="12%" color="#ffffff" class="all" align="center">PRECIO</th>
			<th width="12%" color="#ffffff" class="all" align="center">IMPORTE</th>
		</tr>
		$body
		<tr>
			<th class="all" align="left" colspan="5">SON: $monto_escrito</th>
			<th class="all" align="right">$valor_total</th>
		</tr>
	</table> 
	<br><br> 
	<table cellpadding="1">
		<tr>
			<td width="20%" class="none"><b>Código de control:</b></td>
			<td width="30%" class="none">$valor_codigo</td>
			<td width="50%" class="none" rowspan="2" align="right">
				<img src="$qr_imagen" width="80">
			</td>
		</tr>
		<tr>
			<td class="none"><b>Fecha límite de emisión:</b></td>
			<td class="none">$valor_limite</td>
		</tr>
	</table>
	<h4 align="center">"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"</h4>
	<div align="center"><b>Ley Nº 453:</b> "$valor_leyenda".</div>
EOD;
	
		//$pdf->SetTextColor(0, 0, 0);
		$pdf->SetTextColor(80, 80, 80);
	    
	    $pdf->SetFillColor(0, 138, 216); 
	    //$pdf->SetTextColor(192, 192, 192);
		//$pdf->SetFillColor(175, 175, 175); 
        		//$pdf->SetFillColor(101, 177, 220); 
        //izquierda
		$pdf->Polygon(array(612,10,0,10,0,94,612,94), 'DF', 1, array(237,237,237), false);//42, 120, 178
		//derecha
        $pdf->Polygon(array(280,98,380,0,612,0,612,98), 'DF', 1, array($_institution['color_r1'],$_institution['color_g1'],$_institution['color_b1']), false);

		//centro
		//$pdf->Polygon(array(380,0,612,0,612,3,415,3,280,97,239,97), 'DF', 1, array(42, 130, 178), false);

		//izquierda
       	$pdf->Polygon(array(0,168,522,168,502,188,0,188), 'DF', 1, array(237, 237, 237), false);
		//plomo
		//$pdf->Polygon(array(400,120,0,120,0,175,275,175), 'DF', 1, array(237, 237, 237), false);
		//derecha
		$pdf->Polygon(array(612,166,380,166,350,188,612,188), 'DF', 1, array($_institution['color_r1'],$_institution['color_g1'],$_institution['color_b1']), false);


				//$pdf->SetTextColor(0, 0, 0);
		//$pdf->SetTextColor(255, 255, 255);
	// Imprime la tabla
		$pdf->writeHTML($tabla, true, false, false, false, '');
			//$pdf->SetTextColor(0, 0, 0);
		//$pdf->SetTextColor(140, 140, 140);
		// Imprime la tabla
		$pdf->writeHTML($tabla1, true, false, false, false, '');


	// Obtiene el ancho de la pagina
	$width_page = $pdf->GetPageWidth();
	$width_page = $width_page-75;
		// Define los margenes
	$margin_left = $margin_right = 35;
	$margin_top = $margin_bottom = 30;
	// Obtiene la posicion vertical final
	$final = $pdf->getY() - 27;
	// Define las variables
	$width_image = 145;
	$height_image = 145;
	$rows = 9;
	$padding = 5;
	$height_cell = ($height_image - $padding) / $rows;
	$width_table = ($width_page * (1 - ($width_image / $width_page))) - $padding;
	//$pdf->RoundedRect(EJE x, EJE y, ANCHO, ALTO, REDONDEO DE ESQUINAS, 'SE DEFINE QUE ESQUINAS SE REDONDEA CON 0-1 ', 'DF');
	$pdf->RoundedRect($margin_left, $final + $padding, $width_page, $height_cell * 1.5, $padding, '0000');




	// Genera el nombre del archivo
	$nombre = 'factura_' . $id_egreso . '_' . date('Y-m-d_H-i-s') . '.pdf';
}

// ------------------------------------------------------------

// Cierra y devuelve el fichero pdf
ob_end_clean();
$pdf->Output($nombre, 'I');

?>
