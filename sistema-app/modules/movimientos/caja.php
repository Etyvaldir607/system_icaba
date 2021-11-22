 
<?php 

$ultimo_registro_caja = $db->query("SELECT * FROM `inv_caja` WHERE fecha = (SELECT MAX(fecha) AS fecha FROM inv_caja) AND id_caja = (SELECT MAX(id_caja) AS fecha FROM inv_caja)")->fetch_first(); 

$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

$historial_caja  = $db->from('inv_caja')->order_by('fecha','desc')->fetch();  
?>

<?php require_once show_template('header-sidebar'); ?>


<div class="panel-heading">
	<h3 class="panel-title" data-header="true">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Historial de Cierre de Caja</strong>
	</h3>
</div>
<div class="panel-body" data-servidor="">
	<div class="row">		
		<div class="col-xs-6">
			<div class="text-label hidden-xs">Seleccionar acción:</div>
			<div class="text-label visible-xs-block">Acciones:</div>
		</div>
		<div class="col-xs-6 text-right">
			<?php if ($ultimo_registro_caja): ?>
				<?php if ($ultimo_registro_caja['estado'] == 'CAJA' || $ultimo_registro_caja['estado'] == 'INICIO'): ?>
					<a href="?/movimientos/cerrar_caja" data-toggle="modal" class="btn btn-danger" data-backdrop="static" data-keyboard="false"><span class="glyphicon glyphicon-folder-close"></span> Cerrar Caja</a>					
				<?php endif ?>				
				<?php else: ?>
					<a href="?/movimientos/balance_caja" data-toggle="modal" class="btn btn-success" data-backdrop="static" data-keyboard="false"><span class="glyphicon glyphicon-folder-close"></span> Generar Balance de Caja</a>	
				<?php endif ?>
			</div>
		</div>
		<hr>
		<div class="well">
			<p class="lead margin-none">
				<b>Empleado:</b>
				<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
			</p>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive margin-none">
					<table id="tabla_caja" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
						<thead>
							<tr class="active">
								<th class="text-middle">Nº</th>
								<th class="text-middle">FECHA</th>
								<th class="text-nowrap text-middle">TOTAL INGRESOS</th>
								<th class="text-nowrap text-middle">TOTAL EGRESOS </th>
								<th class="text-middle text-middle">TOTAL SALDOS</th>
								<th class="text-middle text-middle">TOTAL FINAL</th>
								<th class="text-middle text-middle">ESTADO</th>
								<th class="text-middle text-middle">DOCUMENTOS ANULADOS</th>
							</tr>
						</thead>
						<tfoot>
							<tr class="active">
								<th class="text-middle">Nº</th>
								<th class="text-middle">FECHA</th>
								<th class="text-nowrap text-middle">TOTAL INGRESOS</th>
								<th class="text-nowrap text-middle">TOTAL EGRESOS </th>
								<th class="text-middle text-middle">TOTAL SALDOS</th>
								<th class="text-middle text-middle">TOTAL FINAL</th>
								<th class="text-middle text-middle">ESTADO</th>
								<th class="text-middle text-middle">DOCUMENTOS ANULADOS</th>
							</tr>
						</tfoot>
						<tbody>	
							<?php foreach ($historial_caja as $key => $historial): ?>
								<?php 	if($historial['estado'] == 'INICIO')
								$clase = 'success';
								elseif ($historial['estado'] == 'CIERRE') 									
									$clase = 'danger';
								else
									$clase = '';
								?>
								<tr class="<?= $clase; ?>" >
									<td class="text-nowrap"><?= $historial['id_caja'] ?></td>
									<td class="text-nowrap"><?= date_decode($historial['fecha'],$_institution['formato']) ?> <font size="1"> <?= $historial['hora_caja'] ?></font></td>
									<td class="text-nowrap"><?= $historial['total_ingresos'] ?></td>
									<td class="text-nowrap"><?= $historial['total_egresos'] ?></td>
									<td class="text-nowrap"><?= $historial['total_saldo'] ?></td>
									<td class="text-nowrap <?= ($historial['total_total'] < 0) ? 'danger':'' ?>"><?= $historial['total_total'] ?></td>
									<td class="text-nowrap"><?= $historial['estado'] ?></td>
									<td class="text-nowrap">
										<?php $anulados = $db->query("select * from inv_egresos WHERE estado = 'Anulado' and fecha_egreso = '" . $historial['fecha'] . "'")->fetch();
										if ($anulados) {																		
											foreach ($anulados as $key => $anulado) {
												if ($anulado['id_egreso']) { ?>
													<p>
														<span class="text-success">Monto Total: <?= $anulado['monto_total'] .' ' . escape($moneda); ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
														<span>Nº <?= $anulado['nro_factura'] ?> </span>
														<span><?= ($anulado['descripcion'] == 'Orden de compra')? 'Nota de Remisión':$anulado['descripcion']; ?> </span>
														<span>Cliente: <?= $anulado['nombre_cliente'] ?></span>
													</p>
												<?php 	}
											} 
										}else{ ?>
											<span>Sin anular.</span>
										<?php } ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>					
					</table>
				</div>
			</div>
		</div>
	</div>




	<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
	<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
	<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
	<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
	<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
	<script src="<?= js; ?>/jquery.base64.js"></script>
	<script src="<?= js; ?>/pdfmake.min.js"></script>
	<script src="<?= js; ?>/vfs_fonts.js"></script>
	<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
	<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

	<script>
		var table = $('#tabla_caja').DataFilter({
			filter: true,
			name: 'productos',
			reports: 'excel|word|pdf|html',
			size: 8
		});
	</script>

	<?php require_once show_template('footer-sidebar'); ?>