<?php

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

if ($id_egreso == 0) {
	
} else {
	// Obtiene los permisos
	$permisos = explode(',', permits);
	
	// Almacena los permisos en variables
	$permiso_ver = in_array('ver', $permisos);
	
	// Obtiene los egreso
	

	// Obtiene el id_egreso
	$id_egreso = (sizeof($params) > 0) ? $params[0] : 0;

	// Obtiene los egreso
	$egreso =  $db->select('i.*, i.plan_de_pagos, p.id_pago, pd.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, ex.nombres as nombrex, ex.paterno as paternox, ex.materno as maternox')
				  ->from('inv_egresos i')
				  ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			  	  ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			  	  ->join('inv_pagos p', 'p.movimiento_id = i.id_egreso', 'left')
				  ->join('inv_pagos_detalles pd', 'pd.pago_id=p.id_pago', 'left')			  
				  ->join('sys_empleados ex', 'pd.empleado_id = ex.id_empleado', 'left')
			  	  ->where('id_pago_detalle', $id_egreso)
				  ->fetch_first();

	// Verifica si existe el egreso
	if (!$egreso) {
		// Error 404
		require_once not_found();
		exit;
	}
}



//determinar nro de cuota
$cuotas = $db->select('COUNT(pago_id)as numero_de_cuota')
				  ->from('inv_pagos_detalles pd')			  
				  ->where('pago_id', $egreso['pago_id'])
				  ->where('nro_cuota<', $egreso['nro_cuota'])				  
				  ->order_by('nro_cuota, fecha, fecha_pago')
				  ->fetch_first();

//determinar saldo pendiente
$monto_pagado = $db->select('SUM(monto)as monto_pagado')
				  ->from('inv_pagos_detalles pd')			  
				  ->where('pago_id', $egreso['pago_id'])
				  ->where('nro_cuota<=', $egreso['nro_cuota'])				  
				  ->order_by('nro_cuota, fecha, fecha_pago')
				  ->fetch_first();

$montoPendiente=$egreso['monto_total']-$monto_pagado['monto_pagado'];

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
define('PROPIETARIO', escape($_institution['propietario']));
define('PIE', escape($_institution['pie_pagina']));
define('FECHA', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Ln(5);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, NOMBRE, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, PROPIETARIO, 0, true, 'R', false, '', 0, false, 'T', 'M');
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

if ($id_egreso == 0) {
	// Documento general -----------------------------------------------------

	// Adiciona la pagina
	$pdf->AddPage('L', array(PDF_PAGE_FORMAT_WIDTH, PDF_PAGE_FORMAT_HEIGHT));
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'EGRESOS', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// Define variables
	$valor_moneda = $moneda;

	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'reporte_pago_' . date('Y-m-d_H-i-s') . '.pdf';
} else {
	// Documento individual --------------------------------------------------
	
	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'COMPROBANTE DE PAGO #' . $id_egreso, 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
	
	// Define las variables
	$valor_fecha = escape(date_decode($egreso['fecha_egreso'], $_institution['formato']) . ' ' . $egreso['hora_egreso']);
	$valor_nombre_cliente = escape($egreso['nombre_cliente']);
	$valor_nit_ci = escape($egreso['nit_ci']);
	$valor_tipo = escape($egreso['tipo']);
	$valor_descripcion = escape($egreso['descripcion']);
	$valor_monto_total = escape($egreso['monto_total']);
	$valor_nro_registros = escape($egreso['nro_registros']);
	$valor_almacen = escape($egreso['almacen']);
	$valor_empleado = escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']);
	$valor_moneda = $moneda;
	$total = 0;

	// Estructura la tabla de cuotas
	$body2 = '';
	if (escape($egreso['plan_de_pagos'])=="si"){
		$total=0;
		
		$body2 .= '<table cellpadding="5">';

		$body2 .= '<tr>';
		$body2 .= '<td colspan="2" class="left-right"><b>Detalle de la cuota</b></td>';
		$body2 .= '</tr>';
		$body2 .= '</table>';

		$body2 .= '<table cellpadding="5">';
		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Detalle:</th>';
		$body2 .= '<td width="60%" class="left-right">Cuota #'.($cuotas['numero_de_cuota']+1).'</td>';
		$body2 .= '</tr>';

		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Fecha programada:</th>';
		$body2 .= '<td width="60%" class="left-right">'.escape(date_decode($egreso['fecha'], $_institution['formato'])).'</td>';
		$body2 .= '</tr>';

		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Fecha de pago:</th>';
		$body2 .= '<td width="60%" class="left-right">'.escape(date_decode($egreso['fecha_pago'], $_institution['formato'])).'</td>';
		$body2 .= '</tr>';
		/*
		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Estado:</th>';
		$body2 .= '<td width="60%" class="left-right">';		
			if($egreso['estado']==0){
				$body2 .='Pendiente';
			}else{
				$body2 .='Cancelado';
			}
		$body2 .= '</td>';
		$body2 .= '</tr>';
		*/
		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Tipo de pago:</th>';
		$body2 .= '<td width="60%" class="left-right">' . $egreso['tipo_pago'] . '</td>';
		$body2 .= '</tr>';

		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Empleado Cobrador:</th>';
		$body2 .= '<td width="60%" class="left-right">' . $egreso['paternox'].' '.$egreso['maternox'].' '.$egreso['nombrex'] . '</td>';
		$body2 .= '</tr>';

		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Monto Pagado:'.$valor_moneda.'</th>';
		$body2 .= '<td width="60%" class="left-right">'.number_format($egreso['monto'], 2, '.', '') . '</td>';
		$body2 .= '</tr>';

		$body2 .= '<tr>';
		$body2 .= '<th width="40%" align="right" class="left-right">Monto Pendiente:'.$valor_moneda.'</th>';
		$body2 .= '<td width="60%" class="left-right">'.number_format($montoPendiente, 2, '.', '') . '</td>';
		$body2 .= '</tr>';

		$body2 .= '</table>';		
	}
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
			<td colspan="2" class="all"><b>Infomación del egreso</b></td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Fecha y hora:</th>
			<td width="60%" class="left-right">$valor_fecha</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Cliente:</th>
			<td width="60%" class="left-right">$valor_nombre_cliente</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">CI / NIT:</th>
			<td width="60%" class="left-right">$valor_ci_nit</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Tipo de egreso:</th>
			<td width="60%" class="left-right">$valor_tipo</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Descripción:</th>
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
			<th width="40%" align="right" class="left-right" style="border-bottom: 1px solid #444;">Empleado Vendedor:</th>
			<td width="60%" class="left-right" style="border-bottom: 1px solid #444;">$valor_empleado</td>
		</tr>
	</table>
	$body2		
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'comprobante_de_egreso_' . $id_egreso . '_' . date('Y-m-d_H-i-s') . '.pdf';
}

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
