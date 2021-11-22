<?php
// Obtiene los parametros
$id_cliente = (isset($params[0])) ? $params[0] : 0;

// Obtiene la cadena csrf
//$csrf = set_csrf();

// Obtiene el cliente
$cliente = $db->select('z.*')
				->from('inv_clientes z')
				->where('z.id_cliente', $id_cliente)
				->fetch_first();
$categorias = $db->from('inv_categorias_cliente')->order_by('categoria')->fetch();

// Ejecuta un error 404 si no existe el cliente
if (!$cliente) { 
	require_once not_found(); 
	exit; 
}

// Obtiene los permisos
$permiso_listar 	= in_array('listar', $_views);
$permiso_crear 		= in_array('crear', $_views);
$permiso_ver 		= in_array('ver', $_views);
$permiso_eliminar 	= in_array('eliminar', $_views);
$permiso_imprimir 	= in_array('imprimir', $_views);

// $permiso_saltar = in_array('saltar', $permisos);
// $permiso_subir = in_array('subir', $permisos);
// $permiso_suprimir = in_array('suprimir', $permisos);
$permiso_saltar = true;
$permiso_subir = true;
$permiso_suprimir = true;

?>
<?php require_once show_template('header-sidebar'); ?>

<link rel="stylesheet" href="<?= css; ?>/cropper.css">
<link rel="stylesheet" href="<?= css; ?>/main.css">

