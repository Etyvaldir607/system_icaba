<?php
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);

$permiso_asignar = in_array('asignar', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>

<style>
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

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Almacenes</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $almacenes)) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón: </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<?php if ($permiso_imprimir) { ?>
					<a href="?/almacenes/imprimir" target="_blank" class="btn btn-info">
						<span class="glyphicon glyphicon-print"></span>
						<span class="hidden-xs">Imprimir</span>
					</a>
				<?php } ?>
				<?php if ($permiso_crear) { ?>
					<a href="?/almacenes/crear" class="btn btn-primary">
						<span class="glyphicon glyphicon-plus"></span>
						<span>Nuevo</span>
					</a>
				<?php } ?>
			</div>
		</div>
		<hr>
	<?php } ?>

	<table id="tableJQ" class="table table-bordered stripe row-border order-column display nowrap" style="width:100%">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle">#</th>
				<th class="text-nowrap align-middle">Almacén</th>
				<th class="text-nowrap align-middle">Dirección</th>
				<th class="text-nowrap align-middle">Teléfono</th>
				<th class="text-nowrap align-middle">Descripción</th>
				<th class="text-nowrap align-middle">Encargado</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
					<th class="text-nowrap align-middle column-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
	</table>

	<!-- modal asigancion de empleado -->
	<div class="modal fade" id="modal_asignacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabe2" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header" id="title_modal_asignacion"></div>
				<div class="modal-body">

					<form id="formulario" class="form-horizontal" autocomplete="off">

						<input type="hidden" id="almacen_id" value="" name="almacen_id" data-validation="required">

						<div class="form-group">
							<label for="empleado" class="col-sm-2 control-label">Empleado:</label>
							<div class="col-sm-10">
								<select name="empleado_id" id="empleado" class="form-control" data-validation="required"></select>
							</div>
						</div>

						<div class="modal-footer" style="padding-bottom: 0;">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span id="btn_submit">Asignar</span>
							</button>
							<button type="reset" class="btn btn-default" data-dismiss="modal" onclick="reset()">
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
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>





<script>
	$(function() {
		var $formulario = $('#formulario');
		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function() {
				guardar();
			}
		});
		$formulario.on('submit', function(e) {
			e.preventDefault();
		});
	});
</script>



