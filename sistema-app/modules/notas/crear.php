<?php
// Obtiene los formatos para la fecha

$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);
// Obtiene los permisos
$permisos 		= explode(',', permits);
// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
$id_almacen 	= (isset($params[0])) ? $params[0] : 0;
// Verifica si existe el almacen

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// @etysoft  obtiene el id del usuario actual
$id_user_current = $_user['id_user'];
// @etysoft  obtiene el almacen asignado al usuario actual
$almacen = $db->query("
	SELECT
		a.id_almacen,
		a.almacen,
		s.id_sucursal,
		s.sucursal
	FROM inv_almacen_empleados ae
	JOIN inv_almacenes a ON a.id_almacen = ae.almacen_id
	JOIN inv_almacen_sucursales asu ON asu.almacen_id = a.id_almacen 
	JOIN inv_sucursal s ON s.id_sucursal = asu.sucursal_id
	JOIN sys_users u  ON u.persona_id = ae.empleado_id
	WHERE u.id_user = $id_user_current
	AND a.id_almacen = $id_almacen
	
")->fetch_first();

// @etysoft si no encuentra el almacen asignado redirecciona a seleccionar sucursal
if(!$almacen){
	redirect('?/notas/seleccionar_sucursal');
}

// Obtiene los clientes
$clientes = $db->query("
	select c.id_cliente, c.nombre_cliente, c.nit_ci, c.telefono
		from  inv_clientes c 
		order by c.nombre_cliente asc
")->fetch();

//obtiene las categorias de los clientes
$categoria_clientes = $db->query("
	select * 
		from inv_categorias_cliente
		order by categoria asc
")->fetch();


//var_dump($clientes);
//si no encuentra el almacen asignado lo vuelve a redireccionar a la seleccion del sucursal

if (!$almacen) {
	redirect('?/ingresos/seleccionar_sucursal');
}



$empleados = $db->from("sys_empleados")->fetch();

?>

<?php require_once show_template('header-sidebar-yottabm'); ?>


<!--@yotabm ********************************************************************** stylos para mensajes de validacion stock backend  ******************************************************************************************************************************* -->

<style>
	.space_div {
		display: flex;
		justify-content: left;
	}

	.first {
		width: 26%;
	}

	.last {
		width: auto;
		margin-left: 3px;
	}


	#container>div {
		display: flex;
	}

	#container>div>div {
		width: 100%;
	}

	#space-between {
		justify-content: space-between;
	}
</style>


<style>
	.has-error .checkbox,
	.has-error .checkbox-inline,
	.has-error .control-label,
	.has-error .help-block,
	.has-error .radio,
	.has-error .radio-inline,
	.has-error.checkbox label,
	.has-error.checkbox-inline label,
	.has-error.radio label,
	.has-error.radio-inline label {
		color: #a94442;
		font-weight: 500;
		font-size: 12px;
	}

	.text-wrap {
		white-space: normal;
	}


	.content-space-between {
		display: flex;
		justify-content: space-between;
	}

	.td-bold {
		font-weight: bold
	}

	.td-right {
		text-align: right;
	}

	.td-center {
		text-align: center;
		vertical-align: middle;
	}

	.btn-group,
	.btn-group-vertical {
		margin-bottom: 15px;
		float: right;
	}





	div.dataTables_scrollHeadInner {
		box-sizing: content-box !important;
		width: auto !important;
		/*padding-right: 17px !important;*/
	}

	div.dataTables_scrollHead table.table-bordered {
		width: 100% !important;
		margin-left: 0px !important;
	}

	div.dataTables_wrapper div.dataTables_filter {
		float: left;
	}

	@media screen and (max-width: 767px) {

		div.dataTables_wrapper div.dataTables_length,
		div.dataTables_wrapper div.dataTables_filter,
		div.dataTables_wrapper div.dataTables_info,
		div.dataTables_wrapper div.dataTables_paginate {
			text-align: initial !important;
		}
	}


	.modal {
		text-align: center;
		padding: 0 !important;
	}

	.modal:before {
		content: '';
		display: inline-block;
		height: 100%;
		vertical-align: middle;
		margin-right: -4px;
	}

	.modal-dialog {
		display: inline-block;
		text-align: left;
		vertical-align: middle;
	}
</style>



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



	#cuentasporpagar td {
		padding: 0;
		height: 0;
		border-width: 0px;
	}

	.cuota_div {
		height: 0;
		overflow: hidden;
	}
</style>



<div class="row">

	<div class="col-md-6">

		<div class="panel panel-warning" data-formato="<?= strtoupper($formato_textual); ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Nota de remisión</strong>
				</h3>
			</div>
			<div class="panel-body">

				<form id="formulario" class="form-horizontal">

