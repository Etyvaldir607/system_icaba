<?php
// Obtiene el id_almacen
$id_compra 	= (isset($params[0])) ? $params[0] : 0;
$compra 	= $db->from('inv_ingresos')->where('id_ingreso', $id_compra)->fetch_first();
$id_almacen = $compra['almacen_id'];
$almacen 	= $db->from("inv_almacenes")->where('id_almacen',$id_almacen)->fetch_first();

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

if (!$compra) {
	// Error 404
	require_once not_found();
	exit;
}

$detalles = $db->from('inv_ingresos i')
				->join('inv_ingresos_detalles d','d.ingreso_id=i.id_ingreso','left')
				->join('inv_productos p','d.producto_id = p.id_producto','left')
				->join('inv_asignaciones a','a.id_asignacion = d.asignacion_id','left')
				->join('inv_unidades u','a.unidad_id = u.id_unidad','left')
				->join('inv_categorias c','p.categoria_id = c.id_categoria','left')
				->where(['d.ingreso_id'=>$id_compra,'i.almacen_id'=>$id_almacen])
				->order_by('d.id_detalle')
				->fetch();

$tipo_="Ingreso";

$pago_simple = $db->select('*, count(id_pago_detalle) as nro_cuotas')
			->from('inv_pagos')
			->join('inv_pagos_detalles','pago_id = id_pago','left')
			->where('movimiento_id', $id_compra)
			->where('tipo', $tipo_)
			->fetch_first();

$pagos = $db->select('*, d.monto as monto_cuota')
			->from('inv_pagos')
			->join('inv_pagos_detalles d','pago_id = id_pago','left')
			->where('movimiento_id', $id_compra)
			->where('tipo', $tipo_)
			->fetch();

$query = "SELECT p.id_producto,
		       p.imagen,
		       p.codigo,
		       p.codigo,
		       p.nombre,
		       p.nombre_factura,
		       p.cantidad_minima,
		       p.descripcion,
		       IFNULL(I.cantidad_ingresos, 0) AS cantidad_ingresos,
		       IFNULL(E.cantidad_egresos, 0) AS cantidad_egresos,
		       c.categoria,
		       z.id_asignacion,
		       z.unidad_id,
		       z.tamanio,
		       z.unidad_descripcion
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
		   WHERE e.almacen_id = $id_almacen
		   GROUP BY d.producto_id) E ON E.producto_id = p.id_producto
		LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
		LEFT JOIN
		  (SELECT w.producto_id,
		          GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion,
		          GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id,
		          GROUP_CONCAT(w.unidad, ':', w.precio_actual SEPARATOR '&') AS unidad_descripcion,
		          GROUP_CONCAT(w.tamanio SEPARATOR '|') AS tamanio
		   FROM
		     (SELECT *
		      FROM inv_asignaciones q
		      LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
		      ORDER BY u.unidad DESC) w
		   GROUP BY w.producto_id) z ON p.id_producto = z.producto_id
		ORDER BY c.orden ASC,
		         p.codigo";

$productos = $db->query($query)->fetch();

//$proveedores = $db->select('nombre_proveedor, count(nombre_proveedor) as nro_visitas, sum(monto_total) as total_compras')->from('inv_ingresos')->group_by('nombre_proveedor')->order_by('nombre_proveedor asc')->fetch();

$proveedores = $db->select('id_proveedor, nombre_proveedor')
				  ->from('inv_proveedores')
				  ->group_by('nombre_proveedor')
				  ->order_by('nombre_proveedor asc')
				  ->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
