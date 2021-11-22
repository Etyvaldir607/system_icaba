<?php
function calcularStock($id_producto,$id_almacen){
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
   WHERE e.almacen_id = $id_almacen
   GROUP BY d.producto_id) E ON E.producto_id = p.id_producto
WHERE p.id_producto = $id_producto";
 
    $resultado = $db->query($query)->fetch_first();
    $stock = $resultado != null ? $resultado['cantidad_ingresos']-$resultado['cantidad_egresos']: 0;
    return $stock;
}

$id_egreso = (isset($params[0])) ? $params[0] : 0;

$egreso = $db->from('inv_egresos')
					->join('inv_sucursal','sucursal_id = id_sucursal','left')
					->where('id_egreso', $id_egreso)
					->fetch_first();

$id_almacen = $egreso['almacen_id'];
$almacen 	= $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

$detalles = $db->from('inv_egresos e')
				->join('inv_egresos_detalles d','d.egreso_id = e.id_egreso','left')
				->join('inv_productos p','d.producto_id = p.id_producto','left')
				->join('inv_asignaciones a','a.id_asignacion = d.asignacion_id','left')
				->join('inv_unidades u','a.unidad_id = u.id_unidad','left')
				->join('inv_categorias c','p.categoria_id = c.id_categoria','left')
				->where(['d.egreso_id'=>$id_egreso,'e.almacen_id'=>$id_almacen])
				->order_by('d.id_detalle')
				->fetch();


// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
$permiso_sucursal = in_array('seleccionar_sucursal', $permisos);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene el rango de fechas
$gestion 		= date('Y');
$gestion_base 	= date('Y-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

$clientes = $db->query("SELECT id_egreso, nombre_cliente, nit_ci, telefono, direccion, GROUP_CONCAT(b.estado SEPARATOR '|') AS pago FROM inv_egresos c
	LEFT JOIN inv_pagos a ON c.id_egreso = a.movimiento_id
	LEFT JOIN inv_pagos_detalles b ON a.id_pago = b.pago_id
	GROUP BY c.nombre_cliente, c.nit_ci
	ORDER BY c.nombre_cliente ASC, c.nit_ci ASC "
					)->fetch();

// Define el limite de filas
$limite_longitud = 200;

// Define el limite monetario
$limite_monetario = 10000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

?>
<?php require_once show_template('header-sidebar'); ?>
<style>
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
.width-none {
	width: 10px;
}
.table-display > .thead > .tr,
.table-display > .tbody > .tr,
.table-display > .tfoot > .tr {
	margin-bottom: 15px;
}
.table-display > .thead > .tr > .th,
.table-display > .tbody > .tr > .th,
.table-display > .tfoot > .tr > .th {
	font-weight: bold;
}
@media (min-width: 768px) {
	.table-display {
		display: table;
	}
	.table-display > .thead,
	.table-display > .tbody,
	.table-display > .tfoot {
		display: table-row-group;
	}
	.table-display > .thead > .tr,
	.table-display > .tbody > .tr,
	.table-display > .tfoot > .tr {
		display: table-row;
	}
	.table-display > .thead > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tbody > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .tfoot > .tr > .td {
		display: table-cell;
	}
	.table-display > .tbody > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tfoot > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .thead > .tr > .th {
		padding-bottom: 15px;
		vertical-align: top;
	}
	.table-display > .tbody > .tr > .td:first-child,
	.table-display > .tbody > .tr > .th:first-child,
	.table-display > .tfoot > .tr > .td:first-child,
	.table-display > .tfoot > .tr > .th:first-child,
	.table-display > .thead > .tr > .td:first-child,
	.table-display > .thead > .tr > .th:first-child {
		padding-right: 15px;
	}

	.tabla_filtrar > .medida{
		height:500px;
		overflow:scroll;
	}

}

@media (max-width: 1920px) {
	#medida{
		height:500px;
		overflow:scroll;
	}
}

