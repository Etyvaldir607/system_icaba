<?php

// Obtiene los productos
$productos = $db->select('z.*, a.unidad as unidad, b.categoria as categoria')->from('inv_productos z')->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')->order_by('z.id_producto')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', PERMITS);

// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_ver = in_array('ver', $permisos);

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
		<span class="<?= ICON_PANEL; ?>"></span>
		<strong>Lista de precios</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
				<th class="text-nowrap">Nombre</th>
				<th class="text-nowrap">Categoría</th>
				<th class="text-nowrap">Cantidad</th>
				<th class="text-nowrap">Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap">Costo Total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Precio Total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Utilidad <?= escape($moneda); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nombre</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Categoría</th>
				<th class="text-nowrap align-middle">Cantidad</th>
				<th class="text-nowrap align-middle">Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle">Costo Total <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle">Precio Total <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle">Utilidad <?= escape($moneda); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php 
			foreach ($productos as $nro => $producto) { 
				
				$query="SELECT  SUM(cantidad*tamanio)as cantidadAcumul ";
				$query.=" FROM inv_egresos_detalles vd ";
				$query.=" INNER JOIN inv_egresos v ON egreso_id=id_egreso ";
				$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
				$query.=" LEFT JOIN inv_unidades u ON id_unidad=unidad_id ";
				$query.=" WHERE vd.producto_id='".$producto['id_producto']."' ";
				$ventas = $db->query($query)->fetch();
				
				$cantidadTotal = 0; 
				foreach ($ventas as $nro2 => $venta) { 
					$cantidadTotal = escape($venta['cantidadAcumul']);
				}
			
				$mensaje="";
				
				$prodIngresados=0;
				$swPrimerIngreso=true;
				$costo=0;
				$costoTotal=0;
				$query="SELECT  * ";
				$query.=" FROM inv_ingresos_detalles vd ";
				$query.=" INNER JOIN inv_ingresos as v ON ingreso_id=id_ingreso ";
				$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
				$query.=" LEFT JOIN inv_unidades u ON id_unidad=unidad_id ";
				$query.=" WHERE vd.producto_id='".$producto['id_producto']."' ORDER BY fecha_ingreso";
				$iAntiguos = $db->query($query)->fetch();
				foreach ($iAntiguos as $nro3 => $iAntiguo) { 
					$prodIngresados=$prodIngresados+($iAntiguo['cantidad']*$iAntiguo['tamanio']);
					//se compara los productos previamente vendidos y costos antiguos
					//para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
					if($prodIngresados>$cantidadTotal){
						//verificar si es el primer Ingreso
						if($swPrimerIngreso){
							$saldo=$prodIngresados-$cantidadTotal;	
							$swPrimerIngreso=false;					
						}
						else{
							$saldo=$iAntiguo['cantidad'];	
						}
						//echo $saldo."<br>";
						//echo $iAntiguo['costo']."<br>";

						//dividimos entre el tamaño de la unidad de medida, de esa compra en especifico
						$costoTotal=$costoTotal+($saldo*($iAntiguo['costo']/$iAntiguo['tamanio']));
						$costo=$iAntiguo['costo'];
					}				
				}
				$cantidad=$prodIngresados-$cantidadTotal;
				
				

				//obtener los precios y costos guardados en asignaciones
				$query="SELECT  * ";
				$query.=" FROM inv_asignaciones a ";
				$query.=" LEFT JOIN inv_unidades u ON id_unidad=unidad_id ";
				$query.=" WHERE producto_id='".$producto['id_producto']."' ";
				$query.=" ORDER BY tamanio ASC";
				$precioActual = $db->query($query)->fetch_first();
				if($precioActual){
					$precio=$precioActual['precio_actual'];
					$costo=$precioActual['costo_actual'];
				}
				

				$precioT=$precio*$cantidad;
				//$costoT=$costo*$cantidad;
				$utilidad=$precioT-$costoTotal;
				?>
				<tr>
					<th class="text-nowrap"><?= $nro + 1; ?></th>
					<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
					<td class="width-lg"><?= escape($producto['nombre']); ?></td>
					<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
					<td class="text-nowrap text-right"><?= escape($cantidad); ?></td>
					<td class="text-nowrap text-right"><?= number_format($costo,2,"."," "); ?></td>
					<td class="text-nowrap text-right"><?= number_format($precio,2,"."," "); ?></td>
					<td class="text-nowrap text-right" data-costo="<?= $costoTotal; ?>"><?= number_format($costoTotal,2,"."," "); ?></td>
					<td class="text-nowrap text-right" data-precio="<?= $precioT; ?>"><?= number_format($precioT,2,"."," "); ?></td>
					<td class="text-nowrap text-right" data-total="<?= $utilidad; ?>"><?php echo number_format($utilidad,2,"."," "); ?></td>				
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

<div class="well">
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Costo Total:</b>
			<u id="costototal">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Precio Total:</b>
			<u id="preciototal">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Utilidad Total:</b>
			<u id="total">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="clearfix"></div>
</div>

<!-- Inicio modal precio-->
<?php if ($permiso_cambiar) { ?>
<div id="modal_precio" class="modal fade">
	<div class="modal-dialog">
		<form id="form_precio" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Actualizar precio</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Código:</label>
							<p id="codigo_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Precio actual <?= escape($moneda); ?>:</label>
							<p id="actual_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Precio nuevo <?= escape($moneda); ?>:</label>
							<input type="text" value="" id="producto_precio" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" value="" id="nuevo_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_precio" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal precio-->

<script src="<?= JS; ?>/jquery.dataTables.min.js"></script>
<script src="<?= JS; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= JS; ?>/jquery.base64.js"></script>
<script src="<?= JS; ?>/pdfmake.min.js"></script>
<script src="<?= JS; ?>/vfs_fonts.js"></script>
<script src="<?= JS; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.es.js"></script>
<script src="<?= JS; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
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

	<?php if ($permiso_cambiar) { ?>
	$.validate({
		form: '#form_precio',
		modules: 'basic',
		onSuccess: function () {
			var producto = $('#producto_precio').val();
			var precio = $('#nuevo_precio').val();

			$loader_precio.fadeIn(100);

			$.ajax({
				type: 'POST',
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

	<?php if ($productos) { ?>
	var table = $('#table').on('search.dt order.dt page.dt length.dt', function () {
		var suma = 0;
		$('[data-costo]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-costo'));
			suma = suma + total;
		});
		$('#costototal').text(suma.toFixed(2));

		suma = 0;
		$('[data-precio]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-precio'));
			suma = suma + total;
		});
		$('#preciototal').text(suma.toFixed(2));

		suma = 0;
		$('[data-total]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total'));
			suma = suma + total;
		});
		$('#total').text(suma.toFixed(2));
	}).DataFilter({
		filter: true,
		name: 'reporte_diario',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
		
});
</script>
<?php require_once show_template('footer-sidebar'); ?>