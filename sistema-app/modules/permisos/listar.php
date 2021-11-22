<?php

// Obtiene los roles
$roles = $db->select('r.*')->from('sys_roles r')->fetch();

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Permisos</b>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-xs-12">
			<div class="text-label">
				<span>Para asignar permisos a los roles de la lista hacer clic en el enlace</span>
				<span class="glyphicon glyphicon-lock"></span>
				<span>.</span>
			</div>
		</div>
	</div>
	<hr>
	<?php if ($roles) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle column-collapse">#</th>
				<th class="text-nowrap align-middle">Rol</th>
				<th class="text-nowrap align-middle column-collapse">Opciones</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Rol</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($roles as $rol) { ?>
			<tr>
				<td class="text-nowrap"><?= escape($rol['id_rol']); ?></td>
				<td class="text-nowrap"><?= escape($rol['rol']); ?></td>
				<td class="text-nowrap">
					<a href="?/permisos/asignar/<?= $rol['id_rol']; ?>" data-toggle="tooltip" title="Asignar permisos"><i class="glyphicon glyphicon-lock"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen roles registrados en la base de datos, por o cual es imposible asignar los permisos correspondientes.</p>
	</div>
	<?php } ?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<?php if ($roles) { ?>
<script>
$(function () {
	var table = $('#table').DataFilter({
		filter: false,
		name: 'permisos',
		reports: 'xls|doc|pdf|html'
	});
});
</script>
<?php } ?>
<?php require_once show_template('footer-sidebar'); ?>