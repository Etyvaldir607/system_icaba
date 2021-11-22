<?php

// Obtiene los productos
/*$productos = $db->select('z.*, a.unidad as unidad, b.categoria as categoria')->from('inv_productos z')->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')->order_by('z.id_producto')->fetch();*/
//$productos = $db->select('z.*, b.categoria as categoria')->from('inv_productos z')->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')->order_by('c.id_categoria ASC')->order_by('z.codigo')->fetch();
$productos = $db->query("SELECT p.*, c.categoria
						FROM inv_productos p
						LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria
						ORDER BY c.orden ASC, p.codigo")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_asignar = in_array('asignar', $permisos);
$permiso_unidad_base =  in_array('unidad', $permisos);
$permiso_fijar = false;
$permiso_quitar = in_array('quitar', $permisos);
$permiso_cambiar_precio = in_array('cambiar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<style>
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
</style>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de precios</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para imprimir el informe general hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/precios/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
				<th class="text-nowrap">Nombre</th>
				<th class="text-nowrap">Categoría</th>
				<?php if ($permiso_ver || $permiso_cambiar) { ?>
				<th class="text-nowrap">Precios <?= escape($moneda); ?></th>
				<th class="text-nowrap">Nuevo precio</th>
				<th class="text-nowrap">Ver historial</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nombre</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Categoría</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Precio <?= escape($moneda); ?></th>
				<?php if ($permiso_asignar) { ?>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Nuevo precio</th>
				<?php } ?>
				<?php if ($permiso_ver) { ?>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Ver historial</th>
				<?php } ?>				
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($productos as $nro => $producto) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
				<td class="width-lg" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></td>
				<!--td class="text-nowrap" data-precio="<?php // $producto['id_producto']; ?>"><?php // escape($producto['precio_actual']); ?></td-->
				<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
				<td class="text-nowrap align-middle " data-precioTD="<?= $producto['id_producto']; ?>" align="right">
					<?php 
					 	$id_producto = $producto['id_producto'];
					 	$asignaciones = $db->query("SELECT a.*, u.*
                    								FROM inv_asignaciones a 
                    								LEFT JOIN inv_productos p ON p.id_producto = a.producto_id
                    								LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                    								WHERE p.id_producto = $id_producto AND 
                    									  a.visible = 's'
                    								")->fetch();
                    	//echo $producto['u.unidad'];
                    	//var_dump($asignaciones);die;
					 ?>

					 <?php foreach ($asignaciones as $i => $asignacion) : ?>
                        <?php if (empty($asignaciones)) : ?>
                            <span>No asignado</span>
                        <?php else : ?>
                        	<span data-unidad="<?= $asignacion['id_asignacion']; ?>">
                        		<?= escape($asignacion['unidad']); ?>. 
                        	</span>
                            <b> Precio: 
                            	<span data-precio="<?= $asignacion['id_asignacion']; ?>"><?= escape($asignacion['precio_actual']); ?></span> 
                            </b>
                            
                            <?php if ($permiso_unidad_base || $permiso_cambiar_precio || $permiso_quitar) { ?>
	                        	<?php if ($permiso_unidad_base) : ?>
	                                <?php if ($asignacion['tipo'] == 'principal') { ?>
										<input type="radio" name="" data-unidad="true" data-toggle="tooltip" data-title="Precio Base" id="unidad" onclick="unidad_base(<?= $asignacion['id_asignacion']; ?>)" style="transform: scale(0.7); margin-top: 1%; margin-right: -1%;" checked>
										<?php $permiso_quitar = false?>
										<?php } else { ?>
										<input type="radio" data-unidad="true" data-toggle="tooltip" data-title="Precio Base" id="unidad" onclick="unidad_base(<?= $asignacion['id_asignacion']; ?>)" style="transform: scale(0.7); margin-top: 1%; margin-right: -1%;">
										<?php $permiso_quitar = true?>
									<?php } ?>
	                            <?php endif ?>
	                            &nbsp
	                            <?php if ($permiso_cambiar_precio) : ?>
	                                <a href="?/precios/asignar/" class="underline-none" data-toggle="tooltip" data-title="Editar precio" data-cambiar-precio="<?= $producto['id_producto']; ?>" data-asignacion="<?= $asignacion['id_asignacion']; ?>">
	                                    <span class="glyphicon glyphicon-refresh"></span>
	                                </a>
	                            <?php endif ?>
	                            <?php if ($permiso_quitar) : ?>
	                                <a href="?/precios/quitar/<?= $asignacion['id_asignacion']; ?>" class="underline-none" data-toggle="tooltip" data-title="Eliminar unidad" data-quitar="true">
	                                    <span class="glyphicon glyphicon-remove-circle"></span>
	                                </a>
	                            <?php endif ?>
	                            <br>
	                        <?php } ?>
                     	<?php endif ?>
                    <?php endforeach ?>
				</td>	
				<?php if ($permiso_asignar) { ?>
					<td class="text-nowrap align-middle">
					<a href="?/precios/asignar/<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Nuevo precio" data-asignar="<?= $producto['id_producto']; ?>">
						<button type="button" class="btn btn-primary" tabindex="-1"><span class="glyphicon glyphicon-tag"></span> Nuevo Precio</button>
					</a>
				</td>
				<?php } ?>
				<?php if ($permiso_ver) { ?>
				<td class="text-nowrap align-middle">
					<a href="?/precios/ver/<?= $asignacion['id_asignacion']; ?>" target="_blank" data-toggle="tooltip" data-title="Ver historial">
						<button type="button" class="btn btn-primary" tabindex="-1"><span class="glyphicon glyphicon-list-alt"></span> Ver historial</button>
					</a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen productos registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal agregar nuevo precio-->
<?php if ($permiso_asignar) { ?>
<div id="modal_asignar" class="modal fade">
	<div class="modal-dialog">
		<form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
			<div class="modal-header">
				<h4 class="modal-title">Asignar nuevo Precio</h4>
			</div>
			<div class="modal-body">
					<div class="form-group">
                        <label for="tamano" class="control-label">
                            <span>Producto:</span>
                            <span class="text-primary"></span>
                        </label>
                        <input type="text" value="" name="nombre_producto" id="nombre_producto" class="form-control" data-validation="number" data-validation-optional="true" disabled>
                    </div>
					<div class="form-group">
                        <label for="unidad_id_asignar" class="control-label">Unidad de venta:</label>
                        <input type="hidden" value="" name="id_asignacion" id="id_asignacion" class="form-control">
                        <select name="unidad_id" id="unidad_id" class="form-control" data-validation="required">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="producto_precio" class="control-label">
                            <span>Precio de venta:</span>
                            <span class="text-primary"><?= $moneda; ?></span>
                        </label>
                        <input type="text" value="" name="precio" id="producto_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float">
                    </div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar-asignar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_asignar" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal agregar nuevo precio-->

<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content loader-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script>
$(function () {
	var $quitar = $('[data-quitar]');
    <?php if ($permiso_quitar) : ?>
    $quitar.on('click', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var csrf = '<?= $csrf; ?>';
        bootbox.confirm('Está seguro que desea eliminar la unidad?', function (result) {
            if (result) {
                $.request(href, csrf);
            }
        });
    });
    <?php endif ?>

	<?php if ($permiso_cambiar) { ?>
	var $modal_precio = $('#modal_precio');
	var $form_precio = $('#form_precio');
	var $loader_precio = $('#loader_precio');

	$form_precio.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_precio.on('hidden.bs.modal', function () {
		$form_precio.trigger('reset');
	});

	$modal_precio.on('shown.bs.modal', function () {
		$modal_precio.find('.form-control:first').focus();
	});

	$modal_precio.find('[data-cancelar]').on('click', function () {
		$modal_precio.modal('hide');
	});

	$('[data-cambiar]').on('click', function (e) {
		e.preventDefault();
		var id_producto = $(this).attr('data-cambiar');
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var precio = $.trim($('[data-precio=' + id_producto + ']').text());

		$('#producto_precio').val(id_producto);
		$('#codigo_precio').text(codigo);
		$('#actual_precio').text(precio);
		
		$modal_precio.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>

	<?php if ($permiso_asignar) { ?>
		var $modal_asignar = $('#modal_asignar');
		var $form_asignar = $('#form_asignar');
		var $loader_asignar = $('#loader_asignar');

		$('[data-asignar]').on('click', function (e) {
			e.preventDefault();
			var id_producto = $(this).attr('data-asignar');
			var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
			var href = $(this).attr('href');
			$form_asignar.attr('action', href);
			$("#nombre_producto").val(nombre);
			$modal_asignar.modal("show");
			listar_unidades(id_producto);
		});

		$modal_asignar.find('[data-cancelar-asignar]').on('click', function () {
			$modal_asignar.modal('hide');
		});
	<?php } ?>

	<?php if ($permiso_cambiar_precio) { ?>
		var $modal_asignar = $('#modal_asignar');
		var $form_asignar = $('#form_asignar');
		var $loader_asignar = $('#loader_asignar');

		$('[data-cambiar-precio]').on('click', function (e) {
			e.preventDefault();
			var id_producto = $(this).attr('data-cambiar-precio');
			var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
			var id_asignacion = $(this).attr('data-asignacion');
			var unidad = $.trim($('[data-unidad=' + id_asignacion + ']').text());
			var precio = $.trim($('[data-precio=' + id_asignacion + ']').text());

			var href = $(this).attr('href');
			ruta = href + id_producto //concatena la ruta con el id del producto
			$form_asignar.attr('action', ruta);
			$("#nombre_producto").val(nombre);
			$("#id_asignacion").val(id_asignacion);
			$modal_asignar.modal("show");

			buscar_unidad(id_asignacion);

			$("#unidad_id").html("");
			$("#producto_precio").val(precio);
			$("#unidad_id").append('<option value="">'+ unidad +'</option>');
			
		});

		$modal_asignar.find('[data-cancelar-asignar]').on('click', function () {
			$modal_asignar.modal('hide');
		});
	<?php } ?>

	<?php if ($productos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'lista_precios',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>

	<?php if ($permiso_cambiar) { ?>
	$.validate({
		form: '#form_precio',
		modules: 'basic',
		onSuccess: function () {
			var producto = $('#producto_precio').val();
			var precio = $('#nuevo_precio').val();

			$loader_precio.fadeIn(100);

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/precios/cambiar',
				data: {
					id_producto: producto,
					precio: parseFloat(precio).toFixed(2)
				}
			}).done(function (producto) {
				var cell = table.cell($('[data-precio=' + producto.producto_id + ']'));
				cell.data(producto.precio).draw();
				
				$.notify({
					title: '<strong>Actualización satisfactoria!</strong>',
					message: '<div>El precio del producto se actualizó correctamente.</div>'
				}, {
					type: 'success'
				});
			}).fail(function () {
				$.notify({
					title: '<strong>Advertencia!</strong>',
					message: '<div>Ocurrió un problema y el precio del producto no se actualizó correctamente.</div>'
				}, {
					type: 'danger'
				});
			}).always(function () {
				$loader_precio.fadeOut(100, function () {
					$modal_precio.modal('hide');
				});
			});
		}
	});
	<?php } ?>
});

function unidad_base(id_asignacion){
	//console.log(id_asignacion);
	var url = "?/precios/unidad/" + id_asignacion
	bootbox.confirm('Está seguro que desea poner esta unidad como base?', function (result) {
		if(result){
			window.location = url;
		}
	});
}

function listar_unidades(producto_id){
	$.ajax({
		type: 'POST',
        url: '?/productos/procesos',
        dataType: 'json',
        data: {'producto_id': producto_id, 'boton': 'listar_unidades'},
        success: function(data){
            //console.log(data);
            $("#unidad_id").html("");
            $('#unidad_id').append($('<option>', {
                    value: '',
                    text: 'Seleccione'
                }));
            for (var i = 0; i < data.length; i++) {
                $('#unidad_id').append($('<option>', {
                    value: data[i].id_unidad,
                    text: data[i].unidad
                }));
            }
        }
	});
}

function buscar_unidad(asignacion_id){
	$.ajax({
		type: 'POST',
        url: '?/productos/procesos',
        dataType: 'json',
        data: {'asignacion_id': asignacion_id, 'boton': 'buscar_unidad'},
        success: function(data){
            $("#unidad_id").html("");
            $('#unidad_id').append($('<option>', {
                    value: data.id_unidad,
                    text: data.unidad
            }));     
        }
	});
}
</script>
<?php require_once show_template('footer-sidebar'); ?>