<!--@yotabm ********************************************************************** fromulariodesde sucursal hasta observacion  ******************************************************************************************************************************* -->

					<input type="hidden" name="usuario" value="<?= $_user['persona_id']; ?>">

					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Sucursal:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= $almacen['sucursal']; ?>" disabled="disabled">
						</div>
						<input type="hidden" value="<?= $almacen['id_sucursal']; ?>" name="sucursal_id" id="sucursal" />

					</div>
					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= $almacen['almacen']; ?>" disabled="disabled">
						</div>
						<input type="hidden" value="<?= $almacen['id_almacen']; ?>" name="almacen_id" id="id_almacen" />
					</div>

					<div class="form-group">
						<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
						<div class="col-sm-8">
							<input type="hidden" value="0" id="cliente_id" name="cliente_id">
							<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation-optional="true">
								<option value="">Buscar</option>
								<?php foreach ($clientes as $cliente) { ?>
									<option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']) . '|' . escape($cliente['id_cliente']) . '|' . escape($cliente['telefono']); ?>"> <?= escape($cliente['nit_ci']) . ' &mdash; ' . escape($cliente['nombre_cliente']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="categoria_cliente_id" class="col-sm-4 control-label">Categoría:</label>
						<div class="col-sm-8">
							<select name="categoria_cliente_id" id="categoria_cliente_id" class="form-control text-uppercase" data-validation="required">
								<option value="1">SIN CATEGORIA</option>
								<?php foreach ($categoria_clientes as $categoria_clientes) { ?>
									<?php if ($categoria_clientes['id_categoria_cliente'] != 1) { ?>
										<option value="<?= escape($categoria_clientes['id_categoria_cliente']); ?>"><?= escape($categoria_clientes['categoria']); ?></option>
									<?php } ?>
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
						<label for="telefono" class="col-sm-4 control-label">Telefono:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max100" data-validation-optional="true" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="direccion" class="col-sm-4 control-label">Dirección:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation="letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max200" data-validation-optional="true">
						</div>
					</div>
					<div class="form-group">
						<label for="observacion" class="col-sm-4 control-label">Observación:</label>
						<div class="col-sm-8">
							<textarea name="observacion" id="observacion" class="form-control text-uppercase" rows="2" autocomplete="off" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
						</div>
					</div>


<!--@yotabm ********************************************************************** div que renderiza los mensajes de validadcion del backen de stock ******************************************************************************************************************************* -->

					<!--@yotabm espaciado para mostrar warning de stock insuficiente -->
					<div id="div_stock_insuficiente"></div>



<!--@yotabm ********************************************************************** cesta de productos  ******************************************************************************************************************************* -->


					<div class="table-responsive margin-none" style="margin-bottom: 0;">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none" style="margin-bottom: 5px;">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									<th class="text-nowrap text-left">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap" style="width: 100px;">Cantidad</th>
									<th class="text-nowrap">Unidad</th>
									<th class="text-nowrap" width="20%">Precio</th>
									<th class="text-nowrap" style="width: 100px;">Importe <?= escape($moneda); ?></th>
								</tr>
							</thead>

							<tbody id="tbody_cesta">
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="7">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="" id="importe_total">0.00</th>
								</tr>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">
										Descuento %
									</th>
									<th class="text-nowrap text-right">
										<input type="text" class="form-control input-xs text-right" id="descuento" name="descuento" value="0" maxlength="10" autocomplete="off" data-validation="number" data-validation-allowing="float,range[0;99],negative" data-validation-error-msg="Porcentaje entre 1 99" onchange="isPorcentaje(this)" onkeyup="calcular_descuento_total();">
									</th>
									<th class="text-nowrap text-right" id="importe_total_descuento">0.00
									</th>

								</tr>
							</tfoot>

						</table>
					</div>
					<div class="form-group" style="padding: 0; margin-bottom: 0;">
						<div class="col-xs-12" style="margin-bottom: 0;">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;250]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 250 productos">

							<input type="text" name="monto_total_confirmation" id="monto_total_confirmation" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">

							<input type="text" name="total_descuento_confirmation" id="total_descuento_confirmation" value="0" class="translate" data-total-descuento="" autocomplete="off">

						</div>
					</div>



<!--@yotabm ********************************************************************** checkbox de tipos de pagos ******************************************************************************************************************************* -->

					<div class="col-md-12" style="padding: 0; margin-bottom: 0;">
						<div class="form-group">
							<label for="labelforma" class="col-sm-4 control-label">Tipo de Pago:</label>
							<div class="col-sm-8">
								<div class="col-sm-4">
									<div class="radio">
										<label>
											<input type="radio" name="tipo_pago" value="Efectivo" onchange="set_pago(1);">
											<span>Efectivo</span>
										</label>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="radio">
										<label>
											<input type="radio" name="tipo_pago" value="Tarjeta" onchange="set_pago(2);">
											<span>Tarjeta</span>
										</label>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="radio">
										<label>
											<input type="radio" name="tipo_pago" value="Deposito" onchange="set_pago(3);">
											<span>Deposito</span>
										</label>
									</div>
								</div>
								<div class="clearfix"></div>
								<input type="text" value="0" id="tipo_pago" class="translate" data-validation="required number" data-validation-allowing="range[1;3]" data-validation-error-msg="Selecciona el modo de pago">
							</div>
						</div>
						<hr>
					</div>





					<div class="form-group">
						<div class="col-sm-12 col-md-6">
							<!-- transitorio-->

						</div>
						<div class="col-sm-12 col-md-6">
							<div class="form-group">
								<label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>
								<div class="col-md-8">
									<select name="plan_de_pagos" id="plan_de_pagos" class="form-control" data-validation="required" onchange="change_plan_pagos(this.value)">
										<option value="no">Pago Completo</option>
										<option value="si">Plan de Pagos</option>
									</select>
								</div>

							</div>
						</div>

					</div>


<!--@yotabm ********************************************************************** div que renderiza dinamicamente si se elige plan de pagos  ******************************************************************************************************************************* -->

					<div id="plan_pagos">
						<!--@yottabm render_plan_pagos function javascript-->
					</div>

<!--@yotabm ********************************************************************** botones guardar y restablecer ******************************************************************************************************************************* -->

					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span>Guardar</span>
							</button>

							<button type="reset" class="btn btn-default" onclick="reset_all()">
								<span class="glyphicon glyphicon-refresh"></span>
								<span>Restablecer</span>
							</button>
						</div>

					</div>
				</form>

			</div>
		</div>


<!--@yotabm ********************************************************************** informacion de la transaccion  ******************************************************************************************************************************* -->

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



<!--@yotabm ********************************************************************** datatables jquery productos stock   ******************************************************************************************************************************* -->

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
						<a href="?/notas/listar" class="btn btn-warning">Mis notas de remision</a>
					</div>
				</div>
				<hr>
				<table id="tableYottabm" class="table table-striped table-bordered nowrap display" style="width:100%"></table>
			</div>

		</div>
	</div>

</div>






<!--@yotabm ********************************************************************** seccion de javascript ******************************************************************************************************************************* -->


<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>


<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>






<!--
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
-->

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css">
<!--buttons css export-->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.min.css">


<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.0/js/dataTables.fixedHeader.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap.min.js"></script>
<!--buttons js export-->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>



<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>




<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>





<!-- @yottabm seccion notificacion de stock insuficiente validacion del backend -->
<script>
	function render_stock_insuficiente(arr_product) {
		remove_stock_insuficiente();
		let div_stock_insuficiente = document.getElementById("div_stock_insuficiente");
		let body = "";
		let template = "";
		for (let i = 0; i < arr_product.length; i++) {
			const codigo = arr_product[i]['codigo'];
			const nombre = arr_product[i]['nombre'];
			body += `
					<div id="container">
						<div id="space-between">
							<div><strong>Código = </strong>${codigo}</div>
							<div><strong>Nombre = </strong>${nombre}</div>
							<div><strong>Cantidad disp. = </strong> ${arr_product[i]['cantidad_actual']}</div>
						</div>
					</div>
					`;
			//${ (codigo.length > 20)? (codigo).substring(0, 20)+'...':codigo}
			//${ (nombre.length > 20)? (nombre).substring(0, 20)+'...':nombre}

		}
		template = `<div class="alert alert-primary alert-dismissable"> 
							<button type="button" class="close" data-dismiss="alert">&times;</button><span>${body}</span>
					</div>`; //alert-warning 
		div_stock_insuficiente.innerHTML = template;
	}

	function remove_stock_insuficiente() {
		document.getElementById("div_stock_insuficiente").innerHTML = '';
	}
</script>


<!-- @yottabm seccion de plan de pagos -->
<script>
	let $plan_pagos = document.getElementById('plan_pagos'); //div plan de pagos

	//@yottabm escucha el cambio del select si es o no tipo plan de pago								
	function change_plan_pagos(value) {
		if (value == 'si') {
			render_plan_pagos(); //renderiza el div principal plan de pagos
			render_tbody_plan_pagos(1); //inicializa el nro_cuotas en 1
			set_pagos_individuales(); //inicializa los pagos individuales por defecto

		} else {
			reset_plan_pago(); // elimina del dom el div plan de pagos
		}
	}
	//@yottabm renderiza el div pricipal de plan de pagos
	function render_plan_pagos() {
		const fecha_actual = moment().format('YYYY-MM-DD');
		const template =
			`
				<section id="section_plan_pagos">
					<hr />
					<div class="form-group">
						<label for="almacen" class="col-md-6 control-label">Nro Cuotas:</label>
						<div class="col-md-6">
							<input type="text" value="1" name="nro_cuotas" class="form-control text-right"
								autocomplete="off" data-nro-cuotas="" data-validation="required number"
								data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo"
								onChange="isOne(this);change_nro_cuotas(this.value)" />
						</div>
					</div>

					<table id="table_plan_pagos" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
						<thead>
							<tr class="active">
								<th class="text-nowrap col-xs-4">Detalle</th>
								<th class="text-nowrap text-center col-xs-4">Fecha pago</th>
								<th class="text-nowrap text-center col-xs-4">Monto</th>
							</tr>
						</thead>

						<tbody id="tbody_plan_pagos">
							<tr class="active" id="row_cuota_0">
								<td class="text-middle">
									<strong>PAGO - 1 INICIAL </strong>
									<input type="hidden" value="1" name="arr_nro_cuota[]" class="form-control input-xs text-right">
								</td>
								<td class="text-middle">
									<input type="date" id="fecha_pago_0" name="arr_fecha_pago[]" 
										value="${fecha_actual}" min="${fecha_actual}" 
										onkeydown="return false"
										onchange="handle_fecha_pagos(0)"
										data-validation="required"
										max="2030-12-31" class="form-control input-xs text-right" autocomplete="off" />
								</td>
								<td class="text-nowrap text-middle text-right">
									<input value="0" name="arr_monto[]" autocomplete="off" 
										class="form-control input-xs text-right"
										data-validation="required number"
										data-validation-allowing="range[0.01;10000000.00],float"
										data-validation-error-msg="valor incorrecto"
										onchange="twoDecimal(this)"
										onkeyup="calcular_monto_total_pago()" />
								</td>
							</tr>
						</tbody>

						<tfoot>
							<tr class="active">
								<th class="text-nowrap text-center" colspan="2">Importe total</th>
								<th class="text-nowrap text-right" id="monto_total_pago">0.00</th>
							</tr>
						</tfoot>
						
					</table>

					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" id="total_descuento" name="total_descuento" value=""
								class="translate" tabindex="-1"
								data-validation-error-msg="La suma de las cuotas debe ser igual al importe total de la venta"
								data-validation="required confirmation" />
						</div>
					</div>
				</section>
			`;
		$plan_pagos.innerHTML = template;
	}

	//@yottabm elimina todo el contenido del div plan de pagos
	function reset_plan_pago() {
		$plan_pagos.innerHTML = '';
	}

	//@yottabm escucha el input obtiene el nro de cuotas y renderiza segun el tamanio ademas agrega las fechas y los pagos individuales por defecto
	function change_nro_cuotas(nro_cuotas) {
		render_tbody_plan_pagos(nro_cuotas);
		set_fecha_pagos();
		set_pagos_individuales();

		//fechas y pagos restablecidos
		$.notify({
			title: 'Genial !  ',
			icon: 'glyphicon glyphicon-info-sign',
			message: 'Las fechas y cuotas se han establecido para su optimo uso verifica por favor'
		}, {
			type: 'primary',
			animate: {
				enter: 'animated fadeInUp',
				exit: 'animated fadeOutRight'
			},
			placement: {
				from: "bottom", //from: "bottom" //top,
				align: "center" //align: "left" right
			},
			offset: 10,
			spacing: 10,
			z_index: 1031,
		});
	}

	//@yottabm  añade dinamicamente filas segun nro de cuotas
	function render_tbody_plan_pagos(nro_cuotas) {

		const $tbody_plan_pagos = document.getElementById('tbody_plan_pagos'); // obtiene tbody plan de pagos
		for (let i = 0; i < nro_cuotas; i++) {
			//si la posicion no existe insetarmos una fila al final
			if ($tbody_plan_pagos.rows[i] === undefined) {
				const key = `row_cuota_${i}`;
				const row =
					`
					<tr class="active" id="${key}">
						<td class="text-middle">
							<strong>PAGO - ${(i+1)} </strong>
							<input type="hidden" value="${(i+1)}" name="arr_nro_cuota[]" class="form-control input-xs text-right">
						</td>
						<td class="text-middle">
							<input type="date" id="fecha_pago_${i}" name="arr_fecha_pago[]" value="" 
								min="" max="2030-12-31"
								data-validation="required"
								onkeydown="return false"
								onchange="handle_fecha_pagos(${i})"
								class="form-control input-xs text-right" autocomplete="off">
						</td>
						<td class="text-nowrap text-middle text-right">
							<input value="0" name="arr_monto[]" autocomplete="off"
								class="form-control input-xs text-right"
								data-validation="required number"
								data-validation-allowing="range[0.01;10000000.00],float"
								data-validation-error-msg="valor incorrecto"
								onchange="twoDecimal(this)"
								onkeyup="calcular_monto_total_pago()" >
						</td>
					</tr>
				`;
				$tbody_plan_pagos.insertAdjacentHTML('beforeend', row);
			}
		}
		const rows = $tbody_plan_pagos.rows.length; //obtiene el nro de filas de la tabla tbody plan de pagos
		//console.log(rows);
		if (nro_cuotas < rows) {
			for (let i = nro_cuotas; i < rows; i++) {
				const key = `row_cuota_${i}`;
				document.getElementById(key).remove();
			}
		}
	}

	//@yottabm inicializa las fechas por defecto cada mes +1
	function set_fecha_pagos() {
		//console.log(i)
		let arr_fecha_pago = document.getElementsByName('arr_fecha_pago[]');
		for (let i = 0; i < arr_fecha_pago.length; i++) {
			if (i === 0) {
				const fecha_inicial = arr_fecha_pago[i].value
				const fecha_actual = moment().format('YYYY-MM-DD')
				arr_fecha_pago[i].value = fecha_inicial;
				arr_fecha_pago[i].setAttribute("min", fecha_actual);
			} else {

				const fecha_input_anterior = arr_fecha_pago[i - 1].value
				const fecha_mes_siguiente = moment(fecha_input_anterior).add((1), 'months').format('YYYY-MM-DD') //fecha mes despues segun la variable i
				arr_fecha_pago[i].value = fecha_mes_siguiente;

				const fecha_mes_dia_siguiente = moment(fecha_input_anterior).add(1, 'days').format('YYYY-MM-DD') //le sumamos un dia de la fecha anterior para establecerlo en la validacion
				arr_fecha_pago[i].setAttribute("min", fecha_mes_dia_siguiente);

			}
		}
	}

	//@yottabm escucha los cambios de fechas
	function handle_fecha_pagos(posicion) {

		const init = (posicion > 0) ? (posicion + 1) : 0;

		let arr_fecha_pago = document.getElementsByName('arr_fecha_pago[]');
		for (let i = init; i < arr_fecha_pago.length; i++) {
			if (i === 0) {
				const fecha_inicial = arr_fecha_pago[i].value
				const fecha_actual = moment().format('YYYY-MM-DD')
				arr_fecha_pago[i].value = fecha_inicial;
				arr_fecha_pago[i].setAttribute("min", fecha_actual);
			} else {

				const fecha_input_anterior = arr_fecha_pago[i - 1].value
				const fecha_mes_siguiente = moment(fecha_input_anterior).add((1), 'months').format('YYYY-MM-DD') //fecha mes despues segun la variable i
				arr_fecha_pago[i].value = fecha_mes_siguiente;

				const fecha_mes_dia_siguiente = moment(fecha_input_anterior).add(1, 'days').format('YYYY-MM-DD') //le sumamos un dia de la fecha anterior para establecerlo en la validacion
				arr_fecha_pago[i].setAttribute("min", fecha_mes_dia_siguiente);

			}
		}

	}


	//@yottabm inicializa los pagos de cuotas individuales
	function set_pagos_individuales() {

		//const monto_total = parseFloat(document.getElementById("monto_total_confirmation").value);
		const monto_total = parseFloat(document.getElementById("importe_total_descuento").textContent);

		const nro_cuotas = parseInt(document.getElementById("tbody_plan_pagos").rows.length);

		let sum_monto_total = 0; //sumador del monto total
		let pago_individual = (monto_total / nro_cuotas).toFixed(2).slice(0, -1); //obtenemos monto individual con un decimal
		pago_individual = parseFloat(pago_individual).toFixed(2); //volvemos a convertir a dos decimales

		//console.log(pago_individual)
		let arr_monto = document.getElementsByName('arr_monto[]');
		for (var i = 0; i < arr_monto.length; i++) {
			arr_monto[i].value = pago_individual //insertamos el monto individual a cada input
			sum_monto_total = sum_monto_total + parseFloat(pago_individual); //sumamos cada pago individual
		}

		let residuo = monto_total - sum_monto_total;
		residuo = parseFloat(residuo).toFixed(2);

		let first_pago = parseFloat(pago_individual) + parseFloat(residuo);
		first_pago = parseFloat(first_pago).toFixed(2);

		//insertamos el pago +residuo al 1ra cuota
		arr_monto[0].value = first_pago;
		calcular_monto_total_pago();
	}

	//@yottabm realiza los calculos del monto total de pagos
	function calcular_monto_total_pago() {
		let sum_monto_total = 0;
		let arr_monto = document.getElementsByName('arr_monto[]');
		for (var i = 0; i < arr_monto.length; i++) {
			let pago = parseFloat(arr_monto[i].value);
			pago = (isNaN(pago) || pago <= 0) ? 0 : pago; //si el inpu esta vacio||contiene letras||es menor a 0 por defecto se toma 0
			sum_monto_total = sum_monto_total + pago; //sumamos cada pago
		}
		document.getElementById("monto_total_pago").textContent = parseFloat(sum_monto_total).toFixed(2);
		document.getElementById("total_descuento").value = parseFloat(sum_monto_total).toFixed(2);
		//console.log(sum_monto_total)
	}

	//@yottabm funcion para calculo de importe total se usa en tbody_cesta ver abajo
	function handle_monto_total_pago() {
		if (document.getElementById('section_plan_pagos')) {
			set_pagos_individuales();
		}
	}
</script>


<!-- @yottabm validate formulario -->
<script>
	$(function() {
		var $formulario = $('#formulario');
		$.validate({
			form: '#formulario',
			modules: 'basic,security',
			onSuccess: function() {
				guardar();
			}
		});
		$formulario.on('submit', function(e) {
			e.preventDefault();
		});

	});
</script>


<!-- @yottabm seccion de datatables listado de productos -->
<script type="text/javascript">
	let moneda = "<?= $moneda; ?>";
	let id_almacen = "<?= $id_almacen; ?>";
	//console.log(id_almacen);

	$(document).ready(function() {

		var tableYottabm = $('#tableYottabm').DataTable({
			"dom": 'Blfrtip',
			"order": [
				[0, "desc"]
			],
			//"lengthMenu": [ [10, 25, 50,100,200, -1], [10, 25, 50,100,200, "Todos"] ],
			"lengthMenu": [
				[15, 25, 50, 100, 200, 500],
				[15, 25, 50, 100, 200, 500]
			],
			"buttons": [
				'colvis'

			],
			"processing": true,
			"serverSide": true,
			"info": true, // control table information display field
			//"stateSave": true, //restore table state on page reload,
			//"deferLoading": 2,

			"scrollX": true,
			"scrollY": '57vh',
			"scrollCollapse": true,

			//"scrollCollapse": true,

			"language": {
				"url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
			},

			"ajax": {
				"url": "?/notas/api_obtener_productos",
				"type": "POST",
				"data": {
					"id_almacen": `${id_almacen}`,

				}
			},
			"deferRender": true,
			//"responsive": true,

			"columnDefs": [{
					"className": "td-bold",
					"targets": [0],
					"title": '#',
					"width": "3%",
					"searchable": false,
					"visible": false,
				},
				{
					"className": "dt-right",
					"targets": [1],
					"title": `${moneda}`,
					"width": "5%",
					//"data": 'id_producto', //or 0
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createButtons,
					"searchable": false,
					"orderable": false,

				},


				{
					"className": "text-right td-bold",
					"targets": [2],
					"title": 'Stock',
					"width": "5%",
				},

				{
					"className": "text-wrap",
					"targets": [3],
					"title": 'Codigo',
					"searchable": true,
					"width": "3%",
				},
				{
					"targets": [4],
					"title": 'Nombre Producto',
					"data": 4, //or 0
					"render": createNombre,
					"searchable": true,
					"width": "10%",
				},

				{
					"targets": [5],
					"title": 'Descripcion',
					"visible": false,

				},
				{
					"targets": [6],
					"title": 'Categoria',
					"visible": false,
				},

			],
		});

	});


	let permiso_asignar = true;
	let permiso_ver = true;
	let permiso_editar = true;
	let permiso_eliminar = true;



	function createNombre(nombre) {
		//let name = (nombre.length > 30) ? (nombre).substring(0, 30) + '...' : nombre;
		return "<div class='text-wrap'>" + nombre + "</div>";
	}


	function createButtons(data) {

		let id_producto = data.id_producto;
		let nombre = ((data.nombre)).replace(/"|'/g, ''); //eliminamos caracteres que no permitan evaluarlo como objeto
		let codigo = ((data.codigo)).replace(/"|'/g, ''); //eliminamos caracteres que no permitan evaluarlo como objeto
		let stock = data.stock;

		let arr_asignacion_id = data.arr_asignacion_id ? data.arr_asignacion_id.split("|") : '';
		let arr_unidad = data.arr_unidad ? data.arr_unidad.split("|") : '';
		let arr_tamanio = data.arr_tamanio ? data.arr_tamanio.split("|") : '';
		let arr_precio_actual = data.arr_precio_actual ? data.arr_precio_actual.split("|") : '';

		let template = '';
		for (let i = 0; i < arr_asignacion_id.length; i++) {
			const btn_shop =
				`
				<button type="button" onclick="add_producto({'producto_id':'${id_producto}',
													'asignacion_id':'${arr_asignacion_id[i]}',
													'unidad':'${arr_unidad[i]}',
													'tamanio':'${arr_tamanio[i]}',
													'precio':'${arr_precio_actual[i]}',
													'nombre':'${nombre}',
													'codigo':'${codigo}',
													'stock':'${stock}'
												})"  
					class="btn btn-xs btn-warning" data-toggle="tooltip" 
					data-title="añadir añ carrito">
					<span class="glyphicon glyphicon-shopping-cart"></span>
				</button>
				`
			template += `
					<div class="content-space-between" style="padding-bottom: 3px;">
						<span>
							${btn_shop} ${(arr_unidad[i]).toLowerCase()} 
						</span>&nbsp;&nbsp;&nbsp;
						<span> ${arr_precio_actual[i]}</span>
						
					</div>`;
		}

		return template;
	}
</script>


<!-- @yottabm seccion de cesta de productos -->
<script>
	let tbody_cesta = document.getElementById("tbody_cesta");

	//@yottabm verifica si el producto existe en el Dom
	function search_producto(key) {
		if (document.body.contains(document.getElementById(key))) {
			return true; //Element exists
		} else {
			return false; //Element does not exist!
		}
	}
	//@yottabm declacramos el objeto producto para almacenar la data de la peticion axios y no volver a realizar la peticio si el producto ya existe
	let producto = {};
	//@yottabm añade un producto al carrito recibe un objeto desde el boton
	function add_producto(objeto) {
		let {
			producto_id,
			unidad,
			tamanio,
			precio,
			asignacion_id,
			codigo,
			nombre,
			stock
		} = objeto;

		const key = `row_${producto_id}_${asignacion_id}`;
		const existe = search_producto(key); //@yottabm retorna un valor booleano
		//stock = 100000000; //descomentar para realizar test validacion en en back
		if (!existe) {
			//creamos template row
			const template = `
			<tr class="active" id="${key}" data-producto="${key}">
				
				<td class="text-nowrap text-middle">
					<strong></strong>
				</td>

				<td class="text-nowrap text-middle text-center">
					<button type="button" onclick="remove_producto('${key}'); sum_cantidad('${producto_id}');" class="btn btn-xs btn-primary" data-toggle="tooltip" data-title="Eliminar producto">
						<span class="glyphicon glyphicon-remove"></span>
					</button>
				</td>

				<td class="text-nowrap text-middle">
					<span>
						<input type="hidden" value="${producto_id}" name="arr_producto_id[]" class="form-control input-xs text-right">
						${codigo}
					</span>
				</td>

				<td class="text-nowrap text-middle">
					<input type="hidden" value="${nombre}" name="arr_nombre_producto[]" class="form-control input-xs text-right">
					${ (nombre.length > 30)? (nombre).substring(0, 30)+'...':nombre}
				</td>

				<td class="text-nowrap text-middle">
					<input type="text" data-cantidad="" value="1" name="arr_cantidad[]"
						autocomplete="off"
						data-validation="required number" 
						onchange="isInt(this)"
						onkeyup="calcular_importe('${key}'); sum_cantidad('${producto_id}');" 
						class="form-control input-xs text-right">

					<input type="hidden" value="${tamanio}" data-cantidad-x-tamanio="" name="arr_cantidad_x_tamanio_${producto_id}[]" class="form-control input-xs text-right" readonly>
					<input type="text" value="0" name="sum_cantidad_${producto_id}" 
							class="translate form-control input-xs text-right" 
							data-validation="required number"
							data-validation-allowing="range[1;${parseInt(stock)}]"
							data-validation-error-msg="stock insuficiente"
							readonly>
					
				</td>

				<td class="text-nowrap text-middle">
					${unidad}
					<input type="hidden" value="${asignacion_id}" data-asignacion-id="${asignacion_id}" name="arr_asignacion_id[]" class="form-control input-xs text-right" readonly>
					<input type="hidden" value="${tamanio}" data-tamanio="${tamanio}" name="arr_tamanio[]" class="form-control input-xs text-right" readonly>
				</td>

				<td class="text-middle">
			
					<input type="text" data-precio="" value="${parseFloat(precio).toFixed(2)}"  name="arr_precio[]" 
						autocomplete="off"
						data-validation="required number"
						data-validation-allowing="range[0.01;10000000.00],float"
						data-validation-error-msg="valor incorrecto"
						placeholder="0.00"
						onchange="twoDecimal(this)"
						onkeyup="calcular_importe('${key}')" 
						placeholder="0.00"  
						class="form-control input-xs text-right">
				</td>

				<td class="text-nowrap text-middle text-right">
					<span data-importe-text="" style="font-size: 13px;">0.00</span>
					<input type="hidden" value="" data-importe=""  
						name="arr_importe[]"
						class="form-control input-xs text-right"
						readonly>
				</td>

			</tr>`;
			// @yottabm añadimos el template construido al dom
			tbody_cesta.insertAdjacentHTML('beforeend', template);
			renumerar_productos();
			calcular_importe(key);

			//valida stock para cada uno
			sum_cantidad(producto_id);

			$.validate({
				form: '#formulario',
				modules: 'basic,security',
				onSuccess: function() {
					guardar();
				}
			});

		} else {
			//@yottabm si el producto ya existe en el carrito simplemente incrementamos la cantidad de ese producto con esta funcion
			increase_quantity(key);
			//valida stock para cada uno
			sum_cantidad(producto_id);

		}



	}

	//@yottabm elimina todos los elementos hijos del tbody_cesta
	function increase_quantity(key) {
		let $producto = $(`[data-producto=${key}]`);
		let $cantidad = $producto.find('[data-cantidad]');
		cantidad = $.trim($cantidad.val());
		cantidad = parseInt(cantidad) + 1;
		$cantidad.val(cantidad);
		calcular_importe(key);
	}



	function sum_cantidad(producto_id) {
		let sum_cantidad_total = 0;
		let arr_cantidad_x_tamanio = document.getElementsByName(`arr_cantidad_x_tamanio_${producto_id}[]`);
		for (var i = 0; i < arr_cantidad_x_tamanio.length; i++) {
			let cantidad = parseFloat(arr_cantidad_x_tamanio[i].value);
			cantidad = (isNaN(cantidad) || cantidad <= 0) ? 0 : cantidad; //si el inpu esta vacio||contiene letras||es menor a 0 por defecto se toma 0
			sum_cantidad_total = sum_cantidad_total + cantidad; //sumamos cada pago
		}

		let sum_cantidad = document.getElementsByName(`sum_cantidad_${producto_id}`);
		sum_cantidad.forEach((input) => {
			input.value = parseInt(sum_cantidad_total);
		})

		//console.log(sum_cantidad_total);
	}

	//@yottabm elimina todos los elementos hijos del tbody_cesta
	function reset_all() {
		reset_tbody_cesta();
		reset_plan_pago();
		renumerar_productos();
		calcular_total();
		remove_stock_insuficiente();
		document.getElementById("formulario").reset();
	}

	//@yottabm remueve el elemento hijo de tbody (producto segun su id)
	function reset_tbody_cesta() {
		const myNode = document.getElementById("tbody_cesta");
		while (myNode.firstChild) {
			myNode.removeChild(myNode.lastChild);
		}
	}



	//@yottabm remueve el elemento hijo de tbody (producto segun su id)
	function remove_producto(key) {
		//console.log(key)
		document.getElementById(key).remove();
		renumerar_productos();
		calcular_total();
	}


	function redondear_importe(key) {
		var $producto = $(`[data-producto=${key}]`);
		var $costo = $producto.find('[data-precio]');
		var costo;
		costo = $.trim($costo.val());
		costo = ($.isNumeric(costo)) ? parseFloat(costo).toFixed(2) : costo;
		$costo.val(costo);
		calcular_importe(key);
	}


	//@yottabm calcula el importe
	function calcular_importe(key) {

		let $producto = $(`[data-producto=${key}]`);
		let $cantidad = $producto.find('[data-cantidad]');

		let $costo = $producto.find('[data-precio]');

		let $tamanio = $producto.find('[data-tamanio]');
		let $cantidad_x_tamanio = $producto.find('[data-cantidad-x-tamanio]');

		let $importe_span = $producto.find('[data-importe-text]');
		let $importe = $producto.find('[data-importe]');
		let cantidad, costo, importe, tamanio, cantidad_x_tamanio;

		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;

		tamanio = $.trim($tamanio.val());
		tamanio = ($.isNumeric(tamanio)) ? parseInt(tamanio) : 1;

		cantidad_x_tamanio = cantidad * tamanio;
		$cantidad_x_tamanio.val(cantidad_x_tamanio);

		costo = $.trim($costo.val());

		costo = ($.isNumeric(costo)) ? parseFloat(costo) : 0;

		importe = cantidad * costo;
		importe = importe.toFixed(2);
		$importe_span.text(importe);
		$importe.val(importe);

		calcular_total();
	}

	function calcular_total() {
		var $ventas = $('#ventas tbody');
		var $total = $('[data-subtotal]:first');
		var $importes = $ventas.find('[data-importe-text]');
		var importe, total = 0;
		$importes.each(function(i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});

		$total.text(total.toFixed(2));
		$('[data-ventas]:first').val($importes.length).trigger('blur');
		$('[data-total]:first').val(total.toFixed(2)).trigger('blur');



		$('[data-total-confimation]:first').val(total.toFixed(2));
		$('[data-total-cuota]').text(total.toFixed(2));

		calcular_descuento_total();


	}

	function renumerar_productos() {
		var $ventas = $('#ventas tbody');
		var $productos = $ventas.find('[data-producto]');
		$productos.each(function(i) {
			$(this).find('strong:first').text(i + 1);
		});
	}

	function calcular_descuento_total() {

		let importe_total = document.getElementById('importe_total').textContent;
		let descuento = document.getElementById('descuento').value;

		importe_total = parseFloat(importe_total);
		descuento = parseInt(descuento);
		descuento = (isNaN(descuento) || descuento <= 0 || descuento >= 100) ? 0 : descuento;

		const porcentaje = descuento / 100;
		const importe_total_x_porcentaje = (importe_total * porcentaje);
		const importe_total_descuento = importe_total - importe_total_x_porcentaje;

		document.getElementById('importe_total_descuento').textContent = importe_total_descuento.toFixed(2); //label txt
		document.getElementById('total_descuento_confirmation').value = importe_total_descuento.toFixed(2); //input value

		handle_monto_total_pago();

	}
</script>


<!-- @yottabm funciones usables -->
<script>
	function guardar() {
		let myForm = document.getElementById('formulario');
		let formData = new FormData(myForm);
	
		axios({
			method: "post",
			url: `?/notas/api_guardar_nota`,
			data: formData,
		}).then(({data}) => {

			if (data && data.status == 201) {
				//@yottabm recargamos el datatble
				//$('#tableYottabm').DataTable().ajax.reload(); //reiniicia
				reset_all(); //limpia todos el formulario
				$('#tableYottabm').DataTable().ajax.reload(null, false); //recarga el datatable mantiene la paginacion
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: data.icon,
					message: data.message
				}, {
					type: data.type,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom" //top,
						align: "center" //align: "left" right
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});

			}

			if (data && data.status == 400) {
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: data.icon,
					message: data.message
				}, {
					type: data.type,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom" //top,
						align: "center" //align: "left" right
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
				render_stock_insuficiente(data.productos);
				$('#tableYottabm').DataTable().ajax.reload(null, false); //recarga el datatable mantiene la paginacion
			}

		}).finally(() => {
			
      	});
	}

	//@yottabm validamos el input peso y retornamos un decimal de 2
	function twoDecimal(e) {
		let decimal = e.value;
		if (decimal !== '') {
			e.value = (isNaN(decimal) || decimal <= 0) ? 0 : parseFloat(e.value).toFixed(2);
		} else {
			e.value = 0;
		}
	}

	function isInt(e) {
		let entero = e.value;
		e.value = ((isNaN(entero) || entero <= 0)) ? '' : parseInt(entero);
	}

	function isOne(e) {
		let entero = e.value;
		e.value = ((isNaN(entero) || entero <= 0)) ? 1 : parseInt(entero);
	}

	function isPorcentaje(e) {
		let entero = e.value;
		e.value = ((isNaN(entero) || entero <= 0 || entero >= 100)) ? 0 : parseInt(entero);
	}


	function set_nombre_proveedor(e) {
		let nombre_proveedor = e.options[e.selectedIndex].text;
		document.getElementById('nombre_proveedor').value = nombre_proveedor;
		//console.log(nombre_proveedor);
	}

	function set_pago(value) {
		document.getElementById('tipo_pago').value = value;
	}
</script>



<!-- @yotabm select cliente add input datos -->
<script>
	function es_nit(texto) {
		var numeros = '0123456789';
		for (i = 0; i < texto.length; i++) {
			if (numeros.indexOf(texto.charAt(i), 0) != -1) {
				return true;
			}
		}
		return false;
	}

	$(function() {
		var $cliente = $('#cliente');
		var $nit_ci = $('#nit_ci');
		var $nombre_cliente = $('#nombre_cliente');
		var $formulario = $('#formulario');
		var $cliente_id = $('#cliente_id');
		var $telefono = $('#telefono');

		$cliente.selectize({
			persist: false,
			createOnBlur: true,
			create: true,
			onInitialize: function() {
				$cliente.css({
					display: 'block',
					left: '-10000px',
					opacity: '0',
					position: 'absolute',
					top: '-10000px'
				});
			},
			onChange: function() {
				$cliente.trigger('blur');
			},
			onBlur: function() {
				$cliente.trigger('blur');
			}
		}).on('change', function(e) {
			var valor = $(this).val();
			valor = valor.split('|');
			//console.log(valor);
			$(this)[0].selectize.clear();
			if (valor.length != 1) {
				$nit_ci.prop('readonly', true);
				$nombre_cliente.prop('readonly', true);
				$telefono.prop('readonly', true);
				$nit_ci.val(valor[0]);
				$nombre_cliente.val(valor[1]);
				$cliente_id.val(valor[2]);
				$telefono.val(valor[3]);
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


		$formulario.on('reset', function() {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			$telefono.prop('readonly', false);
		}).trigger('reset');





	});
</script>





<?php require_once show_template('footer-sidebar'); ?>