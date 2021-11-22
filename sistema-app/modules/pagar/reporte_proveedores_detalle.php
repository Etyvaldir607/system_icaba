<?php

// Obtiene el id_ingreso
$id_ingreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los ingreso
$proveedor = $db->select('*')
			->from('inv_ingresos i')
			->where('i.id_ingreso', $id_ingreso)
			->fetch_first();

// Obtiene los ingreso
$ingreso = $db->select('i.*, a.*, e.*, p.*, i.tipo tipo_venta')
			->from('inv_ingresos i')
			->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			->join('inv_pagos p', 'p.movimiento_id = i.id_ingreso                      AND                                  p.tipo="Ingreso"', 'left')
			->where('nombre_proveedor', $proveedor['nombre_proveedor'])
			->order_by('fecha_ingreso')
			->fetch();

// Verifica si existe el ingreso
if (!$ingreso) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_listar = in_array('listar', $permisos);
$permiso_suprimir = in_array('suprimir', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>


<style>
@media (min-width: 768px) { 
	.modal-dialog{
		width: 700px;
	}
}
@media (min-width: 992px) { 
	.modal-dialog{
		width: 950px;
	}
}
/* 
    xs= 0-767 pixels
    sm = 768-991 pixels
    md = 992-1199 pixels
    lg = 1200 pixels and up
*/
</style>

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Proveedor</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de almacenes hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/pagar/reporte_proveedores" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Listado</span>
			</a>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i>Información del Proveedor</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
							<label class="col-md-3 control-label">Nombre proveedor:</label>
							<div class="col-md-3">
								<p class="form-control-static"><?= escape($proveedor['nombre_proveedor']); ?></p>
							</div>
					</div>
				</div>
			</div>

			<?php foreach ($ingreso as $nro => $ingresodt) { ?>
			
			<?php
			
			// Obtiene los detalles de la cuotas
			$detallesCuota = $db->select('*')
						   ->from('inv_pagos_detalles pd')
						   ->where('pd.pago_id', $ingresodt['id_pago'])
						   ->order_by('nro_cuota asc')
						   ->fetch();
			?>


			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i>Información del ingreso</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="col-md-6">					
							<label class="col-md-4 control-label">Fecha y hora:</label>
							<div class="col-md-8">
								<p class="form-control-static"><?= escape(date_decode($ingresodt['fecha_ingreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingresodt['hora_ingreso']); ?></small></p>
							</div>
							<div class="clearfix"></div>
							<label class="col-md-4 control-label">Descripción:</label>
							<div class="col-md-8">
								<p class="form-control-static"><?= escape($ingresodt['descripcion']); ?></p>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="col-md-6">					
							<label class="col-md-4 control-label">Monto total:</label>
							<div class="col-md-8">
								<p class="form-control-static"><?= escape($ingresodt['monto_total']); ?></p>
							</div>
							<div class="clearfix"></div>
							<label class="col-md-4 control-label">Tipo de Pago:</label>
							<div class="col-md-8">
								<?php if (escape($ingresodt['plan_de_pagos'])=="si"){ ?>
									<p class="form-control-static">Plan de Pagos</p>
								<?php }else{ ?>
									<p class="form-control-static">Pago Completo</p>
								<?php } ?>
							</div>
							<div class="clearfix"></div>
						</div>						
					</div>
				</div>
				
				<?php if($ingresodt['id_pago']!=""){ ?>
				
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle del las cuotas</h3>
				</div>
				<div class="panel-body">
					<?php if ($detallesCuota) { ?>
					<div class="table-responsive">
						<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Descripción</th>
									<th class="text-nowrap">Fecha Programada</th>
									<th class="text-nowrap">Fecha de Pago</th>
									<th class="text-nowrap">Tipo de Pago</th>
									<th class="text-nowrap">Monto <?= escape($moneda); ?></th>
									<th class="text-nowrap">Estado</th>									
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detallesCuota as $nro => $detalle) { 
									$total=$total+$detalle['monto'];
								?>
								<tr>
									<th class="text-nowrap"><?= $nro + 1; ?></th>
									<td class="text-nowrap"><?php echo "Cuota #".($nro+1); ?></td>
									<td class="text-nowrap text-center"><?php if($detalle['fecha']!="0000-00-00"){	echo escape(date_decode($detalle['fecha'], $_institution['formato'])); } ?></td>
									<td class="text-nowrap text-center"><?php if($detalle['fecha_pago']!="0000-00-00"){	echo escape(date_decode($detalle['fecha_pago'], $_institution['formato'])); } ?></td>
									<td class="text-nowrap text-center"><?= escape($detalle['tipo_pago']); ?></td>									
									<td class="text-nowrap text-right"><?= number_format($detalle['monto'], 2, '.', ''); ?></td>
									<td class="text-nowrap text-center">
										<?php 
										if($detalle['estado']==0){
											?><span class="text-danger"><b>Pendiente</b></span><?php
										}else{
											?><span class="text-success"><b>Cancelado</b></span><?php
										}
										?>
									</td>									
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap"></th>
								</tr>
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Este ingreso no tiene detalle, es muy importante que todos los ingresos cuenten con un detalle de la compra.</p>
					</div>
					<?php } ?>
				</div>

			<?php } ?>
				
				<div class="col-xs-12 col-lg-2 col-md-1 col-sm-0 text-center"><br></div>
				<div class="col-xs-12 col-lg-8 col-md-10 col-sm-12">				
					<div class="col-xs-12 col-sm-4 text-center">
						<button id="idx<?= $ingresodt['id_ingreso']; ?>" class="btn btn-success" data-cambiar="true" style="width: 100%;">
							<i class="glyphicon glyphicon-calendar"></i><span> Ver Detalle</span>
						</button>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg text-center">
						<br>
					</div>
					<div class="col-xs-12 col-sm-4 text-center">
						<a href="?/pagar/ver/<?= $ingresodt['id_ingreso']; ?>">
						<button id="idy<?= $ingresodt['id_ingreso']; ?>" class="btn btn-success" data-cambiar="true" style="width: 100%;">
							<i class="glyphicon glyphicon-calendar"></i><span> Pagar Cuotas</span>
						</button>
						</a>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg text-center">
						<br>
					</div>
					<div class="col-xs-12 col-sm-4 text-center">
						<a href="?/ingresos/imprimir/<?= $ingresodt['id_ingreso']; ?>" target="_blank">
						<button class="btn btn-success" style="width: 100%;">
							<i class="glyphicon glyphicon-calendar"></i><span> Imprimir</span>
						</button>
						</a>
					</div>
					<div class="clearfix"></div>
					<br>
				</div>	
				<div class="clearfix"></div>
			</div>
			<?php
			}
			?>	
		</div>
	</div>
</div>






<div id="modal_fecha" class="modal fade">
	<div class="modal-dialog">
		<form id="form_fecha" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar fecha</h4>
			</div>
			<div class="modal-body">

			<?php foreach ($ingreso as $nro => $ingresodt) { 
				$detalles = $db->select('d.*, p.codigo, p.nombre')
						   ->from('inv_ingresos_detalles d')
						   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
						   ->where('d.ingreso_id', $ingresodt['id_ingreso'])
						   ->order_by('id_detalle asc')
						   ->fetch();
			?>
									
			<div class="panelSuperior panel_visibleidx<?php echo $ingresodt['id_ingreso']; ?>">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle del egreso</h3>
				</div>
				<div class="panel-body">

					<?php if ($detalles) { ?>					
					<div class="table-responsive">
						<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Costo <?= escape($moneda); ?></th>
									<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
									
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr>
									<?php $cantidad = escape($detalle['cantidad']); ?>
									<?php $costo = escape($detalle['costo']); ?>
									<?php $importe = $cantidad * $costo; ?>
									<?php $total = $total + $importe; ?>
									<th class="text-nowrap"><?= $nro + 1; ?></th>
									<td class="text-nowrap"><?= escape($detalle['nombre']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad; ?></td>
									<td class="text-nowrap text-right"><?= $costo; ?></td>
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
									
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="4">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
									
								</tr>
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Este ingreso no tiene detalle, es muy importante que todos los ingresos cuenten con un detalle de la compra.</p>
					</div>
					<?php } ?>

				</div>
			</div>
			</div>
			<?php
			}
			?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
		</form>
	</div>
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
	var $modal_fecha = $('#modal_fecha');

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$('[data-cambiar]').on('click', function () {
		active=$(this).attr('id');
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
		$('.panelSuperior').css({'display':'none'}); 
		$('.panel_visible'+active).css({'display':'block'}); 
	});	
});
</script>
<?php require_once show_template('footer-sidebar'); ?>