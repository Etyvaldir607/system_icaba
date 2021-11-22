<?php

// Obtiene las unidades
$unidades = $db->select('z.*')->from('inv_unidades z')->order_by('z.id_unidad')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Unidades</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $unidades)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevas unidades hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/unidades/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/unidades/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>

	<table id="tableES" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Unidad</th>
				<th class="text-nowrap">Sigla</th>
				<th class="text-nowrap">Cant. en Unidades</th>
				<th class="text-nowrap">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<!--  @etysoft aquí el contenido renderizado en JS -->
	</table>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>




<script>
$(function () {
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/unidades/crear';
				break;
			}
		}
	});
	<?php } ?>
});
</script>



<!-- @etysoft librerias actualizadas -->
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<!--  @etysoft sweetalert -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	// inicializa variables de permisos
	let permiso_ver = "<?= $permiso_ver; ?>" || false;
	let permiso_editar = "<?= $permiso_editar; ?>" || false;
	let permiso_eliminar = "<?= $permiso_eliminar; ?>" || false;

	//inicializacion de funciones al iniciar
	loadDataTable();

	//inicializamos el datatable
	let dataTable = $('#tableES').DataFilter({
		name: 'unidades',
		reports: 'xls|doc|pdf|html'
	});

	//recarga el tabla de unidades mediante ajax y lo actualiza
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

	//funcion para cargar el datatable recibe como parametro id_unidad
	async function loadDataTable() {
		const url = `?/unidades/api_obtener_unidades`;
		const data = await axios.post(url).then(response => response.data.unidades).catch(err => console.error)
		const arr_unidades = data;

		arr_unidades.forEach((el, index) => {

			const btn_ver = permiso_ver?
							`<a href="?/unidades/ver/${el.id_unidad}" class="text-decoration-none" data-toggle="tooltip" title="Ver unidad">
								<span class="glyphicon glyphicon-search"></span>
							</a>`:'';
			const btn_edit = permiso_editar?
							`<a href="?/unidades/editar/${el.id_unidad}" class="text-decoration-none" data-toggle="tooltip" title="Modificar unidad">
								<span class="glyphicon glyphicon-edit"></span>
							</a>`: '';
			const btn_delete = permiso_eliminar? 
							`<a href="#" class="text-decoration-none" onclick="eliminar_unidad(${el.id_unidad})" data-toggle="tooltip" title="Eliminar unidad" data-eliminar="true">
								<span class="glyphicon glyphicon-trash"></span>
							</a>`:'';

			const td = (permiso_ver || permiso_editar || permiso_eliminar )? `<td>${btn_ver}&nbsp;${btn_edit}&nbsp;${btn_delete}</td>`:''

			const template = `
					<tr>
						<td>${(index + 1)}</td>
						<td>${el.unidad}</td>
						<td>${el.sigla}</td>
						<td>${el.tamanio}</td>
						<td>${el.descripcion}</td>
						${td}
					</tr>`;
			const tr = $(template);
			dataTable.row.add(tr[0]).draw();
		})
	}

</script>

<!--  @etysoft eliminar unidad  -->
<script type="text/javascript">
	/** ejecuta eliminar unidad */
	function confirmar_eliminar_unidad(id) {
		axios.post(`?/unidades/api_eliminar_unidades/${id}`).then(({
			data
		}) => {
			if (data.status && (data.status === 200 || data.status === 400)) {
				reloadDataTable();
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

	/** modal de confirmación */
	function eliminar_unidad(id) {
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
				confirmar_eliminar_unidad(id);
			}
		})
	}
</script>



<?php require_once show_template('footer-sidebar'); ?>