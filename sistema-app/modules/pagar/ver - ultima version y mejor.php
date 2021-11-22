<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');



// Obtiene el id_ingreso
$id_ingreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los ingreso
$ingreso = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, i.plan_de_pagos, p.interes_pago, p.id_pago')
			  ->from('inv_ingresos i')
			  ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			  ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			  ->join('inv_pagos p', 'p.movimiento_id = i.id_ingreso                      AND                                  p.tipo="Ingreso"', 'left')
			  ->where('id_ingreso', $id_ingreso)
			  ->fetch_first();

// Verifica si existe el ingreso
if (!$ingreso) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los detalles
$detalles = $db->select('d.*, p.codigo, p.nombre')
			   ->from('inv_ingresos_detalles d')
			   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
			   ->where('d.ingreso_id', $id_ingreso)
			   ->order_by('id_detalle asc')
			   ->fetch();

$detallesCuota = $db->select('*')
			   ->from('inv_pagos_detalles pd')
			   ->where('pd.pago_id', $ingreso['id_pago'])
			   ->order_by('nro_cuota, fecha asc, fecha_pago asc')			   
			   ->fetch();

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

$permiso_guardar_pago = in_array('guardar_pago', $permisos);
$permiso_eliminar_pago = in_array('eliminar_pago', $permisos);
$permiso_imprimir_comprobante = in_array('imprimir_comprobante', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>

<style>
.table-responsive{ 
	overflow-y:visible; 
	overflow-x:visible; 
	overflow:visible; 
}
#cuentasporpagar td{
	padding:0; height: 0; border-width: 0px;
}
.cuota_div{
	height:0; overflow: hidden;
}
</style>

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Ver ingreso</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_eliminar || $permiso_imprimir || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>		
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>

<form id="fromii" class="form-horizontal" autocomplete="off">
	
<input id="pago" name="pago" type="hidden" value="<?php echo $ingreso['id_pago']; ?>">

