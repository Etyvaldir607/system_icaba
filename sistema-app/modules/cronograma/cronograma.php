<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);


//Revisar nuevas cuentas por pagar
$cronogramas = $db->select('*')->from('cronograma')->fetch();
foreach ($cronogramas as $nro => $cronograma) { 
	
	$fecha_bd = $cronograma['fecha'];
	$monto = $cronograma['monto'];
	$fecha_bd_nro = strtotime($cronograma['fecha']);
	$fecha_actual = strtotime($gestion_base);
	$sw_ingreso=true;
	$sw_while=true;
	
	while($fecha_actual > $fecha_bd_nro && $sw_while){
		$ingresos = $db->select('*')
						->from('cronograma_cuentas')
						->where('fecha',$fecha_bd)
						->where('cronograma_id',$cronograma['id_cronograma'])
						->fetch_first();
		$nroColumna=$db->affected_rows;
		
		if($nroColumna==0){
			$inserrt = array(
				'detalle' => $cronograma['detalle'],
				'fecha' => $fecha_bd,
				'monto' => $monto,
				'estado'=>"0",
				'empleado_id'=>"0",
				'tipo_pago'=>"",
				'fecha_pago'=>"0000-00-00",
				'cronograma_id' => $cronograma['id_cronograma']			
			);
			$db->insert('cronograma_cuentas', $inserrt);
		}

		$fecha = new DateTime($fecha_bd);
		switch($cronograma['periodo']){
			case "mensual":		
					$fecha->add(new DateInterval('P1M'));
					break;
			case "trimestral":		
					$fecha->add(new DateInterval('P3M'));
					break;
			case "anual":		
					$fecha->add(new DateInterval('P1Y'));
					break;
			case "pago unico":		
					$sw_while=false;
					break;
			default: 
					$sw_while=false;
					break;
		}
		$fecha_bd=$fecha->format('Y-m-d');
		$fecha_bd_nro = strtotime ($fecha_bd);		
		
		$sw_ingreso=false;
	}

	if($sw_ingreso){
		$fecha = new DateTime($fecha_bd);
		switch($cronograma['periodo']){
				case "mensual":		
						$fecha->add(new DateInterval('P1M'));
						break;
				case "trimestral":		
						$fecha->add(new DateInterval('P3M'));
						break;
				case "anual":		
						$fecha->add(new DateInterval('P1Y'));
						break;
				case "pago unico":		
						break;
				default: 
						break;
		}			
		$fecha_bd=$fecha->format('Y-m-d');
		$fecha_bd_nro = strtotime ($fecha_bd);
	}

	$ingresos = $db->select('*')
					->from('cronograma_cuentas')
					->where('fecha',$fecha_bd)
					->where('cronograma_id',$cronograma['id_cronograma'])
					->fetch_first();
		
	$nroColumna=$db->affected_rows;
	if($nroColumna==0){
		$inserrt = array(
			'detalle' => $cronograma['detalle'],
			'fecha' => $fecha_bd,
			'monto' => $monto,
			'estado'=>"0",
			'empleado_id'=>"0",
			'tipo_pago'=>"",
			'fecha_pago'=>"0000-00-00",
			'cronograma_id' => $cronograma['id_cronograma']			
		);
		$db->insert('cronograma_cuentas', $inserrt);
	}
}


















// Obtiene los ingresos
$ingresos = $db->select('c.*, MAX(cc.fecha) fechh, SUM(cc.estado) deuda, COUNT(cc.cronograma_id) nro_pagos')
				->from('cronograma c')
				->join('cronograma_cuentas cc','cronograma_id=id_cronograma','left')
				->order_by('c.detalle')
				->group_by('cc.cronograma_id')->fetch();


/*
		$ingresos = $db->select('*')
						->from('cronograma_cuentas')
						->where('fecha',$fecha_bd)
						->where('cronograma_id',$cronograma['id_cronograma'])
						->fetch_first();
		$nroColumna=$db->affected_rows;
*/		
		



// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = true;

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
		<strong>Cronograma</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/cronograma/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
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
				<th class="text-nowrap">Detalle</th>
				<th class="text-nowrap">Fecha</th>
				<th class="text-nowrap">Monto <?php echo $moneda; ?></th>
				<th class="text-nowrap">Periodo</th>
				<th class="text-nowrap">Estado</th>
				<th class="text-nowrap">Observacion</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Detalle</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Fecha</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Monto <?php echo $moneda; ?></th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Periodo</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Estado</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Observacion</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ingresos as $nro => $ingreso) {			
				$ing_fecha = $db->select('MIN(fecha) as fechh')
					->from('cronograma_cuentas')
					->where('cronograma_id',$ingreso['id_cronograma'])
					->where('estado',0)
					->fetch_first();				
			?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($ingreso['detalle']); ?></td>
				<td class="text-nowrap"><?php 
					if($ingreso['nro_pagos']>$ingreso['deuda']){
						echo escape(date_decode($ing_fecha['fechh'], $_institution['formato'])); 
					}
					else{
						echo escape(date_decode($ingreso['fechh'], $_institution['formato'])); 
					}
					?></td>
				<td class="text-nowrap" style="text-align: right;"><?= number_format($ingreso['monto'],2,"."," "); ?></td>
				<td class="text-nowrap"><?= escape($ingreso['periodo']); ?></td>
				<td class="text-nowrap"><?php 
										if($ingreso['nro_pagos']>$ingreso['deuda']){
											?><span class="text-danger"><b>Cuentas Pendientes (<?php echo ($ingreso['nro_pagos']-$ingreso['deuda']); ?> pagos)</b></span><?php
										}else{
											?><span class="text-success"><b>Pagos al dia</b></span><?php
										}										
				?></td>
				<td class="text-nowrap"><?php 
					if($ing_fecha['fechh']!=""){
						$fecha_actual = strtotime(date("Y-m-d"));
						$fecha_entrada = strtotime($ing_fecha['fechh']);
		
						if($fecha_actual > $fecha_entrada){
							echo "En Mora"; 						
						}
						else{
							echo "Pendiente"; 
						}						
					}
					else{
						echo "Al dia"; 
					}						
				?></td>
				
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/cronograma/listar/<?= $ingreso['id_cronograma']; ?>" data-toggle="tooltip" data-title="Ver detalle"><i class="glyphicon glyphicon-list-alt"></i></a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/cronograma/eliminar/<?= $ingreso['id_cronograma']; ?>" data-toggle="tooltip" data-title="Eliminar ingreso" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
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
			
			window.location = '?/ingresos/reporte_cuentas_pagar' + inicial_fecha + final_fecha;
		}
	});

	//$inicial_fecha.mask(mascara).datetimepicker({
	$inicial_fecha.datetimepicker({
		format: formato
	});

	//$final_fecha.mask(mascara).datetimepicker({
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
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>