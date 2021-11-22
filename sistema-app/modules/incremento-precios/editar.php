<?php

$id_rol = (isset($params[0])) ? $params[0] : 0;

// Obtiene los productos
$unidades = $db->query("SELECT *
						FROM inv_unidades
						ORDER BY tamanio
						")->fetch();

?>
<?php require_once show_template('header-sidebar'); ?>
<style>
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
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="<?= ICON_PANEL; ?>"></span>
		<strong>Lista de Precios segun al Rol</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($unidades) { ?>
	<form id="form_precio" action="?/incremento-precios/guardar"  method="post">
		<input type="text" value="<?php echo $id_rol; ?>" name="id_rol">	
		<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap">#</th>
					<th class="text-nowrap">Sigla</th>
					<th class="text-nowrap">Unidad</th>
					<th class="text-nowrap">Tamaño</th>
					<th class="text-nowrap">Incremento %</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Sigla</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Unidad</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Tamaño</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Incremento</th>
				</tr>
			</tfoot>
			<tbody>
				<?php 
				foreach ($unidades as $nro => $unidad) { 

					$incremento = $db->query("SELECT *
											FROM inv_precios_roles
											WHERE unidad_id='".$unidad['id_unidad']."' AND rol_id='".$id_rol."'
											")->fetch_first();
				?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape($unidad['sigla']); ?></td>
						<td class="width-lg"><?= escape($unidad['unidad']); ?></td>
						<td class="text-nowrap"><?= escape($unidad['tamanio']); ?></td>
						<td class="text-nowrap text-right"><input type="text" style="width: 100%;" <?php 
							
							echo ' name="incremento_'.$unidad['id_unidad'].'"';
							
							if($incremento){
								echo ' value="'.$incremento['incremento'].'" ';
							}
							else{
								echo ' value="0" ';	
							}
						?>></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<button type="submit" class="btn btn-primary">
			<span class="glyphicon glyphicon-ok"></span>
			<span>Guardar</span>
		</button>
		<button type="button" class="btn btn-default" data-cancelar="true">
			<span class="glyphicon glyphicon-remove"></span>
			<span>Cancelar</span>
		</button>
	</form>

	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen unidades registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>

<script src="<?= JS; ?>/jquery.dataTables.min.js"></script>
<script src="<?= JS; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= JS; ?>/jquery.base64.js"></script>
<script src="<?= JS; ?>/pdfmake.min.js"></script>
<script src="<?= JS; ?>/vfs_fonts.js"></script>
<script src="<?= JS; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.es.js"></script>
<script src="<?= JS; ?>/bootstrap-notify.min.js"></script>

<?php require_once show_template('footer-sidebar'); ?>