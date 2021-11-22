<?php
// Obtiene los clientes
/*
$clientes = $db->select('nombre_cliente, nit_ci, id_egreso')
				->from('inv_egresos')
				->fetch();

// Actualiza la informacion
foreach ($clientes as $key => $elemento) {

	// Obtiene los clientes
	$cliente2 = $db->query("SELECT *
							from inv_clientes 
							where nombre_cliente='".$elemento['nombre_cliente']."'
								  AND nit_ci='".$elemento['nit_ci']."'
							")
					->fetch_first();

	if($cliente2){
		$nota = array(
			'cliente_id' => $cliente2['id_cliente']
		);
		$condicion = array('id_egreso' => $elemento['id_egreso'] );

		$db->where($condicion)->update('inv_egresos', $nota);
	}
}

// Obtiene los clientes
$clientes = $db->select('nombre_cliente, nit_ci, id_proforma')
				->from('inv_proformas')
				->fetch();

// Actualiza la informacion
foreach ($clientes as $key => $elemento) {

	// Obtiene los clientes
	$cliente2 = $db->query("SELECT *
							from inv_clientes 
							where nombre_cliente='".$elemento['nombre_cliente']."'
								  AND nit_ci='".$elemento['nit_ci']."'
							")
					->fetch_first();

	if($cliente2){
		$nota = array(
			'cliente_id' => $cliente2['id_cliente']
		);
		$condicion = array('id_proforma' => $elemento['id_proforma'] );

		$db->where($condicion)->update('inv_proformas', $nota);
	}
}
*/
// Obtiene los clientes
/*
$clientes = $db->select('nombre_cliente, nit_ci,telefono,direccion, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')
				->from('inv_egresos')
				->group_by('nombre_cliente, nit_ci')
				->order_by('nombre_cliente asc, nit_ci asc')
				->fetch();
*/

