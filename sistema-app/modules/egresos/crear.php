<?php
// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Obtiene el almacen principal
// $almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();
$empleados = $db->from("sys_empleados")->fetch();

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
	WHERE u.id_user = $id_user_current and a.id_almacen = $id_almacen
")->fetch_first();

// @etysoft si no encuentra el almacen asignado redirecciona a seleccionar sucursal
if(!$almacen){
	redirect('?/egresos/seleccionar_sucursal');
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
$permiso_baja = in_array('permiso_baja', $permisos);
$permiso_traspaso = in_array('permiso_traspaso', $permisos);
$permiso_guardar = in_array('api_guardar_egreso', $permisos);
?>
<?php require_once show_template('header-sidebar'); ?>
<style>
	.text-wrap{
		white-space:normal;
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
</style>


<style>
	td.text-wrap-name.text-middle {
		max-width: 230px;
		white-space: nowrap;
		text-overflow: ellipsis;
		overflow: hidden;
	}

	td.text-wrap-button.text-middle {
		text-align-last: center;
	}
</style>

<div class="row">
	<div class="col-md-6">
		<!-- @etysoft panel - detalle de egreso -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos del egreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form id="formulario" class="form-horizontal">
					<div class="form-group">
						<label for="almacen" class="col-md-4 control-label">Usuario:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= ($_user['persona_id'] == 0) ? 'No asignado' : escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?>" disabled="disabled">	
							<input type="hidden" name="usuario" value="<?= $_user['persona_id']; ?>">
						</div>
						<input type="hidden" value="<?php echo $almacenes['id_almacen']; ?>" name="almacen_id" id="almacen"/>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= escape($almacen['almacen']); ?>" disabled="disabled">
						</div>
					</div>
					<!-- cargar empleados mediante petición -->
					<div id="change_responsable" class="form-group">
					</div>

					<!-- seleccionar tipo de egreso -->
					<div class="form-group">
						<label for="tipo" class="col-sm-4 control-label">Tipo de egreso:</label>
						<div class="col-sm-8">
							<select name="tipo" id="tipo" class="form-control" data-validation="required" onchange="changeTipo(this)">
								<option value="">Seleccionar</option>
								<?php if($permiso_traspaso){ ?>
									<option value="Traspaso">Egreso como traspaso</option>
								<?php }if($permiso_baja){ ?>
									<option value="Baja">Egreso como baja</option>
								<?php } ?>								
							</select>
						</div>
					</div>
					<!-- cargar almacenes mediante petición -->
					<div id="change_almacen_destino" class="form-group">
                        <label for="almac" class="col-sm-4 control-label">Almacén:</label>
                        <div class="col-sm-8">
                            <select name="almacen_destino_id" id="almacen_destino_id" class="form-control" data-validation="required">
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
					<div class="form-group" id="conductor">
                        <label for="conductor" class="col-sm-4 control-label">Conductor:</label>
                        <div class="col-sm-8">
                            <select name="conductor" id="conductor" class="form-control" data-validation="required">
                                <?php foreach($empleados as $elemento){ ?>
                                <option value="<?= $elemento['id_empleado'] ?>"><?= escape($elemento['nombres'].' '.$elemento['paterno'].' '.$elemento['materno']); ?></option>
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
					
					<!-- @ visualiza el contenido, error de validación stock -->
					<div id="content_stock"></div>


					<div class="table-responsive margin-none">
						<table class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center text-center">
										<span class="glyphicon glyphicon-trash"></span>
									</th>
									<th class="text-nowrap text-center">CÓDIGO</th>
									<th class="text-wrap-name text-center">PRODUCTO</th>
									<th class="text-nowrap text-center">CANTIDAD</th>
									<th class="text-nowrap text-center">UNIDAD</th>
									<th class="text-nowrap text-center">PRECIO</th>
									<th class="text-nowrap text-center">IMPORTE</th>
								</tr>
							</thead>
							<tbody id="table_egresosES"></tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="7">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;250]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 250">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>

					<div class="col-xs-6 text-left">
					    <div class="form-group" id="transitorio_check">
                            <label for="almacen" class="col-md-6 control-label">Almacén transitorio:</label>
                            <div class="col-md-6 right">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="reserva" aria-label="...">
                                    </span>
                                    <input type="text" name="des_reserva" placeholder="Motivo" class="form-control" aria-label="...">
                                </div>
                            </div>
                        </div>
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

		<!-- @etysoft panel - información de la transacción -->
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
		<!-- @etysoft panel - stock de productos -->
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
						<a href="?/egresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de egresos</span></a>
					</div>
				</div>
				<hr>
				<table id="tableES" class="table table-striped table-bordered nowrap" style="width:100%"></table>
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
<script>
$(function () {

    $('#alma').hide();
    $('#conductor').hide();
    $('#responsable_ingreso').hide();
    $('#transitorio_check').hide();

});



</script>


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


<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<!--  @etysoft sweetalert -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--  @etysoft cargar tabla de busqueda de productos -->
<script type="text/javascript">
	let moneda = "<?= $moneda;?>";
	let id_almacen = "<?= $id_almacen; ?>";

	//recarga el tabla de productos mediante ajax y lo actualiza
	function reloadDataTable() {
		clearDataTable();
		reloadDataTable();
	};

	//limpia la tabla funcion de datatable
	function clearDataTable() {
		dataTable.clear();
	}

	$(document).ready(function() {
		var table_stock = $("#tableES").DataTable({
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


			"scrollY": '57vh',
			"scrollX": true,
			"scrollCollapse": true,

			"language": {
				"url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
			},

			"ajax": {
				"url": "?/egresos/api_obtener_productos",
				"type": "POST",
				"data": {
					"id_almacen": `${id_almacen}`
				}
			},
			"deferRender": true,
			//"responsive": true,

			"columnDefs": [
				{
					"className": "td-bold",
					"targets": [0],
					"title": '#',
					"width": "3%",
					"searchable": false,
					"visible": false,
				},
				{
					"className": "td-middle td-center",
					"targets": [1],
					"title": `UNIDADES - PRECIO ${moneda}`,
					"width": "5%",
					//"data": 'id_producto', //or 0
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createButtons,
					"searchable": false,
					"orderable": false,
					"visible": true,
				},
				{
					"className": "td-right",
					"targets": [2],
					"title": `${moneda} - COSTO ACTUAL`,
					"width": "5%",
					//"data": 'id_producto', //or 0
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createCostosActuales,
					"searchable": false,
					"orderable": false,
					"visible": false,

				},
				{
					"className": "text-center td-bold",
					"targets": [3],
					"title": 'STOCK',
					"width": "5%",
				},
				{
					"targets": [4],
					"title": 'CODIGO',
					"width": "7%",
				},
				{
					"targets": [5],
					"title": 'NOMBRE PRODUCTO',
					"data": 'nombre', //or 0
					"render": createNombre,
					"width": "10%",
				},
				{
					"targets": [6],
					"title": 'DESCRIPCION',
					"visible": false,
				},
				{
					"targets": [7],
					"title": 'CATEGORIA',
					"visible": false,
				},
			],
		});
	});

	let permiso_asignar = true;
	let permiso_ver = true;
	let permiso_editar = true;
	let permiso_eliminar = true;

	


	// @etysoft visualizar nombre de producto
	function createNombre(nombre) {
		//let name = (nombre.length > 30) ? (nombre).substring(0, 30) + '...' : nombre;
		return "<div class='text-wrap'>" + nombre + "</div>";
	}

	// @etysoft crear boton y visualizar precio actual por tipo de unidad
	function createButtons(data) {
		let id_producto = data.id_producto;
		let nombre = ((data.nombre)).replace(/"|'/g, '');
		let codigo = ((data.codigo)).replace(/"|'/g, '');
		let arr_asignacion_id = data.arr_asignacion_id ? data.arr_asignacion_id.split("|") : '';
		let arr_unidad = data.arr_unidad ? data.arr_unidad.split("|") : '';
		let arr_tamanio = data.arr_tamanio ? data.arr_tamanio.split("|") : '';
		let arr_precio_actual = data.arr_precio_actual ? data.arr_precio_actual.split("|") : '';
		let stock = data[3];

		let template = '';
		for (let i = 0; i < arr_asignacion_id.length; i++) {
			const btn_shop =
				`
				<button type="button" onclick="
					add_producto({
						'id_producto':'${id_producto}',
						'asignacion_id':'${arr_asignacion_id[i]}',
						'unidad':'${arr_unidad[i]}',
						'tamanio':'${arr_tamanio[i]}',
						'precio_actual':'${arr_precio_actual[i]}',
						'stock':'${stock}',
						'nombre':'${nombre}',
						'codigo':'${codigo}'
					})"
					class="btn btn-xs btn-primary" data-toggle="tooltip" 
					data-title="añadir añ carrito">
					<span class="glyphicon glyphicon-shopping-cart"></span>
				</button>
				`;
			template += `
				<div class="content-space-between" style="padding-bottom: 3px;">
					<span>
						${btn_shop} ${(arr_unidad[i]).toLowerCase()} 
					</span>&nbsp;&nbsp;&nbsp;
					<span> ${arr_precio_actual[i]}</span>
					
				</div>`; //<span> ${arr_precio_actual[i]}<span class="glyphicon glyphicon-usd"></span></span> de (${arr_tamanio[i]} u.)
		}
		return template;
	}

	// @etysoft visualizar los costos actuales por tipo de unidad
	function createCostosActuales(data) {
		let arr_costo_actual = data.arr_costo_actual ? data.arr_costo_actual.split("|") : '';
		let template = '';
		for (let i = 0; i < arr_costo_actual.length; i++) {
			template += `<span>${arr_costo_actual[i]}<span class="glyphicon glyphicon-usd"></span></span> <br>`;
		}
		return template;
	}
</script>


<script>
	// se usa el parametro id_almacen declarado en el script que carga el data table
	let $almacen = document.getElementById('almacen_destino_id');
	let $almacen_change = document.getElementById('change_almacen_destino');
	$almacen_change.style.display = "none";

	//@etysoft funcion para cargar el select con los almacen disponibles a excepción del actual
	function show_select_store() {
		axios.post('?/egresos/api_almacenes_menos_actual/'+id_almacen ).then(({
			data
		}) => {
			const arr_almacenes = data;
			let template = `<option value="">Seleccione Almacen</option>`;
			for (let i = 0; i < arr_almacenes.length; i++) {
				//@etysoft opciones disponibles
				template += `<option value="${arr_almacenes[i]['id_almacen']}">${arr_almacenes[i]['almacen']}</option>`;
			}
			$almacen.innerHTML = template;
		})
		.catch((err) => {
			console.error(err);
		});
	}

	show_select_store();

	// @etysoft cambiar el tipo de egreso
	function changeTipo(e){
		let lista = document.getElementById('tipo');
		let actual = lista.parentElement.parentElement;
		let tipo_seleccionado = e.options[lista.selectedIndex].value;
		let $almacen_change = document.getElementById('change_almacen_destino');
		if (tipo_seleccionado === "Traspaso") {
			// @etysoft visualiza seleccionar almacen
			$almacen_change.style.display = "block";
		} else {
			// @etysoft oculta seleccionar almacen
			$almacen_change.style.display = "none";
		}
	}
</script>

<script>
	// obteniene el id del usuario que esta en sesión
	let id_rol_current_user = "<?= $_user['rol_id']; ?>";
	if (id_rol_current_user == 1 || id_rol_current_user == 2) {
		show_select_employers(); // ejecuta la carga de empleados
	}else{
		show_current_employer(); // ejecuta la carga de los datos del usuario actual
	}


	//@etysoft funcion para cargar el select con los empleados
	function show_select_employers() {
		let $id_form = document.getElementById("change_responsable");
		axios.post('?/egresos/api_obtener_empleados').then(({
			data
		}) => {
			const arr_empleados = data;
			let template = `
					<label for="responsable" class="col-sm-4 control-label">Responsable de salida:</label>
					<div class="col-sm-8">
						<select name="responsable" id="responsable" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">`;
							template += `<option value="">Seleccione Empleado</option>`;
							for (let i = 0; i < arr_empleados.length ; i++) {
								//@etysoft opciones disponibles
								template += `<option value="${arr_empleados[i]['id_empleado']}"> ${arr_empleados[i]['empleado'] }</option>`;
							}
					template +=
						`</select>
					</div>`;
			$id_form.innerHTML = template;
		})
		.catch((err) => {
			console.error(err);
		});
	}

	//@etysoft funcion para cargar el nombre del empleado que esta en sesión actual
	function show_current_employer(){
		let id_empleado = "<?= $_user['persona_id'];?>";
		let nombres = "<?= $_user['nombres'];?>";
		let paterno = "<?= $_user['paterno'];?>";
		let materno = "<?= $_user['materno'];?>";
		let $id_form = document.getElementById("change_responsable");
		let template = `
					<label for="usuario" class="col-md-4 control-label">Usuario:</label>
					<div class="col-sm-8">`;
					template += `
						<input type="text" class="form-control" value="${(id_empleado === 0)  ? 'No asignado' : nombres +' '+paterno+' '+ materno }" disabled="disabled">	
						<input type="hidden" name="responsable" value="${id_empleado}">
					`;
					template += `
					</div>`;
		$id_form.innerHTML = template;
	}
</script>



<script>
	let table_egresosES = document.getElementById("table_egresosES");
	var nro = 1;

	// @etysoft declacamos el objeto producto para almacenar los datos recuperados de la tabla de busqueda
	let producto = {};
	// @etysoft añade un producto al carrito recibe un objeto desde el boton
	function add_producto(objeto) {

		//@etysoft destructuramos el objeto recibido y lo almacenamos en variables
		const {
			id_producto,
			asignacion_id,
			unidad,
			tamanio,
			precio_actual,
			stock,
			nombre,
			codigo
		} = objeto;

		// @etysoft creamos la key para el row
		const key = `row-${id_producto}-${asignacion_id}`;
		// @etysoft verifica si existe el producto en el carrito, retorna un valor booleano
		const existe = search_product(key); 

		if (!existe) {
			// @etysoft se crear una nueva fila
			const template = `
			<tr class="active" id="${key}" data-producto="${key}">
				<td class="text-nowrap text-middle">
					<input type="hidden" value="${nro}" class="form-control input-xs text-right">
					${nro}
				</td>
				<td class="text-nowrap text-middle text-center">
					<button type="button" onclick="remove_product('${key}'); sum_cantidad('${id_producto}');" class="btn btn-xs btn-primary" data-toggle="tooltip" data-title="Eliminar producto">
						<span class="glyphicon glyphicon-remove"></span>
					</button>
				</td>
				<td class="text-nowrap text-middle">
					<input type="hidden" value="${id_producto}" name="arr_id_producto[]" class="form-control input-xs text-right">
					${codigo}
				</td>
				
				<td class="text-wrap-name text-middle">
					<input type="hidden" value="${nombre}" name="arr_nombre_producto[]" class="form-control input-xs text-right">
					${nombre}
				</td>

				<td  class="text-middle hidden"  style="width: 80px;">
					<input type="hidden" value=""  data-cantidad-total="" name="arr_cantidad_unidad[]" 
						class="form-control input-xs text-right isNumber"
						>
				</td>

				<td class="text-nowrap text-middle">
					<input type="text" data-cantidad="" value="1" name="arr_cantidad[]"
						autocomplete="off"
						data-validation="required number" 
						onchange="isInt(this)"
						onkeyup="calculate_amount_row('${key}'); validate_stock('${key}'); sum_cantidad('${id_producto}');" 
						class="form-control input-xs text-right">

					<input type="hidden" value="${tamanio}" data-cantidad-x-tamanio="" name="arr_cantidad_x_tamanio_${id_producto}[]" class="form-control input-xs text-right" readonly>
					<input type="text" value="0" name="sum_cantidad_${id_producto}" 
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
			
				<td class="text-nowrap text-middle">
					<input type="text" value="${precio_actual}"  data-precio="" 
						name="arr_precio[]" onchange="twoDecimal(this)" 
						  placeholder="0.00" 
						onkeyup="calculate_amount_row('${key}')" 
						class="form-control input-xs text-right">
				
				</td>

				<td class="text-nowrap text-middle text-right">
					<span data-importe-text="">0.00</span>
					<input type="hidden" value="" data-importe=""  name="arr_importe[]" readonly class="form-control input-xs text-right">
				</td>


			</tr>`;
			// @etysoft se agrega la fila contruida al Dom
			table_egresosES.insertAdjacentHTML('beforeend', template);
			// @etysoft incrementamos la variable de enumeración
			nro = nro + 1;
			calculate_amount_row(key, true);
			validate_stock(key,stock,true);

			//valida stock para cada uno
			sum_cantidad(id_producto);

			$.validate({
				form: '#formulario',
				modules: 'basic, security',
				onSuccess: function() {
					guardar();
				}
			});

		} else {
			// @etysoft si el producto ya existe, solo se ejecuta la funcion incrementar cantidad interna
			increase_quantity(key);
			//valida stock para cada uno
			sum_cantidad(id_producto);
		}

	}

	// @etysoft verifica si el producto existe en el Dom
	function search_product(key) {
		if (document.body.contains(document.getElementById(key))) {
			return true;
		} else {
			return false;
		}
	}
	
	// @etysoft incrementar cantidad interna
	function increase_quantity(key) {
		let $producto = $(`[data-producto=${key}]`);
		let $cantidad = $producto.find('[data-cantidad]');
		cantidad = $.trim($cantidad.val());
		cantidad = parseInt(cantidad) + 1;
		$cantidad.val(cantidad);
		calculate_amount_row(key, true);
	}

	// @etysoft elimina el producto actual
	function remove_product(key) {
		document.getElementById(key).remove();
		renumber_rows();
		calculate_total_amount();
	}

	// @etysoft reenumerar filas
	function renumber_rows() {
		var $ventas = $('#table_egresosES');
		var $productos = $ventas.find('[data-producto]');
		$productos.each(function (i) {
			$(this).find('td:first').text(i + 1);
		});
	}

	// @etysoft calcular importe
	function calculate_amount_row(key, push = false) {

		let $producto = $(`[data-producto=${key}]`);

		let $asignacion_id = $producto.find('[data-asignacion-id]');
		let $cantidad = $producto.find('[data-cantidad]');
		let $cantidad_total = $producto.find('[data-cantidad-total]');
		let $precio = $producto.find('[data-precio]');

		let $tamanio = $producto.find('[data-tamanio]');
		let $importe_span = $producto.find('[data-importe-text]');
		let $importe = $producto.find('[data-importe]');

		let asignacion_id, tamanio, cantidad, precio, importe;

		asignacion_id = $.trim($asignacion_id.val());
		asignacion_id = ($.isNumeric(asignacion_id)) ? parseInt(asignacion_id) : 0;

		tamanio = $.trim($tamanio.val());
		tamanio = ($.isNumeric(tamanio)) ? parseInt(tamanio) : 0;

		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;

		precio = $.trim($precio.val());
		precio = ($.isNumeric(precio)) ? parseFloat(precio) : 1.00;

		const cantidad_total = cantidad * parseInt(tamanio);
		$cantidad_total.val(cantidad_total); 

		importe = cantidad * precio;

		importe = importe.toFixed(2);
		$importe_span.text(importe);
		$importe.val(importe);
		// @etysoft ejecuta función para calcular importe total
		calculate_total_amount();
	}

	// @etysoft calcular importe total
	function calculate_total_amount() {
		var $ventas = $('#table_egresosES');
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
	}

	// @etysoft eliminar el contenido parcial
	function reset_all() {
		reset_table_ES();
		renumber_rows();
		calculate_total_amount();
		$almacen_change.style.display = "none";
		document.getElementById("formulario").reset();
	}

	// @etysoft eliminar el contenido parcial en el carrito 
	function reset_table_ES() {
		const myNode = document.getElementById("table_egresosES");
		while (myNode.firstChild) {
			myNode.removeChild(myNode.lastChild);
		}
	}

</script>

<script>
	// @etysoft validamos el input peso y retornamos un decimal de 2
	function twoDecimal(e) {
		let decimal = e.value;
		if (decimal !== '') {
			e.value = (isNaN(decimal) || decimal <= 0) ? parseFloat(1).toFixed(2) : parseFloat(e.value).toFixed(2);
		} else {
			e.value = 0.00;
		}
	}

	// @etysoft validamos el input entero
	function isInt(e) {
		let entero = e.value;
		e.value = ((isNaN(entero) || entero <= 0)) ? '' : parseInt(entero);
	}

	// @etysoft validamos el stock
	function validate_stock(key) {
		let $egreso = $('#table_egresosES');
		let $productos = $egreso.find(`[data-producto=${key}]`);

		let $producto = $(`[data-producto=${key}]`);

		let $cantidad = $producto.find('[data-cantidad]');
		let $cantidad_total = $producto.find('[data-cantidad-total]');
		let $tamanio = $producto.find('[data-tamanio]');

		let tamanio, cantidad;

		tamanio = $.trim($tamanio.val());
		tamanio = ($.isNumeric(tamanio)) ? parseInt(tamanio) : 0;

		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;

		let cantidad_total = cantidad * tamanio;
		$cantidad_total.val(cantidad_total);
	}


	function sum_cantidad(producto_id) {
		let sum_cantidad_total = 0;
		let arr_cantidad_x_tamanio = document.getElementsByName(`arr_cantidad_x_tamanio_${producto_id}[]`);
		for (var i = 0; i < arr_cantidad_x_tamanio.length; i++) {
			let cantidad = parseFloat(arr_cantidad_x_tamanio[i].value);
			cantidad = (isNaN(cantidad) || cantidad <= 0) ? 0 : cantidad; 
			sum_cantidad_total = sum_cantidad_total + cantidad;
		}

		let sum_cantidad = document.getElementsByName(`sum_cantidad_${producto_id}`);
		sum_cantidad.forEach((input) => {
			input.value = parseInt(sum_cantidad_total);
		})
	}

</script>




<script>

	let permiso_guardar = "<?=$permiso_guardar; ?>";

	function guardar() {
		let myForm = document.getElementById('formulario');
		let formData = new FormData(myForm);
		const uri = '?/egresos/api_guardar_egreso';
        axios({
            method: "post",
            url: uri,
            data: formData,
        }).then(({
            data
        }) => {
			if (data.status && data.status === 201) {
				// recargamos el datatble
				reset_all();
				$('#tableES').DataTable().ajax.reload(null, false);
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
						from: "bottom",
						align: "center"
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}
			
			if (data.status && data.status === 400 ){
				let productos_agotados = data.productos;
				let mensaje = "";
				let $template = "";
				mensaje += `<div id="container">`;
				$.each(productos_agotados, (index, producto_agotado) =>{	
					mensaje += "El producto: "+ producto_agotado.codigo + " de nombre: "+ producto_agotado.nombre + " solo dispone de: "+ producto_agotado.stock +" en: "+producto_agotado.unidad +"<br>"
				});
				mensaje += `<div id="container">`;

				notification_stock(mensaje);

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
						from: "bottom",
						align: "center"
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}
        });
	}

	
	$(function() {					
		var $formulario = $('#formulario');
		$.validate({
			form: '#formulario',
			modules: 'basic, security',
			onSuccess: function() {
				guardar();
			}
		});
		$formulario.on('submit', function(e) {
			e.preventDefault();
		});

	});

</script>


<!-- @etysoft notificamos al usuario respecto al stock -->
<script>
	function notification_stock(body_notification) {
		remove_notifiacion_stock();
		let content_stock = document.getElementById("content_stock");
		template = `<div class="alert alert-primary alert-dismissable"> 
							<button type="button" class="close" data-dismiss="alert">&times;</button><span>${body_notification}</span>
					</div>`; //alert-warning 
		content_stock.innerHTML = template;
	}

	function remove_notifiacion_stock() {
		document.getElementById("content_stock").innerHTML = '';
	}
</script>

<?php require_once show_template('footer-sidebar'); ?>