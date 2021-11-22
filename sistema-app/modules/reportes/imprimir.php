<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$costoSSST=0;
$importeSSST=0;
$utilidadTotal=0;

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

$param1 = (isset($params[0])) ? $params[0] : $gestion_base;
$param2 = (isset($params[1])) ? $params[1] : $gestion_limite;

// Obtiene las ventas
$query="SELECT fecha_egreso ";
$query.=" FROM inv_egresos ";
$query.=" WHERE fecha_egreso between '$fecha_inicial' and '$fecha_final' ";
$query.=" GROUP BY fecha_egreso ";
$query.=" ORDER BY fecha_egreso ";
$vFechas = $db->query($query)->fetch();
			
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

if (!$movimientos) {
	// Error 404
	//require_once not_found();
	//exit;
}


// Importa la libreria para generar el reporte
require_once LIBRARIES . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once LIBRARIES . '/numbertoletter-class/NumberToLetterConverter.php';

// Instancia el documento pdf
$pdf = new TCPDF('L', 'pt', 'LETTER', true, 'UTF-8', false);
//pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator($_institution['propietario']);
$pdf->SetAuthor($_institution['propietario']);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

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

$pdf->Cell($width_page * 1.1, $height_cell * 12.5, ' REPORTE DE UTILIDADES ' ,  0, 0, 'C', 0, '', 1, true, 'T', 'M');

$pdf->SetFont($font_name_data, 'B', 8);
$pdf->Ln($padding+13);





$pdf->Ln($padding+29);


// Tercera sección
$pdf->SetTextColor(48, 48, 48);

/*
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
$pdf->Cell($width_page * 0.30, $height_cell * 2.3 , 'PRECIO:', 0, 0, 'R', 0, '', 1, true, 'T', 'M');
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
$pdf->Cell($width_page * 1.35, $height_cell * -3.5, $almacenName, 0, 0, 'L', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.30, $height_cell * -0.7,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page*0.10, $height_cell * -0.7, $nombre, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.65, $height_cell * -0.7,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 1.35, $height_cell * -0.7, $direccion, 0, 0, 'L', 0, '', 1, true, 'T', 'M');

$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.30, $height_cell * 2.3,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 0.10, $height_cell * 2.3, $precio, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Ln($padding-5);
$pdf->Cell($width_page*0.65, $height_cell * 2.3,  '', 0, 0, 'L', 0, '', 1, true, 'T', 'M');
$pdf->Cell($width_page * 1.35, $height_cell * 2.3, $principal, 0, 0, 'L', 0, '', 1, true, 'T', 'M');
*/
//$pdf->Ln($padding+0);
//$pdf->Ln($padding +457);

// Imprime la imagen
$imagen = (IMAGEN != '') ? INSTITUCION . '/' . escape($_institution['imagen_encabezado']) : IMGS . '/empty.jpg' ;
$pdf->Image($imagen, $margin_left , '25', '110', '50', 'jpg', '', 'T', false, false, '', false, false, 0, false, false, false);

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

$saldo_cantidad = 0; 
$saldo_costo = 0; 
$ingresos = array(); 