// Obtiene los clientes
// $clientes = $db->select('c.imagen, c.id_cliente, c.nombre_cliente, c.nit_ci,c.telefono,c.escalafon, 
// 						count(c.nombre_cliente) as nro_visitas, sum(e.monto_total) as total_ventas')
// 				->from('inv_egresos e')
// 				->join('inv_clientes c','id_cliente=cliente_id')
// 				->group_by('e.cliente_id')
// 				->order_by('e.nombre_cliente asc, e.nit_ci asc')
// 				->fetch();
// $cliente = $db->query("select * from inv_egresos where = ' '");
// $clientes = $db->query("SELECT c.imagen,c.id_cliente,c.nombre_cliente,c.nit_ci,c.telefono,c.escalafon,ca.categoria,count(e.cliente_id) as nro_visitas, ifnull(sum(e.monto_total), 0) as total_ventas
//                             FROM  inv_clientes c
//                             LEFT JOIN inv_egresos e ON id_cliente=cliente_id
//                             LEFT JOIN inv_categorias_cliente ca ON c.categoria_cliente_id=ca.id_categoria_cliente
//                             GROUP BY c.id_cliente
//                             ORDER BY e.nombre_cliente asc, e.nit_ci asc")->fetch();
$clientes = $db->query("SELECT c.imagen,c.id_cliente,c.nombre_cliente,c.nit_ci,c.telefono,c.escalafon,ca.categoria,count(e.cliente_id) as nro_visitas, ifnull(sum(e.monto_total), 0) as total_ventas
                            FROM  inv_clientes c
                            LEFT JOIN inv_egresos e ON id_cliente=cliente_id
                            LEFT JOIN inv_categorias_cliente ca ON c.categoria_cliente_id=ca.id_categoria_cliente
                            where c.telefono != '78673844'
                            AND c.telefono != '72345234'
                            AND c.telefono != '79416699'
                            AND c.telefono != '73405151'
							GROUP BY c.id_cliente                            
							ORDER BY c.nombre_cliente ASC, c.nit_ci ASC")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_modificar 	= in_array('modificar', $permisos);
$permiso_eliminar 	= in_array('eliminar', $permisos);
// $permiso_eliminar   = true;
$permiso_ver		= true;

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>
<?php require_once show_template('header-sidebar'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Clientes</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para ver el reporte hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/clientes/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($clientes) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Imagen</th>
				<th class="text-nowrap">Cliente</th>
				<th class="text-nowrap">NIT/CI</th>
				<th class="text-nowrap">Teléfono</th>
				<th class="text-nowrap">Dirección</th>
				<th class="text-nowrap">Categoria</th><!--inv_categorias_cliente-->
				<th class="text-nowrap">Visitas</th>
				<th class="text-nowrap">Ventas <?= escape($moneda); ?></th>
				<?php if ($permiso_ver || $permiso_modificar || $permiso_eliminar) : ?>
					<th class="text-nowrap">Opciones</th>
				<?php endif ?>			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Imagen</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Cliente</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">NIT/CI</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Teléfono</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Dirección</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Categoria</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Visitas</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Ventas <?= escape($moneda); ?></th>
				<?php if ($permiso_ver || $permiso_modificar || $permiso_eliminar) : ?>
					<th class="text-nowrap">Opciones</th>
				<?php endif ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($clientes as $nro => $cliente) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap align-middle text-center">
					<img src="<?= ($cliente['imagen'] == '') ? imgs . '/image.jpg' : files . '/clientes/' . $cliente['imagen']; ?>"  class="img-rounded cursor-pointer" data-toggle="lightbox" data-lightbox-size="modal-md" data-lightbox-content="<?php 
						echo escape('<B>CODIGO: </B>'.$cliente['id_cliente'].'<BR>'); 
						// echo escape('<B>DESCRIPCIÓN: </B>'.$cliente['descripcion'].'<BR>'); 
						
						// $id_cliente = $cliente['id_cliente'];
						// $asignaciones = $db->query("SELECT a.*, u.*
						// 							FROM inv_asignaciones a 
						// 							LEFT JOIN inv_clientes p ON p.id_cliente = a.cliente_id
						// 							LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
						// 							WHERE p.id_cliente = $id_cliente AND 
						// 								  a.visible = 's' 
						// 						  ")->fetch();
						
						// foreach ($asignaciones as $i => $asignacion){
						//     if (empty($asignaciones)){
								
						//     }else{
						//     	echo "<B>PRECIO ".$asignacion['unidad'].":</B> ".$asignacion['precio_actual']."<BR>";
						//     }
						// }
						// echo escape('<B>UBICACIÓN: </B>'.$cliente['ubicacion'].'<BR>'); 
					?>" width="75" height="75">
				</td>
				<td class="text-nowrap"><?= escape($cliente['nombre_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['nit_ci']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['telefono']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['escalafon']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['categoria']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['nro_visitas']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['total_ventas']); ?></td>
				<?php if ($permiso_ver || $permiso_modificar || $permiso_eliminar) : ?>
					<td class="text-nowrap">
						<?php if ($permiso_modificar) : ?>
						<a href="?/clientes/modificar/<?= $cliente['id_cliente']; ?>" data-toggle="tooltip" title="Modificar cliente"><span class="glyphicon glyphicon-edit"></span></a>
						<?php endif ?>
						<?php if ($permiso_eliminar) : ?>
						    <?php if ($cliente['nro_visitas'] == 0 && $cliente['total_ventas']) : ?>
						        <a href="?/clientes/eliminar/<?= $cliente['id_cliente']; ?>" data-toggle="tooltip" title="Eliminar cliente" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
						    <?php endif ?>
						<?php endif ?>
					</td>
				<?php endif ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen clientes registrados en la base de datos.</p>
	</div>
	<?php } ?>
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
	<?php if ($permiso_imprimir) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'p':
					e.preventDefault();
					window.location = '?/clientes/imprimir';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('¿Está seguro que desea eliminar este cliente?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($clientes) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'clientes',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>