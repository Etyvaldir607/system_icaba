<?php
function calcularStock($id_producto,$id_almacen,$id_egreso){
    global $db;
    $query ="SELECT IFNULL(I.cantidad_ingresos, 0) AS cantidad_ingresos,
			       IFNULL(E.cantidad_egresos, 0) AS cantidad_egresos
			FROM inv_productos p
			LEFT JOIN
			  (SELECT d.producto_id,
			          SUM(d.cantidad * u.tamanio) AS cantidad_ingresos
			   FROM inv_ingresos_detalles d
			   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
			   LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
			   LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
			   WHERE i.almacen_id = $id_almacen
			   GROUP BY d.producto_id) I ON I.producto_id = p.id_producto
			LEFT JOIN
			  (SELECT d.producto_id,
			          SUM(d.cantidad * u.tamanio) AS cantidad_egresos
			   FROM inv_egresos_detalles d
			   LEFT JOIN inv_egresos e ON (e.id_egreso = d.egreso_id
			                               AND estado = 'V')
			   LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
			   LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
			   WHERE e.almacen_id = $id_almacen AND id_egreso!=$id_egreso
			   GROUP BY d.producto_id) E ON E.producto_id = p.id_producto
			WHERE p.id_producto = $id_producto";
 
    $resultado = $db->query($query)->fetch_first();
    $stock = $resultado != null ? $resultado['cantidad_ingresos']-$resultado['cantidad_egresos']: 0;
    return $stock;
}

// Obtiene el id_almacen
$id_egreso = (isset($params[0])) ? $params[0] : 0;

$egreso 	= $db->from('inv_egresos')->where('id_egreso', $id_egreso)->fetch_first();
$id_almacen = $egreso['almacen_id'];
$almacen 	= $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

if (!$egreso) {
	// Error 404
	require_once not_found();
	exit;
}

$detalles = $db->from('inv_egresos e')
				->join('inv_egresos_detalles d','d.egreso_id = e.id_egreso','left')
				->join('inv_productos p','d.producto_id = p.id_producto','left')
				->join('inv_asignaciones a','a.id_asignacion = d.asignacion_id','left')
				->join('inv_unidades u','a.unidad_id = u.id_unidad','left')
				->join('inv_categorias c','p.categoria_id = c.id_categoria','left')
				->where(['d.egreso_id'=>$id_egreso,'e.almacen_id'=>$id_almacen])
				->order_by('d.id_detalle')
				->fetch();

