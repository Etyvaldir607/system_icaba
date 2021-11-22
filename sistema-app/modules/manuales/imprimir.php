<?php

// Obtiene el orden de compra
$id_orden = (isset($params[0])) ? $params[0] : 0;

if ($id_orden == 0) {
	// Error 404
	require_once not_found();
	exit;
} else {
	// Obtiene el orden de compra
	$orden = $db->select('n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
				->from('inv_egresos n')->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')
				->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')
				
				->join('inv_pagos p', 'p.movimiento_id = n.id_egreso AND p.tipo="egreso"', 'left')
				
				->where('n.id_egreso', $id_orden)
				->where('n.tipo', 'Venta')
				->where('n.provisionado', 'N')
				->fetch_first();

	// Verifica si existe el orden de compra
	if ($orden) {
		// Obtiene los detalles
		/*$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')->from('inv_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_orden)->order_by('codigo asc')->fetch();*/
		$detalles = $db->query("SELECT d.*, p.codigo, p.nombre, p.nombre_factura, u.tamanio, u.unidad
								FROM inv_egresos_detalles d
								LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
								LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
								LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
								LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
								WHERE d.egreso_id = $id_orden
								ORDER BY c.orden asc, p.codigo asc")->fetch();
	} else {
		// Error 404
		require_once not_found();
		exit;
	}
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

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
//define('TELEFONO', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Operaciones con la imagen del header
list($ancho_header, $alto_header) = getimagesize(imgs . '/header.jpg');
$relacion = $alto_header / $ancho_header;
$ancho_header = 612;
$alto_header = round($ancho_header * $relacion);
 define('alto_header', $alto_header);

// Operaciones con la imagen del footer
list($ancho_footer, $alto_footer) = getimagesize(imgs . '/footer.jpg');
$relacion = $alto_footer / $ancho_footer;

$ancho_footer = 612;
//$ancho_footer = 0;
$alto_footer = round($ancho_footer * $relacion);
define('ancho_footer', $ancho_footer);
define('alto_footer', $alto_footer);


// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Image(imgs . '/header.jpg', 0, 0, ancho_header, alto_header);
		/*$this->Ln(50);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, DIRECCION, 0, true, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, ATENCION, 0, true, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, TELEFONO, 0, true, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, ATENCION, 0, true, 'L', false, '', 0, false, 'T', 'M');
		//$imagen = (IMAGEN != '') ? INSTITUCION . '/' . IMAGEN : IMGS . '/empty.jpg' ;
		$this->Image(imgs . '/logo-dulcifarma.jpg', 300, 5, ancho_header, alto_header, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);*/
	}
	public function Footer() {
		$this->Image(imgs . '/footer.jpg', 0, 698, ancho_footer, alto_footer);
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
$pdf->SetMargins(30, alto_header + 15, 30);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, alto_footer + 15);

// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('P');

// Adiciona la pagina
$pdf->AddPage();

// Establece la fuente del titulo
$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);



// Titulo del documento
$pdf->Cell(0, 10, 'VENTA MANUAL - FACTURA # ' . $orden['nro_factura'], 0, true, 'C', false, '', 0, false, 'T', 'M');

// Salto de linea
$pdf->Ln(5);

// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

// Define las variables
$valor_fecha = escape(date_decode($orden['fecha_egreso'], $_institution['formato']) . ' ' . $orden['hora_egreso']);
$valor_nombre_cliente = escape($orden['nombre_cliente']);
$valor_nit_ci = escape($orden['nit_ci']);
$valor_direccion = escape($orden['direccion']);
$valor_telefono = escape($orden['telefono']);
$valor_monto_total = escape($orden['monto_total']);
$valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno'] . ' ' . $orden['materno']);
$valor_descuento = escape( number_format($orden['descuento']), 0);
$nro_autorizacion = escape($orden['nro_autorizacion']);
$valor_fecha_entrega = escape($orden['fecha_entrega']);

$plan = escape($orden['plan_de_pagos']);
if($plan=="si"){
	$plan="<p><b>FORMA DE PAGO:</b> Pago por cuotas</p>";
}else{	
	$plan="<p><b>FORMA DE PAGO:</b> Pago completo al contado</p>";
}

$valor_moneda = $moneda;
$total = 0;
switch ($orden['activo']) {
	case 'S': $valor_entregado = "Nota de Remisión"; break;
	case 'F': $valor_entregado = "Con factura"; break;
	case 'B': $valor_entregado = "Bonificación"; break;
} 

// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

// Estructura la tabla
$body = '';
$fooder = '';
foreach ($detalles as $nro => $detalle) {
	$cantidad = escape($detalle['cantidad']);
	$precio = escape($detalle['precio']);
	$descuento = escape($detalle['descuento']);
	$importe = $cantidad * $precio;
	$total = $total + $importe;
	$body .= '<tr>';
	$body .= '<td class="left-right" align="right">' . ($nro + 1) . '</td>';
	$body .= '<td class="left-right">' . escape($detalle['codigo']) . '</td>';
	$body .= '<td class="left-right">' . escape($detalle['nombre_factura']) . '</td>';
	$body .= '<td class="left-right" align="right">' . $detalle['unidad'] . "(" . $detalle['tamanio'] . ")" . '</td>';
	$body .= '<td class="left-right" align="right">' . $cantidad . '</td>';
	$body .= '<td class="left-right" align="right">' . $precio . '</td>';
	$body .= '<td class="left-right" align="right">' . number_format($importe, 2, '.', '') . '</td>';
	$body .= '</tr>';
}

//consulta si el monto es mayor a 200
//if($total >= 200){
	//calcula el porcentaje
	$sin_descuento = $total;
	$descuento = ($total * $orden['descuento']) / 100 ;
	$descuento_total = $total - $descuento;
/*}else{
	$descuento_total = $total;
}*/

$descuento_total_2 = $descuento_total;

//pregunta si el porcentaje es mayor a 0 para mostrar el fooder
if($orden['descuento'] > 0){
	$fooder .= '<tr>';
	$fooder .= '<th class="all" align="left" colspan="6" align="right">SIN DESCUENTO</th>';
	$fooder .= '<th class="all" align="left" align="right"> ' . number_format($sin_descuento, 2, '.', '') . '</th>';
	$fooder .= '</tr>';
	$fooder .= '<tr>';
	$fooder .= '<th class="all" align="left" colspan="4" align="right">DESCUENTO APLICADO</th>';
	$fooder .= '<th class="all" align="left" align="right">' . number_format($orden['descuento'], 0) . ' %</th>';
	$fooder .= '<th class="all" align="left" align="right"> Importe total</th>';
	$fooder .= '<th class="all" align="left" align="right"> ' . number_format($descuento_total, 2, '.', '') . '</th>';
	$fooder .= '</tr>';
}

if( ($orden['descuento'] == 0) ){
	$fooder .= '<tr>';
	$fooder .= '<th class="all" align="left" colspan="6" align="right">IMPORTE TOTAL </th>';
	$fooder .= '<th class="all" align="right" align="right"> ' . number_format($descuento_total_2, 2, '.', '') . '</th>';
	$fooder .= '</tr>';
}

// Obtiene el valor total
$valor_total = number_format($descuento_total, 2, '.', '');

// Obtiene los datos del monto total
$conversor = new NumberToLetterConverter();
$monto_textual = explode('.', $valor_total);
$monto_numeral = $monto_textual[0];
$monto_decimal = $monto_textual[1];
$monto_literal = upper($conversor->to_word($monto_numeral));

$body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;

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
	<table cellpadding="1">
		<tr>
			<td width="15%" class="none"><b>SEÑOR(ES):</b></td>
			<td width="35%" class="none">$valor_nombre_cliente</td>
			<td width="20%" class="none"><b>EMPLEADO:</b></td>
			<td width="35%" class="none">$valor_empleado</td>
		</tr>
		<tr>
			<td class="none"><b>NIT / CI:</b></td>
			<td class="none">$valor_nit_ci</td>
			<td class="none"><b>FECHA DE REGISTRO:</b></td>
			<td class="none">$valor_fecha</td>
		</tr>
		<tr>
			<td class="none"><b>TELÉFONO:</b></td>
			<td class="none">$valor_telefono</td>
			<td class="none"><b>FECHA DE ENTREGA:</b></td>
			<td class="none">$valor_fecha_entrega</td>
		</tr>
		<tr>
			<td class="none"><b>DIRECCIÓN:</b></td>
			<td class="none">$valor_direccion</td>
			<td class="none"><b>NRO. AUTORIZACIÓN:</b></td>
			<td class="none">$nro_autorizacion</td>
		</tr>
		<tr>
			<td width="15%"><b>OBSERVACIÓN:</b></td>
			<td>$valor_observacion</td>
		</tr>
	</table>
	<br><br>
	<table cellpadding="5">
		<tr>
			<th width="5%" class="all" align="right">#</th>
			<th width="12%" class="all" align="left">CÓDIGO</th>
			<th width="37%" class="all" align="left">DETALLE</th>
			<th width="10%" class="all" align="right">UNIDAD</th>
			<th width="10%" class="all" align="right">CANT.</th>
			<th width="13%" class="all" align="right">PRECIO $valor_moneda</th>
			<th width="13%" class="all" align="right">IMPORTE $valor_moneda</th>
		</tr>
		$body
		$fooder
	</table>
	<p>$monto_literal $monto_decimal /100</p>

	<p></p>
	<p></p>
	<p></p>
	$plan

EOD;

/*<tr>
	<th class="all" align="left" colspan="6">IMPORTE TOTAL $valor_moneda</th>
	<th class="all" align="right">$valor_total</th>
</tr>*/

// Imprime la tabla
$pdf->writeHTML($tabla, true, false, false, false, '');

// Genera el nombre del archivo
$nombre = 'VENTA_MANUAL_' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
