<?php

// Obtiene el id_moneda
$id_moneda = (isset($params[0])) ? $params[0] : 0;

// Obtiene la moneda
$moneda = $db->select('z.*')->from('inv_monedas z')->where('z.id_moneda', $id_moneda)->fetch_first();

// Verifica si existe la moneda
if (!$moneda) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Modificar moneda</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/monedas/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_ver) { ?>
			<a href="?/monedas/ver/<?= $moneda['id_moneda']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-search"></span>
				<span class="hidden-xs hidden-sm">Ver</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/monedas/eliminar/<?= $moneda['id_moneda']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/monedas/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/monedas/guardar" class="form-horizontal">
				<div class="form-group">
					<label for="moneda" class="col-md-3 control-label">Moneda:</label>
					<div class="col-md-9">
						<input type="hidden" value="<?= $moneda['id_moneda']; ?>" name="id_moneda" data-validation="required">
						<input type="text" value="<?= $moneda['moneda']; ?>" name="moneda" id="moneda" class="form-control" autocomplete="off" data-validation="required letter length" data-validation-allowing="() " data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="sigla" class="col-md-3 control-label">Sigla:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $moneda['sigla']; ?>" name="sigla" id="sigla" class="form-control" autocomplete="off" data-validation="required letter length" data-validation-allowing=". " data-validation-length="max10">
					</div>
				</div>
				<div class="form-group">
					<label for="oficial" class="col-md-3 control-label">Oficial:</label>
					<div class="col-md-9">
						<div class="radio">
							<label>
								<input type="radio" name="oficial" value="N" <?= ($moneda['oficial'] == 'N') ? 'checked' : ''; ?>>
								<span>No</span>
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="oficial" value="S" <?= ($moneda['oficial'] == 'S') ? 'checked' : ''; ?>>
								<span>Si</span>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary">
							<span class="glyphicon glyphicon-floppy-disk"></span>
							<span>Guardar</span>
						</button>
						<button type="reset" class="btn btn-default">
							<span class="glyphicon glyphicon-refresh"></span>
							<span>Restablecer</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script>
$(function () {
	$.validate({
		modules: 'basic'
	});
	
	$('.form-control:first').select();
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la moneda?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>