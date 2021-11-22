<?php

// Obtiene el modelo unidades
$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

// Obtiene el modelo categorias
$categorias = $db->from('inv_categorias')->order_by('categoria')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
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
			<a href="?/productos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8">
			<form method="post" action="?/productos/api_guardar_producto" class="form-horizontal" autocomplete="off">
				<div class="form-group">
					<label for="codigo" class="col-md-3 control-label">Código:</label>
					<div class="col-md-9">
					
						<input type="text" value="" name="codigo" id="codigo" class="form-control" data-validation="required alphanumeric length server" data-validation-allowing="-/.#º() " data-validation-length="max50" data-validation-url="?/productos/validar">
					</div>
				</div>
				<div class="form-group">
					<label for="codigo_barras" class="col-md-3 control-label">Código de barras:</label>
					<div class="col-md-9">
						<div class="input-group">
							<span class="input-group-btn">
								<button type="text" id="IC" class="btn btn-default">
									<span class="hidden-xs">IC</span>
								</button>
							</span>
							<input type="text" value="" name="codigo_barras" id="codigo_barras" class="form-control" data-validation="alphanumeric length server" data-validation-allowing="-/.#º() " data-validation-length="max50" data-validation-url="?/productos/validar_barras" data-validation-optional="true">                       
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
						<input type="text" value="" name="nombre" id="nombre" class="form-control" data-validation-length="max100"> <!-- data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"() ' -->
					</div>
				</div>
				<div class="form-group">
					<label for="nombre_factura" class="col-md-3 control-label">Nombre en la factura:</label>
					<div class="col-md-9">
						<input type="text" value="" name="nombre_factura" id="nombre_factura" class="form-control" data-validation-length="max50"><!-- data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"() '-->
					</div>
				</div>
				<div class="form-group">
					<label for="categoria_id" class="col-md-3 control-label">Categoría:</label>
					<div class="col-md-9">
						<select name="categoria_id" id="categoria_id" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($categorias as $elemento) { ?>
							<option value="<?= $elemento['id_categoria']; ?>"><?= escape($elemento['categoria']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="cantidad_minima" class="col-md-3 control-label">Cantidad mínima:</label>
					<div class="col-md-9">
						<input type="text" value="0" name="cantidad_minima" id="cantidad_minima" class="form-control" data-validation="required number" data-validation-allowing="range[0;1000000]">
					</div>
				</div>
				<div class="form-group">
					<label for="unidad_id" class="col-md-3 control-label">Unidad:</label>
					<div class="col-md-9">
						<select name="unidad_id" id="unidad_id" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($unidades as $elemento) { ?>
							<option value="<?= $elemento['id_unidad']; ?>"><?= escape($elemento['unidad']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
                <div class="form-group">
                    <label for="precio_actual" class="col-md-3 control-label">Precio del producto:</label>
                    <div class="col-md-9">
                        <input type="text" value="" name="precio_actual" id="precio_actual" class="form-control" data-validation="number" data-validation-allowing="range[0.0;100000],float" data-validation-optional="true">
                    </div>
                </div>
                <div class="form-group">
                    <label for="precio_actual" class="col-md-3 control-label">Rango:</label>
                    <div class="col-md-9">
                        <input type="text" value="" name="rango" id="rango" class="form-control" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true">
                    </div>
                </div>
				<div class="form-group">
					<label for="label_regalo" class="col-md-3 control-label">Producto de Regalo:</label>
					<div class="col-md-9">
						<select name="producto_regalo" id="producto_regalo" class="form-control" data-validation="required">
							<option value="no">No</option>
							<option value="si">Si</option>							
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="ubicacion" class="col-md-3 control-label">Ubicación:</label>
					<div class="col-md-9">
						<textarea name="ubicacion" id="ubicacion" class="form-control" rows="3" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" rows="3" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
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

		<div class="col-sm-4">
			<?php if ($producto['codigo_barras'] != 'IC') : ?>
				<div class="thumbnail hidden" data-print-code="true">
					<img class="barcode img-responsive" jsbarcode-format="code128" jsbarcode-value="" jsbarcode-displayValue="true" jsbarcode-width="2" jsbarcode-height="64" jsbarcode-margin="0" jsbarcode-textMargin="-3" jsbarcode-fontSize="20" jsbarcode-lineColor="#333">
				</div>
				<div class="thumbnail">
					<svg id="codigo_barras" class="barcode img-responsive" jsbarcode-format="code128" jsbarcode-value="" jsbarcode-displayValue="true" jsbarcode-width="2" jsbarcode-height="64" jsbarcode-margin="0" jsbarcode-textMargin="-3" jsbarcode-fontSize="20" jsbarcode-lineColor="#333"></svg>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/JsBarcode.all.min.js"></script>
<script>
	$(function () {
		$('#IC').on('click',function(e){
			e.preventDefault();
		});
		$('#codigo_barras').on('keyup',function(){
			var codigo_barras = $('#codigo_barras').val();
			codigo_barras = 'IC' + codigo_barras;
			console.log(codigo_barras);
			//JsBarcode('.barcode', codigo_barras).init();
			//$(".barcode").JsBarcode(codigo_barras);

			JsBarcode(".barcode", codigo_barras, {
				width:7,
				height:200,
				displayValue: true
			});

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

				//console.log(objeto);

				$codigo_crear.val(objeto.codigo);
				$codigo_crear.trigger('blur');
				var codigo_barras = $('#codigo_barras').val();
				codigo_barras = 'IC' + codigo_barras;
				JsBarcode(".barcode", codigo_barras, {
					width:7,
					height:200,
					displayValue: true
				});		
			}).fail(function (jqXHR, textStatus, errorThrown) {
				$codigo_crear.val('');
				$codigo_crear.trigger('blur');				
			});
		});




		$.validate({
			modules: 'basic,security'
		});

		$('#nombre').on('keyup', function () {
			$('#nombre_factura').val($.trim($(this).val()));
		});

		$('.form-control:first').select();
	});
</script>

<?php require_once show_template('footer-sidebar'); ?>