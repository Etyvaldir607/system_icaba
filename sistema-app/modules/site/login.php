<?php

// Importa la configuracion para el manejo de la base de datos
require_once config . '/database.php';

// Obtiene datos de la empresa $_institution = palabra reservada
$_institution = $db->from('sys_instituciones')->fetch_first();

// Generamos un random para evitar el cacheado del script
$version = rand(0, 1000000);

?>
<!doctype html>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="mobile-web-app-capable" content="yes">
		<title><?= $_institution['nombre']; ?></title>
		<link rel="stylesheet" href="<?= css; ?>/bootstrap.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-grid.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-utilities.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-icons.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-spinner.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-dashboard.min.css">
		<link rel="stylesheet" href="<?= css; ?>/animate.min.css">
		<link rel="stylesheet" href="<?= themes . '/' . (($_institution['tema'] == '') ? 'bootstrap' : $_institution['tema']); ?>/style.min.css">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-structured.min.css">
		<link rel="stylesheet" href="<?= css; ?>/animate.min.css">
		<link rel="icon" type="image/png" href="<?= project; ?>/favicon.png">
		<style>
		.form-group .form-control-feedback {
			pointer-events: initial;
			cursor: pointer;
		}
		</style>
		<!--[if lt IE 9]>
			<script src="<?= js; ?>/html5shiv.min.js"></script>
			<script src="<?= js; ?>/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="container-fluid h-100">
			<div class="row h-100">
				<div class="col-md-8 wrapper">
					<div class="row align-items-center justify-content-center h-100">
						<div class="col-md-11 col-xl-10 text-center text-md-left">
							<div class="m-0 px-4 py-5">
								<p class="lead">Bienvenido a</p>
								<p class="h1">
									<span>ICABA importaciones</span>
									<sup><small>v2.0</small></sup>
								</p>
								<!--<p>CheckCode te ayuda a gestionar los procesos más complejos dentro de tu empresa.</p>-->
								<!--<p class="m-0">-->
								<!--	<a href="https://www.checkcode.bo" class="btn btn-default" target="_blank">Conoce más</a>-->
								<!--</p>-->
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="row align-items-center justify-content-center h-100">
						<div class="col-md-10 col-xl-8">
							<div class="m-0 px-4 py-5">
								<p class="lead text-primary" data-header="true">Iniciar sesión</p>
								<hr class="visible-xs-block">
								<form method="post" action="?/<?= site; ?>/auth" class="m-0" autocomplete="off" data-formvalidator="true">
									<input type="hidden" name="locale" value="">
									<div class="form-group">
										<input type="text" name="username" id="username" class="form-control" placeholder="Usuario" autofocus="autofocus" data-validation="required">
									</div>
									<div class="form-group has-feedback">
										<input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" data-validation="required">
										<span class="glyphicon glyphicon-eye-close form-control-feedback"></span>
									</div>
									<div class="form-group">
										<div class="checkbox">
											<label>
												<input type="checkbox" name="remember">
												<span>Recuérdame</span>
											</label>
										</div>
									</div>
									<div class="form-group m-0">
										<button type="submit" class="btn btn-block btn-primary">Ingresar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="<?= js; ?>/jquery.min.js"></script>
		<script src="<?= js; ?>/bootstrap.min.js"></script>
		<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
		<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
		<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
		<script src="<?= js; ?>/animo.min.js"></script>
		<script src="<?= js; ?>/buzz.min.js"></script>
		<script src="<?= js; ?>/functions.min.js"></script>
		<script src="http://localhost:9000/<?= name_project; ?>/locale.js?v=<?= $version; ?>"></script>
		<script>
		$(function () {
			if (window.l || false) {
				$('input[name=locale]').val(window.l);
			}

			$.validate();

			var $wrapper = $('.wrapper'), $feedback = $('.form-control-feedback');

			if ($wrapper) {
				if (is_dark($wrapper.css('background-color'))) {
					$wrapper.css('color', 'rgba(255, 255, 255, 0.8)');
				} else {
					$wrapper.css('color', 'rgba(0, 0, 0, 0.8)');
				}
			}

			$feedback.on('click', function () {
				if ($feedback.hasClass('glyphicon-eye-close')) {
					$feedback.removeClass('glyphicon-eye-close');
					$feedback.addClass('glyphicon-eye-open');
					$feedback.prev().attr('type', 'text');
				} else {
					$feedback.removeClass('glyphicon-eye-open');
					$feedback.addClass('glyphicon-eye-close');
					$feedback.prev().attr('type', 'password');
				}
			});

			<?php if (isset($_SESSION[temporary])) { ?>
			<?php unset($_SESSION[temporary]); ?>
			$.notify({
				message: 'La información enviada no coincide con los registros, asegúrese de introducir correctamente su nombre de usuario y contraseña.' 
			},{
				type: 'danger'
			});
			<?php } ?>
		});
		</script>
	</body>
</html>