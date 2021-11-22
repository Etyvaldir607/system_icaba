<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);
$permiso_sucursal = in_array('permiso_sucursal', $permisos);

// Obtiene el id_proforma
$id_proforma = (isset($params[1])) ? $params[1] : 0;

if($permiso_sucursal){
	$id_sucursal = (isset($params[0])) ? $params[0] : 0;

	$id_almacen = 0;
	//en el caso de tener permisos pero no haber elegido almacen, se lo enviara al almacen principal

	if ($id_sucursal == 0) {
		header("Location: ?/electronicas/seleccionar_sucursal");
	} else {
		$almacen  =  $db->from('inv_sucursal')
						->where('id_sucursal',$id_sucursal)
						->fetch_first();

		$id_almacen = ($almacen) ? $almacen['almacen_id'] : 0;	
		$nombre_sucursal = ($almacen) ? $almacen['sucursal'] : '';	
	}
}else{
	$almacen  =  $db->from('inv_almacenes')
					->join('inv_sucursal s', 'almacen_id=id_almacen', 'inner')
					->join('sys_users u', 'sucursal_id=id_sucursal', 'inner')
					->where('id_user', $_user['id_user'])
					->fetch_first();

	$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
	$nombre_sucursal = ($almacen) ? $almacen['sucursal'] : '';	
	$id_sucursal = ($almacen) ? $almacen['id_sucursal'] : '';
}

// Obtiene la proforma
$proforma = $db->from('inv_proformas')->where('id_proforma', $id_proforma)->fetch_first();

// Verifica si existe la proforma
if (!$proforma) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los detalles
$detalles =  $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')
				->from('inv_proformas_detalles d')
				->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				->where('d.proforma_id', $id_proforma)
				->order_by('id_detalle asc')
				->fetch();

