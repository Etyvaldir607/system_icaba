<?php

// Obtiene el id_user
$id_user = (isset($params[0])) ? $params[0] : 0;

// Obtiene el user
$user = $db->select("u.*, r.rol, ifnull(e.paterno, '') as paterno, ifnull(e.materno, '') as materno, ifnull(e.nombres, '') as nombres")->from('sys_users u')->join('sys_roles r', 'u.rol_id = r.id_rol', 'left')->join('sys_empleados e', 'u.persona_id = e.id_empleado', 'left')->where('u.id_user', $id_user)->fetch_first();

// Verifica si existe el user
if (!$user) {
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
$permiso_subir = in_array('subir', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<link href="<?= css; ?>/jquery.Jcrop.min.css" rel="stylesheet">
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Ver usuario</b>
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
			<a href="?/usuarios/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<a href="?/usuarios/editar/<?= $user['id_user']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-edit"></span>
				<span class="hidden-xs hidden-sm">Modificar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/usuarios/eliminar/<?= $user['id_user']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/usuarios/imprimir/<?= $user['id_user']; ?>" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs hidden-sm">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/usuarios/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-3">
			<img src="<?= ($user['avatar'] == '') ? imgs . '/avatar-default.jpg' : profiles . '/' . $user['avatar']; ?>" class="img-responsive thumbnail cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Avatar">
			<div class="list-group">
				<a href="#" class="list-group-item text-truncate" data-subir="true">
					<span class="glyphicon glyphicon-picture"></span>
					<span>Subir imagen</span>
				</a>
			</div>
		</div>
		<div class="col-sm-9">
			<div class="well">
				<p class="lead">Información del usuario</p>
				<hr>
				<div class="table-display" data-print-data="true">
					<div class="tbody">
						<div class="tr">
							<div class="th text-nowrap">Usuario:</div>
							<div class="td"><?= escape($user['username']); ?></div>
						</div>
						<div class="tr">
							<div class="th text-nowrap">Correo:</div>
							<div class="td"><?= escape($user['email']); ?></div>
						</div>
						<div class="tr">
							<div class="th text-nowrap">Rol:</div>
							<div class="td"><?= escape($user['rol']); ?></div>
						</div>
						<div class="tr">
							<div class="th text-nowrap">Estado:</div>
							<div class="td"><?= ($user['active'] == 1) ? 'Activado' : 'Bloqueado'; ?></div>
						</div>
						<div class="tr">
							<div class="th text-nowrap">Empleado:</div>
							<div class="td"><?= (trim($user['nombres'] . ' ' . $user['paterno'] . ' ' . $user['materno']) === '') ? 'No asignado' : escape($user['nombres'] . ' ' . $user['paterno'] . ' ' . $user['materno']); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal subir avatar -->
<?php if ($permiso_subir) { ?>
<div id="modal_subir" class="modal fade">
	<div class="modal-dialog">
		<form method="POST" action="?/usuarios/subir" enctype="multipart/form-data" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Subir avatar</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<input type="hidden" value="<?= $id_user; ?>" name="id_user" data-validation="required">
					<input type="file" name="avatar" class="form-control" data-validation="required mime size" data-validation-allowing="jpg, png" data-validation-max-size="512kb">
				</div>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="#" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span><span class="hidden-xs"> Cancelar</span></a>
						<button type="submit" id="enviar_subir" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span><span class="hidden-xs"> Guardar</span></button>
						<button type="reset" id="borrar_subir" class="hidden"></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- End subir avatar -->

<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content spinner-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="spinner_mostrar" class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.resize.min.js"></script>
<script src="<?= js; ?>/jquery.Jcrop.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el usuario?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_subir) { ?>
	$.validate({
		modules: 'file'
	});

	var $modal_subir = $('#modal_subir');

	$('[data-subir]').on('click', function (e) {
		e.preventDefault();
		$modal_subir.modal({
			backdrop: 'static'
		});
	});

	$modal_subir.on('show.bs.modal', function (e) {
		$('#borrar_subir').trigger('click');
	});

	$modal_subir.on('shown.bs.modal', function (e) {
		$('#enviar_subir').focus();
	});
	<?php } ?>

	var $modal_mostrar = $('#modal_mostrar'), $spinner_mostrar = $('#spinner_mostrar'), size, title, image;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$spinner_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
		image = $(e.relatedTarget).attr('src');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
		$modal_mostrar.find('.modal-title').text(title);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$spinner_mostrar.hide();
	});
});
</script>
<?php require_once show_template('footer-sidebar'); ?>