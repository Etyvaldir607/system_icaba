<?php

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene los clientes
$clientes = $db->query("select * from ((select nombre_cliente, nit_ci from inv_egresos) union (select nombre_cliente, nit_ci from inv_proformas)) c group by c.nombre_cliente, c.nit_ci order by c.nombre_cliente asc, c.nit_ci asc")->fetch();

// Define el limite de filas
$limite_longitud = 200;

// Define el limite monetario
$limite_monetario = 10000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

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

@media (max-width: 1024px) {
	#medida{
		height:500px;
		overflow:scroll;
	}
}

</style>
<div class="row">
	<?php if ($almacen) { ?>
	<div class="col-md-6">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Orden de compra</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-warning">Orden de compra</h2>
				<hr>
				<form id="formulario" class="form-horizontal">
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
							<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
						</div>
					</div>
					<div class="form-group">
						<label for="telefono" class="col-sm-4 control-label">Teléfono:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max100" data-validation-optional="true">
						</div>
					</div>
					<div class="form-group">
						<label for="direccion" class="col-sm-4 control-label">Dirección:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max200" data-validation-optional="true">
						</div>
					</div>
					<!--div class="form-group">
						<label for="descuento" class="col-sm-4 control-label">Descuento:</label>
						<div class="col-sm-8">
							<input type="text" value="0.00" name="descuento" id="descuento" class="form-control text-uppercase" autocomplete="off" data-validation="required number" data-validation-allowing="float">
						</div>
					</div-->
					<div class="form-group">
						<label for="observacion" class="col-sm-4 control-label">Observación:</label>
						<div class="col-sm-8">
							<textarea name="observacion" id="observacion" class="form-control text-uppercase" rows="2" autocomplete="off" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
						</div>
					</div>

					<div class="form-group">
						<label for="fecha_entrega" class="col-sm-4 control-label">Fecha de Entrega:</label>
						<div class="col-sm-8">
							<input type="text" name="fecha_entrega" id="fecha_entrega" class="form-control text-uppercase" autocomplete="off" data-validation-allowing="float">
						</div>
					</div>

					<div class="form-group">
						<!--label for="active" class="col-sm-4 control-label">Estado:</label-->
						<div class="col-sm-offset-2 col-sm-3">
							<div class="radio">
								<label>
									<input type="radio" name="activo" id="active" value="F" checked>
									<span>Con Factura</span>
								</label>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="radio">
								<label>
									<input type="radio" name="activo" value="S">
									<span>Orden de Compra</span>
								</label>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="radio">
								<label>
									<input type="radio" name="activo" value="B">
									<span>Bonificación</span>
								</label>
							</div>
						</div>
					</div>

					<div class="table-responsive margin-none" id="medida">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
									<th class="text-nowrap text-center">UNIDAD DE MEDIDA</th>
									<th class="text-nowrap text-center">CANTIDAD</th>
									<th class="text-nowrap text-center">PRECIO</th>
									<th class="text-nowrap text-center hidden">DESCUENTO</th>
									<th class="text-nowrap text-center">IMPORTE</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="active" id="fila_monto_total">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
								<tr class="active" id="fila_descuento" style="display:none;">
									<th class="text-nowrap text-right" colspan="3">DESCUENTO EN %</th>
									<th class="text-nowrap text-right"><input type="text" class="form-control text-right" id="valor_descuento" name="valor_descuento" maxlength="10" autocomplete="off" data-validation="required number" data-validation-allowing="float,range[0;10],negative" data-validation-error-msg="Debe ser un número entre 0 y 10" onkeyup="calcular_descuento_total();"  value="0">	</th>
									<th class="text-nowrap text-right" colspan="2">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subporcentaje="">0.00</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
							<tbody></tbody>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;<?= $limite_longitud; ?>]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a <?= $limite_longitud; ?>">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
							<input type="text" name="monto_porcentaje" value="0" class="translate" tabindex="-1" data-porcentaje="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
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
					<a href="?/notas/mostrar" class="btn btn-warning">Mis órdenes de compra</a>
				</p>
				<?php endif ?>
				<form method="post" action="?/notas/buscar" id="form_buscar_0" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código" autofocus="autofocus">
						<span class="glyphicon glyphicon-barcode form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<form method="post" action="?/notas/buscar" id="form_buscar_1" class="margin-bottom" autocomplete="off">
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
					<strong>Proforma</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<p>Usted no puede realizar órdenes de compra, verifique que la siguiente información sea correcta:</p>
					<ul>
						<li>El almacén principal no esta definido, ingrese al apartado de "almacenes" y designe a uno de los almacenes como principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<!--h2 class="btn-warning position-left-bottom display-table btn-circle margin-all display-table hidden-xs" data-toggle="tooltip" data-title="Esto es una orden de compra" data-placement="right">
	<span class="glyphicon glyphicon-star display-cell"></span>
