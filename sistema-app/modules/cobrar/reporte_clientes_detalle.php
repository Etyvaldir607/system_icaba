<?php

// Obtiene el id_ingreso
$id_ingreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los ingreso
$proveedor = $db->select('*')
			->from('inv_egresos i')
			->where('i.id_egreso', $id_ingreso)
			->fetch_first();

// Obtiene los ingreso
$ttipo="Venta";
$ingreso = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, p.*, i.tipo tipo_venta, i.descripcion description')
			->from('inv_egresos i')
			->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			->join('inv_pagos p', 'p.movimiento_id = i.id_egreso                      AND                                  p.tipo="Egreso"', 'left')
			->where('nombre_cliente', $proveedor['nombre_cliente'])
			->where('nit_ci', $proveedor['nit_ci'])
			->where('i.tipo', $ttipo)
			->where('i.estado', 'V')
			->order_by('fecha_egreso')
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
		<strong>Reporte de Cliente</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de almacenes hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/cobrar/reporte_clientes" class="btn btn-primary">
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
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i>Información del cliente</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
							<label class="col-md-3 control-label">Nombre cliente:</label>
							<div class="col-md-3">
								<p class="form-control-static"><?= escape($proveedor['nombre_cliente']); ?></p>
							</div>
							<label class="col-md-3 control-label">NIT / CI:</label>
							<div class="col-md-3">
								<p class="form-control-static"><?= escape($proveedor['nit_ci']); ?></p>
							</div>
						
					</div>
				</div>
			</div>

			<?php foreach ($ingreso as $nro => $ingresodt) { ?>
			
			<?php
			$detallesCuota = $db->select('*')
						   ->from('inv_pagos_detalles pd')
						   ->where('pd.pago_id', $ingresodt['id_pago'])
						   ->order_by('fecha asc')
						   ->fetch();
			?>


			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i>Información del ingreso</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="col-md-6">					
							<label class="col-md-3 control-label">Fecha y hora:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape(date_decode($ingresodt['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingresodt['hora_egreso']); ?></small></p>
							</div>
							<div class="clearfix"></div>
							<label class="col-md-3 control-label">Descripción:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingresodt['description']); ?></p>
							</div>
							<div class="clearfix"></div>							
						</div>
						<div class="col-md-6">					
							<label class="col-md-3 control-label">Monto total:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingresodt['monto_total']); ?></p>
							</div>
							<div class="clearfix"></div>
							
							<label class="col-md-3 control-label">Tipo de Pago:</label>
							<div class="col-md-9">
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
									<?php //if ($permiso_suprimir) { ?>
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
									<?php //} ?>
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
						<button id="idx<?= $ingresodt['id_egreso']; ?>" class="btn btn-success" data-cambiar="true" style="width: 100%;">
							<i class="glyphicon glyphicon-calendar"></i><span> Ver Detalle</span>
						</button>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg text-center">
						<br>
					</div>
					<div class="col-xs-12 col-sm-4 text-center">
						<a href="?/cobrar/notas_ver/<?= $ingresodt['id_egreso']; ?>">
						<button id="idy<?= $ingresodt['id_egreso']; ?>" class="btn btn-success" data-cambiar="true" style="width: 100%;">
							<i class="glyphicon glyphicon-calendar"></i><span> Pagar Cuotas</span>
						</button>
						</a>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg text-center">
						<br>
					</div>
					<div class="col-xs-12 col-sm-4 text-center">
						<a href="?/egresos/imprimir/<?= $ingresodt['id_egreso']; ?>" target="_blank">
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
				$detalles = $db->select('d.*, p.*')
						   ->from('inv_egresos_detalles d')
						   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
						   ->where('d.egreso_id', $ingresodt['id_egreso'])
						   ->order_by('id_detalle asc')
						   ->fetch();
			?>
									
			<div class="panelSuperior panel_visibleidx<?php echo $ingresodt['id_egreso']; ?>">
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
									<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
									<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
									<?php if ($permiso_suprimir) { ?>
									<th class="text-nowrap">Opciones</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr>
									<?php $cantidad = escape($detalle['cantidad']); ?>
									<?php $precio = escape($detalle['precio']); ?>
									<?php $importe = $cantidad * $precio; ?>
									<?php $total = $total + $importe; ?>
									<th class="text-nowrap"><?= $nro + 1; ?></th>
									<td class="text-nowrap"><?= escape($detalle['nombre_factura']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad; ?></td>
									<td class="text-nowrap text-right"><?= $precio; ?></td>
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
									<?php if ($permiso_suprimir) { ?>
									<td class="text-nowrap">
										<a href="?/egresos/suprimir/<?= $egreso['id_egreso']; ?>/<?= $detalle['id_detalle']; ?>" data-toggle="tooltip" data-title="Eliminar detalle" data-suprimir="true"><span class="glyphicon glyphicon-trash"></span></a>
									</td>
									<?php } ?>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="4">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
									<?php if ($permiso_suprimir) { ?>
									<th class="text-nowrap">Opciones</th>
									<?php } ?>
								</tr>
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle que especifique la operación realizada.</p>
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
			<!--button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar fecha</span></button-->




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
			backdrop: 'static',
			size:'large'
		});
		$('.panelSuperior').css({'display':'none'}); 
		$('.panel_visible'+active).css({'display':'block'}); 
	});	
});
</script>
<?php require_once show_template('footer-sidebar'); ?>