// Obtiene el almacen principal
//$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
//$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) {
	// Obtiene los productos
	$productos = $db->query("SELECT p.id_producto, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, 
								ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, 
								u.unidad, u.sigla, c.categoria 
							from inv_productos p 
							left join (
								select d.producto_id, sum(d.cantidad) as cantidad_ingresos 
								from inv_ingresos_detalles d 
								left join inv_ingresos i on i.id_ingreso = d.ingreso_id 
								where i.almacen_id = $id_almacen 
								group by d.producto_id
							) as e on e.producto_id = p.id_producto 
							left join (
								select d.producto_id, sum(d.cantidad) as cantidad_egresos 
								from inv_egresos_detalles d 
								left join inv_egresos e on e.id_egreso = d.egreso_id 
								where e.almacen_id = $id_almacen 
								group by d.producto_id
							) as s on s.producto_id = p.id_producto 
							left join inv_unidades u on u.id_unidad = p.unidad_id 
							left join inv_categorias c on c.id_categoria = p.categoria_id
							")->fetch();
} else {
	$productos = null;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Define la fecha de hoy
$hoy = date('Y-m-d');

// Obtiene la dosificacion del periodo actual
$dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();

// Obtiene los clientes
$clientes =  $db->select('nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')
				->from('inv_egresos')
				->group_by('nombre_cliente, nit_ci')
				->order_by('nombre_cliente asc, nit_ci asc')
				->fetch();

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
	<?php //if ($_terminal && $dosificacion && $almacen) { ?>
	<?php if ($dosificacion && $almacen){ ?>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la venta</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form id="formulario" class="form-horizontal">
					
					<div class="form-group">
	                    <label for="almacen" class="col-md-4 control-label">Sucursal:</label>
	                    <div class="col-sm-8">
	                        <input type="text" class="form-control" value="<?php echo $nombre_sucursal; ?>" disabled="disabled">
	                    </div>
	                    <input type="hidden" value="<?= $id_almacen; ?>" name="almacen_id" id="almacen"/>
	                    <input type="hidden" value="<?= $id_sucursal; ?>" name="sucursal_id" id="sucursal"/>
	                </div>
	                
					<div class="form-group">
						<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
						<div class="col-sm-8">
							<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
								<option value="">Buscar</option>
								<?php foreach ($clientes as $cliente) { ?>
								<option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']); ?>"><?= escape($cliente['nit_ci']) . ' &mdash; ' . escape($cliente['nombre_cliente']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $proforma['nit_ci']; ?>" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" readonly="true" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $proforma['nombre_cliente']; ?>" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" readonly="true" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Precio</th>
									<th class="text-nowrap">Decuento</th>
									<th class="text-nowrap">Importe</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</thead>
							<tbody>
								<?php $proforma_total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr data-producto="<?= $detalle['producto_id']; ?>" data-detalle="<?= $detalle['producto_id']; ?>">
									<?php $detalle_cantidad = intval(escape($detalle['cantidad'])); ?>
									<?php $detalle_precio = escape($detalle['precio']); ?>
									<?php $detalle_descuento = intval(escape($detalle['descuento'])); ?>
									<?php $detalle_importe = $detalle_cantidad * $detalle_precio; ?>
									<?php $proforma_total = $proforma_total + $detalle_importe; ?>
									<td class="text-nowrap"><?= $nro + 1; ?></td>
									<td class="text-nowrap"><input type="text" value="<?= $detalle['producto_id']; ?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><?= escape($detalle['codigo']); ?></td>
									<td><input type="text" value="<?= escape($detalle['nombre_factura']); ?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?= escape($detalle['nombre_factura']); ?></td>
									<td><input type="text" value="<?= $detalle_cantidad; ?>" name="cantidades[]" class="form-control input-xs text-right" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;0]" data-validation-error-msg="Debe ser un número positivo entre 1 y 0" onkeyup="calcular_importe(<?= $detalle['producto_id']; ?>)"></td>
									<td><input type="text" value="<?= $detalle_precio; ?>" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="<?= $detalle_precio; ?>" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(<?= $detalle['producto_id']; ?>)"></td>
									<td><input type="text" value="<?= $detalle_descuento; ?>" name="descuentos[]" class="form-control input-xs text-right" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100;100],negative" data-validation-error-msg="Debe ser un número entre -100 y 100" onkeyup="descontar_precio(<?= $detalle['producto_id']; ?>)"></td>
									<td class="text-nowrap text-right" data-importe=""><?= number_format($detalle_importe, 2, '.', ''); ?></td>
									<td class="text-nowrap text-center">
										<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(<?= $detalle['producto_id']; ?>)"><span class="glyphicon glyphicon-remove"></span></button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal=""><?= number_format($proforma_total, 2, '.', ''); ?></th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $id_almacen; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="<?= $proforma['nro_registros']; ?>" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 100">
							<input type="text" name="monto_total" value="<?= $proforma['monto_total']; ?>" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>





					<div class="col-md-12">
                        <div class="form-group">
                            <label for="labelforma" class="col-sm-4 control-label">Tipo de Pago:</label>
                            <div class="col-sm-8">
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="tipo_pago" value="Efectivo" onchange="setPago();">
                                            <span>Efectivo</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="tipo_pago" value="Tarjeta" onchange="setPago();">
                                            <span>Tarjeta</span>
                                        </label>
                                    </div>                              
                                </div>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="tipo_pago" value="Deposito" onchange="setPago();">
                                            <span>Deposito</span>
                                        </label>
                                    </div>                              
                                </div>
                                <div class="clearfix"></div>
                                <input type="text" value="0" id="data-tipo-pago" class="translate" data-validation="required number" data-validation-allowing="range[1;2],float" data-validation-error-msg="No eligio el modo de pago">
                            </div>
                        </div>
                    </div>
                    
                    <br>
                    <br>
                    <br>

                    <div class="form-group">
                        <label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>
                        <div class="col-md-8">
                            <select name="forma_pago" id="forma_pago" class="form-control" data-validation="required number" onchange="set_plan_pagos()">
                                <option value="1">Pago Completo</option>
                                <option value="2">Plan de Pagos</option>                                
                            </select>
                        </div>
                    </div>
                    <div id="plan_de_pagos" style="display:none">
                        <div class="form-group">
                            <label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>
                            <div class="col-md-8">
                                <input type="text" value="1" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">
                            </div>
                        </div>

                        <table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
                            <thead>
                                <tr class="active">
                                    <th class="text-nowrap text-center col-xs-4">Detalle</th>
                                    <th class="text-nowrap text-center col-xs-4">Fecha de Pago</th>
                                    <th class="text-nowrap text-center col-xs-4">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i=1;$i<=36;$i++){ ?>
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

                                        <td><div data-cuota="<?= $i ?>" class="cuota_div"><div class="col-sm-12">
                                            <input id="inicial_fecha_<?= $i ?>" name="fecha[]" value="" class="form-control" autocomplete="off" <?php if($i==1){ ?> data-validation="required" <?php } ?> data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" onblur="javascript:change_date(<?= $i ?>);" 
                                            <?php if($i>1){ ?>
                                                disabled="disabled"
                                            <?php } ?>
                                            >
                                        </div></div></td>

                                        <td><div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="0" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');"></div></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th class="text-nowrap text-center" colspan="2">Importe total <?= escape($moneda); ?></th>
                                    <th class="text-nowrap text-right" data-totalcuota="">0.00</th>
                                </tr>
                            </tfoot>                            
                        </table>
                        <br>
                    </div>
                                                        
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" id="nro_plan_pagos" name="nro_plan_pagos" value="1" class="translate" tabindex="-1" data-nro-pagos="1" data-validation="required number" data-validation-allowing="range[1;360]" data-validation-error-msg="Debe existir como mínimo una cuota">
                            <input type="text" id="monto_plan_pagos" name="monto_plan_pagos" value="0" class="translate" tabindex="-1" data-total-pagos="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="La suma de las cuotas debe ser igual al costo total de la venta">
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
		<div class="panel panel-default" data-servidor="<?= ip_local . 'servidor/factura.php'; ?>">
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
						<i class="glyphicon glyphicon-phone"></i>
						<strong>Terminal: </strong>
						<span><?= escape($_terminal['terminal']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-print"></i>
						<strong>Impresora: </strong>
						<span><?= escape($_terminal['impresora']); ?></span>
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
				<?php if ($permiso_mostrar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/electronicas/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Ventas personales</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Imagen</th>
							<th class="text-nowrap">Código</th>
							<th class="text-nowrap">Nombre</th>
							<th class="text-nowrap">Categoría</th>
							<th class="text-nowrap">Stock</th>
							<th class="text-nowrap">Precio</th>
							<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($productos as $nro => $producto) { ?>
						<tr>
							<td class="text-nowrap"><img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" width="75" height="75"></td>
							<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
							<td>
								<span><?= escape($producto['nombre']); ?></span>
								<span class="hidden" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></span>
							</td>
							<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
							<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
							<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['precio_actual']); ?></td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-xs btn-primary" data-vender="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-share-alt"></span></button>
								<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
	<?php } else { ?>
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="glyphicon glyphicon-home"></i> Ventas electrónicas</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<strong>Advertencia!</strong>
					<p>Usted no puede realizar esta operación, verifique que todo este en orden tomando en cuenta las siguientes sugerencias:</p>
					<ul>
						<?php if (!$_terminal) { ?>
						<li>Usted no está en una terminal registrada y autorizada para la venta de productos, debe ponerse en contacto con el administrador del sistema.</li>
						<?php } ?>
						<?php if (!$dosificacion) { ?>
						<li>No existe una dosificación registrada o el periodo de dosificación anterior ya caducó, debe registrar una nueva dosificación para que el proceso de venta se lleve a cabo.</li>
						<?php } ?>
						<?php if (!$almacen) { ?>
						<li>No existe el almacén principal de la cual se puedan tomar los productos necesarios para la venta, debe fijar un almacén principal.</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<h2 class="btn-danger position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una factura" data-placement="right"><i class="glyphicon glyphicon-qrcode display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>

<script>
var formato = $('[data-formato]').attr('data-formato');
var $inicial_fecha=new Array();

$(function () {
	var table;
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
	var $nombre_cliente = $('#nombre_cliente');
	var $formulario = $('#formulario');

	$('[data-vender]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-vender')));
	});

	$('[data-actualizar]').on('click', function () {
		var id_producto = $.trim($(this).attr('data-actualizar'));
		
		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/electronicas/actualizar',
			data: {
				id_producto: id_producto
			}
		}).done(function (producto) {
			if (producto) {
				var precio = parseFloat(producto.precio).toFixed(2);
				var stock = parseInt(producto.stock); 
				var cell;

				cell = table.cell($('[data-valor=' + producto.id_producto + ']'));
				cell.data(precio);
				cell = table.cell($('[data-stock=' + producto.id_producto + ']'));
				cell.data(stock);
				table.draw();

				var $producto = $('[data-producto=' + producto.id_producto + ']');
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
					title: '<strong>Actualización satisfactoria!</strong>',
					message: '<div>El stock y el precio del producto se actualizaron correctamente.</div>'
				}, {
					type: 'success'
				});
			} else {
				$.notify({
					title: '<strong>Advertencia!</strong>',
					message: '<div>Ocurrió un problema, no existe almacén principal.</div>'
				}, {
					type: 'danger'
				});
			}
		}).fail(function () {
			$.notify({
				title: '<strong>Advertencia!</strong>',
				message: '<div>Ocurrió un problema y no se pudo actualizar el stock ni el precio del producto.</div>'
			}, {
				type: 'danger'
			});
		}).always(function () {
			$('#loader').fadeOut(100);
		});
	});

	$('[data-detalle]').each(function (i) {
		var id_producto = $.trim($(this).attr('data-detalle'));
		var stock = $.trim($('[data-stock=' + id_producto + ']').text());
		var $producto = $('[data-producto=' + id_producto + ']');
		var $cantidad = $producto.find('[data-cantidad]');

		if ($producto.size()) {
			$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
			$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
		}
	});

	table = $('#productos').DataTable({
		info: false,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});

	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

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
			$nit_ci.prop('readonly', true);
			$nombre_cliente.prop('readonly', true);
			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
		} else {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			if (es_nit(valor[0])) {
				$nit_ci.val(valor[0]);
				$nombre_cliente.val('').focus();
			} else {
				$nombre_cliente.val(valor[0]);
				$nit_ci.val('').focus();
			}
		}
	});

	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function () {
			bootbox.confirm('<strong>Usted esta por imprimir una factura</strong><p>Esta seguro de continuar con el proceso?</p>', function (respuesta) {
				if (respuesta) {
					guardar_factura();
				}
			});
		}
	});

	$formulario.on('submit', function (e) {
		e.preventDefault();
	});

	$formulario.on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		calcular_total();
	});

	for(i=1;i<36;i++){
        $inicial_fecha[i] = $('#inicial_fecha_'+i+'');
        $inicial_fecha[i].datetimepicker({
            format: formato
        });
    }

    $inicial_fecha[1].on('dp.change', function (e) {    $inicial_fecha[2].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[2].on('dp.change', function (e) {    $inicial_fecha[3].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[3].on('dp.change', function (e) {    $inicial_fecha[4].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[4].on('dp.change', function (e) {    $inicial_fecha[5].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[5].on('dp.change', function (e) {    $inicial_fecha[6].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[6].on('dp.change', function (e) {    $inicial_fecha[7].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[7].on('dp.change', function (e) {    $inicial_fecha[8].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[8].on('dp.change', function (e) {    $inicial_fecha[9].data('DateTimePicker').minDate(e.date);   });
    $inicial_fecha[9].on('dp.change', function (e) {    $inicial_fecha[10].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[10].on('dp.change', function (e) {   $inicial_fecha[11].data('DateTimePicker').minDate(e.date);  });
        
    $inicial_fecha[11].on('dp.change', function (e) {   $inicial_fecha[12].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[12].on('dp.change', function (e) {   $inicial_fecha[13].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[13].on('dp.change', function (e) {   $inicial_fecha[14].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[14].on('dp.change', function (e) {   $inicial_fecha[15].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[15].on('dp.change', function (e) {   $inicial_fecha[16].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[16].on('dp.change', function (e) {   $inicial_fecha[17].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[17].on('dp.change', function (e) {   $inicial_fecha[18].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[18].on('dp.change', function (e) {   $inicial_fecha[19].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[19].on('dp.change', function (e) {   $inicial_fecha[20].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[20].on('dp.change', function (e) {   $inicial_fecha[21].data('DateTimePicker').minDate(e.date);  });

    $inicial_fecha[21].on('dp.change', function (e) {   $inicial_fecha[22].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[22].on('dp.change', function (e) {   $inicial_fecha[23].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[23].on('dp.change', function (e) {   $inicial_fecha[24].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[24].on('dp.change', function (e) {   $inicial_fecha[25].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[25].on('dp.change', function (e) {   $inicial_fecha[26].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[26].on('dp.change', function (e) {   $inicial_fecha[27].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[27].on('dp.change', function (e) {   $inicial_fecha[28].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[28].on('dp.change', function (e) {   $inicial_fecha[29].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[29].on('dp.change', function (e) {   $inicial_fecha[30].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[30].on('dp.change', function (e) {   $inicial_fecha[31].data('DateTimePicker').minDate(e.date);  });

    $inicial_fecha[31].on('dp.change', function (e) {   $inicial_fecha[32].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[32].on('dp.change', function (e) {   $inicial_fecha[33].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[33].on('dp.change', function (e) {   $inicial_fecha[34].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[34].on('dp.change', function (e) {   $inicial_fecha[35].data('DateTimePicker').minDate(e.date);  });
    $inicial_fecha[35].on('dp.change', function (e) {   $inicial_fecha[36].data('DateTimePicker').minDate(e.date);  });

    set_cuotas();

});

function es_nit (texto) {
	var numeros = '0123456789';
	for(i = 0; i < texto.length; i++){
		if (numeros.indexOf(texto.charAt(i), 0) != -1){
			return true;
		}
	}
	return false;
}

function adicionar_producto(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
	var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;
	var nomina;

	nomina = nombre.replace(/"/g, "''");

	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr data-producto="' + id_producto + '">' +
						'<td class="text-nowrap">' + numero + '</td>' +
						'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td><input type="text" value="' + nomina + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						'<td><input type="text" value="' + valor + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + valor + '" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100;100],negative" data-validation-error-msg="Debe ser un número entre -100 y 100" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-center">' +
							'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
						'</td>' +
					'</tr>';

		$ventas.append(plantilla);

		$ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
			$(this).select();
		});

		$ventas.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				bootbox.confirm('<strong>Usted esta por imprimir una factura</strong><p>Esta seguro de continuar con el proceso?</p>', function (respuesta) {
					if (respuesta) {
						guardar_factura();
					}
				});
			}
		});
	}

	calcular_importe(id_producto);
}

function eliminar_producto(id_producto) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_producto + ']').remove();
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

function descontar_precio(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $precio = $producto.find('[data-precio]');
	var $descuento = $producto.find('[data-descuento]');
	var precio, descuento;

	precio = $.trim($precio.attr('data-precio'));
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseInt(descuento) : 0;
	precio = precio - (precio * descuento / 100);
	$precio.val(precio.toFixed(2));

	calcular_importe(id_producto);
}

function calcular_importe(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-precio]');
	var $descuento = $producto.find('[data-descuento]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, precio, importe, fijo;

	fijo = $descuento.attr('data-descuento');
	fijo = ($.isNumeric(fijo)) ? parseInt(fijo) : 0;
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseInt(descuento) : 0;
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

function guardar_factura() {
	var data = $('#formulario').serialize();

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/electronicas/guardar_facturar',
		data: data
	}).done(function (venta) {
		if (venta) {
			$.notify({
				title: '<strong>Creación satisfactoria!</strong>',
				message: '<div>La venta fue registrada correctamente.</div>'
			}, {
				type: 'success'
			});
			
			imprimir_factura(venta);
		} else {
			$('#loader').fadeOut(100);
			$.notify({
				title: '<strong>Error!</strong>',
				message: '<div>Ocurrió un problema al obtener la dosificación y guardar los datos de la venta, verifique se guardó parcialmente.</div>'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			title: '<strong>Error!</strong>',
			message: '<div>Ocurrió un problema al obtener la dosificación y guardar los datos de la venta, verifique se guardó parcialmente.</div>'
		}, {
			type: 'danger'
		});
	});
}

function imprimir_factura(venta) {
	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));

	$('#formulario').find(':reset').trigger('click');

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: servidor,
		data: venta
	}).done(function (respuesta) {
		$('#loader').fadeOut(100);
		switch (respuesta.estado) {
			case 'success':
				window.location = '?/electronicas/crear';
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
		$('#loader').fadeOut(100);
		$.notify({
			title: '<strong>Error!</strong>',
			message: '<div>Ocurrió un problema en el envío de la información, vuelva a intentarlo nuevamente y si persiste el problema contactese con el administrador.</div>'
		}, {
			type: 'danger'
		});
	});
}


function set_cuotas() {
    var cantidad = $('#nro_cuentas').val();
    var $compras = $('#cuentasporpagar tbody');

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
    nro     = $('#nro_cuentas').val();
    valor   = parseFloat($('[data-subtotal]:first').text());
    valor   = valor/nro;
    for(i = 1;i<=nro;i++){
        $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(2));
    }
}

function set_plan_pagos() {
    if($("#forma_pago").val()==1){
        $('#plan_de_pagos').css({'display':'none'});
        if( $('#nro_cuentas').val()<=0 ){
            $('#nro_cuentas').val('1');
            calcular_cuota(1000);
            $("#nro_plan_pagos").val('1');
        }
    } else{
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
    if(nro>x){
        valor=(valor-total)/(nro-x);
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
    valor = parseFloat($('[data-subtotal]:first').text());
    if (valor == total.toFixed(2)){
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
function setPago(){
    $('#data-tipo-pago').val(2);
}

</script>
<?php require_once show_template('footer-sidebar'); ?>