</h2-->

<!-- Plantillas filtrar inicio -->
<div id="tabla_filtrar" class="hidden">
	<div class="table-responsive medida" id="medida">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap align-middle text-center width-none">Imagen</th>
					<th class="text-nowrap align-middle text-center">Código</th>
					<th class="text-nowrap align-middle text-center">Producto</th>
					<th class="text-nowrap align-middle text-center">Categoría</th>
					<th class="text-nowrap align-middle text-center">Unidades</th>
					<th class="text-nowrap align-middle text-center">Stock</th>
					<th class="text-nowrap align-middle text-center">Precio</th>
					<th class="text-nowrap align-middle text-center width-none">Acciones</th>
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
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
			</td>
			<td class="text-nowrap align-middle" data-codigo=""></td>
			<td class="align-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-nowrap align-middle"></td>
			<td class="text-nowrap align-middle text-right" data-unidades=""></td>
			<td class="text-nowrap align-middle text-right" data-stock=""></td>
			<td class="text-nowrap align-middle text-right" data-valor=""></td>
			<td class="text-nowrap align-middle text-center width-none">
				<button type="button" class="btn btn-warning" data-vender="" onclick="vender(this)">Vender</button>
				<button type="button" class="btn btn-default" data-actualizar="" onclick="actualizar(this)">Actualizar</button>
			</td>
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
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>

