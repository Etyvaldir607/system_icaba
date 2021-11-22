<?php

// Obtiene el modelo unidades
$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

// Obtiene el modelo categorias
$categorias = $db->from('inv_categorias')->order_by('categoria')->fetch();

// Obtiene el id_producto
$id_producto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->query("
					select 
						p.id_producto,
						p.codigo,
						p.codigo_barras,
						p.nombre,
						p.nombre_factura,
						p.ubicacion,
						p.descripcion,
						p.imagen,
						p.cantidad_minima,
						p.bonificacion,
						p.rango,
						c.id_categoria,
						c.categoria,
						a.id_asignacion,
						a.precio_actual,
						u.id_unidad,
						u.tamanio,
						u.unidad
						from inv_asignaciones as a
							left join inv_productos as p on p.id_producto = a.producto_id
							left join inv_unidades as u on u.id_unidad = a.unidad_id 
							left join inv_categorias as c on p.categoria_id = c.id_categoria
						where p.id_producto = $id_producto
						AND a.tipo = 'principal'
						limit 1
					")
				->fetch_first();

// Verifica si existe el producto
if (!$producto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Editar producto</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/productos/crear" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs hidden-sm"> Nuevo</span></a>
			<?php } ?>
			<?php if ($permiso_ver) { ?>
			<a href="?/productos/ver/<?= $producto['id_producto']; ?>" class="btn btn-warning"><i class="glyphicon glyphicon-search"></i><span class="hidden-xs hidden-sm"> Ver</span></a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/productos/eliminar/<?= $producto['id_producto']; ?>" class="btn btn-danger" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i><span class="hidden-xs hidden-sm"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/productos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs"> Listado</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/productos/api_editar_producto" class="form-horizontal" autocomplete="off">
				<div class="form-group">
					<label for="codigo" class="col-md-3 control-label">Código:</label>
					<div class="col-md-9">
						<input type="hidden" value="<?= $producto['id_producto']; ?>" name="id_producto" data-validation="required">
						<input type="hidden" value="<?= $producto['id_asignacion']; ?>" name="id_asignacion" data-validation="required">

						<input type="text" value="<?= $producto['codigo']; ?>" name="codigo" id="codigo" class="form-control" data-validation="required alphanumeric length" data-validation-allowing="-/.#º() " data-validation-length="max50">
					</div>
				</div>
				<div class="form-group">
					<label for="codigo_barras" class="col-md-3 control-label">Código de barras:</label>
					<div class="col-md-9">
						<div class="input-group">
							<span class="input-group-btn">
								<button type="text" id="FM" class="btn btn-default">
									<span class="hidden-xs">IC</span>
								</button>
							</span>
							<input type="text" value="<?= substr($producto['codigo_barras'],2); ?>" name="codigo_barras" id="codigo_barras" class="form-control" data-validation="alphanumeric length server" data-validation-allowing="-/.#º()" data-validation-length="max50" data-validation-url="?/productos/validar_barras_editar/<?= $id_producto ?>" data-validation-optional="true">                       
							<span class="input-group-btn">
								<button type="button" id="generar_crear" class="btn btn-success">
									<span class="glyphicon glyphicon-barcode"></span>
									<span class="hidden-xs">Generar</span>
								</button>
							</span>
						</div>
					</div>
				</div>	
				<div class="form-group">
					<label for="nombre" class="col-md-3 control-label">Nombre del producto:</label>
					<div class="col-md-9">
						<input type="text" value="<?= escape($producto['nombre']); ?>" name="nombre" id="nombre" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º*"() ' data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="nombre_factura" class="col-md-3 control-label">Nombre en la factura:</label>
					<div class="col-md-9">
						<input type="text" value="<?= escape($producto['nombre_factura']); ?>" name="nombre_factura" id="nombre_factura" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º*"() ' data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="categoria_id" class="col-md-3 control-label">Categoría:</label>
					<div class="col-md-9">
						<select name="categoria_id" id="categoria_id" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($categorias as $elemento) { ?>
								<?php if ($elemento['id_categoria'] == $producto['id_categoria']) { ?>
								<option value="<?= $elemento['id_categoria']; ?>" selected><?= escape($elemento['categoria']); ?></option>
								<?php } else { ?>
								<option value="<?= $elemento['id_categoria']; ?>"><?= escape($elemento['categoria']); ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="cantidad_minima" class="col-md-3 control-label">Unidad:</label>
					<div class="col-md-9">
						<select name="unidad_id" id="unidad_id" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($unidades as $elemento) { ?>
								<?php if ($elemento['id_unidad'] == $producto['id_unidad']) { ?>
									<option value="<?= $elemento['id_unidad']; ?>" selected><?= escape($elemento['unidad']); ?></option>
								<?php }else{ ?>
									<option value="<?= $elemento['id_unidad']; ?>"><?= escape($elemento['unidad']); ?></option>
								<?php 
								} 
							} 
							?>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="precio_actual" class="col-md-3 control-label">Precio del producto:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $producto['precio_actual'] ?>" name="precio_actual" id="precio_actual" class="form-control" data-validation="number" data-validation-allowing="range[0.0;100000],float">
					</div>
				</div>
                <div class="form-group">
                    <label for="rango" class="col-md-3 control-label">Rango:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $producto['rango'] ?>" name="rango" id="rango" class="form-control" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true">
                    </div>
                </div>
				<div class="form-group">
					<label for="cantidad_minima" class="col-md-3 control-label">Cantidad mínima:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $producto['cantidad_minima']; ?>" name="cantidad_minima" id="cantidad_minima" class="form-control" data-validation="required number" data-validation-allowing="range[0;100000]">
					</div>
				</div>
				<div class="form-group">
					<label for="precio_actual" class="col-md-3 control-label">Producto de Regalo:</label>
					<div class="col-md-9">
						<select name="producto_regalo" id="producto_regalo" class="form-control" data-validation="required">
							<?php
							if($producto['bonificacion']=="no"){ 
							?>
								<option value="no" selected="selected">No</option>
								<option value="si">Si</option>
							<?php
							}
							else{ 
							?>
								<option value="no">No</option>
								<option value="si" selected="selected">Si</option>
							<?php
							}
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="ubicacion" class="col-md-3 control-label">Ubicación:</label>
					<div class="col-md-9">
						<textarea name="ubicacion" id="ubicacion" class="form-control" rows="3" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"><?= escape($producto['ubicacion']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" rows="3" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"><?= escape($producto['descripcion']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary">
							<span class="glyphicon glyphicon-floppy-disk"></span>
							<span>Editar</span>
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
<script src="<?= js; ?>/JsBarcode.all.min.js"></script>
<script>
$(function () {
	$('#FM').on('click',function(e){
			e.preventDefault();
		});
		$('#codigo_barras').on('keyup',function(){
			var codigo_barras = $('#codigo_barras').val();
			codigo_barras = 'FM' + codigo_barras;
			//console.log(codigo_barras);
			//JsBarcode('.barcode', codigo_barras).init();
			//$(".barcode").JsBarcode(codigo_barras);

			/*JsBarcode(".barcode", codigo_barras, {
				width:7,
				height:20,
				displayValue: true
			});*/

			//$('#codigo_barras').val(codigo_barras);
			//console.log(codigo_barras);
		});

		var $generar_crear = $('#generar_crear');
		var $codigo_crear = $('#codigo_barras');
		$generar_crear.on('click', function () {

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/productos/generarbc'
			}).done(function (objeto) {
				$codigo_crear.val(objeto.codigo);
				$codigo_crear.trigger('blur');
				var codigo_barras = $('#codigo_barras').val();
				codigo_barras = 'FM' + codigo_barras;
				/*JsBarcode(".barcode", codigo_barras, {
					width:7,
					height:20,
					displayValue: true
				});*/
			}).fail(function () {
				$codigo_crear.val('');
				$codigo_crear.trigger('blur');
			});
		});



	$.validate({
			modules: 'basic,security'
		});

	$('.form-control:first').select();
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
});

</script>
<?php require_once show_template('footer-sidebar'); ?>