$productos = $db->query("SELECT
                                    p.id_producto,
                                    p.imagen,
                                    p.codigo,
                                    p.codigo,
                                    p.nombre,
                                    p.nombre_factura,
                                    p.cantidad_minima,
                                    p.descripcion,
                                    IFNULL(I.cantidad_ingresos,0) AS cantidad_ingresos,
                                    IFNULL(E.cantidad_egresos,0) AS cantidad_egresos,
                                    c.categoria,
                                    z.id_asignacion, z.unidad_id, z.tamanio, z.unidad_descripcion
                                FROM inv_productos p
                                LEFT JOIN (SELECT
                                                d.producto_id,
                                                SUM(d.cantidad*u.tamanio) AS cantidad_ingresos
                                            FROM
                                                inv_ingresos_detalles d
                                            LEFT JOIN inv_ingresos i ON
                                                i.id_ingreso = d.ingreso_id
                                            LEFT JOIN inv_asignaciones a ON
                                                a.id_asignacion = d.asignacion_id
                                            LEFT JOIN inv_unidades u ON
                                                u.id_unidad = a.unidad_id
                                            WHERE
                                                i.almacen_id = $id_almacen
                                            GROUP BY
                                                d.producto_id
                                          ) I ON  I.producto_id = p.id_producto
                                LEFT JOIN ( SELECT
                                                d.producto_id,
                                                SUM(d.cantidad*u.tamanio) as cantidad_egresos
                                            FROM inv_egresos_detalles d
                                            LEFT JOIN inv_egresos e ON
                                                (e.id_egreso = d.egreso_id AND estado='V')
                                            LEFT JOIN inv_asignaciones a ON
                                                a.id_asignacion = d.asignacion_id
                                            LEFT JOIN inv_unidades u ON
                                                u.id_unidad = a.unidad_id
                                            WHERE
                                                e.almacen_id = $id_almacen AND id_egreso!=$id_egreso
                                                GROUP BY d.producto_id
                                            ) E ON E.producto_id = p.id_producto
                                LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id 
                                LEFT JOIN ( SELECT
                                                w.producto_id,
                                                GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion,
                                                GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id,
                                                GROUP_CONCAT(
                                                    w.unidad,
                                                    ':',
                                                    w.precio_actual SEPARATOR '&'
                                                ) AS unidad_descripcion,
                                                GROUP_CONCAT(w.tamanio SEPARATOR '|') AS tamanio
                                            FROM
                                                (SELECT
                                                        *
                                                    FROM
                                                        inv_asignaciones q
                                                    LEFT JOIN inv_unidades u ON
                                                        q.unidad_id = u.id_unidad
                                                    ORDER BY
                                                        u.unidad
                                                    DESC
                                                ) w GROUP BY w.producto_id 

                                          ) z ON p.id_producto=z.producto_id
                                order by c.orden ASC, p.codigo")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
$empleados = $db->from("sys_empleados")->fetch();

?>
<?php require_once show_template('header-sidebar'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
.input-xs {
	height: 22px;
	padding: 1px 5px;
	font-size: 12px;
	line-height: 1.5;
	border-radius: 3px;
}
.position-left-bottom {
	bottom: 0;
	left: 0;
	position: fixed;
	z-index: 1030;
}
.margin-all {
	margin: 15px;
}
.display-table {
	display: table;
}
.display-cell {
	display: table-cell;
	text-align: center;
	vertical-align: middle;
}
.btn-circle {
	border-radius: 50%;
	height: 75px;
	width: 75px;
}

</style>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos del egreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form method="post" action="?/egresos/guardar" id="formulario" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
							<input type="hidden" value="<?= $id_egreso; ?>" name="id_egreso">
							<input type="hidden" value="<?= $egreso['tipo']; ?>" name="tipo">
							<input type="hidden" value="<?= $egreso['empleado_id']; ?>" name="usuario">
							<input type="hidden" value="<?= $egreso['responsable_id']; ?>" name="responsable">
							<input type="hidden" value="<?= $egreso['conductor_id']; ?>" name="conductor">
						</div>
					</div>
					<div class="form-group" id="alma">
                        <label for="almac" class="col-sm-4 control-label">Almacén:</label>
                        <div class="col-sm-8">
                            <select name="almac" id="almac" class="form-control" data-validation="required">
                                <?php foreach($otro_alma as $otro){ ?>
                                <option value="<?= $otro['id_almacen'] ?>"><?= $otro['almacen'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
					<div class="form-group" id="responsable_ingreso">
						<label for="responsable_ingreso" class="col-sm-4 control-label">Responsable de ingreso:</label>
						<div class="col-sm-8">
							<select name="responsable_ingreso" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
								<option value="">Buscar</option>
								<?php foreach ($empleados as $elemento) { ?>
								<option value="<?= escape($elemento['id_empleado']); ?>"><?= escape($elemento['nombres'].' '.$elemento['paterno'].' '.$elemento['materno']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
                    <div class="form-group">
						<label for="descripcion" class="col-sm-4 control-label">Observaciones:</label>
						<div class="col-sm-8">
							<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true" readonly></textarea>
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
									<th class="text-nowrap text-center">CANTIDAD</th>
									<th class="text-nowrap text-center">UNIDAD</th>
									<th class="text-nowrap text-center">PRECIO</th>
									<th class="text-nowrap text-center">IMPORTE</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($detalles as $key => $detalle): ?>
								<?php $cantidad = escape($detalle['cantidad']); ?>
								<?php $precio 	= escape($detalle['precio']); ?>
								<?php $importe 	= $cantidad * $precio; ?>
								<?php $total 	= $total + $importe; ?>
								<?php $stock 	= calcularStock($detalle['producto_id'],$id_almacen,$id_egreso); ?>
								<tr class="active" data-producto="<?= $detalle['producto_id']?>" data-asignacion="<?= $detalle['asignacion_id']; ?>">
									<td class="text-nowrap align-middle"><input type="text" value="<?= $detalle['id_detalle']; ?>" name="detalles[]" class="translate" tabindex="-1" data-validation="required"><b><?= ($key+1); ?></b></td>
									<td class="text-nowrap align-middle"><input type="text" value="<?= $detalle['producto_id']?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="<?= $detalle['asignacion_id']; ?>"><?= $detalle['codigo']?></td>
									<td class="align-middle"><input type="text" value="<?= $detalle['nombre']; ?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?= $detalle['nombre']; ?></td>
									<td class="align-middle">
										<input type="text" value="<?= $detalle['cantidad']; ?>" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="<?= $detalle['asignacion_id']; ?>" data-validation="required number" data-validation-allowing="range[1;<?= $stock; ?>]" data-validation-error-msg="Debe ser un número positivo entre 1 y <?= $stock; ?>" onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>); validarStock(<?= $detalle['producto_id']?>,<?= $detalle['asignacion_id']; ?>,<?= $stock; ?>);">

									<td class="align-middle"><input type="text" value="<?= $detalle['unidad']; ?>" class="form-control text-right" autocomplete="off" data-tamanio-stock="<?= $detalle['tamanio'];?>" data-unidad="<?= $detalle['unidad']; ?>" readonly></td>
		
									<td class="align-middle"><input type="text" value="<?= $precio; ?>" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="<?= $detalle['precio']; ?>" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>)" onchange="setTwoNumberDecimal(this)"></td>
									<td class="text-nowrap align-middle text-right" data-importe=""><?= number_format($importe, 2, '.', ''); ?></td>
									<td class="text-nowrap align-middle text-center">
										<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(<?= $detalle['asignacion_id']; ?>)">
											<span class="glyphicon glyphicon-trash"></span>
										</button>
									</td>
								</tr>	
								<?php endforeach ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal=""><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="<?= count($detalles);?>" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;20]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
							<input type="text" name="monto_total" value="<?= number_format($total, 2, '.', ''); ?>" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12 text-right">
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
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-cog"></span>
					<strong>Datos generales</strong>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="list-group">
					<li class="list-group-item">
						<i class="glyphicon glyphicon-home"></i>
						<strong>Casa Matriz: </strong>
						<span><?= escape($_institution['nombre']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-qrcode"></i>
						<strong>NIT: </strong>
						<span><?= escape($_institution['nit']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-user"></i>
						<strong>Empleado: </strong>
						<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
					</li>
				</ul>
			</div>
			<div class="panel-footer text-center"><?= credits; ?></div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>Búsqueda de productos</strong>
				</h3>
			</div>
			<div class="panel-body">
				<?php if ($permiso_listar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/egresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Lista de egresos</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Código</th>
							<th class="text-nowrap">Nombre</th>
							<th class="text-nowrap">Categoría</th>
							<th class="text-nowrap">Stock</th>
							<th class="text-nowrap">Precio</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach ($productos as $nro => $producto) { 
							$asignaciones =  $db->select('*')
												->from('inv_asignaciones a')
												->join('inv_unidades u','a.unidad_id=u.id_unidad')
												->where('a.producto_id',$producto['id_producto'])
												->fetch();?>
						<tr>
							<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
							
							<td class="text-nowrap">
								<span data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></span>
							</td>
							
							<td><?= escape($producto['categoria']); ?></td>
							
							<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
							
							<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>">
								
								<?php foreach($asignaciones as $asignacion){ ?>
									<p style="margin-bottom: 3%;">
                                    <span>
                                    <span data-nombre-unidad="<?= $asignacion['id_asignacion']; ?>"><?= escape($asignacion['unidad']); ?></span>(<span data-tamanio-asignacion="<?= $asignacion['id_asignacion']; ?>"><?= escape($asignacion['tamanio']); ?></span>):
									<span data-precio-asignacion="<?= $asignacion['id_asignacion']; ?>"><?= escape($asignacion['precio_actual']); ?></span>
									
                                    <button type="button" class="btn btn-xs btn-primary" data-egresar="<?= $producto['id_producto']; ?>" data-id-asignacion="<?= $asignacion['id_asignacion']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-share-alt"></span></button> 
                                    
                                    <button type="button" class="btn btn-xs btn-success" data-actualizar-producto="<?= $producto['id_producto']; ?>" data-actualizar="<?= $asignacion['id_asignacion']; ?>" onclick="actualizar(this);" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
                                    </span>
                                    </p>
                                <?php } ?>

							</td>
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
		</div>
	</div>
</div>
<h2 class="btn-info position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es un egreso" data-placement="right"><i class="glyphicon glyphicon-log-out display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	var table;

    $('#alma').hide();
    $('#conductor').hide();
    $('#responsable_ingreso').hide();
    
	$('[data-egresar]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-egresar')),$.trim($(this).attr('data-id-asignacion')));
	});

	table = $('#productos').DataTable({
		info: false,
		order: []
	});

	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

	$.validate({
		form: '#formulario',
		modules: 'basic'
	});

	$('#formulario').on('reset', function () {
		$('#ventas tbody').empty();
		calcular_total();
	});
});

function actualizar (elemento){
	var $elemento = $(elemento), id_asignacion,id_producto;
	id_producto = $elemento.attr('data-actualizar-producto');
	id_asignacion = $elemento.attr('data-actualizar');
	var id_almacen = <?= $id_almacen; ?>;

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/egresos/actualizar',
		data: {
			id_producto: id_producto,
			id_asignacion: id_asignacion,
			id_almacen: id_almacen
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var unidad 	= producto.unidad;
			var precio = parseFloat(producto.precio_actual).toFixed(2);
			var stock = parseInt(producto.stock);
			console.log(producto.stock);
			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-nombre-unidad='+id_asignacion+']').text(unidad);
			$busqueda.find('[data-precio-asignacion='+id_asignacion+']').text(precio);
			
			var $producto = $('[data-asignacion=' + producto.id_asignacion + ']');
			var $cantidad = $producto.find('[data-cantidad]');
			var $precio = $producto.find('[data-precio]');

			if ($producto.size()) {
				$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
				$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
				$precio.val(precio);
				$precio.attr('data-precio', precio);
				descontar_precio(producto.id_producto);
			}

			$.notify({
				message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
			}, {
				type: 'success'
			});
		} else {
			$.notify({
				message: 'Ocurrió un problema durante el proceso, es posible que no existe un almacén principal.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$.notify({
			message: 'Ocurrió un problema durante el proceso, no se pudo actualizar el stock ni el precio del producto.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#loader').fadeOut(100);
	});
}

