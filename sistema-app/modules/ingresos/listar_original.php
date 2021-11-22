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
?>

<?php require_once show_template('header-sidebar'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Listado de ingresos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar un ingreso hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar</span></button>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/ingresos/seleccionar_sucursal" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs"> Ingresar</span></a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/ingresos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
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
	<?php if ($ingresos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Fecha</th>
				<!--<th class="text-nowrap">Proveedor</th>-->
				<th class="text-nowrap">Tipo</th>
				<!--<th class="text-nowrap">Observaciones</th>-->
				<th class="text-nowrap">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Registros</th>
				<th class="text-nowrap">Almacen</th>
				<th class="text-nowrap">Usuario</th>
				<th class="text-nowrap">Transitorio</th>
				<th class="text-nowrap">Usuario que agregó a inventario</th>
                <th class="text-nowrap">Observacion trasitorio</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Fecha</th>
				<!--<th class="text-nowrap align-middle" data-datafilter-filter="true">Proveedor</th>-->
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Tipo</th>
				<!--<th class="text-nowrap align-middle" data-datafilter-filter="true">Descripción</th>-->
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Registros</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Almacen</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Usuario</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Transitorio</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Usuario que agregó a inventario</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Observacion trasitorio</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ingresos as $nro => $ingreso) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingreso['hora_ingreso']); ?></small></td>
				<!--<td class="text-nowrap"><?= escape($ingreso['nombre_proveedor']); ?></td>-->
				<td class="text-nowrap"><?= escape($ingreso['tipo']); ?></td>
				<!--<td class="width-md"><?= escape($ingreso['descripcion']); ?></td>-->
				<td class="text-nowrap text-right"><?= escape($ingreso['monto_total']); ?></td>
				<td class="text-nowrap text-right"><?= escape($ingreso['nro_registros']); ?></td>
				<td class="text-nowrap <?= ($ingreso['principal'] == 'S') ? 'info' : ''; ?>"><?= escape($ingreso['almacen']); ?></td>
				<td class="width-md"><?= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']); ?></td>				
				<td class="text-nowrap"><?php if($ingreso['transitorio']!=0){ ?><a href="javascript:;" onclick="cambiar_estado_transitorio(<?= $ingreso['id_ingreso']; ?>);" role="button" class="label label-danger" data-toggle="tooltip" title="Cambiar estado"><?php if($ingreso['transitorio']!=0){echo 'No esta en inventario';} ?></a><?php }else{?><span  class="label label-success" >En inventario</span> <?php } ?></td>
				<!-- <td class="text-nowrap"><?php if($ingreso['transitorio']!=0){ ?><a href="?/ingresos/activar/<?= $ingreso['id_ingreso']; ?>" class="label label-danger" data-toggle="tooltip" data-title="Cambiar estado" data-activar="true"><?php if($ingreso['transitorio']!=0){echo 'No esta en inventario';} ?></a><?php }else{?><span  class="label label-success" >En inventario</span> <?php } ?></td> -->
				<?php 
    				$id_user_t = $ingreso['user_cambiotransitorio'];
    				$user_t = $db->query("select * from sys_empleados where id_empleado ='$id_user_t'")->fetch_first(); 
				?>
                <td class="text-nowrap"><?= $user_t['nombres'].' '.$user_t['paterno'].' '.$user_t['materno']; ?></td>
                <td class="text-nowrap"><?= escape($ingreso['des_transitorio']); ?></td>
                
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/ingresos/ver/<?= $ingreso['id_ingreso']; ?>" data-toggle="tooltip" data-title="Ver detalle" title=""><i class="glyphicon glyphicon-list-alt"></i></a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/ingresos/editar/<?= $ingreso['id_ingreso']; ?>" data-toggle="tooltip" data-title="Editar ingreso" title="" data-editar="true"><i class="glyphicon glyphicon-edit"></i></a>
					<?php } ?>
					<?php if ($permiso_historiar) { ?>
					<a href="?/ingresos/historiar/<?= $ingreso['id_ingreso']; ?>" data-toggle="tooltip" data-title="Historial de modificaciones" title=""><i class="glyphicon glyphicon-list"></i></a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/ingresos/eliminar/<?= $ingreso['id_ingreso']; ?>" data-toggle="tooltip" data-title="Eliminar ingreso" title="" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ingresos registrados en la base de datos.</p>
	</div>
	<?php } ?>
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

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el ingreso y todo su detalle?', function (result) {
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
					window.location = '?/ingresos/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_cambiar) { ?>
	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');
	var $inicial_fecha = $('#inicial_fecha');
	var $final_fecha = $('#final_fecha');

	$.validate({
		form: '#form_fecha',
		modules: 'date',
		onSuccess: function () {
			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/ingresos/listar' + inicial_fecha + final_fecha;
		}
	});

	$inicial_fecha.datetimepicker({
		format: formato
	});

	$final_fecha.datetimepicker({
		format: formato
	});

	$inicial_fecha.on('dp.change', function (e) {
		$final_fecha.data('DateTimePicker').minDate(e.date);
	});
	
	$final_fecha.on('dp.change', function (e) {
		$inicial_fecha.data('DateTimePicker').maxDate(e.date);
	});

	var $form_fecha = $('#form_fecha');
	var $modal_fecha = $('#modal_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('show.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('[data-aceptar]').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$modal_fecha.find('[data-aceptar]').on('click', function () {
		$form_fecha.submit();
	});

	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ingresos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'ingresos',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
function cambiar_estado_transitorio(id) {
	bootbox.confirm('&iquest;Está seguro que desea cambiar el estado a <b>En inventario</b>?', function (result) {
		if(result){			
			window.location = '?/ingresos/activar/'+id;
		}
	});
}
</script>
<?php require_once show_template('footer-sidebar'); ?>