#cuentasporpagar td{
	padding:0; height: 0; border-width: 0px;
}
.cuota_div{
	height:0; overflow: hidden;
}
</style>
<div class="row">
	<?php if ($id_almacen!=0) { ?>
	<div class="col-md-6">
		<div class="panel panel-warning" data-formato="<?= strtoupper($formato_textual); ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Nota de remisión</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-warning">Nota de remisión</h2>
				<hr>
				<form id="formulario" class="form-horizontal" data-locked="false">
					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Sucursal:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $egreso['sucursal']; ?>" disabled="disabled">							
						</div>
						<input type="hidden" value="<?php echo $egreso['sucursal_id']; ?>" name="sucursal_id" id="sucursal"/>
					</div>					
					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $almacen['almacen']; ?>" disabled="disabled">
							<input type="hidden" value="<?= $id_egreso; ?>" name="id_egreso">
						</div>
						<input type="hidden" value="<?php echo $almacen['id_almacen']; ?>" name="almacen_id" id="almacen"/>
					</div>					
					<div class="form-group">
						<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $egreso['nit_ci'];?>" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $egreso['nombre_cliente'];?>" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="telefono" class="col-sm-4 control-label">Telefono:</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $egreso['telefono'];?>" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max100" data-validation-optional="true" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="direccion" class="col-sm-4 control-label">Dirección:</label>
						<div class="col-sm-8">
							<input type="text" value="<?= $egreso['direccion'];?>" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max200" data-validation-optional="true" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="observacion" class="col-sm-4 control-label">Observación:</label>
						<div class="col-sm-8">
							<textarea name="observacion" id="observacion" class="form-control text-uppercase" rows="2" autocomplete="off" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true" readonly><?= $egreso['observacion']; ?></textarea>
						</div>
					</div>

					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
									<th class="text-nowrap text-center">CANTIDAD</th>
									<th class="text-nowrap text-center">UNIDAD DE MEDIDA</th>
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
								<?php $stock 	= calcularStock($detalle['producto_id'],$id_almacen); ?>
								<tr class="active" data-producto="<?= $detalle['producto_id']; ?>" data-asignacion="<?= $detalle['asignacion_id']; ?>">
                    				<td class="text-nowrap align-middle">
										<input type="text" value="<?= $detalle['id_detalle']; ?>" name="detalles[]" class="translate" tabindex="-1" data-validation="required">
                    					<b><?= ($key+1); ?></b>
                    					<input type="hidden" value="0" class="detallesEliminar" name="detallesEliminar[]">
                    				</td>
                    				<td class="text-nowrap align-middle"><input type="text" value="<?= $detalle['producto_id']; ?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="<?= $detalle['asignacion_id']; ?>"><?= $detalle['codigo']; ?></td>
                    				<td class="align-middle"><input type="text" value="<?= $detalle['nombre']; ?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?= $detalle['nombre']; ?></td>

                    				<td class="text-middle"><input type="text" value="<?= $detalle['cantidad']; ?>" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="<?= $detalle['asignacion_id']; ?>" data-validation="required number" data-validation-allowing="range[1;<?= $stock; ?>]" data-validation-error-msg="Debe ser un número positivo entre 1 y <?= $stock; ?>" onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>);">

                        			<!-- <input type="text" data-tamanio="<?= $detalle['asignacion_id']; ?>" class="translate" data-validation="required number"></td> -->

                    				<td class="align-middle"><input type="text" value="<?= $detalle['unidad']; ?>" class="form-control text-right" autocomplete="off" data-tamanio-stock="<?= $detalle['tamanio'];?>" data-unidad="<?= $detalle['unidad']; ?>" readonly/></td>

                    				<td class="align-middle">
                    					<input type="text" value="<?= $precio; ?>" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="<?= $precio; ?>" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(<?= $detalle['asignacion_id']; ?>)" onchange="setTwoNumberDecimal(this)">
                    				</td>
    								<td class="text-nowrap align-middle text-right" data-importe=""><?= number_format($importe, 2, '.', ''); ?></td>
                    				<td class="text-nowrap align-middle text-center">
                    					<button type="button" class="btn btn-success" tabindex="-1" onclick="eliminar_antiguo_producto(<?= $detalle['asignacion_id']; ?>)">Eliminar</button>
                    				</td>
                				</tr>	
								<?php endforeach ?>
							</tbody>
							<tfoot>
								<tr class="active" id="fila_monto_total">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal=""><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
								<tr class="active" id="fila_sin_descuento">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL SIN DESCUENTO<?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal-sin=""><?= number_format($total, 2, '.', ''); ?></th>
									<th class="text-nowrap text-center"></th>
								</tr>
								<tr class="active" id="fila_descuento" style="display:none;">
									<th class="text-nowrap text-right" colspan="3">DESCUENTO EN %</th>
									<th class="text-nowrap text-right">
										<input type="text" class="form-control text-right" id="valor_descuento" name="valor_descuento" maxlength="10" autocomplete="off" data-validation="required number" data-validation-allowing="float,range[0;10],negative" data-validation-error-msg="Debe ser un número entre 0 y 10" onkeyup="calcular_descuento_total();"  value="0">	
									</th>
									<th class="text-nowrap text-right" colspan="2">IMPORTE TOTAL DESCUENTO APLICADO<?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subporcentaje="">0.00</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="nro_registros" value="<?= count($detalles);?>" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;<?= $limite_longitud; ?>]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a <?= $limite_longitud; ?>">
							<input type="text" name="monto_total" value="<?= $total; ?>" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
							<input type="text" name="monto_porcentaje" value="<?= $total; ?>" class="translate" tabindex="-1" data-porcentaje="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-warning">Guardar</button>
							<button type="reset" class="btn btn-default">Restablecer</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="panel panel-warning" data-servidor="<?= ip_local . name_project . '/nota.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<strong>Información sobre la transacción</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-warning">Información sobre la transacción</h2>
				<hr>
				<div class="table-display">
					<div class="tbody">
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-home"></span>
								<span>Casa matriz:</span>
							</div>
							<div class="td"><?= escape($_institution['nombre']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-qrcode"></span>
								<span>NIT:</span>
							</div>
							<div class="td"><?= escape($_institution['nit']); ?></div>
						</div>
						<?php if ($_terminal) : ?>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-phone"></span>
								<span>Terminal:</span>
							</div>
							<div class="td"><?= escape($_terminal['terminal']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-print"></span>
								<span>Impresora:</span>
							</div>
							<div class="td"><?= escape($_terminal['impresora']); ?></div>
						</div>
						<?php endif ?>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-user"></span>
								<span>Empleado:</span>
							</div>
							<div class="td"><?= ($_user['persona_id'] == 0) ? 'No asignado' : escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></div>
						</div>
					</div>
				</div>
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
				<h2 class="lead">Búsqueda de productos</h2>
				<hr>
				<?php if ($permiso_mostrar) : ?>
				<p class="text-right">
					<a href="?/notas/mostrar" class="btn btn-warning">Mis notas de remision</a>
				</p>
				<?php endif ?>
				<form method="post" action="?/notas/buscar/<?php echo $id_almacen; ?>" id="form_buscar_0" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código" autofocus="autofocus">
						<span class="glyphicon glyphicon-barcode form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<form method="post" action="?/notas/buscar/<?php echo $id_almacen; ?>" id="form_buscar_1" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código, producto o categoría">
						<span class="glyphicon glyphicon-search form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<div id="contenido_filtrar"></div>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="col-xs-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Nota de remisión</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<p>Usted no puede realizar la nota de remisión, verifique que la siguiente información sea correcta:</p>
					<ul>
						<li>El almacén principal no esta definido, ingrese al apartado de "almacenes" y designe a uno de los almacenes como principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<!-- Plantillas filtrar inicio -->
<div id="tabla_filtrar" class="hidden">
	<div class="table-responsive medida" id="medida">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap align-middle text-center width-none">Imagen</th>
					<th class="text-nowrap align-middle text-center">Código</th>
					<th class="text-nowrap align-middle text-center">Producto</th>
					<!-- <th class="text-nowrap align-middle text-center">Categoría</th> -->
					<th class="text-nowrap align-middle text-center">Stock</th>
					<th class="text-nowrap align-middle text-center">Unidades</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<table class="hidden">
	<tbody id="fila_filtrar" data-negativo="<?= imgs; ?>/" data-positivo="<?= files; ?>/productos/">
		<tr>
			<td class="text-nowrap align-middle text-center width-none">
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" data-modal-content="holh" width="75" height="75">
			</td>
			<td class="text-nowrap align-middle" data-codigo=""></td>
			<td class="align-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-nowrap align-middle text-right" data-stock=""></td>
			<td class="text-nowrap align-middle text-right" data-valor=""></td>
		</tr>
	</tbody>
</table>
<div id="mensaje_filtrar" class="hidden">
	<div class="alert alert-danger">No se encontraron resultados</div>
</div>
<!-- Plantillas filtrar fin -->

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
                <hr/>
                <b>Precio tentativo:  </b><span class="modal-content2" data-modal-content2="" ></span><br/>
                <b>Ubicación:  </b><span class="modal-content3" data-modal-content3="" ></span>
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>

<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>

<script>
var formato = $('[data-formato]').attr('data-formato');
var $inicial_fecha=new Array();

$(function () {
	var $cliente 		= $('#cliente');
	var $nit_ci 		= $('#nit_ci');
	var $telefono 		= $("#telefono");
	var $direccion 		= $("#direccion");
	var $nombre_cliente = $('#nombre_cliente');
	var $formulario 	= $('#formulario');

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
			$telefono.prop('readonly', true);
            $direccion.prop('readonly', true);
			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
			$telefono.val(valor[2]);
            $direccion.val(valor[3]);
            
			
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

	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function (form) {
			guardar_nota(form);
		}
	});

	var $modal_cantidad = $('#modal_cantidad');
	$modal_cantidad.find('[data-cancelar-cantidad]').on('click', function () {
		$modal_cantidad.modal('hide');
	});

	var $fecha_entrega = $('#fecha_entrega');
	
	$fecha_entrega.datetimepicker({
		format : "YYYY/MM/DD h:m"
	});

	$formulario.on('submit', function (e) {
		e.preventDefault();
	});

	$formulario.on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		$telefono.prop('readonly', false);
		$direccion.prop('readonly', false);
		$('#nro_cuentas').val("1")
		set_plan_pagos();
		set_cuotas();
		calcular_total();
		$("#plan_de_pagos").css({"display":"none"});
	});

	var blup = new buzz.sound('<?= media; ?>/blup.mp3');

	var $form_filtrar = $('#form_buscar_0, #form_buscar_1');
	$contenido_filtrar = $('#contenido_filtrar');
	$tabla_filtrar = $('#tabla_filtrar');
	$fila_filtrar = $('#fila_filtrar');
	$modal_mostrar = $('#modal_mostrar');
	$loader_mostrar = $('#loader_mostrar');
	$mensaje_filtrar = $('#mensaje_filtrar');

	$form_filtrar.on('submit', function (e) {
		e.preventDefault();
		var $this, url, busqueda;
		$this = $(this);
		url = $this.attr('action');
		busqueda = $this.find(':text').val();
		$this.find(':text').attr('value', '');
		$this.find(':text').val('');
		if ($.trim(busqueda) != '') {
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: url,
				data: {
					busqueda: busqueda
				}
			}).done(function (productos) {
				if (productos.length) {
					console.log(productos);
					var $ultimo;
					$contenido_filtrar.html($tabla_filtrar.html());
					for (var i in productos) {

						console.log(i);
					
						var asignacion = productos[i].id_asignacion;
						var unidad = productos[i].unidad_id;
						var descrip = productos[i].unidad_descripcion;
						var bonificacion = productos[i].bonificacion;
						var tamanio = productos[i].tamanio;
						var listas = "";
						if(asignacion == null){
							id_asignacion = "";
						}else{
							var id_asignacion =asignacion.split("|");
							var id_unidad = unidad.split("|");
							var id_tamanio = tamanio.split("|");
							var descripcion = descrip.split("&");				
							
						}
						
						for (var j= 0 ; j < id_asignacion.length; j++){
							var des_unidad = descripcion[j].split(":");

                            <?php if($_user['incremento']!=0){ ?>
                                des_unidad[1] = parseFloat(des_unidad[1]) + parseFloat(des_unidad[1]*<?= $_user['incremento']/100; ?>);
                            <?php } ?>
							listas += '<p style="margin-bottom: 3%;"><span data-nombre-unidad="'+ id_asignacion[j] +'">'+ des_unidad[0] +' </span>(';
							listas += '<span data-tamanio-asignacion="'+ id_asignacion[j] +'">'+ id_tamanio[j] +'</span>):';
							listas += '<span data-precio-asignacion="'+ id_asignacion[j] +'">'+ parseFloat(des_unidad[1]).toFixed(3) +'</span> Bs. ';
							listas += '<button type="button" class="btn btn-sm btn-warning" data-vender="'+ productos[i].id_producto+'" data-id-producto="'+ productos[i].id_producto +'" data-id-asignacion="'+ id_asignacion[j] +'" onclick="vender(this); "><span class="glyphicon glyphicon-shopping-cart"></span></button></p>';
						}
						listas += '<span data-bonificacion="'+ productos[i].id_producto+'" style="display:none;">'+bonificacion+'</span>';
						
						var asignacion = productos[i].unidad_descripcion;
						asignacion = '*'+productos[i].unidad+':'+productos[i].precio_actual+'\n'+'*'+asignacion;

						//if((parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos)) >0) {
							productos[i].imagen = (productos[i].imagen == '') ? $fila_filtrar.attr('data-negativo') + 'image.jpg' : $fila_filtrar.attr('data-positivo') + productos[i].imagen;
							productos[i].codigo = productos[i].codigo;
							$contenido_filtrar.find('tbody').append($fila_filtrar.html());
							$contenido_filtrar.find('tbody tr:last').attr('data-busqueda', productos[i].id_producto);
							$ultimo = $contenido_filtrar.find('tbody tr:last').children();
                            $ultimo.eq(0).find('img').attr('src', productos[i].imagen);
                        $ultimo.eq(0).find('img').attr('data-modal-content2', productos[i].rango);
                        $ultimo.eq(0).find('img').attr('data-modal-content3', productos[i].ubicacion);

							$ultimo.eq(1).attr('data-codigo', productos[i].id_producto);
							$ultimo.eq(1).text(productos[i].codigo);
							$ultimo.eq(2).find('em').text(productos[i].nombre);
							$ultimo.eq(2).find('span').attr('data-nombre', productos[i].id_producto);
							$ultimo.eq(2).find('span').text(productos[i].nombre_factura);
							//$ultimo.eq(3).text(productos[i].categoria);
							$ultimo.eq(3).attr('data-stock', productos[i].id_producto);
							$ultimo.eq(3).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
							$ultimo.eq(4).attr('data-valor', productos[i].id_producto);
							$ultimo.eq(4).html(listas);
					}
					if (productos.length == 1) {
					    //$contenido_filtrar.find('table tbody tr button').trigger('click');
					}
					$.notify({
						message: 'La operación fue ejecutada con éxito, se encontraron ' + productos.length + ' resultados.'
					}, {
						type: 'success'
					});
					blup.stop().play();
				} else {
					$contenido_filtrar.html($mensaje_filtrar.html());
				}
			}).fail(function () {
				$contenido_filtrar.html($mensaje_filtrar.html());
				$.notify({
					message: 'La operación fue interrumpida por un fallo.'
				}, {
					type: 'danger'
				});
				blup.stop().play();
			});
		} else {
			$contenido_filtrar.html($mensaje_filtrar.html());
		}
	}).trigger('submit');

	var $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar'), size, title, image, mayor, ubicacion;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$loader_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
        image = $(e.relatedTarget).attr('src');
        mayor = $(e.relatedTarget).attr('data-modal-content2');
        ubicacion = $(e.relatedTarget).attr('data-modal-content3');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
        $modal_mostrar.find('.modal-title').text(title);
        $modal_mostrar.find('.modal-content2').text(mayor);
        $modal_mostrar.find('.modal-content3').text(ubicacion);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$loader_mostrar.hide();
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

	set_cuotas();
});

