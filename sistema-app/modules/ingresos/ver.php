<?php

// Obtiene el id_ingreso
$id_ingreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los ingreso
$ingreso = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, i.plan_de_pagos, p.id_pago,res.nombres as nombre_resp, res.paterno as paterno_resp,res.materno as materno_resp')
			  ->from('inv_ingresos i')
			  ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			  ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			  ->join('sys_empleados res', 'i.responsable_id = res.id_empleado', 'left')
			  ->join('inv_pagos p', 'p.movimiento_id = i.id_ingreso                      AND                                  p.tipo="Ingreso"', 'left')
			  ->where('id_ingreso', $id_ingreso)
			  ->fetch_first();

// Verifica si existe el ingreso
if (!$ingreso) {
	// Error 404
	require_once not_found();
	exit;
}

$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, u.tamanio')
				   ->from('inv_ingresos_detalles d')
				   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				   ->join('inv_asignaciones a', 'a.id_asignacion = d.asignacion_id', 'left')
				   ->join('inv_unidades u', 'u.id_unidad = a.unidad_id', 'left')
				   ->where('d.ingreso_id', $id_ingreso)
				   ->order_by('categoria_id asc')
				   ->order_by('codigo asc')
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

?>
<?php require_once show_template('header-sidebar'); ?>
<style type="text/css">
.text-strike {
	text-decoration: line-through;
}

.text-danger {
	color:#a94442;
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
        			<a href="#" data-opcion="5" data-estado="true">Costo</a>
        		</li>
        		<li>
        			<a href="#" data-opcion="6" data-estado="true">Importe</a>
        		</li>
      		</ul>
      	</div>
			<?php if ($permiso_crear) { ?>
			<a href="?/ingresos/crear" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs hidden-sm"> Nuevo</span></a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/ingresos/eliminar/<?= $ingreso['id_ingreso']; ?>" class="btn btn-danger" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i><span class="hidden-xs hidden-sm"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/ingresos/imprimir/<?= $ingreso['id_ingreso']; ?>" id="btn_imprimir" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs hidden-sm"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>"> Listado</span></a>
			<?php } ?>
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
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary">
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
									<th class="text-nowrap" data-columna="codigo">Código</th>
									<th class="text-nowrap" data-columna="nombre">Nombre</th>
									<th class="text-nowrap" data-columna="cantidad">Cantidad</th>
									<th class="text-nowrap" data-columna="unidad">Unidad</th>
									<th class="text-nowrap" data-columna="costo">Costo <?= escape($moneda); ?></th>
									<th class="text-nowrap" data-columna="importe">Importe <?= escape($moneda); ?></th>
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
									<td class="text-nowrap"><?= escape($detalle['unidad']); ?></td>
									<td class="text-nowrap text-right"><?= $costo; ?></td>
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6" id="importe_total">Importe total <?= escape($moneda); ?></th>
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
							<label class="col-md-3 control-label">Observaciones:</label>
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
							<label class="col-md-3 control-label">Usuario:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Responsable:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombre_resp'] . ' ' . $ingreso['paterno_resp'] . ' ' . $ingreso['materno_resp']); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el ingreso?', function (result) {
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
		bootbox.confirm('Está seguro que desea eliminar el detalle del ingreso?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	let columnas = ['codigo','nombre','cantidad','unidad','costo','importe']; 
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
		var url = '?/ingresos/imprimir/'+"<?= $id_ingreso; ?>";
		var form = $('<form action="' + url + '" method="post" target="_blank">' +
		     '<input type="text" name="columnas" value="' + columnas + '" />' +
		     '</form>');
		$('body').append(form);
		form.submit();
	});
});
</script>
<?php require_once show_template('footer-sidebar'); ?>