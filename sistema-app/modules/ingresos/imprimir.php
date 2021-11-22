<?php

// Obtiene el id_ingreso
$id_ingreso = (isset($params[0])) ? $params[0] : 0;
$columnas = $_POST['columnas'];
$columnas = explode(',', $columnas);

if ($id_ingreso == 0) {
	// Obtiene los ingresos
	$ingresos = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
				   ->from('inv_ingresos i')
				   ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
				   ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
				   ->join('inv_pagos p','p.movimiento_id=i.id_ingreso AND p.tipo="Ingreso"','left')
				   ->order_by('i.fecha_ingreso desc, i.hora_ingreso desc')
				   ->fetch();
} else {
	// Obtiene los permisos
	$permisos = explode(',', permits);
	
	// Almacena los permisos en variables
	$permiso_ver = in_array('ver', $permisos);
	
	// Obtiene los ingreso
	$ingreso = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, resp.nombres as nombres_resp,resp.paterno as paterno_resp,resp.materno as materno_resp')
				  ->from('inv_ingresos i')
				  ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
				  ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
				  ->join('sys_empleados resp', 'i.responsable_id = resp.id_empleado', 'left')
				   ->join('inv_pagos p','movimiento_id=i.id_ingreso AND p.tipo="Ingreso"','left')
				  ->where('id_ingreso', $id_ingreso)
				  ->fetch_first();
	
	// Verifica si existe el ingreso
	if (!$ingreso) {
		// Error 404
		require_once not_found();
		exit;
	} elseif (!$permiso_ver) {
		// Error 401
		require_once bad_request();
		exit;
	}

	// Obtiene los detalles
	$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, u.tamanio')
				   ->from('inv_ingresos_detalles d')
				   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				   ->join('inv_asignaciones a', 'a.id_asignacion = d.asignacion_id', 'left')
				   ->join('inv_unidades u', 'u.id_unidad = a.unidad_id', 'left')
				   ->where('d.ingreso_id', $id_ingreso)
				   ->order_by('categoria_id asc')
				   ->order_by('codigo asc')
				   ->fetch();
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Define variables globales
define('NOMBRE', escape($_institution['nombre']));
define('IMAGEN', escape($_institution['imagen_encabezado']));
define('DIRECCION', escape($_institution['direccion']));
define('PIE', escape($_institution['pie_pagina']));
//define('TELEFONO', escape($_institution['telefono']));
define('FECHA', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Ln(5);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'B', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, NOMBRE, 0, true, 'R', false, '', 0, false, 'T', 'M');
		
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, DIRECCION, 0, true, 'R', false, '', 0, false, 'T', 'M');
		//$this->Cell(0, 5, TELEFONO, 0, true, 'R', false, '', 0, false, 'T', 'M');
		
		$this->Cell(0, 5, FECHA, 'B', true, 'R', false, '', 0, false, 'T', 'M');
		
		$imagen = (IMAGEN != '') ? institucion . '/' . IMAGEN : imgs . '/empty.jpg' ;
		$this->Image($imagen, PDF_MARGIN_LEFT, 5, '', 14, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}	
	public function Footer() {
		$this->SetY(-10);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$length = ($this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT) / 2;
		$number = $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
		$this->Cell($length, 5, $number, 'T', false, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell($length, 5, PIE, 'T', true, 'R', false, '', 0, false, 'T', 'M');
	}
}

// Instancia el documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// ------------------------------------------------------------

