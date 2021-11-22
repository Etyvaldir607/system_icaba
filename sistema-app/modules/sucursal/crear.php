<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// sucursal los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Crear Sucursal</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para regresar al listado de sucursales hacer clic en el siguiente botón:</div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<a href="?/sucursal/listar" class="btn btn-primary">
					<span class="glyphicon glyphicon-list-alt"></span>
					<span>Listado</span>
				</a>
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
						<input type="hidden" value="0" name="id_sucursal" data-validation="required">
						<input type="text" value="" name="sucursal" id="sucursal" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-#()_ ">
					</div>
				</div>
				<div class="form-group">
					<label for="direccion" class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<input type="text" value="" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-/.,#º() ">
					</div>
				</div>
				<div class="form-group">
					<label for="telefono" class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<input type="text" value="" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="principal" class="col-md-3 control-label">Almacen:</label>
					<div class="col-md-9">
						<select name="almacen" id="almacen" class="form-control" data-validation="required">
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;@#()_\n " data-validation-optional="true"></textarea>
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
	$(function() {
		$.validate({
			modules: 'basic'
		});

		$('#telefono').selectize({
			persist: false,
			createOnBlur: true,
			create: true,
			onInitialize: function() {
				$('#telefono').css({
					display: 'block',
					left: '-10000px',
					opacity: '0',
					position: 'absolute',
					top: '-10000px'
				});
			},
			onChange: function() {
				$('#telefono').trigger('blur');
			},
			onBlur: function() {
				$('#telefono').trigger('blur');
			}
		});

		$(':reset').on('click', function() {
			$('#telefono')[0].selectize.clear();
		});

		$('.form-control:first').select();
	});
</script>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	//@etysoft configuramos axios XMLHttpRequest en los headers de manera global en este modulo
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
	let $almacen = document.getElementById('almacen');

	//@etysoft funcion para cargar el select para asignar almacén
	function asignar_almacen() {
		axios.post('?/sucursal/api_almacenes_habilitados').then(({
				data
			}) => {
				let template = "";
				const arr_almacenes = data
				template += `<option value="">Seleccione Almacen</option>`;
				for (let i = 0; i < arr_almacenes.length; i++) {
					//@etysoft opciones disponibles
					template += `<option value="${arr_almacenes[i]['id_almacen']}">${arr_almacenes[i]['almacen']}</option>`;
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
		const uri = '?/sucursal/api_guardar_sucursal';

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