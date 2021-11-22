<?php

// Obtiene el id_egreso
$id_movimiento = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los formatos
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el egreso
// $egreso = $db->from('caj_movimientos')->where('id_movimiento', $id_egreso)->fetch_first();
$movimiento = $db->query("SELECT m.*, upper(IFNULL(i.sucursal, 'sucursal no registrado')) AS sucursal, ifnull(concat( e.nombres, ' ', e.paterno, ' ', e.materno ), '-') as nombre_autorizado_por, ifnull(concat( er.nombres, ' ', er.paterno, ' ', er.materno ), '-') as nombre_recibido_por
                        FROM caj_movimientos as m
                        LEFT JOIN sys_empleados as e ON m.empleado_id = e.id_empleado
                        LEFT JOIN sys_empleados as er ON m.recibido_por = er.id_empleado
						LEFT JOIN inv_sucursal as i ON i.id_sucursal = m.sucursal_id
                        WHERE id_movimiento = $id_movimiento")->fetch_first();
                        
$empleados = $db->query("select * from sys_empleados")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('egresos_listar', $permisos);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Modificar egreso</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de egresos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/movimientos/egresos_listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de egresos</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/movimientos/egresos_guardar" class="form-horizontal">
				<div class="form-group">
					<label for="nro_comprobante" class="col-md-3 control-label">Sucursal:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $movimiento['sucursal']; ?>" readonly name="sucursal" id="sucursal" class="form-control" data-validation="required number">
						<input type="hidden" value="<?= $movimiento['sucursal_id'] ?>" name="sucursal_id" data-validation="required">
					</div>
				</div>	
				<div class="form-group">
					<label for="nro_comprobante" class="col-md-3 control-label">Número de comprobante:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $movimiento['nro_comprobante']; ?>" readonly name="nro_comprobante" id="nro_comprobante" class="form-control" data-validation="required number">
						<input type="hidden" value="<?= $movimiento['id_movimiento'] ?>" name="id_movimiento" data-validation="required">
					</div>
				</div>
				<div class="form-group">
					<label for="monto" class="col-md-3 control-label">Fecha y Hora:</label>
					<div class="col-md-9">
						<input type="text" value="<?= date_decode($movimiento['fecha_movimiento'], $_institution['formato']).' - '.$movimiento['hora_movimiento']; ?>" name="monto" id="monto" class="form-control"  readonly>
						<input type="hidden" value="<?= date_decode($movimiento['fecha_movimiento'], $_institution['formato']); ?>" name="fecha_movimiento" id="fecha_movimiento" class="form-control" autocomplete="off" data-validation="required date" data-validation-format="<?= $formato_textual; ?>">
						<input type="hidden" value="<?= $movimiento['hora_movimiento']; ?>" name="hora_movimiento" id="hora_movimiento" class="form-control" autocomplete="off" data-validation="required alphanumeric length" data-validation-allowing=":" data-validation-length="8">
					</div>
				</div>
			    <div class="form-group">
					<label for="monto" class="col-md-3 control-label">Monto <?= $moneda; ?>:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $movimiento['monto']; ?>" name="monto" id="monto" maxlength="10" class="form-control" data-validation="required number" data-validation-allowing="float" readonly>
					</div>
				</div>
				<div class="form-group">
					<label for="concepto" class="col-md-3 control-label">Por concepto de:</label>
					<div class="col-md-9">
						<textarea type="text" name="concepto" id="concepto" value="" maxlength="65" class="form-control" data-validation="required letternumber" data-validation-allowing="+-/.,:;#()\n " ><?= $movimiento['concepto']; ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="autorizado" class="col-md-3 control-label">Autorizado por:</label>
					<div class="col-md-9">
						<input type="text" value= "<?= upper($movimiento['nombre_autorizado_por']); ?>" class="form-control" readonly></input>
						<input type="hidden"name="id_empleado_a" id="id_empleado_a" value= "<?= $movimiento['empleado_id']; ?>" class="form-control" ></input>
					</div>
				</div>
				<div class="form-group">
					<label for="recibido" class="col-md-3 control-label">Recibido por:</label>
					<div class="col-md-9">
						<input type="text" value= "<?= upper($movimiento['nombre_recibido_por']); ?>" class="form-control" readonly></input>
						<input type="hidden"name="id_empleado_r" id="id_empleado_r" value= "<?= $movimiento['recibido_por']; ?>" class="form-control" ></input>
					</div>
				</div>
				<div class="form-group">
					<label for="observacion" class="col-md-3 control-label">Observación:</label>
					<div class="col-md-9">
						<textarea name="observacion" id="observacion" value="" maxlength="65" class="form-control" data-validation="letternumber" data-validation-allowing="+-/.,:;#()\n " data-validation-optional="true"><?= $movimiento['observacion']; ?></textarea>
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
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
	var $fecha_movimiento = $('#fecha_movimiento');

	$.validate({
		modules: 'basic'
	});

	$fecha_movimiento.datetimepicker({
		format: '<?= strtoupper($formato_textual); ?>'
	});
});
</script>
<?php require_once show_template('footer-sidebar'); ?>