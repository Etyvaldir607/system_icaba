<?php require_once show_template('header-sidebar'); ?>
<p class="lead" data-header="true">Información de la empresa</p>
<hr>
<div class="row">
	<div class="col-sm-8 hidden-xs">
		<div class="text-label">Para editar la información hacer clic en el siguiente botón:</div>
	</div>
	<div class="col-xs-12 col-sm-4 text-right">
		<a href="?/configuraciones/institucion_editar" class="btn btn-primary">
			<span class="glyphicon glyphicon-edit"></span>
			<span>Modificar</span>
		</a>
	</div>
</div>
<hr>
<div class="alert alert-warning">Los datos mostrados a continuación deben ser propios de su empresa, ya que con esta información serán generados todos los documentos del sistema.</div>
<div class="well">
	<div class="table-display">
		<div class="tbody">
			<div class="tr">
				<div class="th text-nowrap">Nombre de la empresa:</div>
				<div class="td text-truncate"><?= escape($_institution['nombre']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Información de la empresa:</div>
				<div class="td text-truncate"><?= escape($_institution['lema']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Actividad económica:</div>
				<div class="td text-truncate"><?= escape($_institution['razon_social']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">NIT de la empresa:</div>
				<div class="td text-truncate"><?= escape($_institution['nit']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Propietario:</div>
				<div class="td text-truncate"><?= escape($_institution['propietario']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Dirección de la empresa:</div>
				<div class="td text-truncate"><?= escape($_institution['direccion']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Correo electrónico:</div>
				<div class="td text-truncate"><?= escape($_institution['correo']); ?></div>
			</div>
			<div class="tr">
				<div class="th text-nowrap">Teléfono:</div>
				<div class="td text-truncate"><?= ($_institution['telefono'] == '') ? 'No asignado' : str_replace(',', ' / ', escape($_institution['telefono'])); ?></div>
			</div>
		</div>
	</div>
</div>
<script>
$(function () {
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'u':
					e.preventDefault();
					window.location = '?/configuraciones/institucion_editar';
				break;
			}
		}
	});
});
</script>
<?php require_once show_template('footer-sidebar'); ?>