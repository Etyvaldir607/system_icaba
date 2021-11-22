<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion 		= date('Y');
$gestion_base 	= date('Y-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);




// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene los ingresos
$ingresos = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
	->from('inv_ingresos i')
	->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
	->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
	->where('i.fecha_ingreso >= ', $fecha_inicial)
	->where('i.fecha_ingreso <= ', $fecha_final)

	->order_by('i.fecha_ingreso desc, i.hora_ingreso desc')
	->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear 		= in_array('crear', $permisos);
$permiso_ver 		= in_array('ver', $permisos);
$permiso_eliminar 	= in_array('eliminar', $permisos);
$permiso_imprimir 	= in_array('imprimir', $permisos);
$permiso_editar 	= in_array('editar', $permisos);
$permiso_historiar 	= in_array('historiar', $permisos);
$permiso_cambiar 	= true;


//var_dump($fecha_inicial);
//var_dump($fecha_final);
?>


<!-- style datatbles -->
<style>
	span.dt-down-arrow {
		color: #fff !important;
	}

	div.dataTables_wrapper div.dataTables_filter {
		text-align: right !important;
	}

	div.dataTables_wrapper div.dataTables_length {
		text-align: left !important;
		height: 34px !important;
	}

	div.dataTables_wrapper div.dataTables_filter input {
		width: 250px !important;
		/*width: 100vw !important;
		max-width: 100%;
		margin-left: 0 !important;
		height: 34px !important;*/
	}
</style>

<?php require_once show_template('header-sidebar-yottabm'); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Listado de Ingresos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
		<div class="row">


			<div class="col-xs-12 col-sm-12 text-right">
				<div class="row">
					<?php if ($permiso_cambiar) { ?>
						<div class='col align-self-start'>
							<div class="form-group">
								<label for="categoria_id" class="control-label" style="float: left; font-weight: 700;">Fecha incial</label>
								<input type='text' class="form-control" name="fecha_inical" id="fecha_inical" value="" onchange="set_fecha(this)" />

							</div>
						</div>
						<div class='col align-self-start'>
							<div class="form-group">
								<label for="categoria_id" class="control-label" style="float: left; font-weight: 700;">Fecha final</label>
								<input type='text' class="form-control" name="fecha_final" id="fecha_final" value="" />
							</div>
						</div>
					<?php } ?>



					<div class='col-xs-12 col-md-6'>

						<div class="row">
							<div class='col align-self-start'>
								<div class="form-group">
									<label for="categoria_id" class="control-label" style="float: left; font-weight: 700;">Seleccionar almacen</label>
									<select name="categoria_id" id="categoria_id" class="form-control" data-validation="required">
										<option value="">Seleccionar</option>
									</select>
								</div>
							</div>

							<div class='col align-self-start'>
								<div style="padding-top: 25px;">
									<?php if ($permiso_crear) { ?>
										<a href="?/ingresos/seleccionar_sucursal" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span> Ingresar </span></a>
									<?php } ?>
									<?php if ($permiso_imprimir) { ?>
										<a href="?/ingresos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span> Imprimir</span></a>
									<?php } ?>
								</div>
							</div>

						</div>



					</div>
				</div>

			</div>
		</div>
		<hr>
	<?php } ?>


	<div class="clearfix" style="margin-bottom: 10px;">
		<div class="pull-right tableTools-container"></div>
	</div>


	<h1>En desarrollo En desarrollo En desarrollo En desarrollo En desarrollo En desarrollo </h1>
	<table id="tableYottabm" class="table table-striped table-bordered nowrap display" style="width:100%"></table>



</div>

<!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
	<div id="modal_fecha" class="modal fade">
		<div class="modal-dialog">
			<form id="form_fecha" class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Cambiar fecha</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="inicial_fecha">Fecha inicial:</label>
								<input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="final_fecha">Fecha final:</label>
								<input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-aceptar="true">
						<span class="glyphicon glyphicon-ok"></span>
						<span>Aceptar</span>
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
<!-- Fin modal fecha -->






<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>




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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>




<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
	//funcion autoejecutable
	//locales/bootstrap-datepicker.es.js
	(function() {}(
		$.fn.datepicker.dates['es'] = {
			days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
			daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
			daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
			months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
			monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
			today: "Hoy",
			monthsTitle: "Meses",
			clear: "Borrar",
			weekStart: 1,
			format: "dd/mm/yyyy"
		}
	));
</script>


<script type="text/javascript">
	//const input_search = document.querySelectorAll('input[aria-controls]');


	var getDate = function(input) {
		return new Date(input.date.valueOf());
	}
	$('#fecha_inical, #fecha_final').datepicker({
		//format: "dd/mm/yyyy",
		//format: "yyyy/mm/dd",
		language: 'es',
		//firstDay: 1
	}).datepicker("setDate", new Date());

	$('#fecha_final').datepicker({
		startDate: '+6d',
		endDate: '+36d',
	});

	$('#fecha_inical').datepicker({
		startDate: '+5d',
		endDate: '+35d',
	}).on('changeDate',
		function(selected) {
			$('#fecha_final').datepicker('clearDates');
			$('#fecha_final').datepicker('setStartDate', getDate(selected));
		});


	function set_fecha(e) {
		console.log(e.value)
	}
</script>


<script type="text/javascript">
	//const input_search = document.querySelectorAll('input[aria-controls]');


	/*
	var getDate = function(input) {
		return new Date(input.date.valueOf());
	}
	$('#fecha_inical, #fecha_final').datepicker({
		//format: "dd/mm/yyyy",
		//format: "yyyy/mm/dd",
		language: 'es',
		firstDay: 1
	});
	$('#fecha_final').datepicker({
		startDate: '+6d',
		endDate: '+36d',
	});

	$('#fecha_inical').datepicker({
		startDate: '+5d',
		endDate: '+35d',
	}).on('changeDate',
		function(selected) {
			$('#fecha_final').datepicker('clearDates');
			$('#fecha_final').datepicker('setStartDate', getDate(selected));
		});


	function set_fecha(e) {
		console.log(e.value)
	}
	*/
