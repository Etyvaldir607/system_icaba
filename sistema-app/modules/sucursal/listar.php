<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// sucursal los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
// asignar empleado
$permiso_asignar = in_array('asignar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Sucursales</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $sucursal)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevas sucursales hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/sucursal/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/sucursal/crear" class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span>
				<span>Nuevo</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>

	<table id="tableES" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle width-collapse">#</th>
				<th class="text-nowrap align-middle width-collapse">Sucursal</th>
				<th class="text-nowrap align-middle">Dirección</th>
				<th class="text-nowrap align-middle width-collapse">Teléfono</th>
				<th class="text-nowrap align-middle">Descripción</th>
				<th class="text-nowrap align-middle width-collapse">Almacen</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
					<th class="text-nowrap align-middle width-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<!--  @etysoft aquí el contenido renderizado en JS -->
	</table>
	<!--  @etysoft modal para asignar empleado a un almacen -->
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
	<!--  @etysoft modal para asignar empleado a un almacen -->

</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>


<!-- @etysoft librerias actualizadas -->
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<!-- @etysoft funciones que no afectadas -->
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la sucursal?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/sucursal/crear';
				break;
			}
		}
	});
	<?php } ?>
	
});
</script>



<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>

<script>
	// inicializa variables de permisos
	let permiso_ver = "<?= $permiso_ver; ?>" || false;
	let permiso_editar = "<?= $permiso_editar; ?>" || false;
	let permiso_eliminar = "<?= $permiso_eliminar; ?>" || false;

	//inicializacion de funciones al iniciar
	loadDataTable();

	//inicializamos el datatable
	let dataTable = $('#tableES').DataFilter({
		name: 'sucursales',
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

	//funcion para cargar el datatable recibe como parametro id_sucursal
	async function loadDataTable() {
		const url = `?/sucursal/api_obtener_sucursales`;
		const data = await axios.post(url).then(response => response.data.sucursales).catch(err => console.error)
		const arr_sucursales = data;


		arr_sucursales.forEach((el, index) => {

			const btn_ver = permiso_ver?
							`<a href="?/sucursal/ver/${el.id_sucursal}" class="text-decoration-none" data-toggle="tooltip" title="Ver sucursal">
								<span class="glyphicon glyphicon-search"></span>
							</a>`:'';
			const btn_edit = permiso_editar?
							`<a href="?/sucursal/editar/${el.id_sucursal}" class="text-decoration-none" data-toggle="tooltip" title="Modificar sucursal">
								<span class="glyphicon glyphicon-edit"></span>
							</a>`: '';
			const btn_delete = permiso_eliminar? 
							`<a href="?/sucursal/eliminar/${el.id_sucursal}" class="text-decoration-none" data-toggle="tooltip" title="Eliminar sucursal" data-eliminar="true">
								<span class="glyphicon glyphicon-trash"></span>
							</a>`:'';

			const td = (permiso_ver || permiso_editar || permiso_eliminar )? `<td>${btn_ver}&nbsp;${btn_edit}&nbsp;${btn_delete}</td>`:''


			const template = `
					<tr>
						<td>${(index + 1)}</td>
						<td>${el.sucursal}</td>
						<td>${el.direccion}</td>
						<td><span class="label label-success">${el.telefono}</span></td>
						<td>${el.descripcion}</td>
						<td>${el.almacen}</td>
						${td}
					</tr>`;
			const tr = $(template);
			dataTable.row.add(tr[0]).draw();
		})

	}


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

<?php require_once show_template('footer-sidebar'); ?>