if ($id_ingreso == 0) {
	// Documento general -----------------------------------------------------

	// Adiciona la pagina
	$pdf->AddPage('L', array(PDF_PAGE_FORMAT_WIDTH, PDF_PAGE_FORMAT_HEIGHT));
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'INGRESOS', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// Define variables
	$valor_moneda = $moneda;

	// Estructura la tabla
	$body = '';
	foreach ($ingresos as $nro => $ingreso) {
		$body .= '<tr>';
		$body .= '<td>' . ($nro + 1) . '</td>';
		$body .= '<td>' . escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato']) . ' ' . $ingreso['hora_ingreso']) . '</td>';
		$body .= '<td>' . escape($ingreso['nombre_proveedor']) . '</td>';
		$body .= '<td>' . escape($ingreso['tipo']) . '</td>';
		$body .= '<td>' . escape($ingreso['descripcion']) . '</td>';
		$body .= '<td align="right">' . escape($ingreso['monto_total']) . '</td>';
		$body .= '<td align="right">' . escape($ingreso['nro_registros']) . '</td>';
		$body .= '<td>' . escape($ingreso['almacen']) . '</td>';
		$body .= '<td>' . escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']) . '</td>';
		$body .= '</tr>';
	}
	
	$body = ($body == '') ? '<tr><td colspan="9" align="center">No existen ingresos registrados en la base de datos</td></tr>' : $body;
	
	// Formateamos la tabla
	$tabla = <<<EOD
	<style>
	th {
		background-color: #eee;
		border: 1px solid #444;
		font-weight: bold;
	}
	td {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	table {
		border-bottom: 1px solid #444;
	}
	</style>
	<table cellpadding="5">
		<tr>
			<th width="6%">#</th>
			<th width="8%">Fecha</th>
			<th width="14%">Proveedor</th>
			<th width="8%">Tipo</th>
			<th width="20%">Descripción</th>
			<th width="10%">Monto $valor_moneda</th>
			<th width="8%">Registros</th>
			<th width="12%">Almacén</th>
			<th width="14%">Empleado</th>
		</tr>
		$body
	</table>
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'ingresos_' . date('Y-m-d_H-i-s') . '.pdf';
} else {
	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'COMPROBANTE DE INGRESO # ' . $id_ingreso, 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
	
	// Define las variables
	$valor_fecha 			= escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato']) . ' ' . $ingreso['hora_ingreso']);
	$valor_nombre_proveedor = escape($ingreso['nombre_proveedor']);
	$valor_tipo 			= escape($ingreso['tipo']);
	$valor_descripcion 		= escape($ingreso['descripcion']);
	$valor_monto_total 		= escape($ingreso['monto_total']);
	$valor_nro_registros 	= escape($ingreso['nro_registros']);
	$valor_almacen 			= escape($ingreso['almacen']);
	$valor_empleado 		= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']);
	$valor_responsable_egreso 		= escape($ingreso['nombres_resp'] . ' ' . $ingreso['paterno_resp'] . ' ' . $ingreso['materno_resp']);
	$valor_moneda 			= $moneda;
	$total = 0;

	$firmas='';
	if($ingreso['tipo'] != 'Ajuste'){
		$firmas .= '<table cellpadding="0.8" style="border: hidden">
							<tr style="border: hidden">							
								<td width="100%" class="text-center" align="center" style="border: hidden" >__________________________________________</td>
							</tr>
							<tr style="border: hidden">							
								<td width="100%" class="text-center" align="center" style="border: hidden" ><span><b>RECIBIÓ STOCK: </b> '.$valor_responsable_egreso.'</span></td>
							</tr>
							<tr style="border: hidden">
								<td width="100%" class="center" style="border: hidden" ></td>							
							</tr>
						</table>';
	}

	// Estructura la tabla
	$body = '';
	foreach ($detalles as $nro => $detalle) {
		$cantidad 	= escape($detalle['cantidad']);
		$costo 		= escape($detalle['costo']);
		$importe 	= $cantidad * $costo;
		$total 		= $total + $importe;
		$body .= '<tr>';
		$body .= '<td class="left-right">' . ($nro + 1) . '</td>';
		if (in_array("codigo", $columnas)) 
			$body .= '<td class="left-right">' . escape($detalle['codigo']) . '</td>';
		if (in_array("nombre", $columnas)) 
			$body .= '<td class="left-right">' . escape($detalle['nombre']) . '</td>';
		if (in_array("cantidad", $columnas)) 
			$body .= '<td class="left-right" align="right">' . $cantidad . '</td>';
		if (in_array("unidad", $columnas)) 
			$body .= '<td class="left-right">' . escape($detalle['unidad']) . " (" . escape($detalle['tamanio']) . ')</td>';
		if (in_array("costo", $columnas)) 
		$body .= '<td class="left-right" align="right">' . $costo . '</td>';
		if (in_array("importe", $columnas)) 
			$body .= '<td class="left-right" align="right">' . number_format($importe, 2, '.', '') . '</td>';
		$body .= '</tr>';
	}
	
	$valor_total = number_format($total, 2, '.', '');
	$body = ($body == '') ? '<tr><td colspan="6" align="center" class="all">Este ingreso no tiene detalle, es muy importante que todos los ingresos cuenten con un detalle de la compra.</td></tr>' : $body;
	
	// Formateamos la tabla
	$tabla = <<<EOD
	<style>
	table {
		border-bottom: 1px solid #444;
	}
	th {
		background-color: #eee;
		font-weight: bold;
	}
	.left-right {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
	<table cellpadding="5">
		<tr>
			<td colspan="2" class="all"><b>Infomación del ingreso</b></td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Fecha y hora:</th>
			<td width="60%" class="left-right">$valor_fecha</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Proveedor:</th>
			<td width="60%" class="left-right">$valor_nombre_proveedor</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Tipo de ingreso:</th>
			<td width="60%" class="left-right">$valor_tipo</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Observaciones:</th>
			<td width="60%" class="left-right">$valor_descripcion</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Monto total:</th>
			<td width="60%" class="left-right">$valor_monto_total</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Número de registros:</th>
			<td width="60%" class="left-right">$valor_nro_registros</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Almacén:</th>
			<td width="60%" class="left-right">$valor_almacen</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Usuario:</th>
			<td width="60%" class="left-right">$valor_empleado</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right" style="border-bottom: 1px solid #444;">Responsable:</th>
			<td width="60%" class="left-right" style="border-bottom: 1px solid #444;">$valor_responsable_egreso</td>
		</tr>
		<tr>
			<td colspan="2" class="left-right"><b>Detalle del ingreso</b></td>
		</tr>
	</table>
	<table cellpadding="5">
		<tr>			
EOD;








$tamanio_defecto=36;

if (!in_array("codigo", $columnas)) {
	$tamanio_defecto+=14;
}
if (!in_array("cantidad", $columnas)) {
	$tamanio_defecto+=10;
}
if (!in_array("unidad", $columnas)) {
	$tamanio_defecto+=10;
}
if (!in_array("costo", $columnas)) {
	$tamanio_defecto+=12;
}
if (!in_array("importe", $columnas)) {
	$tamanio_defecto+=12;
}

//********************************************

if (in_array("nombre", $columnas)) {
$tabla.= <<<EOD
<th width="6%" class="all">#</th>
EOD;
}
else{
$tamanio_defecto+=6;	
$tabla.= <<<EOD
<th width="$tamanio_defecto%" class="all">#</th>
EOD;
}

//********************************************

if (in_array("codigo", $columnas)) {
$tabla.= <<<EOD
<th width="14%" class="all">Código</th>
EOD;
}
if (in_array("nombre", $columnas)) {
$tabla.= <<<EOD
<th width="$tamanio_defecto%" class="all">Nombre</th>
EOD;
}
if (in_array("cantidad", $columnas)) {
$tabla.= <<<EOD
<th width="10%" class="all">Cantidad</th>
EOD;
}
if (in_array("unidad", $columnas)) {
$tabla.= <<<EOD
<th width="10%" class="all">Unidad</th>
EOD;
}
if (in_array("costo", $columnas)) {
$tabla.= <<<EOD
<th width="12%" class="all">Costo $valor_moneda</th>
EOD;
}
if (in_array("importe", $columnas)) {
$tabla.= <<<EOD
<th width="12%" class="all">Importe $valor_moneda</th>
EOD;
}

$nro_columnas = count($columnas);
$tabla .=<<<EOD
		</tr>
		$body
EOD;

if (in_array("importe", $columnas)) {
$tabla .=<<<EOD
		<tr>
			<th class="all" align="right" colspan="$nro_columnas">Importe total $valor_moneda</th>
			<th class="all" align="right">$valor_total</th>
		</tr>
EOD;
}

$tabla .=<<<EOD
	</table>
	<br>
	<br>
	<br>
	<br>
	<br>
	$firmas
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'comprobante_de_ingreso_' . $id_ingreso . '_' . date('Y-m-d_H-i-s') . '.pdf';
}

// ------------------------------------------------------------

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
