<?php require_once show_template('header-sidebar'); ?>
<link rel="stylesheet" href="<?= css; ?>/bootstrap-dropzone.min.css">
<p class="lead">Información de usuario</p>
<hr>
<div class="row align-items-center">
	<div class="col-md-4 text-left hidden-xs">Realizar acción:</div>
	<div class="col-md-8 text-right">
		<a href="?/home/index" class="btn btn-primary">
			<span class="glyphicon glyphicon-menu-left"></span>
			<span>Volver</span>
		</a>
		<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal_contrasenear">
			<span class="glyphicon glyphicon-lock"></span>
			<span class="visible-lg-inline">Modificar contraseña</span>
		</a>
		<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal_pinear">
			<span class="glyphicon glyphicon-asterisk"></span>
			<span class="visible-lg-inline">Modificar pin</span>
		</a>
	</div>
</div>
<hr>
<div class="row">
	<div class="col-md-8 col-lg-9 col-xl-10">
		<div class="well">
			<div class="table-display" data-print-data="true">
				<div class="tbody">
					<div class="tr">
						<div class="td text-nowrap">
							<u>Usuario:</u>
						</div>
						<div class="td"><?= escape($_user['username']); ?></div>
					</div>
					<div class="tr">
						<div class="td text-nowrap">
							<u>Correo:</u>
						</div>
						<div class="td"><?= escape($_user['email']); ?></div>
					</div>
					<div class="tr">
						<div class="td text-nowrap">
							<u>Rol:</u>
						</div>
						<div class="td"><?= escape($_user['rol']); ?></div>
					</div>
					<div class="tr">
						<div class="td text-nowrap">
							<u>Pin:</u>
						</div>
						<div class="td h4">* * * *</div>
					</div>
					<div class="tr">
						<div class="td text-nowrap">
							<u>Contraseña:</u>
						</div>
						<div class="td h4">* * * * * * * * * * * *</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-lg-3 col-xl-2">
		<img src="<?= ($_user['avatar'] == '') ? imgs . '/avatar-default.jpg' : profiles . '/' . $_user['avatar']; ?>" class="img-responsive rounded-circle mb-4" data-toggle="lightbox" data-lightbox-size="modal-md" data-lightbox-content="<p class='h2 text-center m-0'><?= escape($_user['username']); ?></p>">
		<p>
			<a href="#" class="btn btn-block btn-default" data-toggle="modal" data-target="#modal_subir">Cambiar imagen</a>
		</p>
		<p>
			<a href="?/home/perfil_eliminar" class="btn btn-block btn-default" data-eliminar="true">Eliminar imagen</a>
		</p>
	</div>
</div>

<!-- Modal contrasenear inicio -->
<div id="modal_contrasenear" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<form method="post" action="?/home/perfil_contrasenear" id="form_contrasenear" class="modal-content spinner-wrapper" autocomplete="off">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Modificar contraseña</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="password_contrasenear_confirmation" class="control-label font-weight-normal">Contraseña:</label>
					<input type="password" value="" name="password_confirmation" id="password_contrasenear_confirmation" class="form-control" data-validation="required length strength" data-validation-length="5-30" data-validation-strength="2">
				</div>
				<div class="form-group">
					<label for="password_contrasenear" class="control-label font-weight-normal">Confirmar contraseña:</label>
					<input type="password" value="" name="password" id="password_contrasenear" class="form-control" data-validation="required confirmation">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-floppy-disk"></span>
					<span>Guardar</span>
				</button>
				<button type="reset" class="btn btn-default">
					<span class="glyphicon glyphicon-repeat"></span>
					<span>Restablecer</span>
				</button>
			</div>
			<div id="spinner_contrasenear" class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</form>
	</div>
</div>
<!-- Modal contrasenear fin -->