<script>
$(function () {
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
	var $nombre_cliente = $('#nombre_cliente');
	var $formulario = $('#formulario');

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
			guardar_nota();
		}
	});

	//se da formato a la fecha de entrega
	var $fecha_entrega = $('#fecha_entrega');
	//$inicial_fecha.mask(mascara).datetimepicker({
	$fecha_entrega.datetimepicker({
		//format: formato
		//format: 'yy/mm/dd', //Se especifica como deseamos representarla
		format : "YYYY/MM/DD h:m"
	});

	$formulario.on('submit', function (e) {
		e.preventDefault();
	});

	$formulario.on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		calcular_total();
	}).trigger('reset');

	var blup = new buzz.sound('<?= media; ?>/blup.mp3');

	var $form_filtrar = $('#form_buscar_0, #form_buscar_1'), $contenido_filtrar = $('#contenido_filtrar'), $tabla_filtrar = $('#tabla_filtrar'), $fila_filtrar = $('#fila_filtrar'), $mensaje_filtrar = $('#mensaje_filtrar'), $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar');

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
					//console.log(productos);
					var $ultimo;
					$contenido_filtrar.html($tabla_filtrar.html());
					for (var i in productos) {
						productos[i].imagen = (productos[i].imagen == '') ? $fila_filtrar.attr('data-negativo') + 'image.jpg' : $fila_filtrar.attr('data-positivo') + productos[i].imagen;
						productos[i].codigo = productos[i].codigo;
						$contenido_filtrar.find('tbody').append($fila_filtrar.html());
						$contenido_filtrar.find('tbody tr:last').attr('data-busqueda', productos[i].id_producto);
						$ultimo = $contenido_filtrar.find('tbody tr:last').children();
						$ultimo.eq(0).find('img').attr('src', productos[i].imagen);
						$ultimo.eq(1).attr('data-codigo', productos[i].id_producto);
						$ultimo.eq(1).attr('data-codigo-asignacion', '');
						$ultimo.eq(1).text(productos[i].codigo);
						$ultimo.eq(2).find('em').text(productos[i].nombre);
						$ultimo.eq(2).find('span').attr('data-nombre', productos[i].id_producto);
						$ultimo.eq(2).find('span').text(productos[i].nombre_factura);
						$ultimo.eq(3).text(productos[i].categoria);

						var asignacion = productos[i].id_asignacion;
						var unidad = productos[i].unidad_id;
						var descrip = productos[i].unidad_descripcion;
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
						
						for (var ii= 0 ; ii < id_asignacion.length; ii++){
						var des_unidad = descripcion[ii].split(":");
						//listas += '<span> '+ des_unidad[0] +' <b>Precio</b> '+ des_unidad[1] +' </span>';
						//listas += '<button class=" btn btn-warning btn-sm" data-id-producto="'+ productos[i].id_producto +'" data-id-asignacion="'+ id_asignacion[ii] +'" onclick="vender2(this)">Vender</button><br><br>';

						listas += '<span data-nombre-unidad="'+ id_asignacion[ii] +'">'+ des_unidad[0] +'</span> <b>Precio: </b>';
						listas += '<span data-precio-asignacion="'+ id_asignacion[ii] +'">'+ des_unidad[1] +' </span>';
						listas += '<span><b>Unidades: </b></span>';
						listas += '<span data-tamanio-asignacion="'+ id_asignacion[ii] +'">'+ id_tamanio[ii] +' </span>';
						listas += '<button class=" btn btn-warning btn-sm" data-id-producto="'+ productos[i].id_producto +'" data-id-asignacion="'+ id_asignacion[ii] +'" onclick="vender2(this)">Vender</button><br><br>';
						}
						
						var asignacion = productos[i].unidad_descripcion;
						asignacion = '*'+productos[i].unidad+':'+productos[i].precio_actual+'\n'+'*'+asignacion;
                        $ultimo.eq(4).attr('data-unidades', productos[i].id_producto);
						$ultimo.eq(4).html(listas);
						$ultimo.eq(5).attr('data-stock', productos[i].id_producto);
						$ultimo.eq(5).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
						$ultimo.eq(6).attr('data-valor', productos[i].id_producto);
						$ultimo.eq(6).text(productos[i].precio_actual);
						$ultimo.eq(7).find(':button:first').attr('data-vender', productos[i].id_producto);
						$ultimo.eq(7).find(':button:last').attr('data-actualizar', productos[i].id_producto);
					}
					if (productos.length == 1) {
					    $contenido_filtrar.find('table tbody tr button').trigger('click');
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

	var $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar'), size, title, image;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$loader_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
		image = $(e.relatedTarget).attr('src');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
		$modal_mostrar.find('.modal-title').text(title);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$loader_mostrar.hide();
	});
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

//function adicionar_producto(id_producto) {
function adicionar_producto(id_producto, id_asignacion) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $asignacion = $ventas.find('[data-asignacion=' + id_asignacion + ']');
	var $cantidad = $asignacion.find('[data-cantidad]');
	//var $asignacion = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;

	//datos de la asignacion
	var precio_asignacion = $.trim($('[data-precio-asignacion=' + id_asignacion + ']').text());
	var tamanio_asignacion = $.trim($('[data-tamanio-asignacion=' + id_asignacion + ']').text());
	var codigo_asignacion = $.trim($('[data-codigo-asignacion=' + id_producto + ']').text());
	var unidad_medida = $.trim($('[data-nombre-unidad=' + id_asignacion + ']').text());

	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
	var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;
	console.log(tamanio_asignacion);
	if ($producto.size() && $asignacion.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? parseFloat(cantidad) + parseFloat(tamanio_asignacion) : cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		/*plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
						'<td class="text-nowrap align-middle"><b>' + numero + '</b></td>' +
						'<td class="text-nowrap align-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td class="align-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td class="align-middle"><input type="text" value="1" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						'<td class="align-middle"><input type="text" value="' + valor + '" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="' + valor + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						//'<td class="align-middle"><input type="text" value="0" name="descuentos[]" class="form-control text-right" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100.00;100.00],negative" data-validation-error-msg="Debe ser un número entre -100.00 y 100.00" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						'<td class="text-nowrap align-middle text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap align-middle text-center">' +
							'<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')">Eliminar</button>' +
						'</td>' +
					'</tr>';*/

		plantilla += '<tr class="active" data-producto="' + id_producto + '" data-asignacion="' + id_asignacion + '">';
		plantilla += '<td class="text-nowrap align-middle"><b>' + numero + '</b></td>';
		plantilla += '<td class="text-nowrap align-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td class="align-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>';
		plantilla += '<td class="text-nowrap align-middle"><input input type="text" value="' + id_asignacion + '" name="asignaciones[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">'+ unidad_medida +'</td>';

		plantilla += '<td class="align-middle"><input type="text" value="'+ tamanio_asignacion +'" name="cantidades[]" class="form-control text-right" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + tamanio_asignacion + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + tamanio_asignacion + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
		plantilla += '<td class="align-middle"><input type="text" value="' + precio_asignacion + '" name="precios[]" class="form-control text-right" autocomplete="off" data-precio="' + precio_asignacion + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
		plantilla += '<td class="text-nowrap align-middle text-right" data-importe="">0.00</td>';
		plantilla += '<td class="text-nowrap align-middle text-center">' +
							'<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')">Eliminar</button>';
						'</td>';

		plantilla += '</tr>';


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
				guardar_nota();
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
	descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
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
	fijo = ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	importe = cantidad * precio;
	importe = importe.toFixed(2);
	$importe.text(importe);

	calcular_total();
}

