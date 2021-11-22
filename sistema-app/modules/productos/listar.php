<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_asignar = true;
$permiso_unidad_base = in_array('unidad', $permisos);
$permiso_fijar = false;
$permiso_quitar = in_array('quitar', $permisos);
$permiso_cambiar_precio = in_array('cambiar', $permisos);

?>
<?php require_once show_template('header-sidebar-yottabm'); ?>

<style>
	.content-space-between {
		display: flex;
		justify-content: space-between;
	}

	.td-bold {
		font-weight: bold
	}

	.btn-group,
	.btn-group-vertical {
		margin-bottom: 15px;
		float: right;
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

<div class="panel-body">
	<p class="lead" data-header="true">Catálogo de productos</p>
	<hr>
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $productos)) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para agregar nuevos productos hacer clic en el siguiente botón: </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<?php if ($permiso_imprimir) { ?>
					<a href="?/productos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
				<?php } ?>
				<?php if ($permiso_crear) { ?>
					<a href="?/productos/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
				<?php } ?>
			</div>
		</div>
		<hr>
	<?php } ?>
	<table id="tableYottabm" class="table table-striped table-bordered nowrap" style="width:100%"></table>

	<div class="modal fade" id="modal_asignacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabe2" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header" id="title_modal_asignacion">
					<h3>ASIGNAR NUEVA UNIDAD</h3>
				</div>
				<div class="modal-body">

					<form id="formulario_asignacion" class="form-horizontal" autocomplete="off">

						<input type="hidden" id="producto_id" value="0" name="producto_id" data-validation="required">

						<div class="form-group">
							<label for="empleado" class="col-sm-3 control-label">Unidad:</label>
							<div class="col-sm-9">
								<select name="unidad_id" id="unidad" class="form-control" data-validation="required"></select>
							</div>
						</div>

						<div class="form-group">
							<label for="empleado" class="col-sm-3 control-label">Precio <?= escape($moneda); ?>:</label>
							<div class="col-sm-9">
								<input type="text" value="" name="precio_actual" id="precio_actual" class="form-control" autocomplete="off" onchange="twoDecimal(this)" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float">
							</div>
						</div>

						<div class="modal-footer" style="padding-bottom: 0;">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span id="btn_submit">Asignar unidad</span>
							</button>
							<button type="reset" class="btn btn-default" data-dismiss="modal" onclick="reset_asignacion()">
								<span class="glyphicon glyphicon-refresh"></span>
								<span>Cancelar</span>
							</button>
						</div>

					</form>

				</div>

			</div>
		</div>
	</div>




	<div class="modal fade" id="modal_actualizacion_precio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabe2" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h3>ACTUALIZAR PRECIO</h3>
				</div>
				<div class="modal-body">

					<form id="formulario_precio" class="form-horizontal" autocomplete="off">

						<input type="hidden" id="asignacion_id" value="0" name="asignacion_id" data-validation="required">

						<div id="label_modal_precio"></div>

						<div class="form-group">
							<label for="empleado" class="col-sm-3 control-label">Nuevo Precio <?= escape($moneda); ?>:</label>
							<div class="col-sm-9">
								<input type="text" value="" name="nuevo_precio" id="nuevo_precio" class="form-control" autocomplete="off" onchange="twoDecimal(this)" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float">
							</div>
						</div>

						<div class="modal-footer" style="padding-bottom: 0;">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span id="btn_submit">Actualizar precio</span>
							</button>
							<button type="reset" class="btn btn-default" data-dismiss="modal" onclick="reset_modal_precio()">
								<span class="glyphicon glyphicon-refresh"></span>
								<span>Cancelar</span>
							</button>
						</div>

					</form>

				</div>

			</div>
		</div>
	</div>



</div>


<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>


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
<!-- @yottabm obtener unidades -->
<script type="text/javascript">
	obtener_unidades();

	let $select_unidad = document.getElementById('unidad');
	let arr_unidades = [];
	async function obtener_unidades() {
		const url = `?/productos/api_obtener_unidades`;
		const data = await axios.post(url).then(response => response.data.unidades).catch(err => console.error)
		arr_unidades = data ? data : [];
		renderizar_unidades(arr_unidades);
	}

	function renderizar_unidades(new_arr_unidades) {
		let template = '<option value="">SELECCIONAR...</option>';
		new_arr_unidades.forEach((el, index) => {
			template += `<option value="${el.id_unidad}">${(el.unidad).toLowerCase()} &nbsp;&nbsp;&nbsp;&nbsp;de (${el.tamanio} u.)</option>`;
		})
		$select_unidad.innerHTML = template;
	}

	//yottabm formatea en decimal de 2
	function twoDecimal(e) {
		let decimal = e.value;
		if (decimal !== '') {
			e.value = (isNaN(decimal) || decimal <= 0) ? 0 : parseFloat(e.value).toFixed(2);
		} else {
			e.value = 0;
		}
	}
