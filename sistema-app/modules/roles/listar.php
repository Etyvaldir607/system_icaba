<?php

// Obtiene los roles
$roles = $db->select('z.*')->from('sys_roles z')->order_by('z.id_rol')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Roles</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $roles)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos roles hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/roles/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/roles/crear" class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span>
				<span>Nuevo</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($roles) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle column-collapse">#</th>
                <th class="text-nowrap align-middle column-collapse">Rol</th>
                <th class="text-nowrap align-middle column-collapse">Incremento</th>
				<th class="text-nowrap align-middle">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle column-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
                <th class="text-nowrap align-middle" data-datafilter-filter="true">Rol</th>
                <th class="text-nowrap align-middle" data-datafilter-filter="true">Incremento</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($roles as $nro => $rol) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
                <td class="text-nowrap"><?= escape($rol['rol']); ?></td>
                <td class="text-nowrap"><?= escape($rol['incremento']); ?></td>
				<td class="text-nowrap"><?= escape($rol['descripcion']); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/roles/ver/<?= $rol['id_rol']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Ver rol">
						<span class="glyphicon glyphicon-search"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/roles/editar/<?= $rol['id_rol']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Modificar rol">
						<span class="glyphicon glyphicon-edit"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/roles/eliminar/<?= $rol['id_rol']; ?>" class="text-decoration-none" data-toggle="tooltip" title="Eliminar rol" data-eliminar="true">
						<span class="glyphicon glyphicon-trash"></span>
					</a>
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
		<p>No existen roles registrados en la base de datos, para crear nuevos roles hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el rol?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/roles/crear';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($roles) { ?>
	var table = $('#table').DataFilter({
		filter: false,
		name: 'roles',
		reports: 'doc|xls|pdf',
		values: {
			order: [[1, 'asc']],
			columnDefs: [{
				targets: 3,
				orderable: false
			}]
		}
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>