function ca( obj , x )
{
    var tipo = obj[ obj.selectedIndex ].value;
    if (tipo == "Traspaso") {
        $('#alma').show();
        $('#conductor').show();
        $('#responsable_ingreso').show();
    } else {
        $('#alma').hide();
        $('#conductor').hide();
        $('#responsable_ingreso').hide();
    }
}

function adicionar_producto(id_producto,id_asignacion) {
	var $ventas 	= $('#ventas tbody');
	var $producto 	= $ventas.find('[data-producto=' + id_producto + ']');
	var $asignacion = $ventas.find('[data-asignacion=' + id_asignacion + ']');
	var $cantidad 	= $producto.find('[data-cantidad='+id_asignacion+']');
	var numero 		= $ventas.find('[data-producto]').size() + 1;
	var codigo 		= $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre 		= $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock 		= $.trim($('[data-stock=' + id_producto + ']').text());
	var valor 		= $.trim($('[data-valor=' + id_producto + ']').text());
	var unidad 		= $.trim($('[data-nombre-unidad='+id_asignacion+']').text());
	var precio 		= $.trim($('[data-precio-asignacion='+id_asignacion+']').text());
	var tamanio     = $.trim($('[data-tamanio-asignacion='+id_asignacion+']').text());
	var plantilla 	= '';
	var cantidad;

	if ($asignacion.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '" data-asignacion="'+id_asignacion+'">' +
						'<td class="text-nowrap align-middle"><b>' + numero + '</b></td>' +
						'<td class="text-nowrap align-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="'+id_asignacion+'">' + codigo + '</td>' +
						'<td class="align-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						
						'<td class="align-middle">'+

							'<input type="text" value="1" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="'+id_asignacion+'" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_asignacion + '); validarStock('+id_producto+','+id_asignacion+','+stock+');">'+

							'<input type="text" data-tamanio='+id_asignacion+' class="translate" data-validation="required number" data-validation-allowing="range[1;'+stock+']" data-validation-error-msg="Stock insuficiente"></td>' +
						
						'<td class="align-middle"><input type="text" value="' + unidad + '" class="form-control text-right" autocomplete="off" data-tamanio-stock="'+tamanio+'" data-unidad="' + unidad + '" </td>' +
						
						'<td class="align-middle"><input type="text" value="' + precio + '" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="' + precio + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_asignacion + ')" onchange="setTwoNumberDecimal(this)"></td>' +
						'<td class="text-nowrap align-middle text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap align-middle text-center">' +
							'<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(' + id_asignacion + ')">Eliminar</button>' +
						'</td>' +
					'</tr>';

		$ventas.append(plantilla);

		$ventas.find('[data-cantidad], [data-precio]').on('click', function () {
			$(this).select();
		});

		$ventas.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		$.validate({
			form: '#formulario',
			modules: 'basic'
		});
	}

	calcular_importe(id_asignacion);
	validarStock(id_producto,id_asignacion,stock);
}

