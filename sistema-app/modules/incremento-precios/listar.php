<?php
$roles = $db->query("SELECT *
						FROM sys_roles
						ORDER BY rol ASC
						")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
/*
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_asignar = in_array('asignar', $permisos);
$permiso_unidad_base =  in_array('unidad', $permisos);
$permiso_fijar = false;
$permiso_quitar = in_array('quitar', $permisos);
$permiso_cambiar_precio = in_array('cambiar', $permisos);
*/
?>

<?php require_once show_template('header-sidebar'); ?>
<style>
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
</style>

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de Precios segun al Rol</strong>
	</h3>
</div>

<div class="panel-body">
	<?php if ($permiso_imprimir) { ?>
	<!--div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para imprimir el informe general hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/precios/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
		</div>
	</div-->
	<hr>
	<?php } ?>
	<?php if ($roles) { ?>

	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Rol</th>
				<th class="text-nowrap">Operaciones</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Rol</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Operaciones</th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($roles as $nro => $rol) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap" data-codigo="<?= $rol['id_rol']; ?>"><?= escape($rol['rol']); ?></td>
				<td class="text-nowrap align-middle" style="text-align: center;">

					<a href="?/incremento-precios/editar/<?= $rol['id_rol']; ?>" data-toggle="tooltip" data-title="Nuevo precio" data-asignar="<?= $rol['id_rol']; ?>">
						<button type="button" class="btn btn-primary" tabindex="-1"><span class="glyphicon glyphicon-tag"></span> Modificar</button>
					</a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>

	<div class="alert alert-danger">

		<strong>Advertencia!</strong>

		<p>No existen productos registrados en la base de datos.</p>

	</div>

	<?php } ?>

</div>







<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>


</script>

<?php require_once show_template('footer-sidebar'); ?>