<!-- Modal pinear inicio -->
<div id="modal_pinear" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<form method="post" action="?/home/perfil_pinear" id="form_pinear" class="modal-content spinner-wrapper" autocomplete="off">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Modificar pin</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="pin_pinear_confirmation" class="control-label font-weight-normal">Pin:</label>
					<input type="password" value="" name="pin_confirmation" id="pin_pinear_confirmation" class="form-control" maxlength="4" data-validation="required length number" data-validation-length="4">
				</div>
				<div class="form-group">
					<label for="pin_pinear" class="control-label font-weight-normal">Confirmar pin:</label>
					<input type="password" value="" name="pin" id="pin_pinear" class="form-control" maxlength="4" data-validation="required length number confirmation" data-validation-length="4">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-floppy-disk"></span>
					<span>Guardar</span>
				</button>
				<button type="reset" class="btn btn-default">
					<span class="glyphicon glyphicon-repeat"></span>
					<span>Restablecer</span>
				</button>
			</div>
			<div id="spinner_pinear" class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</form>
	</div>
</div>
<!-- Modal pinear fin -->

<!-- Modal subir avatar -->
<div id="modal_subir" class="modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/home/perfil_subir" enctype="multipart/form-data" id="form_subir" class="modal-content spinner-wrapper" autocomplete="off">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar imagen</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<input type="file" name="avatar" id="avatar_subir" class="form-control" data-validation="required mime size" data-validation-allowing="jpg, png" data-validation-max-size="1M">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-floppy-disk"></span>
					<span>Guardar</span>
				</button>
				<button type="reset" class="btn btn-default">
					<span class="glyphicon glyphicon-repeat"></span>
					<span>Restablecer</span>
				</button>
			</div>
			<div id="spinner_subir" class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</form>
	</div>
</div>
<!-- End subir avatar -->

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-dropzone.min.js"></script>
<script>
$(function () {
	var $modal_contrasenear = $('#modal_contrasenear'), $spinner_contrasenear = $('#spinner_contrasenear'), $form_contrasenear = $('#form_contrasenear'), $password_contrasenear_confirmation = $('#password_contrasenear_confirmation');
	
	$.validate({
		form: '#form_contrasenear',
		modules: 'security',
		onSuccess: function () {
			$spinner_contrasenear.show();
		}
	});
	
	$modal_contrasenear.on('hidden.bs.modal', function () {
		$form_contrasenear.trigger('reset');
		$spinner_contrasenear.show();
	}).on('shown.bs.modal', function () {
		$spinner_contrasenear.hide();
		$password_contrasenear_confirmation.trigger('focus');
	});

	var $modal_pinear = $('#modal_pinear'), $spinner_pinear = $('#spinner_pinear'), $form_pinear = $('#form_pinear'), $pin_pinear_confirmation = $('#pin_pinear_confirmation');
	
	$.validate({
		form: '#form_pinear',
		modules: 'security',
		onSuccess: function () {
			$spinner_pinear.show();
		}
	});
	
	$modal_pinear.on('hidden.bs.modal', function () {
		$form_pinear.trigger('reset');
		$spinner_pinear.show();
	}).on('shown.bs.modal', function () {
		$spinner_pinear.hide();
		$pin_pinear_confirmation.trigger('focus');
	});

	var $eliminar = $('[data-eliminar]'), url;

	$eliminar.on('click', function (e) {
		e.preventDefault();
		url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la imagen?', function (resultado) {
			if(resultado){
				window.location = url;
			}
		});
	});

	var $modal_subir = $('#modal_subir'), $spinner_subir = $('#spinner_subir'), $form_subir = $('#form_subir'), $avatar_subir = $('#avatar_subir');

	$avatar_subir.dropzone({
		boxClass: 'alert p-5',
		childTemplate: '<div class="col"></div>'
	});

	$.validate({
		form: '#form_subir',
		modules: 'file',
		onSuccess: function () {
			$spinner_pinear.show();
		}
	});

	$modal_subir.on('hidden.bs.modal', function () {
		$form_subir.trigger('reset');
		$spinner_subir.show();
	}).on('shown.bs.modal', function () {
		$spinner_subir.hide();
	});
	
});
</script>
<?php require_once show_template('footer-sidebar'); ?>