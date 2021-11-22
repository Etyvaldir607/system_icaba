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








// Obtiene el id_ingreso
$id_ingreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los ingreso
$ingreso = $db->select('i.*, i.plan_de_pagos, p.interes_pago, p.id_pago, pd.*')
			  ->from('inv_ingresos i')
			  ->join('inv_pagos p', 'p.movimiento_id = i.id_ingreso', 'left')
			  ->join('inv_pagos_detalles pd', 'pd.pago_id=p.id_pago', 'left')			  
			  ->where('id_pago_detalle', $id_ingreso)
			  ->fetch_first();

// Verifica si existe el ingreso
if (!$ingreso) {
	// Error 404
	require_once not_found();
	exit;
}

$detallesCuota = $db->select('COUNT(id_pago_detalle) as nroCuota')
			   ->from('inv_pagos_detalles pd')
			   ->where('pd.pago_id', $ingreso['id_pago'])
			   ->where('pd.fecha<=', $ingreso['fecha'])
			   ->order_by('fecha asc')
			   ->fetch_first();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

$tipo=$ingreso['tipo_pago'];
$estado=$ingreso['estado'];

?>

<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="<?= ICON_PANEL; ?>"></span>
		<strong>Crear producto</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de productos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/productos/listar" class="btn btn-primary"><i class="<?= ICON_LIST; ?>"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>

	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-in"></i> Información del ingreso</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label">Proveedor:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombre_proveedor']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Descripción:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['descripcion']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Detalle:</label>
							<div class="col-md-9">
								<p class="form-control-static">Cuota <?= escape($detallesCuota['nroCuota']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Monto Cuota:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['monto']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Fecha limite de pago:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape(date_decode($ingreso['fecha'], $_institution['formato'])); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

					<
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="POST" action="?/pagar/guardar_plan_pagos" class="form-horizontal" autocomplete="off">
	
				<input type="hidden" name="ip" value="<?= $ingreso['id_ingreso']; ?>">
				<input type="hidden" name="ix" value="<?= $ingreso['id_pago_detalle']; ?>">
				<div class="form-group">
					<label for="codigo" class="col-md-3 control-label">Tipo de Pago:</label>
					<div class="col-md-9">
						<select name="tipo" id="tipo" class="form-control" data-validation="required">
							<option value=""> - Seleccione una opción</option>
							<option value="Efectivo">Efectivo</option>
							<option value="Deposito">Deposito</option>
							<option value="Cheque">Cheque</option>							
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="codigo2" class="col-md-3 control-label">Fecha Pago:</label>
					<div class="col-md-9">
						<input type="text" name="fecha" value="<?= ($ingreso['fecha_pago'] != '0000-00-00') ? date_decode($ingreso['fecha_pago'], $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="required date" data-validation-format="<?= $formato_textual; ?>" minlength="1">					
					</div>
				</div>
				<div class="form-group">
					<label for="nombre" class="col-md-3 control-label">Estado:</label>
					<div class="col-md-9">
						<select name="estado" id="estado" class="form-control" data-validation="required">
							<option value="0">Pendiente</option>
							<option value="1">Cancelado</option>
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
<script src="<?= JS; ?>/jquery.form-validator.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.es.js"></script>
<script>
$(function () {
	$.validate({
		modules: 'basic,security'
	});
	
	$('.form-control:first').select();
});
</script>




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
	$("#tipo option[value='<?php echo $tipo; ?>']").attr("selected",true);		
	$("#estado option[value='<?php echo $estado; ?>']").attr("selected",true);
	
	var formato = $('[data-formato]').attr('data-formato');
	var $inicial_fecha = $('#inicial_fecha');
		
	$inicial_fecha.datetimepicker({
		format: formato
	});
});
</script>




<?php require_once show_template('footer-sidebar'); ?>