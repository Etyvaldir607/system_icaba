<?php require_once show_template('header-sidebar'); ?>
<p class="lead" data-header="true">Información de la empresa</p>
<hr>
<div class="row">
	<div class="col-sm-8 hidden-xs">
		<div class="text-label">Para visualizar la información hacer clic en el siguiente botón:</div>
	</div>
	<div class="col-xs-12 col-sm-4 text-right">
		<a href="?/configuraciones/institucion" class="btn btn-primary">
			<span class="glyphicon glyphicon-search"></span>
			<span>Mostrar</span>
		</a>
	</div>
</div>
<hr>
<div class="alert alert-warning">Los datos mostrados a continuación deben ser propios de su empresa, ya que con esta información serán generados todos los documentos del sistema.</div>
<div class="row">
	<div class="col-sm-8 col-sm-offset-2">
		<form method="post" action="?/configuraciones/institucion_guardar" class="form-horizontal">
			<div class="form-group">
				<label for="nombre" class="col-md-4 control-label">Nombre de la empresa:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['nombre']; ?>" name="nombre" id="nombre" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-. " data-validation-length="max100">
				</div>
			</div>
			<div class="form-group">
				<label for="lema" class="col-md-4 control-label">Información de la empresa:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['lema']; ?>" name="lema" id="lema" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-.,:; " data-validation-length="max200">
				</div>
			</div>
			<div class="form-group">
				<label for="razon_social" class="col-md-4 control-label">Actividad económica:</label>
				<div class="col-md-8">
					<textarea name="razon_social" id="razon_social" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-.,:;\n "><?= $_institution['razon_social']; ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label for="nit" class="col-md-4 control-label">NIT de la empresa:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['nit']; ?>" name="nit" id="nit" class="form-control" autocomplete="off" data-validation="number length" data-validation-length="max20" data-validation-optional="true">
				</div>
			</div>
			<div class="form-group">
				<label for="propietario" class="col-md-4 control-label">Propietario:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['propietario']; ?>" name="propietario" id="propietario" class="form-control" autocomplete="off" data-validation="required letter length" data-validation-allowing=" " data-validation-length="max200">
				</div>
			</div>
			<div class="form-group">
				<label for="direccion" class="col-md-4 control-label">Dirección de la empresa:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['direccion']; ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-/.,#º() " data-validation-length="max300">
				</div>
			</div>
			<div class="form-group">
				<label for="correo" class="col-md-4 control-label">Correo electrónico:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['correo']; ?>" name="correo" id="correo" class="form-control" autocomplete="off" data-validation="required email length" data-validation-length="max100">
				</div>
			</div>
			<div class="form-group">
				<label for="telefono" class="col-md-4 control-label">Teléfono:</label>
				<div class="col-md-8">
					<input type="text" value="<?= $_institution['telefono']; ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-length="max100" data-validation-allowing="+-,() " data-validation-optional="true"data-selectize="<?= $_institution['telefono']; ?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-8 col-md-offset-4">
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
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {
	var $telefono = $('#telefono');

	$.validate({
		modules: 'basic'
	});

	$telefono.selectize({
		plugins: ['restore_on_backspace'],
		create: true,
		createOnBlur: false,
		maxOptions: 7,
		persist: false,
		onInitialize: function () {
			$telefono.show().addClass('selectize-translate');
		},
		onChange: function () {
			$telefono.trigger('blur');
			$telefono.next().find(':text').trigger('blur');
		},
		onBlur: function () {
			$telefono.trigger('blur');
			$telefono.next().find(':text').trigger('blur');
		}
	});

	$(':reset').on('click', function () {
		$telefono.get(0).selectize.setValue($telefono.attr('data-selectize'));
	});
	
	$('.form-control:first').select();
});
</script>
<?php require_once show_template('footer-sidebar'); ?>