</script>





<!-- @yottabm seccion de datatables listado de productos -->
<script type="text/javascript">
	let moneda = "<?= $moneda; ?>";
	let fecha_inicial = "<?= $fecha_inicial; ?>";
	let fecha_final = "<?= $fecha_final; ?>";

	//console.log(id_almacen);

	$(document).ready(function() {

		var tableYottabm = $('#tableYottabm').DataTable({
			//"dom": 'Blfrtip',

			dom: `<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>
				
				<'row'<'col-sm-12'tr>>
				<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>`,


			/*
			`<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>
			<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>
			<'row'<'col-sm-12'tr>>
			<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>`,
			*/

			"order": [
				[0, "desc"]
			],
			//"lengthMenu": [ [10, 25, 50,100,200, -1], [10, 25, 50,100,200, "Todos"] ],
			"lengthMenu": [
				[15, 25, 50, 100, 200, 500],
				[15, 25, 50, 100, 200, 500]
			],
			//"buttons": [
			//	'colvis'
			//],
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
				//"url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"

				"sProcessing": "Procesando...",
				"sLengthMenu": "_MENU_",
				"sZeroRecords": "No se encontraron resultados",
				"sEmptyTable": "Ningún dato disponible en esta tabla",
				"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
				"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
				"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
				"sInfoPostFix": "",
				"sSearch": "",
				"search": "_INPUT_",
				"searchPlaceholder": "Buscar",
				"sUrl": "",
				"sInfoThousands": ",",
				"sLoadingRecords": "Cargando...",
				"oPaginate": {
					"sFirst": "Primero",
					"sLast": "Último",
					"sNext": ">",
					"sPrevious": "<"
				},
				"oAria": {
					"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
					"sSortDescending": ": Activar para ordenar la columna de manera descendente"
				}

			},

			"ajax": {
				"url": "?/ingresos/api_obtener_ingresos",
				"type": "POST",
				"data": {
					"id_almacen": `2`,
					"fecha_inicial": `${fecha_inicial}`,
					"fecha_final": `${fecha_final}`,
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
					//"visible": false,
				},
				{
					"className": "dt-right",
					"targets": [1],
					"title": 'Opciones',
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
					"title": 'Fecha Ingreso',
					"width": "5%",
					//"data": 'id_producto', //or 0
					"data": function(data, type, row, meta) {
						//return "Data 1: " + row.data().user_id + ". Data 2: " + row.data().user_name;
						return data;
					},
					"render": createPreciosActuales,
					"searchable": false,
					"orderable": false,
				},
				{
					"className": "text-center td-bold",
					"targets": [3],
					"title": 'Tipo',
					"width": "5%",
				},

				{
					"className": "text-wrap",
					"targets": [4],
					"title": `Monto Total`,
				},
				{
					"targets": [5],
					"title": 'Registros',
					"width": "5%",
				},

				{
					"targets": [6],
					"title": 'Almacen',
				},
				{
					"targets": [7],
					"title": 'Usuario',
				},
				{
					"targets": [8],
					"title": 'transitorio',
				},
				{
					"targets": [9],
					"title": 'des_transitorio',
				},

			],
		});


		$.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';

		new $.fn.dataTable.Buttons(tableYottabm, {
			buttons: [{
					"extend": "colvis",
					"text": "<i class='fa fa-search'></i> <span></span>",
					"className": "btn btn-white btn-primary btn-bold",
					columns: ':not(:first):not(:last)'
				},
				/*{
					"extend": "copy",
					"text": "<i class='fa fa-copy'></i> <span>Copiar</span>",
					"className": "btn btn-white btn-primary btn-bold",
				},
				{
				"extend": "csv",
				"text": "<i class='fa fa-database'></i> <span>Csv</span>",
				"className": "btn btn-white btn-primary btn-bold"
			}, */
				{
					"extend": "excel",
					"text": "<i class='fa fa-file-excel-o'></i> <span>Excel</span>",
					"className": "btn btn-white btn-primary btn-bold"
				}, {
					"extend": "pdf",
					"text": "<i class='fa fa-file-pdf-o'></i> <span>PDF</span>",
					"className": "btn btn-white btn-primary btn-bold"
				}, {
					"extend": "print",
					"text": "<i class='fa fa-print'></i> <span>Imprimir</span>",
					"className": "btn btn-white btn-primary btn-bold",
					autoPrint: true, //false
					message: 'This print was produced using the Print button for DataTables'
				}
			]
		});
		tableYottabm.buttons().container().appendTo($('.tableTools-container'));

		setTimeout(function() {
			$($('.tableTools-container')).find('a.dt-button').each(function() {
				var div = $(this).find(' > div').first();
				if (div.length == 1) div.tooltip({
					container: 'body',
					title: div.parent().text()
				});
				else $(this).tooltip({
					container: 'body',
					title: $(this).text()
				});
			});
		}, 500);


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
		return 'botones';

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
		return 'fechas';
		let arr_precio_actual = data.arr_precio_actual ? data.arr_precio_actual.split("|") : '';
		let template = '';
		for (let i = 0; i < arr_precio_actual.length; i++) {
			template += `<div style="margin-bottom: 5px;">${arr_precio_actual[i]}</div>`;
		}
		return template;
	}
</script>






<?php require_once show_template('footer-sidebar'); ?>