function calcular_total() {
	var $ventas = $('#ventas tbody');
	var $total = $('[data-subtotal]:first');
	var $porcentaje = $('[data-subporcentaje]:first');
	var $importes = $ventas.find('[data-importe]');
	var importe, total = 0;

	$importes.each(function (i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});

	$total.text(total.toFixed(2));
	$porcentaje.text(total.toFixed(2));
	$('[data-ventas]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
	$('[data-porcentaje]:first').val(total.toFixed(2)).trigger('blur');
	if(total >= 200){
		$("#fila_descuento").show();
		$("#fila_monto_total").hide();
	}else{
		$("#fila_descuento").hide();
		$("#fila_monto_total").show();
	}
}

function calcular_descuento_total(){
		var valor_descuento = $("#valor_descuento").val();
		var total = parseInt($('[data-total]:first').val());
		var $porcentaje = $('[data-subporcentaje]:first');
		if(valor_descuento == ""){
			descuento_total = total;
		}else{
			descuento = (total * parseInt(valor_descuento)) / 100;
			descuento_total = total - descuento;
		}
		$porcentaje.text(descuento_total.toFixed(2));
		$('[data-porcentaje]:first').val(descuento_total.toFixed(2)).trigger('blur');
}

function guardar_nota() {
	var data = $('#formulario').serialize();

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/notas/guardar',
		data: data
	}).done(function (venta) {
		if (venta) {
			$.notify({
				message: 'La orden de compra fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			imprimir_nota(venta);
		} else {
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la orden de compra, verifique si la se guardó parcialmente.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la orden de compra, verifique si la se guardó parcialmente.'
		}, {
			type: 'danger'
		});
	});
}

function imprimir_nota(nota) {
	$.open('?/notas/imprimir/' + nota, true);
	window.location.reload();
}

function vender2(elemento) {
	var $elemento = $(elemento), vender;
	id_producto = $elemento.attr('data-id-producto');
	id_asignacion = $elemento.attr('data-id-asignacion');
	adicionar_producto(id_producto, id_asignacion);
}

function vender(elemento) {
	var $elemento = $(elemento), vender;
	vender = $elemento.attr('data-vender');
	adicionar_producto(vender);
}

function actualizar(elemento) {
	var $elemento = $(elemento), actualizar;
	actualizar = $elemento.attr('data-actualizar');

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/notas/actualizar',
		data: {
			id_producto: actualizar
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var precio = parseFloat(producto.precio).toFixed(2);
			var stock = parseInt(producto.stock);

			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-valor]').text(precio);

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

			/*$.notify({
				message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
			}, {
				type: 'success'
			});*/
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
</script>
<?php require_once show_template('footer-sidebar'); ?>