function es_nit(texto) {
	var numeros = '0123456789';
	for(i = 0; i < texto.length; i++){
		if (numeros.indexOf(texto.charAt(i), 0) != -1){
			return true;
		}
	}
	return false;
}

function vender(elemento) {
	var $elemento = $(elemento), vender;
	vender = $elemento.attr('data-vender');
	var id_asignacion = $elemento.attr('data-id-asignacion');
	adicionar_producto(vender,id_asignacion);
}
function adicionar_producto(vender,id_asignacion) {

	var id_producto 	= vender;
	var id_asignacion 	= id_asignacion;
	var $ventas 		= $('#ventas tbody');
	var $producto 		= $ventas.find('[data-producto=' + id_producto + ']');
	var $asignacion 	= $ventas.find('[data-asignacion=' + id_asignacion + ']');
	var $cantidad 		= $producto.find('[data-cantidad='+id_asignacion+']');
	var numero 			= $ventas.find('[data-producto]').size() + 1;
	var codigo 			= $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre 			= $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock 			= $.trim($('[data-stock=' + id_producto + ']').text());
	var valor 			= $.trim($('[data-valor=' + id_producto + ']').text());
	var unidad 			= $.trim($('[data-nombre-unidad='+id_asignacion+']').text());
	var precio 			= $.trim($('[data-precio-asignacion='+id_asignacion+']').text());
	var tamanio      	= $.trim($('[data-tamanio-asignacion='+id_asignacion+']').text());
	var plantilla 		= '';
	var bonificacion 	= $.trim($('[data-bonificacion=' + id_producto + ']').text());
	var cantidad 		= 1;

	if($cantidad.val() == ""){
	    cantidad = 1;
	}

	if ($asignacion.size()) {
	    cantidad2 = $.trim($cantidad.val());
	    cantidad2 = ($.isNumeric(cantidad2)) ? parseInt(cantidad2)+1 : 0;
	    $cantidad.val(cantidad2);
	} else {
	    plantilla = '<tr class="active" data-producto="' + id_producto + '" data-asignacion="'+id_asignacion+'">' +
                    '<td class="text-nowrap align-middle"><b>' + numero + '</b><input type="hidden" value="0" class="detallesEliminar" name="detallesEliminar[]"></td>' +

                    '<td class="text-nowrap align-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><input type="hidden" name="asignacion[]" value="'+id_asignacion+'">' + codigo + '</td>' +

                    '<td class="align-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +

                    '<td class="text-middle"><input type="text" value="'+ cantidad +'" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="'+id_asignacion+'" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_asignacion + '); validarStock('+id_producto+','+id_asignacion+','+stock+');">'+

                        '<input type="text" data-tamanio='+id_asignacion+' class="translate" data-validation="required number" data-validation-allowing="range[1;'+stock+']" data-validation-error-msg="Stock insuficiente"></td>' +

                    '<td class="align-middle"><input type="text" value="' + unidad + '" class="form-control text-right" autocomplete="off" data-tamanio-stock="'+tamanio+'" data-unidad="' + unidad + '"/> </td>';

                    plantilla +='<td class="align-middle"><input type="text" value="' + precio + '" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="' + precio + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_asignacion + ')" onchange="setTwoNumberDecimal(this)"></td>';

    plantilla +='<td class="text-nowrap align-middle text-right" data-importe="">0.00</td>' +
                    '<td class="text-nowrap align-middle text-center">' +
                        '<button type="button" class="btn btn-success" tabindex="-1" onclick="eliminar_producto(' + id_asignacion + ')">Eliminar</button>' +
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
        onSuccess: function (form) {
            guardar_nota(form);
        }
    });
}

