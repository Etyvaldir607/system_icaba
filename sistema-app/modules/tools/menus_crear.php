<?php

// Obtiene el modelo menus
$menus = $db->from('sys_menus')->fetch();

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear menú</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de menús hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/<?= tools; ?>/menus_listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/<?= tools; ?>/menus_guardar" class="form-horizontal">
				<div class="form-group">
					<label for="menu" class="col-md-3 control-label">Menú:</label>
					<div class="col-md-9">
						<input type="hidden" value="0" name="id_menu" data-validation="required">
						<input type="text" value="" name="menu" id="menu" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-/ " data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="icono" class="col-md-3 control-label">Ícono:</label>
					<div class="col-md-9">
						<input type="text" value="" name="icono" id="icono" class="form-control" autocomplete="off" data-validation="required alphanumeric length" data-validation-allowing="-" data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="ruta" class="col-md-3 control-label">Ruta:</label>
					<div class="col-md-9">
						<input type="text" value="" name="ruta" id="ruta" class="form-control" placeholder="?/nombre_modulo/nombre_archivo" autocomplete="off" data-validation="custom length" data-validation-regexp="^\?(\/[a-z0-9-_]+){2,}$" data-validation-length="max200" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="antecesor_id" class="col-md-3 control-label">Antecesor:</label>
					<div class="col-md-9">
						<select name="antecesor_id" id="antecesor_id" class="form-control" data-validation="number" data-validation-optional="true">
							<option value="">Seleccionar</option>
							<?php foreach ($menus as $elemento) { ?>
							<option value="<?= $elemento['id_menu']; ?>"><?= escape($elemento['id_menu']); ?> &mdash; <?= escape($elemento['menu']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-floppy-disk"></i><span> Guardar</span></button>
						<button type="reset" class="btn btn-default"><i class="glyphicon glyphicon-refresh"></i><span class="hidden-xs"> Limpiar</span></button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {
	$.validate({
		modules: 'basic'
	});

	$('#antecesor_id').selectize({
		maxOptions: 6,
		onInitialize: function () {
			$('#antecesor_id').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function (value) {
			$('#antecesor_id').trigger('blur');
		},
		onBlur: function () {
			$('#antecesor_id').trigger('blur');
		}
	});

	$(':reset').on('click', function () {
		$("#antecesor_id")[0].selectize.clear();
	});
	
	$('.form-control:first').select();
});
</script>
<?php require_once show_template('footer-sidebar'); ?>