$body = '';
$body .= '<tr>';
$body .= '<th width="10%" align="center" style="border:1ps solid #aaa;">#</th>';
$body .= '<th width="15%" align="center" style="border:1ps solid #aaa;">FECHA</th>';
$body .= '<th width="25%" align="center" style="border:1ps solid #aaa;">COSTO TOTAL</th>';		
$body .= '<th width="25%" align="center" style="border:1ps solid #aaa;">PRECIO TOTAL</th>';
$body .= '<th width="25%" align="center" style="border:1ps solid #aaa;">UTILIDAD</th>';
$body .= '</tr>';

			$total = 0; 
			$costoSSSTotal=0;
			$importeSSSTotal=0;				

			foreach ($vFechas as $nro => $vFecha) { 
			
				$costoSTotal=0; 
				$importeSTotal=0; 
				
				// Obtiene las ventas
				$query="SELECT  *, SUM(cantidad)as cantidadAcumul, SUM(precio*cantidad)as importeAcumul ";
				$query.=" FROM inv_productos p, inv_egresos_detalles vd, inv_egresos v ";
				$query.=" WHERE vd.producto_id=p.id_producto AND egreso_id=id_egreso AND v.fecha_egreso = '".$vFecha['fecha_egreso']."'";
				$query.=" GROUP BY p.id_producto ";
				$ventas = $db->query($query)->fetch();

				foreach ($ventas as $nro1 => $venta) { 
					$cantidadTotal = escape($venta['cantidadAcumul']);
					$precio = escape($venta['precio']);
					$importeTotal = escape($venta['importeAcumul']);
					$total = $total + $importeTotal;
					
					$cantidadAnterior=0;
					$query="SELECT COUNT(cantidad)as cantidadAnterior ";
					$query.=" FROM inv_egresos_detalles vd, inv_egresos v ";
					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND egreso_id=id_egreso AND v.fecha_egreso < '".$vFecha['fecha_egreso']."' ";
					$vAntiguos = $db->query($query)->fetch();
					foreach ($vAntiguos as $nro2 => $vAntiguo) { 
						$cantidadAnterior = escape($vAntiguo['cantidadAnterior']);			
					}
					$costo=0;
					$costoTotal=0;
					$prodIngresados=0;
					$saldo=0;
					$prodAc=0;						//
					$ingresoSW=true;				//se termino de obtener los costos
					$detalleCompra="COSTOS:<br>";	//agregar en observaciones
					
					$ultimoSaldo=0;					
					$ultimoCosto=0;
					$nrocompras = 0;
					
					//se obtiene las compras desde inicio de la empresa hasta la fecha limite solicitada por el usuario
					$query="SELECT  * ";
					$query.=" FROM inv_ingresos_detalles vd, inv_ingresos v ";
					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND ingreso_id=id_ingreso AND v.fecha_ingreso <= '".$vFecha['fecha_egreso']."' ORDER BY fecha_ingreso";
					$iAntiguos = $db->query($query)->fetch();
					foreach ($iAntiguos as $nro3 => $iAntiguo) { 
						$prodIngresados=$prodIngresados+$iAntiguo['cantidad'];
						//se compara los productos previamente vendidos y costos antiguos
						//para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
						if($prodIngresados>$cantidadAnterior AND $ingresoSW){
							//verificar si es el primer Ingreso
							if($saldo>0){
								$saldo=$prodIngresados-$cantidadAnterior;						
							}
							else{
								$saldo=$iAntiguo['cantidad'];	
							}

							if($prodAc+$saldo<=$cantidadTotal){
								$saldo=$saldo;						
							}
							else{
								$saldo=$cantidadTotal-$prodAc;
								$ingresoSW=false;						
							}					
							
							$prodAc=$prodAc+$saldo;											
							$costoTotal=$saldo*$iAntiguo['costo'];
							$costo=$iAntiguo['costo'];

							//verificar si hay un nuevo Costo
							if($ultimoCosto!=$costo && $ultimoCosto!=0){
								$detalleCompra.=$ultimoSaldo." unid. a ".$ultimoCosto." ".$moneda."<br>";
								$ultimoSaldo=$saldo;
								$ultimoCosto=$costo;
								$nrocompras++;
							}
							else{
								$ultimoSaldo+=$saldo;
								$ultimoCosto=$costo;
							}
						}				
					}
					//asignar observaciones una vez acabado el foreach
					if($nrocompras==0){
						$detalleCompra="";
					}else{
						$detalleCompra.=$ultimoSaldo." unid. a ".$ultimoCosto." ".$moneda."<br>";
					}
					$detalleCompra="";			
					
					$detalle="VENTAS:<br>";
					//Listar los diferentes precio a los que se a vendido un producto			
					$query="SELECT precio, SUM(cantidad) as cantidadXprecio ";
					$query.=" FROM inv_egresos_detalles vd, inv_egresos v ";
					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND egreso_id=id_egreso AND v.fecha_egreso='".$vFecha['fecha_egreso']."'";
					$query.=" GROUP BY precio ";
					$vventas = $db->query($query)->fetch();
					$nroventas = 0;
					foreach ($vventas as $nro4 => $vventa) { 
						$nroventas++;
						$detalle.=$vventa['cantidadXprecio']." unid. a ".$vventa['precio']." ".$moneda."<br>";
					}
					//si existe mas de un precio se agregara la observacion, sino se deja vacio
					if($nroventas<=1){
						$detalle="";
					} 			

					$costoSTotal+=$costoTotal;
					$importeSTotal+=$importeTotal;
					//echo "costo:".$costoSTotal." / ";
					//echo "importe:".$importeSTotal." / ";				
				} 
				

				$body .= '<tr>';
				$body .= '<td width="10%" align="center">' . escape($nro + 1) . '</td>';
				$body .= '<td width="15%" align="center">' . escape(date_decode($vFecha['fecha_egreso'], $_institution['formato'])).'</td>';
				$body .= '<td width="25%" align="rigth">' . number_format($costoSTotal,2,"."," ") . '</td>';		
				$body .= '<td width="25%" align="rigth">' . number_format($importeSTotal,2,"."," ") . '</td>';
				$body .= '<td width="25%" align="rigth">' . number_format(($importeSTotal-$costoSTotal),2,"."," ") . '</td>';
				$body .= '</tr>';

				$costoSSSTotal+=$costoSTotal;
				$importeSSSTotal+=$importeSTotal;				
			}		
				$body .= '<tr>';
				$body .= '<th width="25%" align="center" style="border:1ps solid #aaa;">TOTAL</th>';
				$body .= '<th width="25%" align="right" style="border:1ps solid #aaa;">'.number_format($costoSSSTotal,2,"."," ").'</th>';
				$body .= '<th width="25%" align="right" style="border:1ps solid #aaa;">'.number_format($importeSSSTotal,2,"."," ").'</th>';
				$body .= '<th width="25%" align="right" style="border:1ps solid #aaa;">'.number_format(($importeSSSTotal-$costoSSSTotal),2,"."," ").'</th>';
				$body .= '</tr>';
	

//$total = number_format($total, 2, '.', '');

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
