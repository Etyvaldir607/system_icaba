<?php

// Obtiene el id_producto
$id_producto = (isset($params[0])) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->select('z.*, a.unidad, a.sigla, b.categoria')->from('inv_productos z')->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')->where('z.id_producto', $id_producto)->fetch_first();

// Verifica si existe el producto
if (!$producto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? escape($moneda['sigla']) : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_listar = in_array('listar', $permisos);
$permiso_subir = in_array('subir', $permisos);
$permiso_suprimir = in_array('suprimir', $permisos);
$permiso_saltar = in_array('saltar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>

<!--link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous"-->
<link rel="stylesheet" href="<?= css; ?>/cropper.css">
<link rel="stylesheet" href="<?= css; ?>/main.css">


<input type="hidden" id="id_producto" value="<?= $producto['id_producto']; ?>"/>

<p class="lead">Detalle del producto</p>
<hr>
<?php if ($permiso_crear || $permiso_editar || $permiso_eliminar || $permiso_imprimir || $permiso_listar) { ?>
<div class="row">
	<div class="col-sm-7 col-md-6 hidden-xs">
		<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
	</div>
	<div class="col-xs-12 col-sm-5 col-md-6 text-right">
		<?php if ($permiso_crear) { ?>
		<a href="?/productos/crear" class="btn btn-success">
			<span class="glyphicon glyphicon-plus"></span>
			<span class="hidden-xs hidden-sm">Nuevo</span>
		</a>
		<?php } ?>
		<?php if ($permiso_editar) { ?>
		<a href="?/productos/editar/<?= $producto['id_producto']; ?>" class="btn btn-warning">
			<span class="glyphicon glyphicon-edit"></span>
			<span class="hidden-xs hidden-sm">Modificar</span>
		</a>
		<?php } ?>
		<?php if ($permiso_eliminar) { ?>
		<a href="?/productos/eliminar/<?= $producto['id_producto']; ?>" class="btn btn-danger" data-eliminar="true">
			<span class="glyphicon glyphicon-trash"></span>
			<span class="hidden-xs hidden-sm">Eliminar</span>
		</a>
		<?php } ?>
		<?php if ($permiso_imprimir) { ?>
		<a href="?/productos/imprimir/<?= $producto['id_producto']; ?>" target="_blank" class="btn btn-info">
			<span class="glyphicon glyphicon-print"></span>
			<span class="hidden-xs hidden-sm">Imprimir</span>
		</a>
		<?php } ?>
		<?php if ($permiso_listar) { ?>
		<a href="?/productos/listar" class="btn btn-primary">
			<span class="glyphicon glyphicon-list-alt"></span>
			<span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>">Listado</span>
		</a>
		<?php } ?>
	</div>
</div>
<hr>
<?php } ?>
<div class="row">
	<div class="col-sm-3">
		<img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" class="img-responsive thumbnail cursor-pointer" data-toggle="lightbox" data-lightbox-content="<?= escape($producto['nombre']); ?>" data-lightbox-size="modal-md">
		<?php if ($permiso_subir || $permiso_suprimir) { ?>
		<div class="list-group">
			<?php if ($permiso_subir) { ?>
			<a href="#" class="list-group-item text-truncate" data-toggle="modal" data-target="#modal_subir" data-backdrop="static" data-keyboard="false">
				<span class="glyphicon glyphicon-picture"></span>
				<span>Subir imagen</span>
			</a>
			<?php } ?>
			<?php if ($permiso_suprimir) { ?>
			<a href="?/productos/suprimir/<?= $id_producto; ?>" class="list-group-item text-truncate" data-suprimir="true">
				<span class="glyphicon glyphicon-eye-close"></span>
				<span>Eliminar imagen</span>
			</a>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<div class="col-sm-6">
		<div class="well">
			<p class="lead">Información del producto</p>
			<hr>
			<div class="table-display" data-print-data="true">
				<div class="tbody">
					<div class="tr">
						<div class="th text-nowrap">Fecha de creación:</div>
						<div class="td">
							<span><?= date_decode($producto['fecha_registro'], $_institution['formato']); ?></span>
							<span class="text-primary"><?= escape($producto['hora_registro']); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Código del producto:</div>
						<div class="td">
							<span><?= escape($producto['codigo']); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Código de barras:</div>
						<div class="td">
							<span><?= substr($producto['codigo_barras'], 2); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Nombre del producto:</div>
						<div class="td">
							<span><?= escape($producto['nombre']); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Nombre en la factura:</div>
						<div class="td">
							<span><?= escape($producto['nombre_factura']); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Categoría:</div>
						<div class="td">
							<span><?= escape($producto['categoria']); ?></span>
						</div>
					</div>
                    <div class="tr">
                        <div class="th text-nowrap">Cantidad mínima:</div>
                        <div class="td">
                            <span><?= escape($producto['cantidad_minima'] . ' ' . $producto['sigla']); ?></span>
                        </div>
                    </div>
                    <div class="tr">
                        <div class="th text-nowrap">Rango de precio:</div>
                        <div class="td">
                            <span><?= escape($producto['rango']); ?></span>
                        </div>
                    </div>
					<div class="tr">
						<div class="th text-nowrap">Unidad:</div>
						<div class="td">
							<span><?= escape($producto['unidad']); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Ubicación:</div>
						<div class="td">
							<span><?= (trim($producto['ubicacion']) == '') ? 'No asignado' : str_replace("\n", "<br>", escape($producto['ubicacion'])); ?></span>
						</div>
					</div>
					<div class="tr">
						<div class="th text-nowrap">Descripción:</div>
						<div class="td">
							<span><?= (trim($producto['descripcion']) == '') ? 'No asignado' : str_replace("\n", "<br>", escape($producto['descripcion'])); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php if ($permiso_saltar) : ?>
		<div class="pager">
			<div class="previous">
				<a href="?/productos/saltar/antes/<?= $id_producto; ?>" class="btn btn-default" data-saltar="true">
					<span class="glyphicon glyphicon-menu-left"></span>
					<span class="hidden-sm">Anterior</span>
				</a>
			</div>
			<div class="next">
				<a href="?/productos/saltar/despues/<?= $id_producto; ?>" class="btn btn-default" data-saltar="true">
					<span class="hidden-sm">Siguiente</span>
					<span class="glyphicon glyphicon-menu-right"></span>
				</a>
			</div>
		</div>
		<?php endif ?>
	</div>
	<div class="col-sm-3">
		<?php if ($producto['codigo_barras'] != 'CB') : ?>
		<div class="thumbnail hidden" data-print-code="true">
			<img class="barcode img-responsive" jsbarcode-format="code128" jsbarcode-value="<?= substr($producto['codigo_barras'], 2); ?>" jsbarcode-displayValue="true" jsbarcode-width="2" jsbarcode-height="64" jsbarcode-margin="0" jsbarcode-textMargin="-3" jsbarcode-fontSize="20" jsbarcode-lineColor="#333">
		</div>
		<div class="thumbnail">
			<svg class="barcode img-responsive" jsbarcode-format="code128" jsbarcode-value="<?= substr($producto['codigo_barras'], 2); ?>" jsbarcode-displayValue="true" jsbarcode-width="2" jsbarcode-height="64" jsbarcode-margin="0" jsbarcode-textMargin="-3" jsbarcode-fontSize="20" jsbarcode-lineColor="#333"></svg>
		</div>
		<?php endif ?>
		<div class="well">
			<p class="lead margin-none">Precio de venta</p>
			<hr>
			<p class="lead margin-none text-info"><?= escape($producto['precio_actual'] . ' ' . $moneda); ?></p>
		</div>
		<?php if ($producto['codigo_barras'] != 'CB') : ?>
		<p class="lead text-danger">Impresión de codigos de barras</p>
		<form id="impresion" data-codigo="<?= substr($producto['codigo_barras'], 2); ?>">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="Cantidad a imprimir">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-warning">
						<span class="glyphicon glyphicon-barcode"></span>
						<span>Imprimir</span>
					</button>
				</span>
			</div>
		</form>
		<?php endif ?>
	</div>
</div>

<!-- Modal subir inicio -->
<?php if ($permiso_subir) { ?>
<div id="modal_subir" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
	  <div class="container" style="background-color: #fff;">
	    <div class="col-md-8">
	      
	      <br><br>
	        
	      <div class="row">
	        <!-- <h3>Demo:</h3> -->
	        <div class="img-container">
	          <img src="images/picture.jpg" alt="Picture">
	        </div>
	      </div>      
	    </div>
	    
	    <div class="col-md-1">
	    <br>
	    </div>

	    <div class="row col-md-3" id="actions">
	      <div class="docs-buttons">
	        
	        <br><br>

	        <div class="btn-group">
	          <label class="btn btn-primary btn-upload" for="inputImage" title="Upload image file">
	            <input type="file" class="sr-only" id="inputImage" name="file" accept="image/*">
	            <span class="docs-tooltip" data-toggle="tooltip" title="Import image with Blob URLs">
	              <span class="glyphicon glyphicon-download-alt"></span> Cargar Imagen
	            </span>
	          </label>
	        </div>

	        <div class="btn-group">
	          <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.zoom(0.1)">
	              <span class="glyphicon glyphicon-zoom-in"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.zoom(-0.1)">
	              <span class="glyphicon glyphicon-zoom-out"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="scaleX" data-option="-1" title="Flip Horizontal">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.scaleX(-1)">
	              <span class="glyphicon glyphicon-resize-horizontal"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="scaleY" data-option="-1" title="Flip Vertical">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.scaleY(-1)">
	              <span class="glyphicon glyphicon-resize-vertical"></span>
	            </span>
	          </button>
	        </div>
	        
	        <div class="btn-group d-flex flex-nowrap">
	          <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Move Left">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.move(-10, 0)">
	              <span class="glyphicon glyphicon-arrow-left"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Move Right">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.move(10, 0)">
	              <span class="glyphicon glyphicon-arrow-right"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="-10" title="Move Up">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.move(0, -10)">
	              <span class="glyphicon glyphicon-arrow-up"></span>
	            </span>
	          </button>
	          <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="10" title="Move Down">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.move(0, 10)">
	              <span class="glyphicon glyphicon-arrow-down"></span>
	            </span>
	          </button>
	        </div>

	        <div class="btn-group d-flex flex-nowrap">
	          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.rotate(-45)">
	              <span class="glyphicon glyphicon-refresh"></span> Girar Izquierda
	            </span>
	          </button>
	        </div>
	        <div class="btn-group d-flex flex-nowrap">
	          <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.rotate(45)">
	              <span class="glyphicon glyphicon-refresh"></span> Girar Derecha
	            </span>
	          </button>
	        </div>
	        
	        <!-- Show the cropped image in modal -->
	        <div class="modal fade docs-cropped" id="getCroppedCanvasModal" role="dialog" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" tabindex="-1">
	          <div class="modal-dialog">
	            <div class="modal-content">
	              <div class="modal-header">
	                <h5 class="modal-title" id="getCroppedCanvasTitle">Cropped</h5>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                  <span aria-hidden="true">&times;</span>
	                </button>
	              </div>
	              <div class="modal-body"></div>
	              <div class="modal-footer">
	                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	                <a class="btn btn-primary" id="download" href="javascript:void(0);" download="cropped.jpg">Download</a>
	              </div>
	            </div>
	          </div>
	        </div><!-- /.modal -->

	      
		  	<div class="docs-toggles">
		        <!-- <h3>Toggles:</h3> -->
		        <div class="btn-group d-flex flex-nowrap" data-toggle="buttons">
		          <label class="btn btn-primary">
		            <input type="radio" class="sr-only" id="aspectRatio5" name="aspectRatio" value="NaN">
		            <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: NaN">
		              Libre
		            </span>
		          </label>
		        </div>

		        <div class="btn-group d-flex flex-nowrap" data-toggle="buttons">
		          <label class="btn btn-primary focus active">
		            <input type="radio" class="sr-only" id="aspectRatio5" name="aspectRatio" value="1" checked="checked">
		            <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 1">
		              Cuadrado
		            </span>
		          </label>
		        </div>

	      	</div><!-- /.docs-toggles -->

	        <div class="btn-group btn-group-crop  d-flex flex-nowrap">
	          <button type="button" class="btn btn-success" data-method="getCroppedCanvas" data-option="{ &quot;maxWidth&quot;: 512, &quot;maxHeight&quot;: 512 }">
	            <span class="docs-tooltip" data-toggle="tooltip" title="cropper.getCroppedCanvas({ maxWidth: 512, maxHeight: 512 })">
	              Guardar
	            </span>
	          </button>          
	        </div>

	        <div class="btn-group btn-group-crop  d-flex flex-nowrap">
	          <button type="button" class="btn btn-danger" onclick="functy();">
	            <span class="docs-tooltip" data-toggle="tooltip">
	              Cancelar
	            </span>
	          </button>          
	        </div>

	      </div><!-- /.docs-buttons -->

	      
	    </div>
	  </div>


<span onclick="functy();" style="color: #fff;">cerrar</span>



	</div>
</div>
<?php } ?>
<!-- Modal subir fin -->

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/JsBarcode.all.min.js"></script>


<!--script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script-->
<script src="<?= js; ?>/cropper.js"></script>
<script src="<?= js; ?>/main.js"></script>



<script>
var $modal_subir = $('#modal_subir');
var $image = $('#image');

$(function () {
	JsBarcode('.barcode').init();

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

	<?php if ($permiso_subir) { ?>
	
	$.validate({
		form: '#form_subir',
		modules: 'file'
	});

	$modal_subir.on('hidden.bs.modal', function () {
		$(this).find('form').trigger('reset');
		$container.hide();
	}).on('show.bs.modal', function (e) {
		if ($('.modal:visible').size() != 0) { 
			e.preventDefault(); 
		}
	});

	$('#imagen').on('validation', function (e, valid) {
		if (valid) {
			var input = $(this).get(0);
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$image.attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		} else {
			$container.hide();
		}
	}).on('change', function () {
		$container.hide();
	});
	<?php } ?>

	<?php if ($permiso_suprimir) { ?>
	$('[data-suprimir]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la imagen del producto?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_saltar) : ?>
	$('[data-saltar]').on('click', function (e) {
		e.preventDefault();
		var href = $(this).attr('href');
		window.location = href;
	});
	<?php endif ?>

	$('#impresion').on('submit', function (e) {
		e.preventDefault();
		var codigo = $(this).attr('data-codigo'), cantidad = $.trim($(this).find(':text').val());
		if ($.isNumeric(cantidad)) {
			window.open('?/productos/generar/' + codigo + '/' + cantidad, '_blank');
		} else {
			$(this).find(':text').val('');
			bootbox.alert('La información enviada debe ser de tipo numérico');
		}
	});
});
function functy(){
	$modal_subir.modal('hide');
}
</script>
<?php require_once show_template('footer-sidebar'); ?>