<script>

	let permiso_asignar = "<?= $permiso_asignar; ?>" || false;
	let permiso_ver = "<?= $permiso_ver; ?>" || false;
	let permiso_editar = "<?= $permiso_editar; ?>" || false;
	let permiso_eliminar = "<?= $permiso_eliminar; ?>" || false;

	

	//inicializacion de funciones al iniciar
	loadDataTable();
	obtener_empleado();

	//inicializamos el datatable
	let dataTable = $('#tableJQ').DataFilter({
		name: 'almacenes',
		reports: 'xls|doc|pdf|html'

	});

	//recarga el tabla de productos mediante ajax y lo actualiza
	function reloadDataTable() {
		clearDataTable();
		loadDataTable();
	};

	//reiniciar sort order
	function resetSorting() {
		//reinicia el order actual al inicial
		dataTable.order([0, "desc"]).draw();
	}

	//limpia la tabla funcion de datatable
	function clearDataTable() {
		dataTable.clear();
	}

	//funcion para cargar el datatable recibe como parametro id_almacen y por defecto le enviamos el almacen principal
	async function loadDataTable() {
		const url = `?/almacenes/api_obtener_almacenes`;
		const data = await axios.post(url).then(response => response.data.almacenes).catch(err => console.error)
		const arr_almacenes = data;


		arr_almacenes.forEach((el, index) => {

			const btn_asignar = permiso_asignar?
							`<span data-toggle="modal" data-target="#modal_asignacion">
								<a href="#" name="btn_asignacion" onclick="asignar_almacen({'almacen_id':'${el.id_almacen}','almacen':'${el.almacen}','encargado':'${el.encargado}'})" 
									data-toggle="tooltip" data-placement="top" title="asignar empleado">
									<span class="glyphicon glyphicon-font"></span>
								</a>
							</span>`:'';
			const btn_ver = permiso_ver?
							`<a href="?/almacenes/ver/${el.id_almacen}" class="text-decoration-none" data-toggle="tooltip" title="Ver almacén">
								<span class="glyphicon glyphicon-search"></span>
							</a>`:'';
			const btn_edit = permiso_editar?
							`<a href="?/almacenes/editar/${el.id_almacen}" class="text-decoration-none" data-toggle="tooltip" title="Modificar almacén">
								<span class="glyphicon glyphicon-edit"></span>
							</a>`: '';
			const btn_delete = permiso_eliminar? 
							`<a href="?/almacenes/eliminar/${el.id_almacen}" class="text-decoration-none" data-toggle="tooltip" title="Eliminar almacén" data-eliminar="true">
								<span class="glyphicon glyphicon-trash"></span>
							</a>`:'';

			const td = ( permiso_asignar || permiso_ver || permiso_editar || permiso_eliminar )? `<td>${btn_asignar}&nbsp;${btn_ver}&nbsp;${btn_edit}&nbsp;${btn_delete}</td>`:''

			const encargado = (el.encargado != '')?
							`${el.encargado}&nbsp;
							<a href="?/almacenes/api_eliminar_asignacion/${el.empleado_almacen_id}" class="text-decoration-none" data-toggle="tooltip" title="Quitar asignacion almacen" data-eliminar-asignacion="true">
								<span class="glyphicon glyphicon-remove"></span>
							</a>`:''; 

			const template = `
					<tr>
						<td>${(index + 1)}</td>
						<td>${el.almacen}</td>
						<td>${el.direccion}</td>
						<td><span class="label label-success">${el.telefono}</span></td>
						<td>${el.descripcion}</td>
						<td>${encargado}</td>
						${td}
					</tr>`;

			const tr = $(template);
			dataTable.row.add(tr[0]).draw();
		})

	}

	let $title_modal_asignacion = document.getElementById('title_modal_asignacion');
	let $input_almacen_id = document.getElementById('almacen_id');
	let $btn_submit = document.getElementById('btn_submit');

	function asignar_almacen(el) {
		const empleado_actual = (el.encargado != '') ? `</br><strong> Encargado actual :</strong> ${el.encargado} ` : '';
		const title = `<h3 class="modal-title">ASIGNACION DEL ALMACEN </br>${(el.almacen).toUpperCase()}</h3>${empleado_actual}`
		$title_modal_asignacion.innerHTML = title;
		$input_almacen_id.value = el.almacen_id;
		const btn_label = (el.encargado == '') ? 'Asignar' : 'Actualizar';
		$btn_submit.innerText = btn_label;
	}



	let $select_empleado = document.getElementById('empleado');
	async function obtener_empleado() {
		const url = `?/almacenes/api_obtener_empleados`;
		const data = await axios.post(url).then(response => response.data.empleados).catch(err => console.error)
		const arr_empleados = data;
		let template = '<option value="">SELECCIONAR...</option>';
		arr_empleados.forEach((el, index) => {
			template += `<option value="${el.id_empleado}">${el.empleado}</option>`;
		})
		$select_empleado.innerHTML = template;
	}

	function reset() {
		document.getElementById("formulario").reset();
	}

	function guardar() {

		let myForm = document.getElementById('formulario');
		let formData = new FormData(myForm);
		$("[data-dismiss=modal]").trigger({
			type: "click"
		});
		axios({
			method: "post",
			url: `?/almacenes/api_guardar_almacen_empleado`,
			data: formData,
		}).then(({
			data
		}) => {
			if (data.status && (data.status === 200 || data.status === 201)) {
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
						from: "top", //from: "bottom",
						align: "right" //align: "left"
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
				reloadDataTable();
				obtener_empleado();
			}
		});
	}
</script>


<script>
	$(function() {

		<?php if ($permiso_asignar) { ?>
			$('[data-eliminar-asignacion]').on('click', function(e) {
				e.preventDefault();
				var url = $(this).attr('href');
				bootbox.confirm('Está seguro que desea quitar la asignacion de almacén?', function(result) {
					if (result) {
						window.location = url;
					}
				});
			});
		<?php } ?>

		<?php if ($permiso_eliminar) { ?>
			$('[data-eliminar]').on('click', function(e) {
				e.preventDefault();
				var url = $(this).attr('href');
				bootbox.confirm('Está seguro que desea eliminar el almacén?', function(result) {
					if (result) {
						window.location = url;
					}
				});
			});
		<?php } ?>

		<?php if ($permiso_crear) { ?>
			$(window).bind('keydown', function(e) {
				if (e.altKey || e.metaKey) {
					switch (String.fromCharCode(e.which).toLowerCase()) {
						case 'n':
							e.preventDefault();
							window.location = '?/almacenes/crear';
							break;
					}
				}
			});
		<?php } ?>
	});
</script>
<?php require_once show_template('footer-sidebar'); ?>