function validarStock(id_producto,id_asignacion,stock) {
	
	var $ventas,$productos,$producto,$cantidad,$tamanio,cantidad_utilizada = 0,cantidad_actual = 1;
	//datos del detalle de venta
	$ventas    = $('#ventas tbody');
	$productos = $ventas.find('[data-producto='+id_producto+']');
	$producto  = $('[data-asignacion=' + id_asignacion + ']');
	$cantidad  = $producto.find('[data-cantidad]');
	$tamanio   = $producto.find('[data-tamanio]');
	$productos.each(function (i,el) {
		if ($(el).data('asignacion') != id_asignacion) {
			cantidad_utilizada += ($(el).find('td:eq(3) [data-cantidad]').val())*($(el).find('td:eq(4) [data-tamanio-stock]').data('tamanio-stock'));
			$(el).find('td:eq(3) [data-cantidad]').attr('data-validation-allowing', 'range[1;' + cantidad_utilizada + ']');
			$(el).find('td:eq(3) [data-cantidad]').attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y '+cantidad_utilizada);
			$(el).find('td:eq(3) [data-tamanio]').attr('data-validation-allowing', 'range[1;' + cantidad_utilizada + ']');
			$(el).find('td:eq(3) [data-tamanio]').attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y'+cantidad_utilizada);
		} else {
			//alert("2 --- "+$(el).data('asignacion')+" --- "+id_asignacion);
			//alert( $(el).find('td:eq(3) [data-cantidad]').val() );
			//alert( $(el).find('td:eq(4) [data-tamanio-stock]').data('tamanio-stock') );
			
			$(el).data('asignacion');
			$(el).find('td:eq(3) [data-cantidad]').val();
			$(el).find('td:eq(4) [data-tamanio-stock]').data('tamanio-stock');
			cantidad_actual = ($(el).find('td:eq(3) [data-cantidad]').val())*($(el).find('td:eq(4) [data-tamanio-stock]').data('tamanio-stock'));
		}
	});

	//alert("cantidad "+cantidad_actual);

	stock_total = stock-cantidad_utilizada;
	$cantidad.attr('data-validation-allowing', 'range[1;' + stock_total + ']');
	$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock_total);
	$tamanio.val(cantidad_actual);
	$tamanio.attr('data-validation-allowing', 'range[1;' + stock_total + ']');
	$tamanio.attr('data-validation-error-msg', 'Sobrepasó las '+stock_total+' unidades, contiene ' + cantidad_actual+' unidades');
}

function eliminar_producto(id_asignacion) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-asignacion=' + id_asignacion + ']').remove();
			renumerar_productos();
			calcular_total();
		}
	});
}

function renumerar_productos() {
	var $ventas = $('#ventas tbody');
	var $productos = $ventas.find('[data-producto]');
	$productos.each(function (i) {
		$(this).find('td:first').text(i + 1);
	});
}

function calcular_importe(id_asignacion) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-asignacion=' + id_asignacion + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-precio]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, precio, importe;

	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	importe = cantidad * precio;
	importe = importe.toFixed(2);
	$importe.text(importe);

	calcular_total();
}

function calcular_total() {
	var $ventas = $('#ventas tbody');
	var $total = $('[data-subtotal]:first');
	var $importes = $ventas.find('[data-importe]');
	var importe, total = 0;

	$importes.each(function (i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});

	$total.text(total.toFixed(2));
	$('[data-ventas]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
}

function setTwoNumberDecimal(obj) {
	obj.value = parseFloat(obj.value).toFixed(2);
}
</script>
<?php require_once show_template('footer-sidebar'); ?>