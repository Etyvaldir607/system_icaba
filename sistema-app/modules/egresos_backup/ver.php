<?php

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

// Obtiene el egreso
$egreso = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno,resp.nombres as nombres_resp, resp.paterno as paterno_resp,resp.materno as materno_resp,con.nombres as nombres_con,con.paterno as paterno_con,con.materno as materno_con')
			 ->from('inv_egresos i')
			 ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			 ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			 ->join('sys_empleados resp', 'i.responsable_id = resp.id_empleado', 'left')
			 ->join('sys_empleados con', 'i.conductor_id = con.id_empleado', 'left')
			 ->where('id_egreso', $id_egreso)
			 ->fetch_first();

// Verifica si existe el egreso
if (!$egreso) {
	// Error 404
	require_once not_found();
	exit;
}

$detalles = $db->query("SELECT d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, u.tamanio
								FROM inv_egresos_detalles d
								LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
								LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
								LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
								WHERE d.egreso_id = $id_egreso
								")->fetch();
								//ORDER BY orden asc, codigo asc")->fetch();

// Obtiene los almacenes
$almacenes = $db->from('inv_almacenes')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
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
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Detalle de egreso</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_eliminar || $permiso_imprimir || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<div class="btn-group">
				<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
	                Columnas <span class="caret"></span>
	            </a>
	      		<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu" id="columna">
	        		<li>
	        			<a href="#" data-opcion="1" data-estado="true">Código</a>
	        		</li>
	        		<li>
	        			<a href="#" data-opcion="2" data-estado="true">Nombre</a>
	        		</li>
	        		<li>
	        			<a href="#" data-opcion="3" data-estado="true">Cantidad</a>
	        		</li>
	        		<li>
	        			<a href="#" data-opcion="4" data-estado="true">Unidad</a>
	        		</li>
	        		<li>
	        			<a href="#" data-opcion="5" data-estado="true">Precio</a>
	        		</li>
	        		<li>
	        			<a href="#" data-opcion="6" data-estado="true">Importe</a>
	        		</li>
	      		</ul>
	      	</div>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/egresos/eliminar/<?= $egreso['id_egreso']; ?>" class="btn btn-danger" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span><span class="hidden-xs hidden-sm"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/egresos/imprimir/<?= $egreso['id_egreso']; ?>" id="btn_imprimir" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs hidden-sm"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/egresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm"> Listado</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<div class="btn-group">
				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
					<span class="glyphicon glyphicon-plus"></span>
					<span>Egresar</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<?php foreach ($almacenes as $elemento) { ?>
					<li><a href="?/egresos/crear/<?= $elemento['id_almacen']; ?>"><i class="glyphicon glyphicon-star"></i> <?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
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
									<th class="text-nowrap" data-columna="codigo">Código</th>
									<th class="text-nowrap" data-columna="nombre">Nombre</th>
									<th class="text-nowrap" data-columna="cantidad">Cantidad</th>
									<th class="text-nowrap" data-columna="unidad">Unidad</th>
									<th class="text-nowrap" data-columna="precio">Precio <?= escape($moneda); ?></th>
									<th class="text-nowrap" data-columna="importe">Importe <?= escape($moneda); ?></th>
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
									<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
									<td class="text-nowrap"><?= escape($detalle['nombre_factura']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad; ?></td>
									<td class="text-nowrap"><?= escape($detalle['unidad']) . " (" . escape($detalle['tamanio']) . ")"; ?></td>
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
									<th class="text-nowrap text-right" colspan="6" id="importe_total">Importe total <?= escape($moneda); ?></th>
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
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-out"></i> Información del egreso</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label">Fecha y hora:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape(date_decode($egreso['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($egreso['hora_egreso']); ?></small></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Almacén:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['almacen']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Tipo de egreso:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['tipo']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Observaciones:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['descripcion']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Monto total:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['monto_total']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Número de registros:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['nro_registros']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Usuario:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Responsable:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['nombres_resp'] . ' ' . $egreso['paterno_resp'] . ' ' . $egreso['materno_resp']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Conductor:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($egreso['nombres_con'] . ' ' . $egreso['paterno_con'] . ' ' . $egreso['materno_con']); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/egresos/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el egreso y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_suprimir) { ?>
	$('[data-suprimir]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el detalle del egreso?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	let columnas = ['codigo','nombre','cantidad','unidad','precio','importe']; 
	$("#columna").on('click','a',function(e){
		var index = $(this).data('opcion');
		var estado = $(this).data('estado');
		if (estado){
			hideColumn(index);
			$(this).data('estado',false);
			$(this).addClass("text-strike text-danger");

			$('#importe_total').attr('colspan',parseInt($('#importe_total').attr('colspan'))-1);
		} else {
			showColumn(index);
			$(this).data('estado',true);
			$(this).removeClass("text-strike text-danger");
			$('#importe_total').attr('colspan',parseInt($('#importe_total').attr('colspan'))+1);
		}

		columnas = [];
		$('#table thead tr th:visible:not(:first)').each(function (i) {
	       columnas.push($(this).data('columna'));
	    });
	});

	function hideColumn(columnIndex) { 
		$('#table tbody td:nth-child('+(columnIndex+1)+')').hide();
		$('#table thead th:nth-child('+(columnIndex+1)+')').hide();
	}

	function showColumn(columnIndex) { 
		$('#table tbody td:nth-child('+(columnIndex+1)+')').show();
		$('#table thead th:nth-child('+(columnIndex+1)+')').show();
	}

	$("#btn_imprimir").on('click',function(e){	
		e.preventDefault();
		var url = '?/egresos/imprimir/'+"<?= $id_egreso; ?>";
		var form = $('<form action="' + url + '" method="post" target="_blank">' +
		     '<input type="text" name="columnas" value="' + columnas + '" />' +
		     '</form>');
		$('body').append(form);
		form.submit();
	});
});
</script>
<?php require_once show_template('footer-sidebar'); ?>