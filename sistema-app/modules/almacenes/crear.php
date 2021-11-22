<?php
//evita crear mas de 10 almacenes
$cantidad = $db->query("SELECT COUNT(*) as cantidad FROM inv_almacenes")->fetch_first();
if($cantidad['cantidad'] >= 10){
    $_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => '¡No se puede crear!',
		'message' => 'Alcanzó la cantidad máxima de almacenes creados, no puede crear mas almacenes.'
	);
	redirect('?/almacenes/listar'); 
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Crear almacén</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de almacenes hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/almacenes/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Listado</span>
			</a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/almacenes/guardar" class="form-horizontal">
				<div class="form-group">
					<label for="almacen" class="col-md-3 control-label">Almacén:</label>
					<div class="col-md-9">
						<input type="hidden" value="0" name="id_almacen" data-validation="required">
						<input type="text" value="" name="almacen" id="almacen" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-#()_ ">
					</div>
				</div>
				<div class="form-group">
					<label for="direccion" class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<input type="text" value="" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-/.,#º() ">
					</div>
				</div>
				<div class="form-group">
					<label for="telefono" class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<input type="text" value="" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100" data-validation-optional="true">
					</div>
				</div>
				<!--<div class="form-group">-->
				<!--	<label for="principal" class="col-md-3 control-label">Principal:</label>-->
				<!--	<div class="col-md-9">-->
				<!--		<div class="radio">-->
				<!--			<label>-->
				<!--				<input type="radio" name="principal" value="N" checked>-->
				<!--				<span>No</span>-->
				<!--			</label>-->
				<!--		</div>-->
				<!--		<div class="radio">-->
				<!--			<label>-->
				<!--				<input type="radio" name="principal" value="S">-->
				<!--				<span>Si</span>-->
				<!--			</label>-->
				<!--		</div>-->
				<!--	</div>-->
				<!--</div>-->
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;@#()_\n " data-validation-optional="true"></textarea>
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
		$telefono.get(0).selectize.clear();
	});
	
	$('.form-control:first').select();
});
</script>
<?php require_once show_template('footer-sidebar'); ?>