<div class="panel-heading">
	<h3 class="panel-title" data-header="true">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Modificar cliente</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar || $permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_imprimir) : ?>
	<div class="row">
		<div class="col-xs-6">
			<div class="text-label hidden-xs">Seleccionar acción:</div>
			<div class="text-label visible-xs-block">Acciones:</div>
		</div>
		<div class="col-xs-6 text-right">
			<div class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<span class="hidden-xs">Acciones</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li class="dropdown-header visible-xs-block">Seleccionar acción</li>
					<?php if ($permiso_listar) : ?>
					<li><a href="?/clientes/listar"><span class="glyphicon glyphicon-list-alt"></span> Listar clientes</a></li>
					<?php endif ?>
					<?php if ($permiso_crear) : ?>
					<li><a href="?/clientes/crear"><span class="glyphicon glyphicon-plus"></span> Crear cliente</a></li>
					<?php endif ?>
					<?php if ($permiso_ver) : ?>
					<li><a href="?/clientes/ver/<?= $id_cliente; ?>"><span class="glyphicon glyphicon-search"></span> Ver cliente</a></li>
					<?php endif ?>
					<?php if ($permiso_eliminar) : ?>
					<li><a href="?/clientes/eliminar/<?= $id_cliente; ?>" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span> Eliminar cliente</a></li>
					<?php endif ?>
					<?php if ($permiso_imprimir) : ?>
						<!--li>
							<a href="?/clientes/imprimir/<?php // $id_cliente; ?>" target="_blank">
							<span class="glyphicon glyphicon-print"></span> Imprimir cliente
							</a>
						</li-->
					<?php endif ?>
				</ul>
			</div>
		</div>
	</div>
	<hr>
	<?php endif ?>
	<div class="row">
		<div class="col-sm-3">
			<img src="<?= ($cliente['imagen'] == '') ? imgs . '/image.jpg' : files . '/clientes/' . $cliente['imagen']; ?>" class="img-responsive thumbnail cursor-pointer" data-toggle="lightbox" data-lightbox-content="<?= escape($cliente['id_cliente']); ?>" data-lightbox-size="modal-md">
			<?php if ($permiso_subir || $permiso_suprimir) { ?>
			<div class="list-group">
				<?php if ($permiso_subir) { ?>
				<a href="#" class="list-group-item text-truncate" data-toggle="modal" data-target="#modal_subir" data-backdrop="static" data-keyboard="false">
					<span class="glyphicon glyphicon-picture"></span>
					<span>Subir imagen</span>
				</a>
				<?php } ?>
				<?php if ($permiso_suprimir) { ?>
				<a href="?/clientes/suprimir/<?= $id_cliente; ?>" class="list-group-item text-truncate" data-suprimir="true">
					<span class="glyphicon glyphicon-eye-close"></span>
					<span>Eliminar imagen</span>
				</a>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<div class="col-sm-8 col-sm-offset-2 col-md-5 col-md-offset-1">
			<form method="post" action="?/clientes/guardar" autocomplete="off">
				<input type="hidden" name="<?= $csrf; ?>">
				<div class="form-group">
					<label for="nombre_cliente" class="control-label">Nombre cliente:</label>
					<input type="text" value="<?= $cliente['nombre_cliente']; ?>" name="nombre_cliente" id="nombre_cliente" class="form-control" autofocus="autofocus" data-validation="required letternumber length" data-validation-allowing="-/.#() " data-validation-length="max40">
					<input type="text" value="<?= $id_cliente; ?>" name="id_cliente" id="id_cliente" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El campo no es válido">
				</div>
				<div class="form-group">
					<label for="nit_ci" class="control-label">Nit/ci:</label>
					<input type="text" value="<?= $cliente['nit_ci']; ?>" name="nit_ci" id="nit_ci" class="form-control" data-validation="required letternumber length" data-validation-allowing="-/.#() " data-validation-length="max15">
				</div>
				<div class="form-group">
					<label for="telefono" class="control-label">Telefono:</label>
					<input type="text" value="<?= $cliente['telefono']; ?>" name="telefono" id="telefono" class="form-control">
				</div>
				<div class="form-group">
					<label for="direccion" class="control-label">Direccion:</label>
					<textarea name="direccion" id="direccion" class="form-control"><?= $cliente['escalafon']; ?></textarea>
				</div>
				<div class="form-group">
					<label for="categoria" class="control-label">Categoría:</label>
					<select name="categoria_id" id="categoria_id" class="form-control" data-validation="required">
						<option value="">Seleccionar</option>
						<?php foreach ($categorias as $elemento) { ?>
							<?php if ($elemento['id_categoria_cliente'] == $cliente['categoria_cliente_id']) { ?>
							<option value="<?= $elemento['id_categoria_cliente']; ?>" selected><?= escape($elemento['categoria']); ?></option>
							<?php } else { ?>
							<option value="<?= $elemento['id_categoria_cliente']; ?>"><?= escape($elemento['categoria']); ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</div>
				<br>
				<br>
				<div class="form-group">
					<button type="submit" class="btn btn-primary">
						<span class="glyphicon glyphicon-floppy-disk"></span>
						<span>Guardar</span>
					</button>
					<button type="reset" class="btn btn-default">
						<span class="glyphicon glyphicon-refresh"></span>
						<span>Restablecer</span>
					</button>
				</div>
			</form>
		</div>
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
	          <img src="imgs/picture.jpg" alt="Picture">
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

<script src="<?= js; ?>/cropper.js"></script>
<script src="<?= js; ?>/main_c.js"></script>


<script>
var $modal_subir = $('#modal_subir');
var $image = $('#image');

$(function () {
	$.validate({
		modules: 'basic'
	});
	
	<?php if ($permiso_crear) : ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/clientes/crear';
				break;
			}
		}
	});
	<?php endif ?>
	
	<?php if ($permiso_eliminar) : ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var href = $(this).attr('href');
		var csrf = '<?= $csrf; ?>';
		bootbox.confirm('Está seguro que desea eliminar el cliente?', function (result) {
			if (result) {
				$.request(href, csrf);
			}
		});
	});
	<?php endif ?>

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
		bootbox.confirm('Está seguro que desea eliminar la imagen del cliente?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_saltar) : ?>//////////////revisar
	$('[data-saltar]').on('click', function (e) {
		e.preventDefault();
		var href = $(this).attr('href');
		window.location = href;
	});
	<?php endif ?>

	
});
function functy(){
	$modal_subir.modal('hide');
}
</script>
<?php require_once show_template('footer-sidebar'); ?>