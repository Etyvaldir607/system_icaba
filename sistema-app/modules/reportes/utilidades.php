<?php
$costoSSST=0;
$importeSSST=0;

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

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

// Obtiene las ventas
$query="SELECT  *, SUM(cantidad*tamanio)as cantidadAcumul, SUM(precio*cantidad)as importeAcumul ";
$query.=" FROM inv_productos p ";

$query.=" INNER JOIN inv_egresos_detalles vd ON vd.producto_id=p.id_producto ";
$query.=" INNER JOIN inv_egresos v ON (vd.egreso_id=v.id_egreso AND estado='V') ";

$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
			
$query.=" WHERE v.fecha_egreso between '$fecha_inicial' and '$fecha_final' AND v.tipo='Venta' ";
$query.=" GROUP BY p.id_producto ";
$ventas = $db->query($query)->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_cambiar = true;

$costoTotalAcumulado=0;
$precioTotalAcumulado=0;
$utilidadTotalAcumulado=0;

?>
<?php require_once show_template('header-sidebar'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
.centryc{
	width: 100%;
}
.centryc td:nth-child(1){
	width: 25%;
	text-align: center;
}
.centryc td:nth-child(2){
	width: 50%;
}
.centryc td:nth-child(3){
	text-align:right;
	width: 25%;
}
.centryc td span{
	font-size:9px;
	/*font-style:italic;*/
}
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<strong>Reporte de Utilidad Neta</strong>
	</h3>
</div>
<div class="panel-body" data-servidor="<?= ip_local . name_project . '/diario.php'; ?>">
	<?php if ($permiso_cambiar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar la fecha hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar fecha</span></button>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($ventas) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Producto</th>				
				<th class="text-nowrap">Cantidad</th>				
				<th class="text-nowrap">Costos</th>				
				<th class="text-nowrap">Precios</th>				
				<th class="text-nowrap">Costo Total</th>				
				<th class="text-nowrap">Precio Total</th>				
				<th class="text-nowrap">Utilidad</th>				
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Costos</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precios</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Costo Total</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio Total</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Utilidad</th>				
			</tr>
		</tfoot>
		<tbody>
			<?php $total = 0; ?>
			<?php foreach ($ventas as $nro => $venta) { ?>
			
			<?php $cantidadTotal = escape($venta['cantidadAcumul']); ?>
			<?php $precio = escape($venta['precio']); ?>			
			<?php $importeTotal = escape($venta['importeAcumul']); ?>			
			<?php $total = $total + $importeTotal; ?>
			
			<?php
			// Obtiene las ventas y salidas anteriores a la fecha inicial
			$cantidad_ventas=0;
			$query="SELECT SUM(cantidad*tamanio)as cantidadAnterior ";
			$query.=" FROM inv_egresos_detalles vd ";
			$query.=" INNER JOIN inv_egresos v ON (egreso_id=id_egreso AND estado='V') ";
			$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
			$query.=" LEFT JOIN inv_unidades u ON id_unidad=unidad_id ";
			$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_egreso < '$fecha_inicial' ";

			$vAntiguos = $db->query($query)->fetch();
			foreach ($vAntiguos as $nro2 => $vAntiguo) { 
				$cantidad_ventas = escape($vAntiguo['cantidadAnterior']);			
			}
			?>

			<?php
			$costo=0;
			$costoTotal=0;
			$tamanio=0;
			$unidad="";
			$prodIngresados=0;
			$saldo=0;
			$prodAc=0;						//
			$ingresoSW=true;				//se termino de obtener los costos
			$detalleCompra="COSTOS:<br>";	//agregar en observaciones
			$detalleCompra="";	//agregar en observaciones
			
			$ultimoSaldo=0;					
			$ultimoTamanio=0;					
			$ultimoCosto=0;
			$ultimaUnidad="";
			$nrocompras = 0;
			
			//se obtiene las compras desde inicio de la empresa hasta la fecha limite solicitada por el usuario
			$query="SELECT  *, u.tamanio, u.unidad ";
			$query.=" FROM inv_ingresos_detalles vd ";
			$query.=" INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
			$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
			$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
			$query.=" WHERE vd.producto_id='".$venta['id_producto']."' ";
			//$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_ingreso <= '$fecha_final' ";
			$query.=" ORDER BY fecha_ingreso, u.tamanio, u.unidad ";
			$iAntiguos = $db->query($query)->fetch();
			foreach ($iAntiguos as $nro3 => $iAntiguo) { 
				$prodIngresados=$prodIngresados+($iAntiguo['cantidad']*$iAntiguo['tamanio']);
				//se compara los productos previamente vendidos y costos antiguos
				//para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
				if($prodIngresados>$cantidad_ventas AND $ingresoSW){
					//verificar si es el primer Ingreso
					if($saldo>0){
						$saldo=$prodIngresados-$cantidad_ventas;						
					}
					else{
						$saldo=$iAntiguo['cantidad']*$iAntiguo['tamanio'];	
					}

					if($prodAc+$saldo<=$cantidadTotal){
						$saldo=$saldo;						
					}
					else{
						$saldo=$cantidadTotal-$prodAc;
						$ingresoSW=false;						
					}					
					
					$prodAc=$prodAc+$saldo;											
					
					$costoTotal+=$saldo*($iAntiguo['costo']/$iAntiguo['tamanio']);
					$costo=$iAntiguo['costo'];
					$tamanio=$iAntiguo['tamanio'];
					$unidad=$iAntiguo['unidad'];

					//verificar si hay un nuevo Costo
					if(($ultimoCosto!=$costo && $ultimoCosto!=0) || ($ultimaUnidad!="" && $ultimaUnidad!=$unidad) ){
						$subtotal=$ultimoCosto*$ultimoSaldo/$ultimoTamanio;
						$subtotal=number_format($subtotal,2,"."," ");
						$ultimoSaldo=Fracciones($ultimoSaldo,$ultimoTamanio);
						$detalleCompra.="<tr><td>".$ultimoSaldo."</td><td><b>".$ultimaUnidad."</b> ".$ultimoCosto." ".$moneda."</td><td>".$subtotal." ".$moneda."</td></tr>";				
						$ultimoSaldo=$saldo;
						$ultimoCosto=$costo;
						$ultimaUnidad=$unidad;
						$ultimoTamanio=$tamanio;						
						$nrocompras++;
					}
					else{
						$ultimoSaldo+=$saldo;
						$ultimoCosto=$costo;
						$ultimaUnidad=$unidad;	
						$ultimoTamanio=$tamanio;					
					}
					
				}
				//echo $saldo." ... ";		
			}

			//echo $costoTotal." ".$saldo;
			
			if($ultimoSaldo!=0){
				//asignar observaciones una vez acabado el foreach
				//$detalleCompra.=$ultimoSaldo." unid. a ".$ultimoCosto." ".$moneda."<br>";
				//echo " /// ".$ultimoSaldo." /// ";
				
				$subtotal=$ultimoCosto*$ultimoSaldo/$ultimoTamanio;
				$subtotal=number_format($subtotal,2,"."," ");
				$ultimoSaldo=Fracciones($ultimoSaldo,$ultimoTamanio);
				$detalleCompra.="<tr><td>".$ultimoSaldo."</td><td><b>".$ultimaUnidad."</b> ".$ultimoCosto." ".$moneda."</td><td>".$subtotal." ".$moneda."</td></tr>";				
			}
			
			$swCostoEstimado=false;

			//echo "<br>".$cantidadTotal.">".$prodAc;

			if($cantidadTotal>$prodAc){
				$query="SELECT  costo, u.unidad ";
				$query.=" FROM inv_ingresos_detalles vd ";
				$query.=" INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
				$query.=" INNER JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
				$query.=" INNER JOIN inv_unidades u ON u.id_unidad=a.unidad_id AND tamanio='1' ";
				$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_ingreso <= '$fecha_final' ";
				$query.=" ORDER BY fecha_ingreso DESC ";
				$iUltimo = $db->query($query)->fetch_first();
				
				if($iUltimo){
					$ultimoSaldo=$cantidadTotal-$saldo;
					$ultimaUnidad=$iUltimo['unidad'];
					$ultimoCosto=$iUltimo['costo'];
					$subtotal=$ultimoSaldo*$ultimoCosto;
					$detalleCompra.="<tr><td style='color:#f00;'>".$ultimoSaldo."</td><td style='color:#f00;'><b>".$ultimaUnidad."</b> ".$ultimoCosto." ".$moneda."</td><td style='color:#f00;'>". number_format($subtotal,'2','.','') ." ".$moneda."</td></tr>";				

					$swCostoEstimado=true;

					$costoTotal+=$subtotal;													
				}
				else{
					$query="SELECT  costo, u.unidad, u.tamanio ";
					$query.=" FROM inv_ingresos_detalles vd ";
					$query.=" INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
					$query.=" INNER JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
					$query.=" INNER JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_ingreso <= '$fecha_final' ";
					$query.=" ORDER BY fecha_ingreso DESC ";
					$iUltimo = $db->query($query)->fetch_first();					

					$ultimoSaldo=$cantidadTotal-$saldo;
					$ultimaUnidad=$iUltimo['unidad'];
					$ultimoCosto=$iUltimo['costo'];
					$subtotal=$ultimoSaldo* ($ultimoCosto/$iUltimo['tamanio']);					

					$ultimoSaldo=Fracciones($ultimoSaldo,$iUltimo['tamanio']);
						

					$detalleCompra.="<tr><td style='color:#f00;'>".$ultimoSaldo."</td><td style='color:#f00;'><b>".$ultimaUnidad."</b> ".$ultimoCosto." ".$moneda."</td><td style='color:#f00;'>". number_format($subtotal,'2','.','') ." ".$moneda."</td></tr>";				

					$swCostoEstimado=true;

					$costoTotal+=$subtotal;						
				}
			}
		
			$detalleCompra="<table class='centryc'>".$detalleCompra."</table>";				
			
			if($swCostoEstimado){
				$detalleCompra.="<span style='color:#f00;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp* Costos estimados, segun al ultimo costo de compra</span>";		
			}//Los detalle de compra no se muestran
			?>


			<?php
			$detalle="";
			//Listar los diferentes precios a los que se a 		vendido un producto		o salio un producto			
			$query="SELECT u.unidad, u.tamanio,  precio, SUM(cantidad) as cantidadXprecio ";
			$query.=" FROM inv_egresos_detalles vd ";
			$query.=" LEFT JOIN inv_egresos v ON (egreso_id=id_egreso AND estado='V') ";

			$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
			$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";

			$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_egreso between '$fecha_inicial' and '$fecha_final' AND v.tipo='venta'";
			$query.=" GROUP BY precio, u.tamanio, unidad ";

			$vventas = $db->query($query)->fetch();
			$nroventas = 0;
			foreach ($vventas as $nro3 => $vventa) { 
				$nroventas++;
				$subtotal=$vventa['precio']*$vventa['cantidadXprecio'];
				$detalle.="<tr><td>".$vventa['cantidadXprecio']."</td><td><b>".$vventa['unidad']."</b> "." de ".$vventa['precio']." ".$moneda."</td><td>".$subtotal." ".$moneda."</td></tr>";
			}
			$detalle="<table class='centryc'>".$detalle."</table>";	

			//echo "<br>";			
			?>

			<tr>
				<td><?= $nro + 1; ?></td>
				<td>
					<?= escape($venta['nombre']); ?>
				</td>				
				<td>
					<?= $cantidadTotal; ?>
				</td>				
				<td class="text-nowrap">
					<?php 
						echo $detalleCompra;
					?>
				</td>				
				<td class="text-nowrap">
					<?php 
						echo $detalle;
					?>
				</td>				
				<td class="text-nowrap text-right" data-costo="<?= $costoTotal; ?>">
					<b><?= number_format($costoTotal,2,"."," "); ?></b>
				</td>				
				<td class="text-nowrap text-right" data-precio="<?= $importeTotal; ?>">
					<b><?= number_format($importeTotal,2,"."," "); ?></b>
				</td>				
				<td class="text-nowrap text-right" data-total="<?= ($importeTotal-$costoTotal); ?>">
					<b><?php 
						if($importeTotal-$costoTotal>0){
							?>
							<span class="text-success">
							<?php	
						} 
						else{
							?>
							<span class="text-danger">
							<?php	
						} 
						echo number_format(($importeTotal-$costoTotal),2,"."," ")."</span>";

						$costoTotalAcumulado+=$costoTotal;
						$precioTotalAcumulado+=$importeTotal;
						$utilidadTotalAcumulado+=$importeTotal-$costoTotal;
					?></b>
				</td>				
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ventas electrónicas registrados en la base de datos.</p>
	</div>
	<?php } ?>
<div class="well">
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Costo Total:</b>
			<u id="costototal"><?= number_format($costoTotalAcumulado,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Precio Total:</b>
			<u id="preciototal"><?= number_format($precioTotalAcumulado,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Utilidad Total:</b>
			<u id="total"><?= number_format($utilidadTotalAcumulado,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="clearfix">
	</div>
</div>

<!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
<div id="modal_fecha" class="modal fade">
	<div class="modal-dialog">
		<form id="form_fecha" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar fecha</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="inicial_fecha">Fecha inicial:</label>
							<input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="final_fecha">Fecha final:</label>
							<input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-aceptar="true">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Aceptar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
		</form>
	</div>
</div>

<?php 
	//echo "costo:".$costoSSST;;
	//echo "importe:".$importeSSST;
?>

<?php } ?>
<!-- Fin modal fecha -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_cambiar) { ?>
	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');
	var $inicial_fecha = $('#inicial_fecha');
	var $final_fecha = $('#final_fecha');

	$.validate({
		form: '#form_fecha',
		modules: 'date',
		onSuccess: function () {
			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/reportes/utilidades' + inicial_fecha + final_fecha;
		}
	});

	//$inicial_fecha.mask(mascara).datetimepicker({
	$inicial_fecha.datetimepicker({
		format: formato
	});

	//$final_fecha.mask(mascara).datetimepicker({
	$final_fecha.datetimepicker({
		format: formato
	});

	$inicial_fecha.on('dp.change', function (e) {
		$final_fecha.data('DateTimePicker').minDate(e.date);
	});
	
	$final_fecha.on('dp.change', function (e) {
		$inicial_fecha.data('DateTimePicker').maxDate(e.date);
	});

	var $form_fecha = $('#form_fecha');
	var $modal_fecha = $('#modal_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('show.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('[data-aceptar]').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$modal_fecha.find('[data-aceptar]').on('click', function () {
		$form_fecha.submit();
	});

	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ventas) { ?>
	var table = $('#table').on('search.dt order.dt page.dt length.dt', function () {
		/*
		var suma = 0;
		$('[data-costo]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-costo'));
			suma = suma + total;
		});
		$('#costototal').text(suma.toFixed(2));

		suma = 0;
		$('[data-precio]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-precio'));
			suma = suma + total;
		});
		$('#preciototal').text(suma.toFixed(2));

		suma = 0;
		$('[data-total]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total'));
			suma = suma + total;
		});
		$('#total').text(suma.toFixed(2));
		*/
	}).DataFilter({	
		filter: true,
		name: 'reporte_diario',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php 


function Fracciones($ultimoSaldo,$ultimoTamanio){
	$str="";
	if($ultimoTamanio!=1){
		$modulo=$ultimoSaldo%$ultimoTamanio;		
		$entero=($ultimoSaldo-$modulo)/$ultimoTamanio;
		
		if($entero!=0){
			$str.=$entero;
		}
		if($entero!=0 && $modulo!=0){
			$str.=" ";
		}
		if($modulo!=0){
			$str.="<span>".$modulo." / ".$ultimoTamanio."</span>";
		}		
	}
	else{
		$str=$ultimoSaldo;
	}
	return $str;	
}



require_once show_template('footer-sidebar'); 



?>