</script>


<!-- @yottabm datatables -->
<script type="text/javascript">
	let moneda = "<?= $moneda; ?>";
	$(document).ready(function() {

		var tableYottabm = $('#tableYottabm').DataTable({
			"dom": 'Blfrtip',
			"order": [[ 0, "desc" ]],
			//"lengthMenu": [ [10, 25, 50,100,200, -1], [10, 25, 50,100,200, "Todos"] ],
			"lengthMenu": [
				[10, 25, 50, 100, 200, 500],
				[10, 25, 50, 100, 200, 500]
			],
			"buttons": [
				//'copyHtml5',
				{
					"extend": 'print',
					"title": 'Icaba - Lista de productos',
					"orientation": 'landscape',
					"pageSize": 'LEGAL',
					"exportOptions": {
						"columns": [0, 2, 3, 4, 5, 6, 7, 8]
					}
				},


				{
					"extend": 'excelHtml5',
					"title": 'Icaba-Productos',
					"orientation": 'landscape',
					"pageSize": 'LEGAL',
					"exportOptions": {
						"columns": [0, 2, 3, 4, 5, 6, 7, 8]
					}
				},
				{
					"extend": 'pdfHtml5',
					"title": 'ICABA-LISTA DE PRODCUTOS',
					"orientation": 'landscape',
					"pageSize": 'LEGAL',
					"exportOptions": {
						"columns": [0, 2, 3, 4, 5, 6, 7, 8]
					},
					customize: function(doc) {
						doc.content.splice(0, 0, {
							margin: [0, 0, 0, 12],
							alignment: 'left',
							image: ''
						});
					}
				},
				'colvis'
			],
			"processing": true,
			"serverSide": true,
			"info": true, // control table information display field
			//"stateSave": true, //restore table state on page reload,
			//"deferLoading": 2,


			"scrollY": '55vh',
			"scrollX": true,
			"scrollCollapse": true,

			"language": {
				"url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
			},

			"ajax": {
				"url": "?/productos/api_obtener_productos",
				"type": "POST",
				"data": {
					"id": "11070630",
					"from": 'bolivia',
					"id_user": '1'
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
				},
				{
					"targets": [1],
					"title": 'OPCIONES',
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
					"targets": [2],
					"title": 'CODIGO',
					"width": "7%",
				},
				{
					"targets": [3],
					"title": 'NOMBRE PRODUCTO',
					"width": "10%",
				},
				{
					"targets": [4],
					"title": 'NOMBRE FACTURA',
					"visible": false,
				},
				{
					"targets": [5],
					"title": `UNIDAD/PRECIO&nbsp;${moneda}`,
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createUnidad,
					"width": "10%",
					"searchable": false,
					"orderable": false,
				},
				{
					"targets": [6],
					"title": 'CATEGORIA',

				},
				{
					"targets": [7],
					"title": 'UBICACION',
					"createdCell": function(td, cellData, rowData, row, col) {
						if (cellData == '') {
							$(td).css('background-color', '#5bc0de1f')
						}
					},
					"visible": false,
				},
				{
					"targets": [8],
					"title": 'DESCRIPCION',
					"visible": false,
				},
				{
					"targets": [9],
					"title": 'IMAGEN',
					"data": 9,
					"render": createImage,
					"searchable": false,
					"orderable": false,

				},
				/*
				{
					"targets": [-1],
					"visible": false,
					"searchable": false,
					"orderable": false,
				},
				*/
			],
		});

		$("body").tooltip({
			selector: '[data-toggle="tooltip"]',
			container: 'body'
		});

	});



	let permiso_asignar = "<?= $permiso_asignar; ?>" || false;
	let permiso_ver = "<?= $permiso_ver; ?>" || false;
	let permiso_editar = "<?= $permiso_editar; ?>" || false;
	let permiso_eliminar = "<?= $permiso_eliminar; ?>" || false;

	function createButtons(data) {


		const btn_asignar = permiso_asignar ?
			`<span data-toggle="modal" data-target="#modal_asignacion">
				<a href="#" class="text-decoration-none" name="btn_asignacion" onclick="asignar_unidad({'id_producto':'${data.id_producto}','arr_id_unidad':'${data.arr_id_unidad}'})" data-toggle="tooltip" data-placement="top" title="añadir unidad">
					<span class="glyphicon glyphicon glyphicon-plus"></span>
				</a>
			</span>` : '';
		const btn_ver = permiso_ver ?
			`<a href="?/productos/ver/${data.id_producto}" class="text-decoration-none" data-toggle="tooltip" title="Ver producto">
				<span class="glyphicon glyphicon-search"></span>
			</a>` : '';
		const btn_edit = permiso_editar ?
			`<a href="?/productos/editar/${data.id_producto}" class="text-decoration-none" data-toggle="tooltip" title="Editar prodcuto">
				<span class="glyphicon glyphicon-edit"></span>
			</a>` : '';
		const btn_delete = permiso_eliminar ?
			`<a href="#" class="text-decoration-none" onclick="eliminar_producto(${data.id_producto})" data-toggle="tooltip" title="Eliminar producto" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
			</a>` : '';

		const btn_all = (permiso_asignar || permiso_ver || permiso_editar || permiso_eliminar) ? `${btn_asignar}${btn_ver}${btn_edit}${btn_delete}` : ''

		return btn_all;
	}

	function createUnidad(data) {

	
		let nombre = ((data.nombre)).replace(/"|'/g, ''); //eliminamos caracteres que no permitan evaluarlo como objeto
		let arr_id_asignacion = data.arr_id_asignacion ? data.arr_id_asignacion.split("|") : '';
		let arr_unidad = data.arr_unidad ? data.arr_unidad.split("|") : '';
		let arr_tamanio = data.arr_tamanio ? data.arr_tamanio.split("|") : '';
		let arr_precio_actual = data.arr_precio_actual ? data.arr_precio_actual.split("|") : '';
		let template = '';
		for (let i = 0; i < arr_id_asignacion.length; i++) {


			const btn_delete = (permiso_eliminar && i > 0) ?
				`<a href="#" onclick="eliminar_asignacion_unidad(${arr_id_asignacion[i]})" data-toggle="tooltip" data-placement="top" title="eliminar unidad">
					<span class="glyphicon glyphicon-remove"></span>
				</a>` : '&nbsp;&nbsp;&nbsp;&nbsp;';

			const btn_update = permiso_editar ?
				`<span data-toggle="modal" data-target="#modal_actualizacion_precio">
					<a href="#" class="text-decoration-none" name="btn_actualizacion_precio" onclick="actualizar_precio({'asignacion_id':'${arr_id_asignacion[i]}','precio_actual':'${arr_precio_actual[i]}','nombre':'${nombre}','unidad':'${arr_unidad[i]}'})" data-toggle="tooltip" data-placement="top" title="actualizar precio">
						<span class="glyphicon glyphicon-refresh"></span>
					</a>
				</span>` : '';

			const btn_all = (permiso_eliminar) ? `${btn_delete}${btn_update}` : ''

			template += `
					<div class="content-space-between">
						<span>
							${btn_all} ${(arr_unidad[i]).toLowerCase()} de (${arr_tamanio[i]} u.)
						</span>&nbsp;&nbsp;&nbsp;
						<span> ${arr_precio_actual[i]}<span class="glyphicon glyphicon-usd"></span></span>
						
					</div>`;
		}

		return template;

	}

	function createImage(url) {
	
		const path = `/sistema-app/files/productos/${url}`;
		//template = `<img src="${path}" alt="Italian Trulli" style="height: 50px;">`;
		template = `<img src="https://www.tibs.org.tw/images/default.jpg" alt="Italian Trulli" style="height: 50px;">`;
		return template;
	}
</script>



<!-- @yottabm eliminar producto -->
<script type="text/javascript">
	function confirmar_eliminar_producto(id) {
		axios.post(`?/productos/api_eliminar_producto/${id}`).then(({
			data
		}) => {
			if (data.status && (data.status === 200 || data.status === 400)) {
				//@yottabm recargamos el datatble
				//$('#tableYottabm').DataTable().ajax.reload();
				$('#tableYottabm').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: 'glyphicon glyphicon-info-sign',
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
		});
	}

	function eliminar_producto(id) {

		Swal.fire({
			title: 'Estas seguro de eliminar este registro?',
			text: "No podrás revertir esta accion!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Si, eliminar!'
		}).then((result) => {
			if (result.isConfirmed) {
				confirmar_eliminar_producto(id);
			}
		})




	}
</script>


<!-- @yottabm modadl asignacion unidad -->
<script type="text/javascript">
	//form validate
	$(function() {
		var $formulario_asignacion = $('#formulario_asignacion');
		$.validate({
			form: '#formulario_asignacion',
			modules: 'basic',
			onSuccess: function() {
				guardar_asignacion();
			}
		});
		$formulario_asignacion.on('submit', function(e) {
			e.preventDefault();
		});

	});

	function reset_asignacion() {
		document.getElementById("formulario_asignacion").reset();
	}

	let $input_producto_id = document.getElementById('producto_id');

	function asignar_unidad(el) {

		let arr_id_unidad = el.arr_id_unidad? el.arr_id_unidad.split("|") : '';
		let	new_arr_unidades = arr_unidades.filter(item => !arr_id_unidad.includes(item.id_unidad));
		
		renderizar_unidades(new_arr_unidades);
		reset_asignacion();
		$input_producto_id.value = el.id_producto;
	}

	function guardar_asignacion() {
		let myForm = document.getElementById('formulario_asignacion');
		let formData = new FormData(myForm);
		$("[data-dismiss=modal]").trigger({
			type: "click"
		});
		axios({
			method: "post",
			url: `?/productos/api_guardar_asignacion_unidad`,
			data: formData,
		}).then(({
			data
		}) => {

			if (data.status && (data.status === 200 || data.status === 500)) {
				//@yottabm recargamos el datatble
				//$('#tableYottabm').DataTable().ajax.reload();
				$('#tableYottabm').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: 'glyphicon glyphicon-info-sign',
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
		});
	}
</script>


<!-- @yottabm modadl actualizar precio -->
<script type="text/javascript">
	//form validate
	$(function() {
		var $formulario_precio = $('#formulario_precio');
		$.validate({
			form: '#formulario_precio',
			modules: 'basic',
			onSuccess: function() {
				actualizar_precio_asignacion();
			}
		});
		$formulario_precio.on('submit', function(e) {
			e.preventDefault();
		});

	});


	function reset_modal_precio() {
		document.getElementById("formulario_precio").reset();
	}

	let $label_modal_precio = document.getElementById('label_modal_precio');
	let $input_asignacion_id = document.getElementById('asignacion_id');

	function actualizar_precio(el) {
		reset_modal_precio();
		const label = `<strong>Nombre producto :</strong> ${el.nombre} </br><strong>Unidad :</strong> ${(el.unidad).toLowerCase() } </br><strong>Precio actual :</strong> ${el.precio_actual} `;
		$label_modal_precio.innerHTML = label;
		$input_asignacion_id.value = el.asignacion_id;
	}


	function actualizar_precio_asignacion() {
		let myForm = document.getElementById('formulario_precio');
		let formData = new FormData(myForm);
		$("[data-dismiss=modal]").trigger({
			type: "click"
		});
		axios({
			method: "post",
			url: `?/productos/api_actualizar_precio_asignacion`,
			data: formData,
		}).then(({
			data
		}) => {

			if (data.status && (data.status === 200)) {
				//@yottabm recargamos el datatble
				//$('#tableYottabm').DataTable().ajax.reload();
				$('#tableYottabm').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: 'glyphicon glyphicon-info-sign',
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
		});
	}
</script>

<!-- @yottabm eliminar asignacion -->
<script type="text/javascript">
	function confirmar_eliminar_asignacion_unidad(id) {


		axios.post(`?/productos/api_eliminar_asignacion_unidad/${id}`).then(({
			data
		}) => {
			if (data.status && (data.status === 200 || data.status === 500)) {
				//@yottabm recargamos el datatble
				//$('#tableYottabm').DataTable().ajax.reload();
				$('#tableYottabm').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: 'glyphicon glyphicon-info-sign',
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
		});
	}

	function eliminar_asignacion_unidad(id) {


		Swal.fire({
			title: 'Estas seguro de eliminar esta unidad?',
			text: "No podrás revertir esta accion!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Si, eliminar!'
		}).then((result) => {
			if (result.isConfirmed) {
				confirmar_eliminar_asignacion_unidad(id);
			}
		})




	}
</script>




<?php require_once show_template('footer-sidebar'); ?>