?>
<?php require_once show_template('header-sidebar'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
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
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default" data-formato="<?= strtoupper($formato_textual); ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la compra</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form method="post" action="?/ingresos/guardar" id="formulario" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
							<input type="hidden" value="<?= $id_compra; ?>" name="id_compra">
							<input type="hidden" value="<?= $id_almacen; ?>" name="almacen_id">
							<input type="hidden" name="usuario" value="<?= $_user['persona_id']; ?>">
							<input type="hidden" name="responsable" value="<?= $compra['responsable_id']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="proveedor" class="col-sm-4 control-label">Proveedor:</label>
						<div class="col-sm-8">
							<select name="nombre_proveedor" id="proveedor" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
								<option value="">Buscar</option>
								<?php foreach ($proveedores as $elemento) { ?>
								<?php if ($compra['proveedor_id'] == $elemento['id_proveedor']): ?>
								<option value="<?= escape($elemento['id_proveedor'])."###".escape($elemento['nombre_proveedor']); ?>" selected><?= escape($elemento['nombre_proveedor']); ?></option>
								<?php else: ?>
								<option value="<?= escape($elemento['id_proveedor'])."###".escape($elemento['nombre_proveedor']); ?>"><?= escape($elemento['nombre_proveedor']); ?></option>
								<?php endif ?>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
						<div class="col-sm-8">
							<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true" readonly></textarea>
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Unidad</th>
									<th class="text-nowrap">Costo</th>
									<th class="text-nowrap">Importe</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $key => $detalle): ?>
								<?php $cantidad = escape($detalle['cantidad']); ?>
								<?php $costo = escape($detalle['costo']); ?>
								<?php $importe = $cantidad * $costo; ?>
								<?php $total = $total + $importe; ?>
								
								<tr class="active" data-producto="<?= $detalle['producto_id']?>" data-asignacion="<?= $detalle['asignacion_id']; ?>">
									<td class="text-nowrap align-middle"><input type="text" value="<?= $detalle['id_detalle']; ?>" name="detalles[]" class="translate" tabindex="-1" data-validation="required"><b><?= ($key+1); ?></b></td>
									<td class="text-nowrap align-middle"><input type="text" value="<?= $detalle['id_producto']; ?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="<?= $detalle['asignacion_id']?>"><?= $detalle['codigo']?></td>
									<td class="align-middle"><input type="text" value="<?= $detalle['nombre']; ?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?= $detalle['nombre']; ?></td>
									<td class="align-middle"><input type="text" value="<?= $detalle['cantidad'];?>" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="<?= $detalle['asignacion_id']; ?>" data-validation="required number" data-validation-allowing="range[1;100000]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100000"  onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>)"></td>
									<td class="align-middle"><?= $detalle['unidad']. '('.$detalle['tamanio'].')'; ?></td>
									<td class="align-middle"><input type="text" value="<?= $detalle['costo']; ?>" name="costos[]" class="form-control text-right" maxlength="10" autocomplete="off" data-precio="<?= $detalle['costo']; ?>" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>)" onchange="setTwoNumberDecimal(this)"></td>
									<td class="text-nowrap align-middle text-right" data-importe=""><?= number_format($importe, 2, '.', ''); ?></td>
									<td class="text-nowrap align-middle text-center">
										<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(<?= $detalle['asignacion_id']; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
										<!-- <button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(<?php // $detalle['asignacion_id']; ?>)"><i class="glyphicon glyphicon-trash"></i></button> -->
									</td>
								</tr>
								<?php endforeach ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal=""><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" >
							
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" >
							
							<input type="text" name="monto_total" id="data-subporcentaje" value="<?= number_format($total, 2, '.', ''); ?>" class="translate" tabindex="-1" data-total="" >
						</div>
					</div>







					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>
						<div class="col-md-8">
							<select name="forma_pago" id="forma_pago" class="form-control" data-validation="required number" onchange="set_plan_pagos()">
								<option value="1" <?php if($detalle['plan_de_pagos']=="no"){ ?> selected <?php } ?> >Pago Completo</option>
								<option value="2" <?php if($detalle['plan_de_pagos']!="no"){ ?> selected <?php } ?> >Plan de Pagos</option>					
							</select>
						</div>
					</div>

					<div id="plan_de_pagos" 
						<?php if($detalle['plan_de_pagos']=="no"){ ?>								
							style="display:none"
						<?php }else{ ?>								
							style="display:block"						
						<?php } ?>													
						>
						<div class="form-group">
							<label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>
							<div class="col-md-8">
								<input type="text" value="<?= $pago_simple['nro_cuotas'] ?>" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">
							</div>
						</div>

						<table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center col-xs-4">Detalle</th>
									<th class="text-nowrap text-center col-xs-4">Fecha</th>
									<th class="text-nowrap text-center col-xs-4">Monto</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$key0=-1;
								foreach ($pagos as $key0 => $pago){
									$key=$key0+1; 
									?>
									<input name="id_pago" value="<?php echo $pago['pago_id']; ?>" type="hidden">

									<tr class="active cuotaclass">
										<?php if($key==1){ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $key ?>" data-cuota2="<?= $key ?>" class="cuota_div">Pago Inicial:</div>
											</td>
										<?php } else{ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $key ?>" data-cuota2="<?= $key ?>" class="cuota_div">Cuota <?= $key ?>:</div>
											</td>
										<?php } ?>
										
										<td><div data-cuota="<?= $key ?>" class="cuota_div"><div class="col-sm-12">
											<input name="id_pago_detalle[]" value="<?php echo $pago['id_pago_detalle']; ?>" type="hidden">

											<input id="inicial_fecha_<?= $key ?>" name="fecha[]" value="<?php
												$vec=explode("-",$pago['fecha']); 
												echo $vec[2]."-".$vec[1]."-".$vec[0]; 
											?>" class="form-control" autocomplete="off" <?php if($key==1){ ?> data-validation="required" <?php } ?> data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $key ?>);" onblur="javascript:change_date(<?= $key ?>);" 													
											>
										</div></div></td>
										
										<td><div data-cuota="<?= $key ?>" class="cuota_div">
											<input type="text" value="<?php echo $pago['monto_cuota'] ?>" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $key ?>');">
										</div></td>
										<td align="center">
											<div data-cuota="<?= $key ?>" class="cuota_div">
											<?php 
												if($pago['estado']==1){
													echo "Cancelado"; 
												}else{ 
													echo "Sin Pagar"; 
												} 
											?>
											</div>
										</td>
									</tr>
								<?php } ?>
								
								<?php for($i=$key0+2;$i<=36;$i++){ ?>
									<tr class="active cuotaclass">
										<?php if($i==1){ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Pago Inicial:</div>
											</td>																	
										<?php } else{ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Cuota <?= $i ?>:</div>
											</td>
										<?php } ?>

										<td>
											<div data-cuota="<?= $i ?>" class="cuota_div">
												<div class="col-sm-12">
													<input id="inicial_fecha_<?= $i ?>" name="fecha[]" value="" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" <?php if($i == 1){ ?> data-validation="required" <?php } ?> onblur="javascript:change_date(<?= $i ?>);"
													<?php if($i>1){ ?>
													disabled="disabled"
													<?php } ?>
											>
												</div>
											</div>
										</td>
										<td>
											<div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="0" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');">
											</div>
										</td>
										<td><div data-cuota="<?= $i ?>" class="cuota_div"></div></td>
									</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-center" colspan="2">Importe total <?= escape($moneda); ?>
									</th>
									<th class="text-nowrap text-right" data-totalcuota="">0.00</th>
									<th class="text-nowrap text-right"></th>
								</tr>
							</tfoot>
						</table>
						<br>
					</div>
					<div class="form-group">

						<div class="col-xs-12">

							<input type="text" id="nro_plan_pagos" name="nro_plan_pagos" value="1" class="translate" tabindex="-1" data-nro-pagos="" data-validation="required number" data-validation-allowing="range[1;36]" data-validation-error-msg="Debe existir como mínimo una cuota">

							<input type="text" id="monto_plan_pagos" name="monto_plan_pagos" value="0" class="translate" tabindex="-1" data-total-pagos="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="La suma de las cuotas debe ser igual al costo totas de la compra">
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
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Lista de ingresos</span></a>
					</div>
				</div>
				<hr>
				<?php if ($productos) { ?>
					
						<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
							<thead>
								<tr class="active">
									<th class="text-nowrap">CÓDIGO</th>
									<th class="text-nowrap">NOMBRE</th>
									<th class="text-nowrap">CATEGORÍA</th>
									<th class="text-nowrap">STOCK</th>
									<th class="text-nowrap">PRECIOS</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($productos as $nro => $producto) { ?>
								<?php $asignaciones = $db->select('*')
														->from('inv_asignaciones a')
														->join('inv_unidades u','a.unidad_id=u.id_unidad')
														->where('a.producto_id',$producto['id_producto'])
														->fetch();
								?>
								<tr>
									<td class="text-nowrap text-middle" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
									<td>
										<span data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></span>
									</td>
									<td class="text-nowrap text-middle">
										<?= escape($producto['categoria']); ?>
									</td>
									<td class="text-nowrap text-middle text-right" data-stock="<?= $producto['id_producto']; ?>">
										<?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?>
									</td>
									<td class="text-nowrap text-middle text-right" data-valor="<?= $producto['id_producto']; ?>">
									<?php foreach($asignaciones as $asignacion){ ?>
										<p style="margin-bottom: 3%;">
			                                <span data-nombre-unidad="<?= $asignacion['id_asignacion']; ?>"><?= escape($asignacion['unidad']); ?></span>
											<span data-tamanio-asignacion="<?= $asignacion['id_asignacion']; ?>">(<?=$asignacion['tamanio']; ?>)</span>:
											<span data-precio-asignacion="<?= $asignacion['id_asignacion']; ?>"><?= number_format($asignacion['costo_actual'],4,'.',''); ?></span> Bs.
											<button type="button" class="btn btn-primary btn-sm" data-comprar="<?= $producto['id_producto']; ?>" data-id-producto="<?= $producto['id_producto']; ?>" data-id-asignacion="<?= $asignacion['id_asignacion']; ?>" onclick="comprar(this);">
												<span class="glyphicon glyphicon-shopping-cart"></span>
											</button>
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
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
var formato = $('[data-formato]').attr('data-formato');
var $inicial_fecha = new Array();

$(function () {
	var table;

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

	for(i=1;i<36;i++){
		$inicial_fecha[i] = $('#inicial_fecha_'+i+'');
		$inicial_fecha[i].datetimepicker({
			format: formato
		});
	}

	$inicial_fecha[1].on('dp.change', function (e) {	$inicial_fecha[2].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[2].on('dp.change', function (e) {	$inicial_fecha[3].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[3].on('dp.change', function (e) {	$inicial_fecha[4].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[4].on('dp.change', function (e) {	$inicial_fecha[5].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[5].on('dp.change', function (e) {	$inicial_fecha[6].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[6].on('dp.change', function (e) {	$inicial_fecha[7].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[7].on('dp.change', function (e) {	$inicial_fecha[8].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[8].on('dp.change', function (e) {	$inicial_fecha[9].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[9].on('dp.change', function (e) {	$inicial_fecha[10].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[10].on('dp.change', function (e) {	$inicial_fecha[11].data('DateTimePicker').minDate(e.date);	});

	$inicial_fecha[11].on('dp.change', function (e) {	$inicial_fecha[12].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[12].on('dp.change', function (e) {	$inicial_fecha[13].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[13].on('dp.change', function (e) {	$inicial_fecha[14].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[14].on('dp.change', function (e) {	$inicial_fecha[15].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[15].on('dp.change', function (e) {	$inicial_fecha[16].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[16].on('dp.change', function (e) {	$inicial_fecha[17].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[17].on('dp.change', function (e) {	$inicial_fecha[18].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[18].on('dp.change', function (e) {	$inicial_fecha[19].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[19].on('dp.change', function (e) {	$inicial_fecha[20].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[20].on('dp.change', function (e) {	$inicial_fecha[21].data('DateTimePicker').minDate(e.date);	});

	$inicial_fecha[21].on('dp.change', function (e) {	$inicial_fecha[22].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[22].on('dp.change', function (e) {	$inicial_fecha[23].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[23].on('dp.change', function (e) {	$inicial_fecha[24].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[24].on('dp.change', function (e) {	$inicial_fecha[25].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[25].on('dp.change', function (e) {	$inicial_fecha[26].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[26].on('dp.change', function (e) {	$inicial_fecha[27].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[27].on('dp.change', function (e) {	$inicial_fecha[28].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[28].on('dp.change', function (e) {	$inicial_fecha[29].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[29].on('dp.change', function (e) {	$inicial_fecha[30].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[30].on('dp.change', function (e) {	$inicial_fecha[31].data('DateTimePicker').minDate(e.date);	});

	$inicial_fecha[31].on('dp.change', function (e) {	$inicial_fecha[32].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[32].on('dp.change', function (e) {	$inicial_fecha[33].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[33].on('dp.change', function (e) {	$inicial_fecha[34].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[34].on('dp.change', function (e) {	$inicial_fecha[35].data('DateTimePicker').minDate(e.date);	});
	$inicial_fecha[35].on('dp.change', function (e) {	$inicial_fecha[36].data('DateTimePicker').minDate(e.date);	});

	set_cuotas000();
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
		url: '?/ajustes/actualizar',
		data: {
			id_producto: id_producto,
			id_asignacion: id_asignacion,
			id_almacen: id_almacen
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var unidad = producto.unidad;
			var precio = parseFloat(producto.precio_actual).toFixed(2);
			var stock = parseInt(producto.stock);
			
			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-nombre-unidad='+id_asignacion+']').text(unidad);
			$busqueda.find('[data-precio-asignacion='+id_asignacion+']').text(precio);

			var $producto = $('[data-asignacion=' + producto.id_asignacion + ']');
			var $cantidad = $producto.find('[data-cantidad]');
			var $precio = $producto.find('[data-precio]');

			if ($producto.size()) {
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

function adicionar_producto(id_producto, id_asignacion) {

	var $compras 	= $('#compras tbody');
	var $producto 	= $compras.find('[data-producto=' + id_producto + ']');
	var $asignacion = $compras.find('[data-asignacion=' + id_asignacion + ']');
	var $cantidad 	= $producto.find('[data-cantidad='+id_asignacion+']');
	var numero 		= $compras.find('[data-producto]').size() + 1;
	var codigo 		= $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre 		= $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock 		= $.trim($('[data-stock=' + id_producto + ']').text());
	var valor 		= $.trim($('[data-valor=' + id_producto + ']').text());
	var unidad 		= $.trim($('[data-nombre-unidad='+id_asignacion+']').text());
	var precio 		= $.trim($('[data-precio-asignacion='+id_asignacion+']').text());
	var tamanio     = $.trim($('[data-tamanio-asignacion='+id_asignacion+']').text());

	var plantilla = '';
	var cantidad;

	if ($asignacion.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '" data-asignacion="'+id_asignacion+'">' +
						'<td class="text-nowrap align-middle"><b>' + numero + '</b></td>' +
						'<td class="text-nowrap align-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="'+id_asignacion+'">' + codigo + '</td>' +
						'<td class="align-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td class="align-middle"><input type="text" value="1" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="'+id_asignacion+'" data-validation="required number" data-validation-allowing="range[1;100000]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100000"  onkeyup="calcular_importe(' + id_asignacion + ')"></td>' +
						'<td class="align-middle">'+ unidad + (tamanio) +'</td>' +
						'<td class="align-middle"><input type="text" value="'+ precio +'" name="costos[]" class="form-control text-right" maxlength="10" autocomplete="off" data-precio="' + precio + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_asignacion + ')" onchange="setTwoNumberDecimal(this)"></td>' +
						'<td class="text-nowrap align-middle text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap align-middle text-center">' +
							'<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(' + id_asignacion + ')"><i class="glyphicon glyphicon-trash"></i></button>' +
						'</td>' +
					'</tr>';

		$compras.append(plantilla);

		$compras.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
			$(this).select();
		});

		$compras.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				
			}
		});
	}

	calcular_importe(id_asignacion);
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

	var $producto 	= $('[data-asignacion=' + id_asignacion + ']');
	var $cantidad 	= $producto.find('[data-cantidad]');
	var $precio 	= $producto.find('[data-precio]');
	var $descuento 	= $producto.find('[data-descuento]');
	var $importe 	= $producto.find('[data-importe]');
	var cantidad, precio, importe, fijo;
	var tamanio 	= $.trim($('[data-tamanio-asignacion='+id_asignacion+']').text());
	var $tamanio 	= $producto.find('[data-tamanio='+id_asignacion+']');
	fijo 			= $descuento.attr('data-descuento');
	fijo 			= ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
	cantidad 		= $.trim($cantidad.val());
	cantidad 		= ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio 			= $.trim($precio.val());
	precio 			= ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento 		= $.trim($descuento.val());
	descuento 		= ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	importe 		= cantidad * precio;
	importe 		= importe.toFixed(2);
	$importe.text(importe);
	$tamanio.val(cantidad*parseInt(tamanio));
	calcular_total();
}

function calcular_total() {

	var $ventas 	= $('#compras tbody');
	var $total 		= $('[data-subtotal]:first');
	var $importes 	= $ventas.find('[data-importe]');

	var importe, total = 0;
	$importes.each(function (i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;

	});

	$total.text(total.toFixed(1));
	$('[data-compras]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(1)).trigger('blur');
}

function comprar(elemento) {
	var $elemento 	= $(elemento), comprar;
	comprar 		= $elemento.attr('data-comprar');
	var id_asignacion = $elemento.attr('data-id-asignacion');
	
	adicionar_producto(comprar,id_asignacion);
}
function set_cuotas000() {
	var cantidad = $('#nro_cuentas').val();
	var $compras = $('#cuentasporpagar tbody');

	if(cantidad<1 && $("#forma_pago").val()==1)
		$("#nro_plan_pagos").val(1);
	else
		$("#nro_plan_pagos").val(cantidad);

	if(cantidad>36){
	    cantidad=36;
	    $('#nro_cuentas').val("36")
	}
	for(i=1;i<=cantidad;i++){
	    $('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});
	    $('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});
	    $('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});
	}
	for(i=parseInt(cantidad)+1;i<=36;i++){
	    $('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});
	    $('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});
	    $('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});
	}
	//set_cuotas_val();
	calcular_cuota(1000);
}

function set_cuotas() {

	

	var cantidad = $('#nro_cuentas').val();

	var $compras = $('#cuentasporpagar tbody');

	

	if(cantidad<1 && $("#forma_pago").val()==1)
		$("#nro_plan_pagos").val(1);
	else
		$("#nro_plan_pagos").val(cantidad);



	if(cantidad>36){

		cantidad=36;

		$('#nro_cuentas').val("36")

	}	

	for(i=1;i<=cantidad;i++){

		$('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});				

		$('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});				

		$('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});				

	}

	for(i=parseInt(cantidad)+1;i<=36;i++){

		$('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});				

		$('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});				

		$('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});

	}

	set_cuotas_val();

	calcular_cuota(1000);

}

function set_cuotas_val() {

	nro=$('#nro_cuentas').val();

	

	valor=parseFloat($('[data-total]:first').val());

	valor=valor/nro;

	for(i=1;i<=nro;i++){

		$('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(2));

	}		

}

function set_plan_pagos(){

	if($("#forma_pago").val()==1){

		$('#plan_de_pagos').css({'display':'none'});

	}

	else{

		$('#plan_de_pagos').css({'display':'block'});

	}

}

function calcular_cuota(x) {
	var cantidad = $('#nro_cuentas').val();
	var total = 0;

	for(i=1;i<=x && i<=cantidad;i++){
	    importe=$('[data-cuota=' + i + ']').children('.monto_cuota').val();
	    importe = parseFloat(importe);
	    total = total + importe;
	}

	valor = parseFloat($('[data-total]:first').val());
	if(cantidad>x){
	    valor=(valor-total)/(cantidad-x);
	}
	else{
	    valor=0;
	}

	for(i=(parseInt(x)+1);i<=cantidad;i++){
	    if(valor>=0){
	        $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor);
	        total = total + valor;
	    }
	    else{
	        $('[data-cuota=' + i + ']').children('.monto_cuota').val("0.00");
	    }
	}

	$('[data-totalcuota]').text(total.toFixed(2));
	valor = parseFloat($('#data-subporcentaje').val());
	if (valor == total.toFixed(2)  || $("#forma_pago").val()==1){
	    $('[data-total-pagos]:first').val(1);
	    $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","");
	} else {
	    $('[data-total-pagos]:first').val(0);
	    $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","La suma de las cuotas es diferente al costo total « "+total+" / "+valor+" »");
	}
}

function change_date(x){
	if($('#inicial_fecha_'+x).val()!=""){
		if(x<36){
			$('#inicial_fecha_'+(x+1)).removeAttr("disabled");
		}
	} else {
		for(i=x;i<=35;i++){
			$('#inicial_fecha_'+(i+1)).val("");
			$('#inicial_fecha_'+(i+1)).attr("disabled","disabled");
		}
	}
}

function setTwoNumberDecimal(obj) {
	obj.value = parseFloat(obj.value).toFixed(2);
}


</script>
<?php require_once show_template('footer-sidebar'); ?>