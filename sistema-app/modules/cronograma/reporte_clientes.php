<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene las ventas 
 	
$ventas = $db->select('*, COUNT(id_egreso) as nro_ventas')
				->from('inv_egresos')
				->where('tipo','Venta')
				->where('estado','V')
				->group_by('nombre_cliente')
				->group_by('nit_ci')
				->order_by('nombre_cliente')
				->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_cambiar = true;

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
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Reporte de Clientes</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($ventas) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Cliente</th>
				<th class="text-nowrap">NIT/CI</th>
				<th class="text-nowrap">Nro Ventas</th>
				<th class="text-nowrap">Detalles</th>
				<th class="text-nowrap">Ver</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Cliente</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">NIT/CI</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nro Ventas</th>				
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Detalle</th>				
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Ver</th>				
			</tr>
		</tfoot>
		<tbody>
			<?php 
			foreach ($ventas as $nro => $venta) { 
				// Obtiene las ventas
				$ttipo="Egreso";
				$detalle = $db->select('*, COUNT(pd.estado)as deuda')
								->from('inv_egresos i')
								->join('inv_pagos p', 'p.movimiento_id = i.id_egreso', 'inner')
								->join('inv_pagos_detalles pd', 'pd.pago_id = p.id_pago', 'inner')
								->where('i.nombre_cliente', $venta['nombre_cliente'])
								->where('i.nit_ci', $venta['nit_ci'])
								->where('pd.estado', 0)
								->where('i.estado', 'V')
								->where('p.tipo', $ttipo)
								->group_by('nombre_cliente')
								->group_by('nit_ci')
								->fetch_first();
			?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($venta['nombre_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($venta['nit_ci']); ?></td>				
				<td class="text-nowrap"><?= escape($venta['nro_ventas']); ?></td>				
				<td class="text-nowrap"><?php 
					if($detalle['deuda']>0){ ?>
						<span class="text-danger"><b>Tiene cuentas pendientes</b></span> 
					<?php }else{  ?>
						<span class="text-success"><b>Cuentas sl dia</b></span>
					<?php } 
				?></td>				
				<td class="text-nowrap"><a href="?/cobrar/reporte_clientes_detalle/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle del cliente"><i class="glyphicon glyphicon-list-alt"></i></a></td>				
			</tr>
			<?php 
			} 
			?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ventas electrónicas registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_cambiar) { ?>
	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ventas) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'reporte_ventas_generales',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>