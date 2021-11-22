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
$permiso_cambiar_rango = in_array('cambiar_rango', $permisos);

// @etysoft  obtiene el id del usuario actual
$id_user_current = $_user['id_user'];
// @etysoft  obtiene el almacen asignado al usuario actual
$almacenes = $db->query("
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
")->fetch();



?>
<?php require_once show_template('header-sidebar-yottabm'); ?>

<!-- @etysoft estilo para el modal de swal -->
<style>
	.swal2-styled.swal2-confirm, .swal2-styled.swal2-cancel {
		border: 0;
		border-radius: 0.25em;
		background: initial;
		background-color: #7367f0;
		color: #fff;
		font-size: 1.5em;
	}
</style>
<style>
	span.dt-down-arrow {
		color: #fff !important;
	}

	.content-space-between {
		display: flex;
		justify-content: space-between;
	}

	.content-space-right {
		display: flex;
		justify-content: right;
	}

	.td-bold {
		font-weight: bold
	}

	.btn-group,
	.btn-group-vertical {
		margin-bottom: 15px;
		float: right;
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
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Listado de egresos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar_rango || $permiso_crear || $permiso_imprimir) { ?>
        <div class="row">
            <!-- @etysoft seleccionar rango de fechas para visualizar -->
			<?php if ($permiso_cambiar_rango) { ?>
				<div class="col-6 col-md-6 col-lg-3 hidden-xs d-block pt-2 pb-2">
					<label for="almacen" class="control-label">Fecha Inicio:</label>
					<div class="input-group date form_datetime">
						<input type='text' class="form-control" name="fecha_inicial" id="fecha_inicial" value="" onkeydown="return false"/>
						<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
					</div>
				</div>
				<div class="col-6 col-md-6 col-lg-3 hidden-xs d-block pt-2 pb-2">
					<label for="almacen" class="control-label">Fecha Fin:</label>
					<div class="input-group date form_datetime">
						<input type='text' class="form-control" name="fecha_final" id="fecha_final" value="" onkeydown="return false"/>
						<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
					</div>
				</div>
			<?php } else {?>
				<div class="col-12 col-md-12 col-lg-6 hidden-xs d-block pt-2 pb-2">
				</div>
			<?php } ?>

            <!-- @etysoft seleccionar almacen a visualizar -->
            <div class="col-6 col-md-6 col-lg-3 hidden-xs d-block pt-2 pb-2">
                <label for="almacen" class="control-label">Almacen:</label>
                <div id="change_almacen" class="form-group">
                </div>
            </div>

            <div class="col-6 col-md-6 col-lg-3 hidden-xs d-block pt-2 pb-2">
				<div class="text-right content-space-right" style="padding-top: 24px;">
					<div class="r-auto">
						<?php if ($permiso_imprimir) { ?>
							<a href="?/egresos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span> Imprimir</span></a>
						<?php } ?>
					</div>
					<div class="pl-5 r-0">
						<?php if ($permiso_crear) { ?>
							<a href="?/egresos/seleccionar_sucursal" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
						<?php } ?>
					</div>
				</div>
            </div>
		</div>
		<hr>
	<?php } ?>
	<div class="clearfix" style="margin-bottom: 10px;">
		<div class="pull-right tableTools-container"></div>
	</div>
	<table id="table_list_egresosES" class="table table-striped table-bordered nowrap display" style="width:100%"></table>

</div>


<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

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

<!-- datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- iconos font awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />


<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- @etysoft datable -->
<script type="text/javascript">
	let moneda = "<?= $moneda; ?>";
    /** recuperamos id_alamcen como variable global */
	let id_almacen = "";
    /** recuperamos fechas por defecto como variables globales */
	let fecha_inicial = "<?= date_encode($fecha_inicial); ?>";
    let fecha_final = "<?= date_encode($fecha_inicial); ?>";
	
	// cargar el datapicker
	document.addEventListener("DOMContentLoaded", function(event) {
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
	});

	// cargar el datatable
	function loadDataTable(id_almacen, fecha_inicial, fecha_final){
		var table_list_egresosES = $('#table_list_egresosES').DataTable({
			"order": [
				[0, "desc"]
			],
			//"lengthMenu": [ [10, 25, 50,100,200, -1], [10, 25, 50,100,200, "Todos"] ],
			"lengthMenu": [
				[15, 25, 50, 100, 200, 500],
				[15, 25, 50, 100, 200, 500]
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
				"url": "?/egresos/api_obtener_egresos",
				"type": "POST",
				"data": {
					"id_almacen": `${id_almacen}`,
					"fecha_inicial": `${fecha_inicial}`,
					"fecha_final": `${fecha_final}`
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
					"width": "6%",
					//"data": 'id_egreso', //or 0
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
					"title": 'FECHA Y HORA DE EGRESO',
					"width": "7%",
				},
				{
					"targets": [3],
					"title": 'TIPO',
					"width": "10%",
				},
				{
					"targets": [4],
					"title": 'DESCRIPCION',
					"visible": false,
				},
				{
					"targets": [5],
					"title": 'MONTO TOTAL',

				},
				{
					"targets": [6],
					"title": 'NÚMERO DE REGISTROS',

				},
				{
					"targets": [7],
					"title": 'ALMACEN',
				},
				{
					"targets": [8],
					"title": 'USUARIO',
				}
			],
		});
		$.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';

		new $.fn.dataTable.Buttons(table_list_egresosES, {
			buttons: [{
				"extend": "colvis",
				"text": "<i class='fa fa-search'></i> <span></span>",
				"className": "btn btn-white btn-primary",
				columns: ':not(:first):not(:last)'
			}, {
				"extend": "copy",
				"text": "<i class='fa fa-copy'></i> <span>Copiar</span>",
				"className": "btn btn-white btn-primary",
			}, {
				"extend": "excel",
				"text": "<i class='fa fa-file-excel-o'></i> <span>Excel</span>",
				"className": "btn btn-white btn-primary"
			}, {
				"extend": "pdf",
				"text": "<i class='fa fa-file-pdf-o'></i> <span>PDF</span>",
				"className": "btn btn-white btn-primary"
			}, {
				"extend": "print",
				"text": "<i class='fa fa-print'></i> <span>Imprimir</span>",
				"className": "btn btn-white btn-primary",
				autoPrint: true,
				message: 'This print was produced using the Print button for DataTables'
			}]
		});
		table_list_egresosES.buttons().container().appendTo($('.tableTools-container'));

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
	}

	let permiso_ver = "<?= $permiso_ver; ?>" || false;
	let permiso_editar = "<?= $permiso_editar; ?>" || false;
	let permiso_eliminar = "<?= $permiso_eliminar; ?>" || false;

	function createButtons(data) {
		// console.log(data)
		const btn_ver = permiso_ver ?
			`<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Ver egreso"
				onclick="show_egreso(${data.id_egreso})" >
				<span class="glyphicon glyphicon-search"></span>
			</button>` : '';
		const btn_edit = permiso_editar ?
			`<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Editar egreso"
				onclick="edit_egreso(${data.id_egreso})" >
				<span class="glyphicon glyphicon-edit"></span>
			</button>` : '';
		const btn_delete = permiso_eliminar ?
			`<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Eliminar egreso"
				onclick="delete_egreso(${data.id_egreso})" >
				<span class="glyphicon glyphicon-trash"></span>
			</button>` : '';
		
		const btn_all = ( permiso_ver || permiso_editar || permiso_eliminar) ?
			`<div class="row content-space-between pl-2 pr-2">
				${btn_ver}${btn_edit}${btn_delete}
			</div>` : '';
		return btn_all;
	}
</script>


<!-- @etysoft renderiza solo el almacen asignados por empleado, en caso de super usuario o rol asignado renderiza un select -->
<script type="text/javascript">
	// @etysoft renderiza almacenes
	render_almacenes();
	//@etysoft funcion para obtener alamacenes 
	function render_almacenes() {
		// obteniene el id del usuario que esta en sesión
		let id_rol_current_user = "<?= $_user['rol_id']; ?>";
		let id_current_user = "<?= $id_user_current; ?>";
		// console.log(id_rol_current_user)
		let $id_form = document.getElementById("change_almacen");
		axios.post(`?/egresos/api_obtener_almacen_empleado/${id_current_user}`).then(({
			data
		}) => {
			const arr_almacenes = data;
			let template =``;
			// console.log(arr_almacenes)
			
			if ((id_rol_current_user == 1 || id_rol_current_user == 2 ) && arr_almacenes.length > 1 ) {
				const arr_almacenes = data;
				//@etysoft funcion para cargar el select con los alamacenes 
				template = `<select name="almacen" id="almacen" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100" onchange=
							set_almacen_select(this)
							>`;
								template += `<option value="">Seleccione Almacen</option>`;
							for (let i = 0; i < arr_almacenes.length ; i++) {
								//@etysoft opciones disponibles
								if (i===0){
									template += `<option value="${arr_almacenes[i]['id_almacen']}" selected> ${arr_almacenes[i]['almacen'] }</option>`;
								}else{
									template += `<option value="${arr_almacenes[i]['id_almacen']}" > ${arr_almacenes[i]['almacen'] }</option>`;
								}
							}
				template +=`</select>`;
				$id_form.innerHTML = template;
				set_almacen_init();
			}else{
				template += `
					<input type="text" class="form-control" value="${(arr_almacenes[0]['id_almacen'] === 0)  ? '' : arr_almacenes[0]['almacen'] }" readonly>	
					<input id="almacen" type="hidden" name="almacen" value="${arr_almacenes[0]['id_almacen']}">
				`;
				$id_form.innerHTML = template;
				set_almacen();
			}
		})
		.catch((err) => {
			// console.error(err);
		});
	}

	// etysoft cargamos el datatable inicial
	function set_almacen_init(){
		let lista = document.getElementById('almacen');
		id_almacen = lista.options[lista.selectedIndex].value;
		// cargamos el datatable inicial
		loadDataTable(id_almacen, fecha_inicial, fecha_final)
	}

	/** etysoft select - habilitado solo para roles ['Super Usuario', 'Administrador'] */
	function set_almacen_select(e){
		let lista = document.getElementById('almacen');
		let actual = lista.parentElement.parentElement;
		id_almacen = e.options[lista.selectedIndex].value;
		// reiniciamos datatable
		reloadDataTable(id_almacen, fecha_inicial, fecha_final);
	}

	//recarga el tabla de productos mediante ajax y lo actualiza
	function reloadDataTable(id_almacen, fecha_inicial, fecha_final) {
		// detruimos el datatable actual
		destroyDataTable();
		// cargamos el datatable inicial
		loadDataTable(id_almacen, fecha_inicial, fecha_final);
	};

	//limpia la tabla funcion de datatable
	function destroyDataTable() {
		$('#table_list_egresosES').DataTable().destroy();
	}

	/** etysoft select - habilitado solo para empleados */
	function set_almacen(){
		let elemento = document.getElementById('almacen');
		id_almacen = elemento.value;
		loadDataTable(id_almacen, fecha_inicial, fecha_final);
	}

</script>


<!-- @etysoft seleccionar fecha y realizar filtrado por fechas -->
<script type="text/javascript">
	//init_fecha();
	let formato = "<?= $formato_textual; ?>";
	let format_in_sql = "YYYY-MM-DD";
	let mascara = "<?= $formato_numeral; ?>" ;
	let gestion = "<?= date_decode($gestion_base, $_institution['formato']); ?>";

	console.log(formato, mascara, gestion )

	var getDate = function(input) {
		return new Date(input.date.valueOf());
	}

	$('#fecha_inicial').datepicker({
		format: formato,
		language: 'es',
		maxDate: $.now()
	}).datepicker("setDate", new Date());

	$('#fecha_final').datepicker({
		format: formato,
		language: 'es',
		maxDate: $.now()
	}).datepicker("setDate", new Date());

	$('#fecha_inicial').datepicker({
		startDate: '+5d',
		endDate: '+35d',
	}).on('changeDate', function(selected) {

		fecha_inicial = getDate(selected);
		fecha_inicial = moment(fecha_inicial).format(format_in_sql);

		if (fecha_inicial <= fecha_final ) {
			console.log("pasa")
			reloadDataTable(id_almacen, fecha_inicial, fecha_final)
			$('#fecha_final').datepicker('setStartDate', getDate(selected));
		}else{
			$('#fecha_final').datepicker('setStartDate', getDate(selected));
			$('#fecha_final').datepicker('setDate', getDate(selected))
		}

		// console.log(id_almacen, fecha_inicial, fecha_final)
	});
	

	$('#fecha_final').datepicker({
		startDate: '+6d',
		endDate: '+36d',
	}).on('changeDate', function(selected) {
		// $('#fecha_inicial').datepicker('clearDates');
		$('#fecha_inicial').datepicker('setEndDate', getDate(selected));
		fecha_final = getDate(selected);
		fecha_final = moment(fecha_final).format(format_in_sql);
		// console.log(id_almacen, fecha_inicial, fecha_final)
		reloadDataTable(id_almacen, fecha_inicial, fecha_final)
	});
</script>


<!-- @etysoft redirecciones  -->
<script type="text/javascript">

	// @etysoft ver egreso
	function show_egreso(id){
		window.location = `?/egresos/ver/${id}`;
	}

	// @etysoft editar egreso
	function edit_egreso(id){
		window.location = `?/egresos/editar/${id}`;
	}
</script>


<!-- @etysoft eliminar egreso  -->
<script type="text/javascript">

	function delete_egreso(id) {

		Swal.fire({
			title: 'Estas seguro de eliminar este registro?',
			text: "No podrás revertir esta accion!",
			icon: 'warning',
			customClass: 'swal-wide',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Si, eliminar!',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				confirmar_eliminar_egreso(id);
			}
		})
	}

	function confirmar_eliminar_egreso(id) {
		let formData = new FormData();
        formData.append("id_egreso", id);
		axios({
			method: "post",
			url: `?/egresos/api_eliminar_egreso`,
			data : formData
		}).then(({
			data
		}) => {
			if (data.status && data.status === 201 ) {
                //@etysoft recargamos el datatble
				$('#table_list_egresosES').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: icon,
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
				// console.log(data)
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
		});
	}
</script>



<?php require_once show_template('footer-sidebar'); ?>