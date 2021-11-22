<?php

// Obtiene el id_venta
$id_venta = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la venta
$venta = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
			->from('inv_egresos i')
			->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
			->join('inv_pagos p', 'p.movimiento_id = i.id_egreso AND p.tipo="Egreso"', 'left')
			->where('id_egreso', $id_venta)
			->fetch_first();

// Verifica si existe el egreso
if (!$venta) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los detalles
/*$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')->from('inv_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_venta)->order_by('id_detalle asc')->fetch();*/
$detalles = $db->query("SELECT d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, u.tamanio, p.id_producto
								FROM inv_egresos_detalles d
								LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
								LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
								LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
								LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
							    WHERE d.egreso_id = $id_venta
								ORDER BY c.orden asc, codigo asc")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la dosificacion del periodo actual
$dosificacion = $db->from('inv_dosificaciones')->where('fecha_limite', $venta['fecha_limite'])->fetch_first();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_editar = in_array('editar', $permisos);
$permiso_mostrar = in_array('mostrar', $permisos);
$permiso_reimprimir = in_array('obtener', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading" data-venta="<?= $id_venta; ?>" data-servidor="<?= ip_local . 'servidor/nota.php'; ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Detalle de orden de compra</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_reimprimir || $permiso_editar || $permiso_mostrar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_reimprimir) { ?>
			<!--div onclick="javascript:generar_factura('<?php //echo $id_venta; ?>');" class="btn btn-default"><i class="glyphicon glyphicon-file"></i><span class="hidden-xs hidden-sm"> Termico</span></div-->
			<?php } ?>
			<?php if ($permiso_reimprimir) { ?>
			<a href="?/notas/imprimir/<?= $id_venta; ?>" target="_blank" class="btn btn-default"><i class="glyphicon glyphicon-file"></i><span class="hidden-xs hidden-sm"> Exportar</span></a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<button type="button" class="btn btn-danger" data-editar="true"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs"> Editar cliente</span></button>
			<a href="?/notas/modificar/<?= $venta['id_egreso']; ?>" class="btn btn-success"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs hidden-sm hidden-md"> Editar</span></a>
			<?php } ?>
			<?php if ($permiso_mostrar) { ?>
			<a href="?/notas/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm hidden-md"> Órdenes de compra</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary" data-servidor="<?= ip_local . name_project . '/nota.php'; ?>">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle de la orden de compra</h3>
				</div>
				<div class="panel-body">
					<?php if ($detalles) { ?>
					<div class="table-responsive">
						<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Unidad</th>
									<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
									<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr>
									<?php $cantidad = escape($detalle['cantidad']); ?>
									<?php $precio = escape($detalle['precio']); ?>
									<?php $importe = $cantidad * $precio; ?>
									<?php $total = $total + $importe; ?>
									<th class="text-nowrap"><?= $nro + 1; ?></th>
									<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
									<td class="text-nowrap"><?php echo escape($detalle['nombre_factura']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad; ?></td>
									<td class="text-nowrap"><?= escape($detalle['unidad']) . " (" . escape($detalle['tamanio']) . ")"; ?></td>
									<td class="text-nowrap text-right"><?= $precio; ?></td>
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<?php
						 		if($total > 0){
									$descuento = ($total * $venta['descuento']) / 100 ;
									$descuento_total = $total - $descuento;
								}else{
									$descuento_total = $total;
								}
								if($venta['descuento'] > 0.00){
								?>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
									</tr>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="6">DESCUENTO DEL <?= escape(number_format($venta['descuento']), 0) . " %"?></th>
										<th class="text-nowrap text-right"><?= escape( number_format($descuento, 2, '.', '')) . ""?></th>
									</tr>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL CON DESCUENTO<?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($descuento_total, 2, '.', '')?></th></th>
									</tr>
								<?php
								}else{
								?>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($descuento_total, 2, '.', ''); ?></th>
									</tr>
								<?php
								}
								?>								
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Esta orden de compra no tiene detalle, es muy importante que todos los egresos cuenten con un detalle que especifique la operación realizada.</p>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-out"></i> Información de la orden de compra</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="col col-md-6">
							<div class="form-group">
								<label class="col-md-3 control-label">Fecha y hora:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Cliente:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nombre_cliente']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">NIT / CI:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nit_ci']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Tipo de egreso:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['tipo']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Número de factura:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nro_factura']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Descripción:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['descripcion']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Monto total:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['monto_total']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Descuento:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['descuento'])." %"; ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Monto total con descuento:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= number_format(escape($venta['monto_total']*(1-$venta['descuento']/100)) ,2,'.',''); ?></p>
								</div>
							</div>
						</div>
						
						<div class="col col-md-6">
							<div class="form-group">
								<label class="col-md-3 control-label">Código de control:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['codigo_control']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Número de registros:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nro_registros']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Almacén:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['almacen']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Empleado:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Inicio modal cliente -->
<?php if ($permiso_editar) { ?>
<div id="modal_cliente" class="modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/notas/editar" id="form_cliente" class="modal-content">
			<input type="hidden" value="<?= $id_venta ?>" name="id_egreso" id="id_egreso" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
			<div class="modal-header">
				<h4 class="modal-title">Editar datos del cliente</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
							<div class="col-sm-8">
								<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
									<option value="">Buscar</option>
									<?php 
									$clientes = $db->query("SELECT id_egreso, nombre_cliente, nit_ci, telefono, direccion, GROUP_CONCAT(b.estado SEPARATOR '|') AS pago 
															FROM inv_egresos c
															LEFT JOIN inv_pagos a ON c.id_egreso = a.movimiento_id
															LEFT JOIN inv_pagos_detalles b ON a.id_pago = b.pago_id
															GROUP BY c.nombre_cliente, c.nit_ci
															ORDER BY c.nombre_cliente ASC, c.nit_ci ASC "
														)->fetch();

									foreach ($clientes as $cliente) {
	                                    $pago = explode('|',$cliente['pago']);
	                                    if(in_array('0', $pago, true)){
	                                        $cliente['pago'] = 0;
	                                    } else {
	                                        $cliente['pago'] = 1;
	                                    }
	                                     ?>
									<option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']) . '|' . escape($cliente['telefono']) . '|' . escape($cliente['direccion']) . '|' . escape($cliente['pago']); ?>"><?= escape($cliente['nit_ci']) . ' &mdash; ' . escape($cliente['nombre_cliente']); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="form-group">
							<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="form-group">
							<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="form-group">
							<label for="telefono" class="col-sm-4 control-label">Telefono:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max100" data-validation-optional="true">
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="form-group">
							<label for="direccion" class="col-sm-4 control-label">Dirección:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max200" data-validation-optional="true">
							</div>
							<div class="clearfix"></div>
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
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal cliente -->

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>

<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>

<script>

$(function () {

	var $cliente 		= $('#cliente');
	var $nit_ci 		= $('#nit_ci');
	var $telefono 		= $("#telefono");
	var $direccion 		= $("#direccion");
	var $nombre_cliente = $('#nombre_cliente');
	
	$cliente.selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$cliente.css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$cliente.trigger('blur');
		},
		onBlur: function () {
			$cliente.trigger('blur');
		}
	}).on('change', function (e) {
		var valor = $(this).val();
		valor = valor.split('|');
		$(this)[0].selectize.clear();
		if (valor.length != 1) {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			$telefono.prop('readonly', false);
            $direccion.prop('readonly', false);
			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
			$telefono.val(valor[2]);
            $direccion.val(valor[3]);
            if(valor[4]==0){
                window.alert('El cliente: '+valor[1]+' tiene una deuda');
            }
			
		} else {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			$telefono.prop('readonly', false);
			$direccion.prop('readonly', false);
			
			if (es_nit(valor[0])) {
				$nit_ci.val(valor[0]);
				$nombre_cliente.val('').focus();
				$telefono.val('');
				$direccion.val('');
			} else {
				$nombre_cliente.val(valor[0]);
				$nit_ci.val('').focus();
				$telefono.val('');
				$direccion.val('');
			}
		}
	});

	<?php if ($permiso_reimprimir) { ?>
	var id_venta = $('[data-venta]').attr('data-venta');

	$('[data-reimprimir]').on('click', function () {
		//$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/notas/obtener',
			data: {
				id_venta: id_venta
			}
		}).done(function (venta) {
			if (venta) {
				var servidor = $.trim($('[data-servidor]').attr('data-servidor'));

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: servidor,
					data: venta
				}).done(function (respuesta) {
					//$('#loader').fadeOut(100);
					switch (respuesta.estado) {
						case 'success':
							$.notify({
								title: '<strong>Operación satisfactoria!</strong>',
								message: '<div>Imprimiendo factura...</div>'
							}, {
								type: 'success'
							});
							break;
						default:
							$.notify({
								title: '<strong>Advertencia!</strong>',
								message: '<div>La impresora no responde, asegurese de que este conectada y vuelva a intentarlo nuevamente.</div>'
							}, {
								type: 'danger'
							});
							break;
					}
				}).fail(function () {
					//$('#loader').fadeOut(100);
					$.notify({
						title: '<strong>Error!</strong>',
						message: '<div>Ocurrió un problema en el envio de la información, vuelva a intentarlo nuevamente y si persiste el problema contactese con el administrador.</div>'
					}, {
						type: 'danger'
					});
				});
			} else {
				//$('#loader').fadeOut(100);
				$.notify({
					title: '<strong>Error!</strong>',
					message: '<div>Ocurrió un problema al obtener los datos de la orden de compra.</div>'
				}, {
					type: 'danger'
				});
			}
		}).fail(function () {
			//$('#loader').fadeOut(100);
			$.notify({
				title: '<strong>Error!</strong>',
				message: '<div>Ocurrió un problema al obtener los datos de la orden de compra.</div>'
			}, {
				type: 'danger'
			});
		});
	});
	<?php } ?>

	<?php if ($permiso_editar) { ?>
	$.validate({
		modules: 'basic'
	});

	var $modal_cliente = $('#modal_cliente');
	var $form_cliente = $('#form_cliente');

	$modal_cliente.on('hidden.bs.modal', function () {
		$form_cliente.trigger('reset');
	});

	$modal_cliente.on('shown.bs.modal', function () {
		$modal_cliente.find('.form-control:first').focus();
	});

	$modal_cliente.find('[data-cancelar]').on('click', function () {
		$modal_cliente.modal('hide');
	});

	$('[data-editar]').on('click', function () {
		$modal_cliente.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
});

function generar_factura(nro) {
	//alert("ingresa 1"+nro);
	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/notas/termico',
		data: 'id='+nro
	}).done(function (venta) {
		//alert("ingresa 2"+venta);
		if (venta) {
			$.notify({
				message: 'La impresión fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			imprimir_factura(venta);
		} else {
			//$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema en el proceso.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		alert("error 2");
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema en el proceso.'
		}, {
			type: 'danger'
		});
	});
}
function imprimir_factura(venta) {
	//alert("ingresa 3");

	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: servidor,
		data: venta
	}).done(function (respuesta) {
		$('#loader').fadeOut(100);
		switch (respuesta.estado) {
			case 's':
				window.location.reload();
				break;
			case 'p':
				$.notify({
					message: 'La impresora no responde, asegurese de que este conectada y registrada en el sistema, una vez solucionado el problema vuelva a intentarlo nuevamente.'
				}, {
					type: 'danger'
				});
				break;
			default:
				$.notify({
					message: 'Ocurrió un problema durante el proceso, no se envió los datos para la impresión de la factura.'
				}, {
					type: 'danger'
				});
				break;
		}
	}).fail(function () {
		//$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema durante el proceso, reinicie la terminal para dar solución al problema y si el problema persiste contactese con el con los desarrolladores.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#formulario').trigger('reset');
		$('#form_buscar_0').trigger('submit');
	});
}
function es_nit(texto) {
	var numeros = '0123456789';
	for(i = 0; i < texto.length; i++){
		if (numeros.indexOf(texto.charAt(i), 0) != -1){
			return true;
		}
	}
	return false;
}
</script>
<?php require_once show_template('footer-sidebar'); ?>