$("#modal_cantidad").modal("hide");
validarStock(id_producto,id_asignacion,stock);
calcular_importe(id_asignacion);
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
	        cantidad_actual = ($(el).find('td:eq(3) [data-cantidad]').val())*($(el).find('td:eq(4) [data-tamanio-stock]').data('tamanio-stock'));

	    }
	});

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
function eliminar_antiguo_producto(id_asignacion) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
	    if(result){
	        $('[data-asignacion=' + id_asignacion + ']').children("td").children(".detallesEliminar").val("1");
	        $('[data-asignacion=' + id_asignacion + ']').css({"display":"none"});
	        //renumerar_productos();
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
	descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	precio = precio - (precio * descuento / 100);
	$precio.val(precio.toFixed(2));

	calcular_importe(id_producto);
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

	fijo 		= $descuento.attr('data-descuento');
	fijo 		= ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
	cantidad 	= $.trim($cantidad.val());
	cantidad 	= ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio 		= $.trim($precio.val());
	precio 		= ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento 	= $.trim($descuento.val());
	descuento  	= ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	importe 	= cantidad * precio;
	importe 	= importe.toFixed(2);
	$importe.text(importe);

	calcular_total();
}

function calcular_total() {
	var $ventas 	= $('#ventas tbody');
	var $total 		= $('[data-subtotal]:first');
	var $total_sin 	= $('[data-subtotal-sin]:first');
	var $porcentaje = $('[data-subporcentaje]:first');
	var $importes	= $ventas.find('[data-importe]');
	var importe, total = 0;

	$importes.each(function (i) {
	    dellete=$(this).parent("tr").children("td").children(".detallesEliminar").val();
		if(dellete==0){
			importe = $.trim($(this).text());
		    importe = parseFloat(importe);
		    total = total + importe;
		}
	});

	$total.text(total.toFixed(2));
	$total_sin.text(total.toFixed(2));
	$porcentaje.text(total.toFixed(2));
	$('[data-ventas]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
	$('[data-porcentaje]:first').val(total.toFixed(2)).trigger('blur');
}

function calcular_descuento_total(){
	var valor_descuento = $("#valor_descuento").val();
	var total 			= parseFloat($('[data-total]:first').val());
	var $porcentaje 	= $('[data-subporcentaje]:first');

	if (valor_descuento == "" || valor_descuento >= 100) {
	    descuento_total = total;
	} else {
	    descuento = (total * parseFloat(valor_descuento)) / 100;
	    descuento_total = total - descuento;
	}

	$porcentaje.text(descuento_total.toFixed(2));
	$('[data-porcentaje]:first').val(descuento_total.toFixed(2)).trigger('blur');

	set_cuotas_val();
	calcular_cuota(1000);
}

function guardar_nota(form) {
	$form = $(form);
	if ($form.data('locked') != undefined && !$form.data('locked')) {
		var data = $form.serialize();
		$('#loader').fadeIn(100);
		
		$.ajax({
		    type: 'post',
		    dataType: 'json',
		    url: '?/notas/guardar',
		    data: data,
		    beforeSend: function() {
		    	$form.data('locked', true);
		 	}
		}).done(function (venta) {
		    if (venta) {
		        $.notify({
		            message: 'La venta con nota de remisión fue realizada satisfactoriamente.'
		        }, {
		            type: 'success'
		        });
		        imprimir_factura(venta);
		        $form.data('locked', false);
		    } else {
		        $('#loader').fadeOut(100);
		        $.notify({
		            message: 'Ocurrió un problema en el proceso, no se puedo obtener los datos de la venta, verifique si la se guardó parcialmente.'
		        }, {
		            type: 'danger'
		        });
		    	$form.data('locked', false);
		    }
		
		}).fail(function () {
		    $('#loader').fadeOut(100);
		    $.notify({
		        message: 'Ocurrió un problema en el proceso, no se pudo guardar los datos de la venta, verifique su conexion a internet.'
		    }, {
		        type: 'danger'
		    });
		    $form.data('locked', false);
		});
	}
}

function imprimir_factura(venta) {
	window.open('?/notas/imprimir/' + venta, '_blank');
	window.location.reload();
}
/*
function imprimir_factura(venta) {
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
    $('#loader').fadeOut(100);
    $.notify({
        message: 'Ocurrió un problema durante el proceso, reinicie la terminal para dar solución al problema y si el problema persiste contactese con el con los desarrolladores.'
    }, {
        type: 'danger'
    });
}).always(function () {
    $('#formulario').trigger('reset');
    $('#form_buscar_0').trigger('submit');
});
}*/

function cargar_cantidad(){
	var id_producto 	= $("#id_producto_cantidad").val();
	var id_asignacion 	= $("#id_asignacion_cantidad").val();
	var cantidad 		= $("#cant_cantidad").val();
}

function actualizar(elemento) {
	var $elemento 	= $(elemento), id_asignacion,id_producto;
	id_producto 	= $elemento.attr('data-actualizar-producto');
	id_asignacion 	= $elemento.attr('data-actualizar');

	$('#loader').fadeIn(100);

	$.ajax({
	    type: 'post',
	    dataType: 'json',
	    url: '?/notas/actualizar',
	    data: {
	        id_producto: id_producto,
	        id_asignacion: id_asignacion
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
	            //$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
	            //$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
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
	nro 	= $('#nro_cuentas').val();
	valor 	= parseFloat($('[data-subporcentaje]:first').text());
	valor 	= valor/nro;
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
	valor = parseFloat($('[data-subporcentaje]:first').text());
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

function setTwoNumberDecimal(obj) {
	obj.value = parseFloat(obj.value).toFixed(2);
}	
</script>
<?php require_once show_template('footer-sidebar'); ?>