<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary" data-formato="<?= strtoupper($formato_textual); ?>">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle del ingreso</h3>
				</div>
				<div class="panel-body">
					<?php if ($detalles) { ?>
					<div class="table-responsive">
						<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Código</th>
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
									<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
									<td class="text-nowrap"><?= escape($detalle['nombre']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad; ?></td>
									<td class="text-nowrap text-right"><?= $costo; ?></td>
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right">
										<?php
											echo number_format($total, 2, '.', '');
											$total=$total+($total*$ingreso["interes_pago"]/100);
										?>
										<input id="totalProducto" type='hidden' value="<?= $total ?>">												
									</th>
								</tr>
								<?php if($ingreso["interes_pago"]>0){ ?>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">+ INTERES <?= escape($ingreso["interes_pago"]); ?>%</th>
									<th class="text-nowrap text-right">
										<?php
											echo number_format($total, 2, '.', ''); 
										?>										
									</th>
								</tr>
								<?php } ?>								
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
			
			<?php if (escape($ingreso['plan_de_pagos'])=="si"){ ?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle del las cuotas</h3>
				</div>
				<div class="panel-body">
					<?php if ($detallesCuota) { ?>
					<div class="table-responsive">
						<table id="cuentasporpagar" class="table table-bordered table-condensed table-restructured table-striped table-hover">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Descripción</th>
									<th class="text-nowrap">Fecha Programada</th>
									<th class="text-nowrap">Fecha de Pago</th>
									<th class="text-nowrap">Tipo de Pago</th>
									<th class="text-nowrap">Monto <?= escape($moneda); ?></th>
									<th class="text-nowrap">Estado</th>
									
									<?php if($permiso_guardar_pago){ ?>
									<th class="text-nowrap">Guardar</th>									
									<?php } ?>
									<?php if($permiso_imprimir_comprobante){ ?>
									<th class="text-nowrap">Imprimir</th>									
									<?php } ?>
									<?php if($permiso_eliminar_pago){ ?>
									<th class="text-nowrap">Eliminar</th>									
									<?php } ?>
									
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detallesCuota as $nro => $detalle) { 
									$total=$total+$detalle['monto'];
									$nrx=($nro+1);
								?>
								<tr>
									<td class="text-nowrap"><div class="cuota_div" data-cuota="<?= $nrx ?>"><?= $nrx ?></div></td>
									<td class="text-nowrap detalle"><div class="cuota_div" data-cuota="<?= $nrx ?>"><?php echo "Cuota #".$nrx; ?></div></td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<div class="col-md-12">											
											<input type="hidden" id="f0<?= $nrx ?>" name="f0<?= $nrx ?>" value="<?= $detalle['id_pago_detalle']; ?>">					
											<input type="hidden" class="fxx" id="fx<?= $nrx ?>" name="fx<?= $nrx ?>" value="<?= $nrx ?>">					
											<input type="text" id="fecha<?= $nrx ?>" name="fecha<?= $nrx ?>" value="<?= date_decode($detalle['fecha'], $_institution['formato']); ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
											<span class="help-block form-error fechaerror" id="fechaerror<?= $nrx ?>" style="color:#a94442;"></span>
										</div></div>										
									</td>	
									<?php //if ($permiso_suprimir) { ?>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<div class="col-md-12">
											<input type="text" id="fechap<?= $nrx ?>" name="fechap<?= $nrx ?>" value="<?= escape(date_decode($detalle['fecha_pago'], $_institution['formato'])); ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
											<span class="help-block form-error fechaperror" id="fechaperror<?= $nrx ?>" style="color:#a94442;"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<div class="col-md-12">
											<select name="tipo<?= $nrx ?>" id="tipo<?= $nrx ?>" class="form-control" data-validation="required">
												<option value=""> - Seleccione una opción</option>
												<option value="Efectivo">Efectivo</option>
												<option value="Deposito">Deposito</option>
												<option value="Cheque">Cheque</option>							
											</select>
											<span class="help-block form-error tipoerror" id="tipoerror<?= $nrx ?>" style="color:#a94442;"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-right"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<div class="col-md-12">
											<input type="text" name="monto<?= $nrx ?>" value="<?= number_format($detalle['monto'], 2, '.', ''); ?>" id="monto<?= $nrx ?>" class="form-control  text-right" maxlength="10" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" data-montocuota="" onchange="calcular_cuota(0);">	
											<span id="montoerror<?= $nrx ?>" class="text-danger" data-montocuota<?= $nrx ?>="0"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<?php 
										if($detalle['estado']==0){
											?>
												<input type="hidden" id="estadohidden<?= $nrx ?>" value="0">
												<span id="estado<?= $nrx ?>" class="text-danger"><b>Pendiente</b></span>
											<?php
										}else{
											?>
												<input type="hidden" id="estadohidden<?= $nrx ?>" value="1">
												<span id="estado<?= $nrx ?>" class="text-success"><b>Cancelado</b></span>
											<?php
										}
										?></div>										
									</td>
									<?php if($permiso_guardar_pago){ ?>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<span class="glyphicon glyphicon-save" onclick="javascript:saveData(<?= $nrx ?>);"></span>
									</div></td>
									<?php } ?>
									<?php if($permiso_imprimir_comprobante){ ?>
									<td class="text-nowrap text-center" id="imprimir<?= $nrx ?>"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<?php 
										if($detalle['estado']==1){
											?><a href="?/pagar/imprimir_comprobante/<?= $detalle['id_pago_detalle']; ?>" target="_blank" data-toggle="tooltip" data-title="Imprimir comprobante"><span class="glyphicon glyphicon-print"></span></a>
										<?php } ?>
									</div></td>
									<?php } ?>
									<?php if($permiso_eliminar_pago){ ?>
									<td class="text-nowrap text-center" id="eliminar<?= $nrx ?>"><div class="cuota_div" data-cuota="<?= $nrx ?>">
										<?php 
										if($detalle['estado']==0){										
										?>
											<span class="glyphicon glyphicon-remove" onclick="javascript:eliminar_cuota(<?= $nrx ?>,<?= $detalle['id_pago_detalle']; ?>);"></span>
										<?php
										}
										?>	
									</div></td>									
									<?php } ?>
								</tr>
								<?php } ?>

								<?php for ($i=($nro+2); $i<=36; $i++) { ?>

								<tr>
									<td class="text-nowrap"><div class="cuota_div" data-cuota="<?= $i ?>"><?= $i; ?></div></td>
									<td class="text-nowrap detalle"><div class="cuota_div" data-cuota="<?= $i ?>"><?php echo "Cuota #".$i; ?></div></td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $i ?>">
										<div class="col-md-12">											
											<input type="hidden" id="f0<?= $i ?>" name="f0<?= $i ?>" value="<?= $detalle['id_pago_detalle']; ?>">					
											<input type="hidden" class="fxx" id="fx<?= $i ?>" name="fx<?= $i ?>" value="<?= $i ?>">					
											<input type="text" id="fecha<?= $i ?>" name="fecha<?= $i ?>" value="<?= date_decode($detalle['fecha'], $_institution['formato']); ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
											<span class="help-block form-error fechaerror" id="fechaerror<?= $i ?>" style="color:#a94442;"></span>
										</div></div>										
									</td>	
									<?php //if ($permiso_suprimir) { ?>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $i ?>">
										<div class="col-md-12">
											<input type="text" id="fechap<?= $i ?>" name="fechap<?= $i ?>" value="<?= escape(date_decode($detalle['fecha_pago'], $_institution['formato'])); ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
											<span class="help-block form-error fechaperror" id="fechaperror<?= $i ?>" style="color:#a94442;"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $i ?>">
										<div class="col-md-12">
											<select name="tipo<?= $i ?>" id="tipo<?= $i ?>" class="form-control" data-validation="required">
												<option value=""> - Seleccione una opción</option>
												<option value="Efectivo">Efectivo</option>
												<option value="Deposito">Deposito</option>
												<option value="Cheque">Cheque</option>							
											</select>
											<span class="help-block form-error tipoerror" id="tipoerror<?= $i ?>" style="color:#a94442;"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-right"><div class="cuota_div" data-cuota="<?= $i ?>">
										<div class="col-md-12">
											<input type="text" name="monto<?= $i ?>" value="<?= number_format($detalle['monto'], 2, '.', ''); ?>" id="monto<?= $i ?>" class="form-control  text-right" maxlength="10" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" data-montocuota="" onchange="calcular_cuota(0);">	
											<span id="montoerror<?= $i ?>" class="text-danger" data-montocuota<?= $i ?>="0"></span>
										</div></div>
									</td>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $i ?>">
										<?php 
										if($detalle['estado']==0){
											?>
												<input type="hidden" id="estadohidden<?= $i ?>" value="0">
												<span id="estado<?= $i ?>" class="text-danger"><b>Pendiente</b></span>
											<?php
										}else{
											?>
												<input type="hidden" id="estadohidden<?= $i ?>" value="1">
												<span id="estado<?= $i ?>" class="text-success"><b>Cancelado</b></span>
											<?php
										}
										?></div>										
									</td>
									<?php if($permiso_guardar_pago){ ?>
									<td class="text-nowrap text-center"><div class="cuota_div" data-cuota="<?= $i ?>">
										<span class="glyphicon glyphicon-save" onclick="javascript:saveData(<?= $i ?>);"></span>
									</div></td>
									<?php } ?>
									<?php if($permiso_imprimir_comprobante){ ?>
									<td class="text-nowrap text-center" id="imprimir<?= $i ?>"><div class="cuota_div" data-cuota="<?= $i ?>">
										<?php 
										if($detalle['estado']==1){
											?><a href="?/pagar/imprimir_comprobante/<?= $detalle['id_pago_detalle']; ?>" target="_blank" data-toggle="tooltip" data-title="Imprimir comprobante"><span class="glyphicon glyphicon-print"></span></a>
										<?php } ?>
									</div></td>
									<?php } ?>
									<?php if($permiso_eliminar_pago){ ?>
									<td class="text-nowrap text-center" id="eliminar<?= $i ?>"><div class="cuota_div" data-cuota="<?= $i ?>">
										<?php 
										if($detalle['estado']==0){										
										?>
											<span class="glyphicon glyphicon-remove" onclick="javascript:eliminar_cuota(<?= $i ?>,<?= $detalle['id_pago_detalle']; ?>);"></span>
										<?php
										}
										?>	
									</div></td>									
									<?php } ?>
								</tr>
															

								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" id="total_cuotas"><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap" colspan="4">												
										<span id="conclusion" class="text-danger"></span>
									</th>
								</tr>
							</tfoot>								
						</table>

						<div class="col-sm-12 text-center">
							<a class="btn btn-success" onclick="javascript:AddCuota();"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs hidden-sm"> Nueva Cuota</span></a>
							<!--&nbsp&nbsp&nbsp
							<a href="" class="btn btn-success"><i class="glyphicon glyphicon-save"></i><span class="hidden-xs hidden-sm"> Guardar</span></a-->
						</div>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Este ingreso no tiene detalle, es muy importante que todos los ingresos cuenten con un detalle de la compra.</p>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i> Información del ingreso</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label">Fecha y hora:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingreso['hora_ingreso']); ?></small></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Proveedor:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombre_proveedor']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Tipo de ingreso:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['tipo']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Descripción:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['descripcion']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Monto total:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['monto_total']); ?></p>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-md-3 control-label">Interes:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['interes_pago']); ?> %</p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Tipo de Pago:</label>
							<div class="col-md-9">
								<?php if (escape($ingreso['plan_de_pagos'])=="si"){ ?>
									<p class="form-control-static">Plan de Pagos</p>
								<?php }else{ ?>
									<p class="form-control-static">Pago Completo</p>
								<?php } ?>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-md-3 control-label">Número de registros:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nro_registros']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Almacén:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['almacen']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Empleado:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</form>


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

<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>

<script>
var formato = $('[data-formato]').attr('data-formato');
var nroCuota=<?php echo $nro+1; ?>;
var $inicial_fecha = new Array();
var $pago_fecha = new Array();

$(function () {
	var formato = $('[data-formato]').attr('data-formato');
	
	$.validate({
		form: '#fromii',
		modules: 'basic',
		onSuccess: function () {				
		}
	});

	<?php 
	foreach ($detallesCuota as $nro => $detalle) { 
	?>	
		$("#tipo<?php echo $nro; ?> option[value='<?php echo $detalle['tipo_pago']; ?>']").attr("selected",true);			
		var $inicial_fecha = $('#fecha<?php echo $nro; ?>');		
		$inicial_fecha.datetimepicker({
			format: 'L' //LT
		});

		var $inicial_fechap = $('#fechap<?php echo $nro; ?>');		
		$inicial_fechap.datetimepicker({
			format: 'L' //LT
		});
	<?php 
	} 
	?>

	for(i=1;i<36;i++){
		$inicial_fecha[i] = $('#inicial_fecha_'+i+'');
		$inicial_fecha[i].datetimepicker({
			format: formato
		});

		$pago_fecha[i] = $('#pago_fecha_'+i+'');
		$pago_fecha[i].datetimepicker({
			format: formato
		});
	}

	$inicial_fecha[1].on('dp.change', function (e) {	$inicial_fecha[2].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[2].on('dp.change', function (e) {	$inicial_fecha[3].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[3].on('dp.change', function (e) {	$inicial_fecha[4].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[4].on('dp.change', function (e) {	$inicial_fecha[5].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[5].on('dp.change', function (e) {	$inicial_fecha[6].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[6].on('dp.change', function (e) {	$inicial_fecha[7].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[7].on('dp.change', function (e) {	$inicial_fecha[8].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[8].on('dp.change', function (e) {	$inicial_fecha[9].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[9].on('dp.change', function (e) {	$inicial_fecha[10].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[10].on('dp.change', function (e) {	$inicial_fecha[11].data('DateTimePicker').minDate(e.date);	});
		
	$inicial_fecha[11].on('dp.change', function (e) {	$inicial_fecha[12].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[12].on('dp.change', function (e) {	$inicial_fecha[13].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[13].on('dp.change', function (e) {	$inicial_fecha[14].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[14].on('dp.change', function (e) {	$inicial_fecha[15].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[15].on('dp.change', function (e) {	$inicial_fecha[16].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[16].on('dp.change', function (e) {	$inicial_fecha[17].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[17].on('dp.change', function (e) {	$inicial_fecha[18].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[18].on('dp.change', function (e) {	$inicial_fecha[19].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[19].on('dp.change', function (e) {	$inicial_fecha[20].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[20].on('dp.change', function (e) {	$inicial_fecha[21].data('DateTimePicker').minDate(e.date);	});

	$inicial_fecha[21].on('dp.change', function (e) {	$inicial_fecha[22].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[22].on('dp.change', function (e) {	$inicial_fecha[23].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[23].on('dp.change', function (e) {	$inicial_fecha[24].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[24].on('dp.change', function (e) {	$inicial_fecha[25].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[25].on('dp.change', function (e) {	$inicial_fecha[26].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[26].on('dp.change', function (e) {	$inicial_fecha[27].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[27].on('dp.change', function (e) {	$inicial_fecha[28].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[28].on('dp.change', function (e) {	$inicial_fecha[29].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[29].on('dp.change', function (e) {	$inicial_fecha[30].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[30].on('dp.change', function (e) {	$inicial_fecha[31].data('DateTimePicker').minDate(e.date);	});

	$inicial_fecha[31].on('dp.change', function (e) {	$inicial_fecha[32].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[32].on('dp.change', function (e) {	$inicial_fecha[33].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[33].on('dp.change', function (e) {	$inicial_fecha[34].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[34].on('dp.change', function (e) {	$inicial_fecha[35].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[35].on('dp.change', function (e) {	$inicial_fecha[36].data('DateTimePicker').minDate(e.date);	});

	$pago_fecha[1].on('dp.change', function (e) {	$pago_fecha[2].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[2].on('dp.change', function (e) {	$pago_fecha[3].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[3].on('dp.change', function (e) {	$pago_fecha[4].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[4].on('dp.change', function (e) {	$pago_fecha[5].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[5].on('dp.change', function (e) {	$pago_fecha[6].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[6].on('dp.change', function (e) {	$pago_fecha[7].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[7].on('dp.change', function (e) {	$pago_fecha[8].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[8].on('dp.change', function (e) {	$pago_fecha[9].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[9].on('dp.change', function (e) {	$pago_fecha[10].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[10].on('dp.change', function (e) {	$pago_fecha[11].data('DateTimePicker').minDate(e.date);	});
		
	$pago_fecha[11].on('dp.change', function (e) {	$pago_fecha[12].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[12].on('dp.change', function (e) {	$pago_fecha[13].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[13].on('dp.change', function (e) {	$pago_fecha[14].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[14].on('dp.change', function (e) {	$pago_fecha[15].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[15].on('dp.change', function (e) {	$pago_fecha[16].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[16].on('dp.change', function (e) {	$pago_fecha[17].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[17].on('dp.change', function (e) {	$pago_fecha[18].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[18].on('dp.change', function (e) {	$pago_fecha[19].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[19].on('dp.change', function (e) {	$pago_fecha[20].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[20].on('dp.change', function (e) {	$pago_fecha[21].data('DateTimePicker').minDate(e.date);	});

	$pago_fecha[21].on('dp.change', function (e) {	$pago_fecha[22].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[22].on('dp.change', function (e) {	$pago_fecha[23].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[23].on('dp.change', function (e) {	$pago_fecha[24].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[24].on('dp.change', function (e) {	$pago_fecha[25].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[25].on('dp.change', function (e) {	$pago_fecha[26].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[26].on('dp.change', function (e) {	$pago_fecha[27].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[27].on('dp.change', function (e) {	$pago_fecha[28].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[28].on('dp.change', function (e) {	$pago_fecha[29].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[29].on('dp.change', function (e) {	$pago_fecha[30].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[30].on('dp.change', function (e) {	$pago_fecha[31].data('DateTimePicker').minDate(e.date);	});

	$pago_fecha[31].on('dp.change', function (e) {	$pago_fecha[32].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[32].on('dp.change', function (e) {	$pago_fecha[33].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[33].on('dp.change', function (e) {	$pago_fecha[34].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[34].on('dp.change', function (e) {	$pago_fecha[35].data('DateTimePicker').minDate(e.date);	});
	$pago_fecha[35].on('dp.change', function (e) {	$pago_fecha[36].data('DateTimePicker').minDate(e.date);	});

	calcular_cuota();

	for(i=1;i<=3;i++){
		$('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});				
		$('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});				
		$('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});				
	}
	for(i=parseInt(3)+1;i<=36;i++){
		$('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});				
		$('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});				
		$('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});
	}

});
function saveData(x){
	f0=$('#f0'+x).val();
	f1=$('#fecha'+x).val();
	f2=$('#fechap'+x).val();
	f3=$('#tipo'+x).val();
	f4=$('#monto'+x).val();

	if(f1==""){ $('#fechaerror'+x).html("No puede estar vacio el campo");	$('#fechaerror'+x).parent('div').addClass('has-error');	
	}else{		$('#fechaerror'+x).html("");	$('#fechaerror'+x).parent('div').removeClass('has-error');	 }

	if(f2==""){ $('#fechaperror'+x).html("No puede estar vacio el campo");	$('#fechaperror'+x).parent('div').addClass('has-error');		
	}else{		$('#fechaperror'+x).html("");	$('#fechaperror'+x).parent('div').removeClass('has-error');		}
	
	if(f3==""){ $('#tipoerror'+x).html("Debe seleccionar una forma de pago");	$('#fechaperror'+x).parent('div').addClass('has-error');	
	}else{		$('#tipoerror'+x).html("");	$('#tipoerror'+x).parent('div').removeClass('has-error'); 		}
	
	if(parseFloat(f4)<=0 || isNaN(f4) ){ $('#montoerror'+x).html("Debe ser un número decimal positivo");	$('#montoerror'+x).parent('div').addClass('has-error');	
	}else{		$('#montoerror'+x).html("");	$('#montoerror'+x).parent('div').removeClass('has-error');		}
	
	/*if(f1!="" && f2!="" && f3!="" && parseFloat(f4)>0){
		saveData2(x);
	}*/	
	if(parseFloat(f4)>0){
		saveData2(x);
	}	
}
function saveData2(x){
				datox=$("#fromii").serialize()+"&nro="+x,		
				$.ajax({
					url: '?/pagar/guardar_pago',
					type: 'post',
					data: ""+datox,
					success: function(data){
						//alert(data);
						v=data.split("|");
						if(v[0]=="1"){
							//alert(v[2]);							
							if(v[2]=="1"){
								$("#estado"+x).removeClass("text-danger");
								$("#estado"+x).addClass("text-success");
								$("#estado"+x).html("<b>Cancelado</b>");
								$("#imprimir"+x).html('<a href="?/pagar/imprimir_comprobante/'+v[1]+'" target="_blank" data-toggle="tooltip" data-title="Imprimir comprobante"><span class="glyphicon glyphicon-print"></span></a>');
								$("#eliminar"+x).html('');
								$("#f0"+x).val(''+v[1]);
								bootbox.alert('Se ha guardado los cambios', ); 
							}
							else{
								$("#estado"+x).addClass("text-danger");
								$("#estado"+x).removeClass("text-success");
								$("#estado"+x).html("<b>Pendiente</b>");
								$("#imprimir"+x).html('');
								$("#eliminar"+x).html('<span class="glyphicon glyphicon-remove" onclick="javascript:eliminar_cuota('+x+','+v[1]+');"></span>');
								$("#f0"+x).val(''+v[1]);
								bootbox.alert('Se ha guardado los cambios', ); 
							}
						}
					}
					,
			    	error: function(XMLHttpRequest, textStatus, errorThrown) {
				        //alert(textStatus);
				    } //EINDE error
				    ,
				    complete: function(data) {
				    } //EINDE complete
				});			
}

function calcular_cuota() {
	var totalProductos = $('#totalProducto').val();
	var $compras = $('#cuotas_table tbody');
	var $importes = $compras.find('[data-montocuota]');
	var total = 0;
	ic=0;
	reg=0;
	$importes.each(function (i) {
		//if($('#estadohidden' + ic).val()=="1"){
			importe = $.trim($(this).val());
			importe = parseFloat(importe);
			total = total + importe;
			reg++;
		//}
		ic++;
	});

	$('#total_cuotas').html(total.toFixed(2));
	
	if(parseFloat(totalProductos)!=parseFloat(total)){
		if(parseFloat(totalProductos)>parseFloat(total)){
			$("#conclusion").html("<b>La suma de las cuotas <br>y el costo del Ingreso <br>no coinciden</b><br>"+parseFloat(totalProductos)+" > "+parseFloat(total))
		}
		else{
			$("#conclusion").html("<b>La suma de las cuotas <br>y el costo del Ingreso <br>no coinciden</b><br>"+parseFloat(totalProductos)+" < "+parseFloat(total))
		}
	}
	else{
		$("#conclusion").html("")
	}
}

function AddCuota(){	
	var $compras = $('#cuotas_table tbody');
	plantilla = ''; 
	plantilla +='<tr data-cuota="'+(nroCuota)+'">' +
						'<th class="text-nowrap">'+(nroCuota+1)+'</th>'+
						'<td class="text-nowrap detalle">Cuota #'+(nroCuota+1)+'</td>'+
									'<td class="text-nowrap text-center">'+
										'<div class="col-md-12">'+											
											'<input type="hidden" id="f0'+nroCuota+'" name="f0'+nroCuota+'" value="">'+
											'<input type="hidden" class="fxx" id="fx'+nroCuota+'" name="fx'+nroCuota+'" value="'+(nroCuota+1)+'">'+					
											
											'<input type="text" id="fecha'+nroCuota+'" name="fecha'+nroCuota+'" value="" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">'+
											'<span class="help-block form-error fechaerror" id="fechaerror'+nroCuota+'" style="color:#a94442;"></span>'+
										'</div>'+										
									'</td>'+	
									'<td class="text-nowrap text-center">'+
										'<div class="col-md-12">'+
											'<input type="text" id="fechap'+nroCuota+'" name="fechap'+nroCuota+'" value="" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">'+
											'<span class="help-block form-error fechaperror" id="fechaperror'+nroCuota+'" style="color:#a94442;"></span>'+
										'</div>'+												
									'</td>'+
									'<td class="text-nowrap text-center">'+
										'<div class="col-md-12">'+
											'<select name="tipo'+nroCuota+'" id="tipo'+nroCuota+'" class="form-control" data-validation="required">'+
												'<option value=""> - Seleccione una opción</option>'+
												'<option value="Efectivo">Efectivo</option>'+
												'<option value="Deposito">Deposito</option>'+
												'<option value="Cheque">Cheque</option>'+							
											'</select>'+
											'<span class="help-block form-error tipoerror" id="tipoerror'+nroCuota+'" style="color:#a94442;"></span>'+
										'</div>'+
									'</td>'+
									'<td class="text-nowrap text-right">'+
										'<div class="col-md-12">'+
											'<input type="text" name="monto'+nroCuota+'" value="0.00" id="monto'+nroCuota+'" class="form-control  text-right" maxlength="10" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" data-montocuota="" onchange="calcular_cuota();">'+
											'<span id="montoerror'+nroCuota+'" class="text-danger"></span>'+
										'</div>'+	
									'</td>'+
									'<td class="text-nowrap text-center">'+
												'<input type="hidden" id="estadohidden'+nroCuota+'" value="0">'+
												'<span id="estado'+nroCuota+'" class="text-danger" data-montocuota'+nroCuota+'="0"><b>Pendiente</b></span>'+				
									'</td>'+
									'<td class="text-nowrap text-center">'+
										'<span class="glyphicon glyphicon-save" onclick="javascript:saveData('+nroCuota+');"></span>'+
									'</td>'+
									'<td class="text-nowrap text-center" id="imprimir'+nroCuota+'">'+
									'</td>'+
									'<td class="text-nowrap text-center" id="eliminar'+nroCuota+'">'+
											'<span class="glyphicon glyphicon-remove" onclick="javascript:eliminar_cuota('+nroCuota+',0);"></span>'+
									'</td>'+																	
								'</tr>';
								



	$compras.append(plantilla);	

	var $inicial_fecha = $('#fecha'+nroCuota+'');		
	$inicial_fecha.datetimepicker({
		format: 'L' //LT
	});

	var $inicial_fechap = $('#fechap'+nroCuota+'');		
	$inicial_fechap.datetimepicker({
		format: 'L' //LT
	});

	nroCuota++;
	calcular_cuota();	
}
function eliminar_cuota(x,id) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			if(id!=0){
				datox="nro="+id,		
					$.ajax({
						url: '?/pagar/eliminar_pago',
						type: 'post',
						data: ""+datox,
						success: function(data){
							//alert(data);
							if(data==1){
								$('[data-cuota=' + x + ']').remove();
								calcular_cuota();			
							}							
						}
						,
				    	error: function(XMLHttpRequest, textStatus, errorThrown) {
					        //alert(textStatus);
					    } //EINDE error
					    ,
					    complete: function(data) {
					    } //EINDE complete
					});					
			}
			else{
				$('[data-cuota=' + x + ']').remove();
				calcular_cuota();
			}
		}
	});

	limite=$("#fx"+x).val();
	var $compras = $('#cuotas_table tbody');
	var $importes = $compras.find('.fxx');
	$importes.each(function (i) {
		importe = $.trim($(this).val());
		if(importe>limite){
			importe--;
			$(this).val(importe);	
			//alert(importe)
			$(this).parent("div").parent("td").parent("tr").children("th").html(""+importe);
			$(this).parent("div").parent("td").parent("tr").children(".detalle").html("Cuota #"+importe);
		}
	});
}
</script>
<?php require_once show_template('footer-sidebar'); ?>