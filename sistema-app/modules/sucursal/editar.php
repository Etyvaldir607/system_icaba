<?php

// Obtiene el sucursal actual
$id_sucursal = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene sucursal
$sucursal = $db->query("
	SELECT s.*,
	a.id_almacen,
	a.almacen
	FROM 
		inv_sucursal s
	LEFT JOIN inv_almacen_sucursales asu ON asu.sucursal_id = s.id_sucursal 
	LEFT JOIN  inv_almacenes a ON a.id_almacen = asu.almacen_id
	WHERE s.id_sucursal = $id_sucursal
	ORDER BY
		s.id_sucursal
")->fetch_first();

// Verifica si existe el almacén
if (!$sucursal) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// sucursala los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Modificar sucursal</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/sucursal/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_ver) { ?>
			<a href="?/sucursal/ver/<?= $sucursal['id_sucursal']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-search"></span>
				<span class="hidden-xs hidden-sm">Ver</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/sucursal/eliminar/<?= $sucursal['id_sucursal']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/sucursal/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form id="formulario" class="form-horizontal">
				<div class="form-group">
					<label for="sucursal" class="col-md-3 control-label">Sucursal:</label>
					<div class="col-md-9">
						<input type="hidden" value="<?= $sucursal['id_sucursal']; ?>" name="id_sucursal" data-validation="required">
						<input type="text" value="<?= $sucursal['sucursal']; ?>" name="sucursal" id="sucursal" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-#()_ ">
					</div>
				</div>
				<div class="form-group">
					<label for="direccion" class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $sucursal['direccion']; ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-/.,#º() ">
					</div>
				</div>
				<div class="form-group">
					<label for="telefono" class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $sucursal['telefono']; ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="principal" class="col-md-3 control-label">Almacen:</label>
					<div class="col-md-9">
						<select name="almacen" id="almacen" class="form-control">
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;@#()_\n " data-validation-optional="true"><?= escape($sucursal['descripcion']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary">
							<span class="glyphicon glyphicon-floppy-disk"></span>
							<span>Guardar</span>
						</button>
						<button type="reset" class="btn btn-default">
							<span class="glyphicon glyphicon-refresh"></span>
							<span>Restablecer</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {

	$.validate({
		modules: 'basic'
	});

	$('#telefono').selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$('#telefono').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$('#telefono').trigger('blur');
		},
		onBlur: function () {
			$('#telefono').trigger('blur');
		}
	});

	$(':reset').on('click', function () {
		$('#telefono')[0].selectize.clear();
	});
	
	$('.form-control:first').select();
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
});
</script>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	//@etysoft configuramos axios XMLHttpRequest en los headers de manera global en este modulo
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
	let $almacen = document.getElementById('almacen');
	let id_sucursal = "<?= $id_sucursal; ?>"

	//@etysoft funcion para cargar el select para asignar almacén
	function asignar_almacen() {
		axios.post('?/sucursal/api_almacenes_habilitados/'+id_sucursal ).then(({data}) => {
			let template = "";
			const arr_almacenes = data
			for (let i = 0; i < arr_almacenes.length; i++) {
				//@etysoft opciones disponibles
				template += (arr_almacenes[i]['id_sucursal'] == 9 )?
				`<option value="${arr_almacenes[i]['id_almacen']}" selected>${arr_almacenes[i]['almacen']}</option>`:
				`<option value="${arr_almacenes[i]['id_almacen']}">${arr_almacenes[i]['almacen']}</option>` ;
			}


			$almacen.innerHTML = template;
		})
		.catch((err) => {
			console.error(err);
		})
	}

	//@etysoft funcion para guardar el registro	
	function guardar() {
		let myForm = document.getElementById('formulario');
		let formData = new FormData(myForm);
		const uri = '?/sucursal/api_editar_sucursal';

		axios({
			method: "post",
			url: uri,
			data: formData,
		}).then(({
			data
		}) => {
			// si http recibe un status 200, entonces redireccionamos
			if (data.status === 200) {
				//realizada la transaccion reseteamos el formulario
				window.location= '?/sucursal/listar';
				// instancia notificación de éxito
				$.notify({
					title: data.title,
					icon: 'glyphicon glyphicon-info-sign',
					message: data.message
				}, {
					type: data.alert ,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom",
						align: "center" //align: "left"
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}else{
				// instancia notificación de error
				$.notify({
					title: data.title,
					icon: 'glyphicon glyphicon-info-sign',
					message: data.message
				}, {
					type: data.alert ,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom",
						align: "center" //align: "left"
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}
		});
		
	}

	asignar_almacen();
</script>
<script>
	/** validar y ejecutar guardar */
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