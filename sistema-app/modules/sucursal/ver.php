<?php

// Obtiene el id_almacen
$id_sucursal = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$sucursal = $db->select('z.*,a.almacen')
				->from('inv_sucursal z')
				->join('inv_almacenes a','a.id_almacen=z.almacen_id','left')
				->where('z.id_sucursal',$id_sucursal)
				->order_by('z.id_sucursal')
				->fetch_first();

// Verifica si existe el almacén
if (!$sucursal) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Ver almacén</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_editar || $permiso_eliminar || $permiso_imprimir || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/sucursal/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<a href="?/sucursal/editar/<?= $sucursal['id_sucursal']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-edit"></span>
				<span class="hidden-xs hidden-sm">Modificar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/sucursal/eliminar/<?= $sucursal['id_sucursal']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/sucursal/imprimir/<?= $sucursal['id_sucursal']; ?>" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs hidden-sm">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/sucursal/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="col-md-3 control-label">#:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($sucursal['id_sucursal']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Almacén:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($sucursal['sucursal']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($sucursal['direccion']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<p class="form-control-static">
						<?php $telefono = explode(',', escape($sucursal['telefono'])); ?>
						<?php foreach ($telefono as $elemento) { ?>
							<span class="label label-success"><?= $elemento; ?></span>
						<?php } ?>
						</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Principal:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($sucursal['almacen']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($sucursal['descripcion']); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if ($permiso_eliminar) { ?>
<script>
$(function () {
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
});
</script>
<?php } ?>
<?php require_once show_template('footer-sidebar'); ?>