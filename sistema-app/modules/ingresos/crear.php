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
	redirect('?/ingresos/seleccionar_sucursal');
}


// Obtiene los proveedores
$proveedores = $db->select('nombre_proveedor, count(nombre_proveedor) as nro_visitas, sum(monto_total) as total_compras')
	->from('inv_ingresos')
	->group_by('nombre_proveedor')
	->order_by('nombre_proveedor asc')
	->fetch();

$proveedores = $db->select('id_proveedor, nombre_proveedor')
	->from('inv_proveedores')
	->group_by('nombre_proveedor')
	->order_by('nombre_proveedor asc')
	->fetch();

$empleados = $db->from("sys_empleados")->fetch();

?>

<?php require_once show_template('header-sidebar-yottabm'); ?>


<style>
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

		<div class="panel panel-default" data-formato="<?= strtoupper($formato_textual); ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos del ingreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-primary">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Advertencia!</strong>
					<ul>
						<li>Para un mejor control del ingreso de productos se recomienda escribir una pequeña descripción acerca de la compra.</li>
						<li>La moneda con la que se esta trabajando es <?= escape($moneda); ?>.</li>
					</ul>
				</div>

				<form id="formulario" class="form-horizontal">


					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Usuario:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= ($_user['persona_id'] == 0) ? 'No asignado' : escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?>" disabled="disabled">
						</div>
					</div>
					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $almacen['almacen']; ?>" disabled="disabled">
						</div>
						<input type="hidden" value="<?= $almacen['id_almacen']; ?>" name="almacen_id" id="id_almacen" />
					</div>
					<div class="form-group">
						<label for="proveedor" class="col-sm-4 control-label">Proveedor:</label>
						<div class="col-sm-8">
							<select name="proveedor_id" id="proveedor_id" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100" onchange="set_nombre_proveedor(this)">
								<option value="">Buscar</option>
								<?php foreach ($proveedores as $elemento) { ?>
									<option value="<?= escape($elemento['id_proveedor']); ?>">
										<?= escape($elemento['nombre_proveedor']); ?>
									</option>
								<?php } ?>
							</select>
							<input type="hidden" value="" name="nombre_proveedor" id="nombre_proveedor" />
						</div>
					</div>
					<div class="form-group">
						<label for="responsable_id" class="col-sm-4 control-label">Responsable de ingreso:</label>
						<div class="col-sm-8">
							<select name="responsable_id" id="responsable_id" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
								<option value="">Buscar</option>
								<?php foreach ($empleados as $elemento) { ?>
									<option value="<?= escape($elemento['id_empleado']); ?>"><?= escape($elemento['nombres'] . ' ' . $elemento['paterno'] . ' ' . $elemento['materno']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="descripcion" class="col-sm-4 control-label">Observaciones:</label>
						<div class="col-sm-8">
							<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
						</div>
					</div>







					<div class="table-responsive margin-none">
						<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap" style="width: 100px;">Cantidad</th>
									<th class="text-nowrap">Unidad</th>
									<th class="text-nowrap" style="width: 100px;">Costo</th>
									<th class="text-nowrap">Importe</th>
								</tr>
							</thead>

							<tbody id="tbody_cesta">
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="7">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
								</tr>
							</tfoot>

						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number" data-validation-allowing="range[1;250]" data-validation-error-msg="Debe existir como mínimo 1 producto y como máximo 250 productos">
							<input type="text" name="monto_total_confirmation" id="monto_total_confirmation" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El costo total de la compra debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>






					<div class="form-group">
						<div class="col-sm-12 col-md-6">
							<div class="form-group">
								<label for="almacen" class="col-md-4 control-label">Transitorio:</label>
								<div class="col-md-8">
									<div class="input-group">
										<span class="input-group-addon">
											<input type="checkbox" value="1" id="transitorio" name="transitorio" aria-label="..." onclick="handle_des_transitorio()">
										</span>
										<input type="text" id="des_transitorio" name="des_transitorio" placeholder="Motivo" class="form-control" aria-label="..." readonly>
									</div>
								</div>
							</div>

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


					<div id="plan_pagos">
						<!--@yottabm render_plan_pagos function javascript-->
					</div>

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



		<div class="panel panel-success" data-servidor="<?= ip_local . name_project . '/nota.php'; ?>">

			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<strong>Información sobre la transacción</strong>
				</h3>
			</div>

			<div class="panel-body">
				<div class="table-display">
					<div class="tbody">
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-user"></span>
								<span>Usuario:</span>
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
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de ingresos</span></a>
					</div>
				</div>
				<hr>
				<table id="tableYottabm" class="table table-striped table-bordered nowrap display" style="width:100%" ></table>
			</div>

		</div>
	</div>

</div>



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
							<input type="text" id="monto_total" name="monto_total" value=""
								class="translate form-control" tabindex="-1"
								data-validation-error-msg="La suma de las cuotas debe ser igual al importe total de la compra"
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

		const monto_total = parseFloat(document.getElementById("monto_total_confirmation").value);
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
		document.getElementById("monto_total").value = parseFloat(sum_monto_total).toFixed(2);
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
				"url": "?/ingresos/api_obtener_productos",
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
					"className": "td-right",
					"targets": [2],
					"title": `${moneda} Catalogo`,
					"width": "5%",
					//"data": 'id_producto', //or 0
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createPreciosActuales,
					"searchable": false,
					"orderable": false,
					"visible": false,

				},

				{
					"className": "text-center td-bold",
					"targets": [3],
					"title": 'Stock',
					"width": "5%",
				},

				{
					"className": "text-wrap",
					"targets": [4],
					"title": 'Codigo',
					"width": "3%",
				},
				{
					"targets": [5],
					"title": 'Nombre Producto',
					"data": 5, //or 0
					"render": createNombre,
					"width": "10%",
				},

				{
					"targets": [6],
					"title": 'Descripcion',
					"visible": false,

				},
				{
					"targets": [7],
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

		let arr_asignacion_id = data.arr_asignacion_id ? data.arr_asignacion_id.split("|") : '';
		let arr_unidad = data.arr_unidad ? data.arr_unidad.split("|") : '';
		let arr_tamanio = data.arr_tamanio ? data.arr_tamanio.split("|") : '';
		let arr_costo_actual = data.arr_costo_actual ? data.arr_costo_actual.split("|") : '';

		let template = '';
		for (let i = 0; i < arr_asignacion_id.length; i++) {
			const btn_shop =
				`
				<button type="button" onclick="add_producto({'producto_id':'${id_producto}',
													'asignacion_id':'${arr_asignacion_id[i]}',
													'unidad':'${arr_unidad[i]}',
													'tamanio':'${arr_tamanio[i]}',
													'costo_actual':'${arr_costo_actual[i]}',
													'nombre':'${nombre}',
													'codigo':'${codigo}'
												})"  
					class="btn btn-xs btn-primary" data-toggle="tooltip" 
					data-title="añadir añ carrito">
					<span class="glyphicon glyphicon-shopping-cart"></span>
				</button>
				`
			template += `
					<div class="content-space-between" style="padding-bottom: 3px;">
						<span>
							${btn_shop} ${(arr_unidad[i]).toLowerCase()} 
						</span>&nbsp;&nbsp;&nbsp;
						<span> ${arr_costo_actual[i]}</span>
						
					</div>`; //<span> ${arr_costo_actual[i]}<span class="glyphicon glyphicon-usd"></span></span> de (${arr_tamanio[i]} u.)
		}

		return template;
	}

	function createPreciosActuales(data) {
		let arr_precio_actual = data.arr_precio_actual ? data.arr_precio_actual.split("|") : '';
		let template = '';
		for (let i = 0; i < arr_precio_actual.length; i++) {
			template += `<div style="margin-bottom: 5px;">${arr_precio_actual[i]}</div>`;
		}
		return template;
	}
</script>


<!-- @yottabm seccion de cesta de productos -->
<script>
	let tbody_cesta = document.getElementById("tbody_cesta");

	//@yottabm obtenemos la fecha actual para su uso en la funcion add_producto
	const fecha_actual = "<?php echo date('Y-m-d')  ?>";

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
		const {
			producto_id,
			unidad,
			tamanio,
			costo_actual,
			asignacion_id,
			codigo,
			nombre
		} = objeto;

		const key = `row-${producto_id}-${asignacion_id}`;
		const existe = search_producto(key); //@yottabm retorna un valor booleano

		if (!existe) {
			//creamos template row
			const template = `
			<tr class="active" id="${key}" data-producto="${key}">
				
				<td class="text-nowrap text-middle">
					<strong></strong>
				</td>

				<td class="text-nowrap text-middle text-center">
					<button type="button" onclick="remove_producto('${key}')" class="btn btn-xs btn-warning" data-toggle="tooltip" data-title="Eliminar producto">
						<span class="glyphicon glyphicon-remove"></span>
					</button>
				</td>

				<td class="text-nowrap text-middle">
					<input type="hidden" value="${producto_id}" name="arr_producto_id[]" class="form-control input-xs text-right">
					${codigo}
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
						onkeyup="calcular_importe('${key}')" 
						class="form-control input-xs text-right">

					<input type="hidden" value="${tamanio}" data-cantidad-x-tamanio="" name="arr_cantidad_x_tamanio[]" class="form-control input-xs text-right" readonly>
					
				</td>

				<td class="text-nowrap text-middle">
					${unidad}
					<input type="hidden" value="${asignacion_id}" data-asignacion-id="${asignacion_id}" name="arr_asignacion_id[]" class="form-control input-xs text-right" readonly>
					<input type="hidden" value="${tamanio}" data-tamanio="${tamanio}" name="arr_tamanio[]" class="form-control input-xs text-right" readonly>
				</td>

				<td class="text-middle">
			
					<input type="text" data-costo="" value="${parseFloat(costo_actual).toFixed(2)}"  name="arr_costo[]" 
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
					<span data-importe-text="">0.00</span>
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

	//@yottabm elimina todos los elementos hijos del tbody_cesta
	function reset_all() {
		reset_tbody_cesta();
		reset_des_transitorio();
		reset_plan_pago();
		renumerar_productos();
		calcular_total();
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
		var $costo = $producto.find('[data-costo]');
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

		let $costo = $producto.find('[data-costo]');

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
		var $compras = $('#compras tbody');
		var $total = $('[data-subtotal]:first');
		var $importes = $compras.find('[data-importe-text]');
		var importe, total = 0;
		$importes.each(function(i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});

		$total.text(total.toFixed(2));
		$('[data-compras]:first').val($importes.length).trigger('blur');
		$('[data-total]:first').val(total.toFixed(2)).trigger('blur');


		$('[data-total-confimation]:first').val(total.toFixed(2));
		$('[data-total-cuota]').text(total.toFixed(2));

		//@yottabm escucha los cambios del total y los refleja al plan de pagos
		handle_monto_total_pago();

	}

	function renumerar_productos() {
		var $compras = $('#compras tbody');
		var $productos = $compras.find('[data-producto]');
		$productos.each(function(i) {
			$(this).find('strong:first').text(i + 1);
		});
	}
</script>


<!-- @yottabm funciones usables -->
<script>
	function guardar() {
		let myForm = document.getElementById('formulario');
		let formData = new FormData(myForm);
		
		axios({
			method: "post",
			url: `?/ingresos/api_guardar_compra`,
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
					message: data.messagge
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

	function set_nombre_proveedor(e){
		let nombre_proveedor = e.options[e.selectedIndex].text;
		document.getElementById('nombre_proveedor').value = nombre_proveedor;
		//console.log(nombre_proveedor);
	}



	//input transitorio
	function reset_des_transitorio(){
		document.getElementById("des_transitorio").readOnly = true;
		document.getElementById("des_transitorio").value = "";
	}

	function handle_des_transitorio(){

		if (document.getElementById("transitorio").checked) {
			document.getElementById("des_transitorio").readOnly = false;
		} else {
			reset_des_transitorio();
		}
	}
</script>




<?php require_once show_template('footer-sidebar'); ?>