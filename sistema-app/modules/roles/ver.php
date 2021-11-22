<?php

// Obtiene el id_rol
$id_rol = (isset($params[0])) ? $params[0] : 0;

// Obtiene el rol
$rol = $db->select('z.*')->from('sys_roles z')->where('z.id_rol', $id_rol)->fetch_first();

// Verifica si existe el rol
if (!$rol) {
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
		<b>Ver rol</b>
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
			<a href="?/roles/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<a href="?/roles/editar/<?= $rol['id_rol']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-edit"></span>
				<span class="hidden-xs hidden-sm">Modificar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/roles/eliminar/<?= $rol['id_rol']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/roles/imprimir/<?= $rol['id_rol']; ?>" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs hidden-sm">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/roles/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="well">
		<div class="table-display">
			<div class="tbody">
				<div class="tr">
					<div class="th text-nowrap">#:</div>
					<div class="td text-truncate"><?= escape($rol['id_rol']); ?></div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Rol:</div>
					<div class="td text-truncate"><?= escape($rol['rol']); ?></div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Descripción:</div>
					<div class="td text-truncate"><?= escape($rol['descripcion']); ?></div>
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
		bootbox.confirm('Está seguro que desea eliminar el rol?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
});
</script>
<?php } ?>
<?php require_once show_template('footer-sidebar'); ?>