<?php

// Obtiene las categorías
$categorias = $db->select('z.*')->from('inv_categorias_cliente z')->order_by('z.id_categoria_cliente')->fetch();

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
		<b>Listado de categorías para clientes</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $categorias)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevas categorías hacer clic en el botón (+ Nuevo). </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/categorias_cliente/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/categorias_cliente/crear" class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span>
				<span>Nuevo</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($categorias) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap align-middle column-collapse">#</th>
				<th class="text-nowrap align-middle column-collapse">Categoría</th>
				<th class="text-nowrap align-middle">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle column-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Categoría</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($categorias as $nro => $categoria) { ?>
			<tr>
				<th class="text-nowrap align-middle"><?= $nro + 1; ?></th>
				<td class="text-nowrap align-middle"><?= escape($categoria['categoria']); ?></td>
				<td class="text-nowrap align-middle"><?= escape($categoria['descripcion']); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<td class="text-nowrap align-middle">
					<?php if ($permiso_ver) { ?>
					<a href="?/categorias_cliente/ver/<?= $categoria['id_categoria_cliente']; ?>" data-toggle="tooltip" title="Ver categoría">
						<span class="glyphicon glyphicon-search"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/categorias_cliente/editar/<?= $categoria['id_categoria_cliente']; ?>" data-toggle="tooltip" title="Modificar categoría"><span class="glyphicon glyphicon-edit"></span></a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					    <?php 
					        $id = $categoria['id_categoria_cliente'];
					        $eliminar = $db->query("SELECT cl.id_cliente as eliminar FROM `inv_categorias_cliente` as ca
                            LEFT JOIN inv_clientes as cl ON cl.categoria_cliente_id = ca.id_categoria_cliente
                            WHERE ca.id_categoria_cliente = '$id'
                            limit 1")->fetch_first(); 
                        ?>
					    <?php if ($categoria['id_categoria_cliente']!=1 ) { ?>
					        <?php if ($eliminar['eliminar'] == null) { ?>
					        <a href="?/categorias_cliente/eliminar/<?= $categoria['id_categoria_cliente']; ?>" data-toggle="tooltip" title="Eliminar categoría" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
					        <?php } ?>
					    <?php } ?>
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
		<p>No existen categorías registradas en la base de datos, para crear nuevas categorías hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		
    		bootbox.confirm('Está seguro que desea eliminar la categoría?', function (result) {
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
					window.location = '?/categorias_cliente/crear';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($categorias) { ?>
	var table = $('#table').DataFilter({
		filter: false,
		name: 'categorias',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>