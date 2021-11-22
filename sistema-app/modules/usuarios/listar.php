<?php

// Obtiene los usuarios
$users = $db->select("u.*, r.rol, ifnull(e.paterno, '') as paterno, ifnull(e.materno, '') as materno, ifnull(e.nombres, '') as nombres")->from('sys_users u')->join('sys_roles r', 'u.rol_id = r.id_rol', 'left')->join('sys_empleados e', 'u.persona_id = e.id_empleado', 'left')->order_by('u.id_user', 'asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_activar = in_array('activar', $permisos);
$permiso_asignar = in_array('asignar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Usuarios</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $users)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos usuarios hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/usuarios/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/usuarios/crear" class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span>
				<span>Nuevo</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($users) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle column-collapse">#</th>
				<th class="text-nowrap align-middle column-collapse">Avatar</th>
				<th class="text-nowrap align-middle column-collapse">Usuario</th>
				<th class="text-nowrap align-middle column-collapse">Correo</th>
				<th class="text-nowrap align-middle column-collapse">Rol</th>
				<th class="text-nowrap align-middle">Empleado</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar) { ?>
				<th class="text-nowrap align-middle column-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Avatar</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Usuario</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Correo</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Rol</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Empleado</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($users as $nro => $user) { ?>
			<tr>
				<th class="text-nowrap align-middle"><?= $nro + 1; ?></th>
				<td class="text-nowrap align-middle">
					<img src="<?= ($user['avatar'] == '') ? imgs . '/avatar-default.jpg' : profiles . '/' . $user['avatar']; ?>" class="img-rounded" width="75" height="75">
				</td>
				<td class="text-nowrap align-middle"><?= escape($user['username']); ?></td>
				<td class="text-nowrap align-middle"><?= escape($user['email']); ?></td>
				<td class="text-nowrap align-middle"><?= escape($user['rol']); ?></td>
				<td class="text-nowrap align-middle"><?= escape($user['paterno'] . ' ' . $user['materno'] . ' ' . $user['nombres']); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar) { ?>
				<td class="text-nowrap align-middle">
					<?php if ($permiso_activar && $user['id_user'] != 1) { ?>
						<?php if ($user['active'] == 1) { ?>
						<a href="?/usuarios/activar/<?= $user['id_user']; ?>" class="text-decoration-none text-success" data-toggle="tooltip" title="Bloquear usuario" data-activar="true"><span class="glyphicon glyphicon-check"></span></a>
						<?php } else { ?>
						<a href="?/usuarios/activar/<?= $user['id_user']; ?>" class="text-decoration-none text-danger" data-toggle="tooltip" title="Desbloquear usuario" data-activar="true"><span class="glyphicon glyphicon-unchecked"></span></a>
						<?php } ?>
					<?php } ?>
					<?php if ($permiso_ver) { ?>
					<a href="?/usuarios/ver/<?= $user['id_user']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Ver usuario">
						<span class="glyphicon glyphicon-search"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/usuarios/editar/<?= $user['id_user']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Modificar usuario"><span class="glyphicon glyphicon-edit"></span></a>
					<?php } ?>
					<?php if ($permiso_eliminar && $user['id_user'] != 1) { ?>
					<a href="?/usuarios/eliminar/<?= $user['id_user']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Eliminar usuario" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
					<?php } ?>
					<?php if ($permiso_asignar) { ?>
					<a href="?/usuarios/listar" data-toggle="tooltip" class="text-decoration-none" title="Asignar / cambiar empleado" data-asignar="<?= $user['id_user']; ?>"><span class="glyphicon glyphicon-user"></span></a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen usuarios registrados en la base de datos, para crear nuevos usuarios hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/usuarios/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_activar) { ?>
	$('[data-activar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea cambiar el estado del usuario?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
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

	<?php if ($permiso_asignar) { ?>
	$('[data-asignar]').on('click', function (e) {
		e.preventDefault();
		var id_user = $(this).attr('data-asignar');
		bootbox.confirm('Desea asignar a este usuario un empleado y/o empleado ya registrado?', function (result) {
			if(result){
				window.location = '?/usuarios/asignar/' + id_user;
			}
		});
	});
	<?php } ?>
	
	<?php if ($users) { ?>
	var table = $('#table').DataFilter({
		filter: false,
		name: 'usuarios',
		reports: 'xls|doc|pdf|html',
		values: {
			order: [[2, 'asc']],
			columnDefs: [{
				targets: 1,
				orderable: false
			}, {
				targets: 6,
				orderable: false
			}]
		}
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>