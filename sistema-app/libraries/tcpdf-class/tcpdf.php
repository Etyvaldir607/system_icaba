<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Importa la libreria para generar el reporte
require_once libraries . '/tcpdf/tcpdf.php';

// Define tamanos y fuentes
$font_name_main = 'roboto';
$font_name_data = 'roboto';
$font_size_main = 12;
$font_size_data = 10;

// Define los estilos
$style = '<style>th {background-color: #eee;border-left: 1px solid #444;border-right: 1px solid #444;font-weight: bold;}td {background-color: #fff;border-left: 1px solid #444;border-right: 1px solid #444;}.first th, .first td {border-top: 1px solid #444;}.last th, .last td {border-bottom: 1px solid #444;}.even th, .even td {background-color: #fff;}.odd th, .odd td {background-color: #eee;}.left {text-align: right;width: 40%;}.right {text-align: left;width: 60%;}img {border: 1px solid #444;height: 100px;text-align: center;}</style>';

// Define variables globales
define('nombre','Checkcode');
define('logotipo', 'logotipo');
define('lema', 'lema');
define('informacion','informacion');
define('fecha', date('H:i:s'));

// Define longitudes
define('margin_left', 30);
define('margin_right', 30);
define('margin_top', 75);
define('margin_bottom', 45);
define('margin_header', 0);
define('margin_footer', 0);

// Extiende la clase tcpdf para crear header y footer
class MYPDF extends TCPDF {
	public function Header() {
		$logotipo = (logotipo != '') ? files . '/logos/' . logotipo : imgs . '/16by9.jpg';
		$this->Ln(20);
		$this->SetFont('roboto', '', 9);
		$this->Cell(0, 10, nombre, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, lema, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, fecha, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, '', 'B', true, 'R', false, '', 0, false, 'T', 'M');
		$this->Image($logotipo, margin_left, 20, '', 40, 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
	
	public function Footer() {
		$this->SetY(15 - margin_bottom);
		$this->SetFont('roboto', '', 9);
		$length = ($this->getPageWidth() - margin_left - margin_right) / 2;
		$number = $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();
		$this->Cell($length, 15, 'Página ' . $number, 'T', false, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell($length, 15, informacion, 'T', true, 'R', false, '', 0, false, 'T', 'M');
	}
}

// Instancia el documento pdf
$pdf = new MYPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator('Checkcode');
$pdf->SetAuthor('Checkcode');
$pdf->SetTitle('nombre');
$pdf->SetSubject('propietario');
$pdf->SetKeywords('sigla');

// Asignamos margenes
$pdf->SetMargins(margin_left, margin_top, margin_right);
$pdf->SetHeaderMargin(margin_header);
$pdf->SetFooterMargin(margin_footer);

?>