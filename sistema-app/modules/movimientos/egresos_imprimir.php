<?php

$id_movimiento = (isset($params[0])) ? $params[0] : 0;

$movimiento = $db->query("SELECT m.*, upper(IFNULL(a.sucursal, 'sucursal no registrado')) AS sucursal, ifnull(concat( e.nombres, ' ', e.paterno, ' ', e.materno ), '-') as autorizado_por, ifnull(concat( er.nombres, ' ', er.paterno, ' ', er.materno ), '-') as recibido_por
                        FROM caj_movimientos as m
                        LEFT JOIN sys_empleados as e ON m.empleado_id = e.id_empleado
                        LEFT JOIN sys_empleados as er ON m.recibido_por = er.id_empleado
						LEFT JOIN inv_sucursal as a ON a.id_sucursal = m.sucursal_id
                        WHERE id_movimiento = $id_movimiento")->fetch_first();

// Obtiene los permisos
$permisos = explode(',', permits);
$permiso_ver = true;//in_array('ver', $permisos);

if (!$movimiento) {
	// Error 404
	require_once not_found();
	exit;
} elseif (!$permiso_ver) {
	// Error 401
	require_once bad_request();
	exit;
}

$nro_comprobante = $movimiento['nro_comprobante'];
$sucursal = $movimiento['sucursal'];
$autorizado_por = upper($movimiento['autorizado_por']);
$recibido_por = upper($movimiento['recibido_por']);
$fecha_movimiento = $movimiento['fecha_movimiento'];
$hora_movimiento = $movimiento['hora_movimiento'];
$monto = $movimiento['monto'];
$monto = number_format($monto, 1, '.', '');
$concepto = $movimiento['concepto'];
$observacion = $movimiento['observacion'];

require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Obtiene los datos del monto
$conversor = new NumberToLetterConverter();
$monto_textual = explode('.', $monto);
$monto_numeral = $monto_textual[0];
$monto_decimal = $monto_textual[1];
$monto_literal = upper($conversor->to_word($monto_numeral));


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
$valor_moneda = $moneda;

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

	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	$width_page = $pdf->GetPageWidth();
	$width_page = $width_page - $margin_left - $margin_right;
    $font_name_main = 'roboto';
    $font_name_data = 'roboto';
    $font_size_main = 10;
    $font_size_data = 8;
    
    // Define los margenes
    $margin_left  = 30;
    $margin_top  = 30;
    $margin_right = 30;
    $margin_bottom = 30;
    
    $rows = 9;
    $padding = 5;
    $height_cell = ($padding) / $rows;
    
    $width_table = ($width_page * (1 - (1 / $width_page))) - $padding;
    
	// Titulo del documento
	$pdf->Cell(0, 10, 'COMPROBANTE DE EGRESO NRO. ' . $nro_comprobante, 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(3);
	$pdf->SetTextColor(48, 48, 48);
    $pdf->SetFont($font_name_data, 'B', $font_size_data);
    $pdf->Cell($width_table * 0.12, $height_cell, 'AUTORIZADO POR:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->SetFont($font_name_data, '', $font_size_data);
    $pdf->Cell($width_table * 0.50, $height_cell, $autorizado_por, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->SetFont($font_name_data, 'B', $font_size_data);
    $pdf->Cell($width_table * 0.16, $height_cell, 'FECHA AUTORIZACIÓN:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->SetFont($font_name_data, '', $font_size_data);
    $pdf->Cell($width_table * 0.50, $height_cell, $fecha_movimiento.' ('.$hora_movimiento.')', 0, 1, 'L', 0, '', 1, true, 'T', 'M');
	$pdf->Ln($padding);
	$pdf->SetFont($font_name_data, 'B', $font_size_data);
    $pdf->Cell($width_table * 0.12, $height_cell, 'SUCURSAL:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->SetFont($font_name_data, '', $font_size_data);
    $pdf->Cell($width_table * 0.50, $height_cell, $sucursal, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
    $pdf->SetFont($font_name_data, 'B', $font_size_data);
	if($observacion != ''){		
		$pdf->Cell($width_table * 0.16, $height_cell, 'OBSERVACIÓN:', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
		$pdf->SetFont($font_name_data, '', $font_size_data);
		$pdf->Cell($width_table * 0.50, $height_cell, $observacion, 0, 1, 'L', 0, '', 1, true, 'T', 'M');
	}
	$pdf->Ln($padding);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// Formateamos la tabla
		
$tabla = <<<EOD
    $datos
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
	td {
	    border: 1px solid #444;
	}
</style>
<table cellpadding="5">
		<tr>
			<th width="65%"class="all"><b>$valor_moneda $monto_literal $monto_decimal /100</b></th>
			<th width="35%"class="all"><b>Monto numeral: $monto $valor_moneda</b></th>
		</tr>
		
		<tr>
			<th width="20%" class="all"><b>Por concepto de: </b></th>
			<td width="80%" class="left-right"><p>$concepto</p></td>
		</tr>
EOD;

if($observacion != ''){
$tabla .=<<<EOD
<!-- <tr>
    <th width="20%" class="all"><b>Observaci贸n: </b></th>
    <td width="80%" class="left-right"><p>$observacion</p></td>
</tr> -->
EOD;
}

$tabla .=<<<EOD
</table>

<br>
<br>
<br>
<br>

<table cellpadding="0.3" style="border: hidden">
        <tr style="border: hidden">
			<td width="50%" class="text-center" align="center" style="border: hidden" >__________________________________________</td>
			<td width="50%" class="text-center" align="center" style="border: hidden" >__________________________________________</td>
		</tr>
		<tr style="border: hidden">
			<td width="50%" class="text-center" align="center" style="border: hidden" ><span><b>AUTORIZADO POR:</b> $autorizado_por</span></td>
			<td width="50%" class="text-center" align="center" style="border: hidden" ><span><b>RECIBIDO POR:</b> $recibido_por</span></td>
		</tr>
		<tr style="border: hidden">
			<td width="50%" class="center" style="border: hidden" ></td>
			<td width="50%" class="center" style="border: hidden" ></td>
		</tr>
</table>

EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'comprobante_de_gastos' . $id_ingreso . '_' . date('Y-m-d_H-i-s') . '.pdf';


// ------------------------------------------------------------

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
