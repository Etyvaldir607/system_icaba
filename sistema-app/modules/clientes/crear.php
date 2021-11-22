<?php
$id_sucursal = (isset($params[0])) ? $params[0] : 0;
$crear = (isset($params[1])) ? $params[1] : 1;

$categorias = $db->from('inv_categorias_cliente')->order_by('categoria')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Crear cliente</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para ver el listado de clientes hacer clic en el boton listado:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/clientes/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Listado de clientes</span>
			</a>
		</div>
	</div>
	<hr>
	<?php } ?>
	
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/clientes/guardar" class="form-horizontal">
				<!--////////////////////////-->
				    <div class="form-group">
						<label for="nit_ci" class="col-sm-3 control-label">NIT / CI:</label>
						<div class="col-sm-9">
							<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-3 control-label">Señor(es):</label>
						<div class="col-sm-9">
							<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
						</div>
					</div>
					<div class="form-group">
						<label for="telefono" class="col-sm-3 control-label">Telefono:</label>
						<div class="col-sm-9">
							<input type="text" value="" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="direccion" class="col-sm-3 control-label">Dirección:</label>
						<div class="col-sm-9">
							<input type="text" value="" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max200" data-validation-optional="true">
						</div>
					</div>
					<div class="form-group">
					<label for="categoria" class="col-sm-3 control-label">Categoría:</label>
					<div class="col-sm-9">
    					<select name="categoria_id" id="categoria_id" class="form-control" data-validation="required">
    						<option value="">Seleccionar</option>
    						<?php foreach ($categorias as $elemento) { ?>
    							<option value="<?= $elemento['id_categoria_cliente']; ?>"><?= escape($elemento['categoria']); ?></option>
    						<?php } ?>
    					</select>
    				</div>
    				<input type="hidden" value="<?= $crear; ?>" name="crear" id="crear" class="form-control">
    				<input type="hidden" value="<?= escape($id_sucursal); ?>" name="id_sucursal" id="id_sucursal" class="form-control">
    				
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
						<?php if($crear == 'notas'){ ?>
    						<a href="?/notas/crear/<?= escape($id_sucursal); ?>" class="btn btn-default">
                				<span class="glyphicon glyphicon-remove"></span>
                				<span>Cancelar</span>
                			</a>
            			<?php } ?>
            			<?php if($crear == 'proformas'){ ?>
    						<a href="?/proformas/crear/<?= escape($id_sucursal); ?>" class="btn btn-default">
                				<span class="glyphicon glyphicon-remove"></span>
                				<span>Cancelar</span>
                			</a>
            			<?php } ?>
            			<?php if($crear == 'electronicas'){ ?>
    						<a href="?/electronicas/crear/<?= escape($id_sucursal); ?>" class="btn btn-default">
                				<span class="glyphicon glyphicon-remove"></span>
                				<span>Cancelar</span>
                			</a>
            			<?php } ?>
            			<?php if($crear == 'manuales'){ ?>
    						<a href="?/manuales/crear/<?= escape($id_sucursal); ?>" class="btn btn-default">
                				<span class="glyphicon glyphicon-remove"></span>
                				<span>Cancelar</span>
                			</a>
            			<?php } ?>
            			
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
});
</script>
<?php require_once show_template('footer-sidebar'); ?>