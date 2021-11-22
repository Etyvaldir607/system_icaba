<?php
// Obtiene los permisos
$permisos = explode(',', PERMITS);
// Almacena los permisos en variables
$permiso_listar = in_array(FILE_LIST, $permisos);

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

?>

<?php require_once show_template('header-sidebar'); ?>

<?php include("utilidad.php"); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="<?= ICON_PANEL; ?>"></span>
		<strong>Crear cronograma</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="POST" action="?/cronograma/guardar" class="form-horizontal" autocomplete="off">
	
				<input type="hidden" name="ip" value="<?= $ingreso['id_ingreso']; ?>">
				<input type="hidden" name="ix" value="<?= $ingreso['id_pago_detalle']; ?>">
				<div class="form-group">
					<label for="codigo" class="col-md-3 control-label">Detalle:</label>
					<div class="col-md-9">
						<input type="text" value="" name="detalle" id="detalle" class="form-control" autocomplete="off" data-validation="required" minlength="1" data-validation-length="max100" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="codigo2" class="col-md-3 control-label">Fecha de pago:</label>
					<div class="col-md-9">
						<input type="hidden" value="0" name="id_producto" data-validation="required">
						<input type="text" name="fecha" value="" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="codigo2" class="col-md-3 control-label">Monto:</label>
					<div class="col-md-9">
						<input type="text" name="monto" value="" id="monto" class="form-control" autocomplete="off" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="nombre" class="col-md-3 control-label">Periodo:</label>
					<div class="col-md-9">
						<select name="periodo" id="periodo" class="form-control" data-validation="required">
							<option value="">Seleccione una opcion...</option>
							<option value="anual">Anual</option>
							<option value="trimestral">Trimestral</option>
							<option value="mensual">Mensual</option>
							<option value="pago unico">Pago Unico</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary">
							<span class="<?= ICON_SUBMIT; ?>"></span>
							<span>Guardar</span>
						</button>
						<button type="reset" class="btn btn-default">
							<span class="<?= ICON_RESET; ?>"></span>
							<span>Restablecer</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
	var formato = $('[data-formato]').attr('data-formato');
	var $inicial_fecha = $('#inicial_fecha');
		
	$inicial_fecha.datetimepicker({
		format: formato
	});

	$.validate({
		modules: 'basic'
	});
	
	$('.form-control:first').select();
});
</script>
<?php require_once show_template('footer-sidebar'); ?>