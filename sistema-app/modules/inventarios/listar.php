<?php

// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Verifica si hay parametros
if ($id_almacen == 0) {
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();
} else {
	// Obtiene los id_almacen
	$id_almacen = explode('-', $id_almacen);

	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $id_almacen)->order_by('id_almacen')->fetch();
}

// Verifica si existen almacenes
if (!$almacenes) {
	// Error 404
	require_once not_found();
	exit;
}

// Genera la consulta
/*$select = "select p.id_producto, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, u.unidad, u.sigla, c.categoria";
$from = " from inv_productos p left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id";
$join = "";*/
$select = "select p.id_producto, p.codigo, p.codigo_barras, p.nombre, p.nombre_factura, p.cantidad_minima, c.categoria";
$from = " from inv_productos p left join inv_categorias c on c.id_categoria = p.categoria_id";
$join = "";

// recorre los almacenes
foreach ($almacenes as $nro => $almacen) {
	$id = $almacen['id_almacen'];
	$select = $select . ", ifnull(e$id.ingresos$id, 0) as ingresos$id, ifnull(s$id.egresos$id, 0) as egresos$id,z$id.id_asignacion, z$id.unidad_id, z$id.tamanio, z$id.unidad_descripcion";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad*u.tamanio) as ingresos$id from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id LEFT JOIN inv_asignaciones a ON
    a.id_asignacion = d.asignacion_id
LEFT JOIN inv_unidades u ON
    u.id_unidad = a.unidad_id where i.almacen_id = $id and i.transitorio = 0 group by d.producto_id) as e$id on e$id.producto_id = p.id_producto";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad*u.tamanio) as egresos$id from inv_egresos_detalles d left join inv_egresos e on (e.id_egreso = d.egreso_id AND estado='V') LEFT JOIN inv_asignaciones a ON
    a.id_asignacion = d.asignacion_id
LEFT JOIN inv_unidades u ON
    u.id_unidad = a.unidad_id where e.almacen_id = $id group by d.producto_id) as s$id on s$id.producto_id = p.id_producto LEFT JOIN (
SELECT
    w.producto_id,
    GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion,
    GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id,
    GROUP_CONCAT(
        w.unidad,
        ':',
        w.precio_actual,
        ':',
        w.tipo SEPARATOR '&'
    ) AS unidad_descripcion,
    GROUP_CONCAT(w.tamanio SEPARATOR '|') AS tamanio
FROM
    (
    SELECT
        *
    FROM
        inv_asignaciones q
    LEFT JOIN inv_unidades u ON
        q.unidad_id = u.id_unidad
) w
GROUP BY
    w.producto_id
) z$id ON p.id_producto=z$id.producto_id";
}

// Arma la consulta
$query = $select . $from . $join . "";

// Obtiene las lista de productos y los stocks en cada almacen
$productos = $db->query($query)->fetch();

//var_dump($productos);
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los ubicaciones
$ubicaciones = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();

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
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Inventario general de productos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($productos) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de almacén hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<button type="button" class="btn btn-primary" data-cambiar="true">
				<span class="glyphicon glyphicon-refresh"></span>
				<span>Cambiar</span>
			</button>
		</div>
	</div>
	<hr>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
				<th class="text-nowrap">Código barras</th>
				<th class="text-nowrap">Nombre del producto</th>
				<th class="text-nowrap">Categoría</th>
				<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap">Mínimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap">Inv. total</th>
				<th class="text-nowrap">Inv. valor <?= escape($moneda); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap align-middle" data-datafilter-filter="true">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Código barras</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nombre del producto</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Categoría</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Mínimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="true"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Inv. total</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Inv. valor <?= escape($moneda); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($productos as $nro => $producto) { 

				$asignacion  = $producto['id_asignacion'];
				$unidad      = $producto['unidad_id'];
				$descrip     = $producto['unidad_descripcion'];
				$tamanio     = $producto['tamanio'];
				$lista = '';
				
				if($asignacion == null) {
					$id_asignacion = "";
				} else {
					$unidades      = explode("|", $unidad);
					$descripciones = explode("&",$descrip);
					$id_tamanio    = explode("|", $tamanio);
				}

				//var_dump($asignacion);
				
				$monto = 0;?>
				<?php foreach ($descripciones as $key => $descripcion) {
						if(empty($asignacion)){
							$listas .= '<span>No hay registros</span>';
						}else{
							$listas = '<span>';
							$des_unidad = explode(":",$descripcion);
							if ($des_unidad[2] == 'principal'){
								$monto += $des_unidad[1];//*$id_tamanio[$key];
								$lista.= '<span><strong class="text-info">'.$des_unidad[0].' (' .$id_tamanio[$key]. '): ' .$des_unidad[1].' Bs. </strong></span>';
								$precio_base = $des_unidad[1];
							} else {
								$lista .= '<span>' .$des_unidad[0].' (' .$id_tamanio[$key]. '): ' .$des_unidad[1].' Bs. </span>';
							}
							$lista.='</span><br>';
						}
					}
				?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($producto['codigo']); ?></td>
				<td class="text-nowrap"><?= escape(substr($producto['codigo_barras'], 2)); ?></td>
				<td class="width-lg"><?= escape($producto['nombre']); ?></td>
				<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
				<td class="text-nowrap text-right">
					<?= $lista;?>
				</td>
				<td class="text-nowrap text-right"><?= escape($producto['cantidad_minima']); ?></td>
				<?php $total = 0; ?>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<?php $stock = escape($producto['ingresos' . $almacen['id_almacen']] - $producto['egresos' . $almacen['id_almacen']]); ?>
				<td class="text-nowrap text-right"><strong class="<?= ($stock < escape($producto['cantidad_minima'])) ? 'text-danger' : 'text-success'; ?>"><?= $stock; ?></strong></td>
				<?php $total = $total + $stock; ?>
				<?php } ?>
				<td class="text-nowrap text-right"><strong class="text-primary"><?= $total; ?></strong></td>
				<td class="text-nowrap text-right"><strong><?= number_format($monto * $total, 2, '.', ''); ?></strong></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>El inventario no puede mostrarse por que no existen productos registrados.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal almacen-->
<?php if ($productos) { ?>
<div id="modal_almacen" class="modal fade">
	<div class="modal-dialog">
		<form id="form_almacen" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar almacén</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<?php foreach ($ubicaciones as $nro => $almacen) { ?>
							<div class="checkbox">
								<label>
									<input type="checkbox" value="<?= escape($almacen['id_almacen']); ?>" data-seleccion="<?= escape($almacen['id_almacen']); ?>">
									<span><?= escape($almacen['almacen']); ?></span>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" data-aceptar="true">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Aceptar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_almacen" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal almacen-->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script>
$(function () {	
	<?php if ($productos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'inventario_general',
		reports: 'xls|doc|pdf|html',
		size: 8
	});

	var $fields = $('#fields_0');
	$fields.find(':checkbox[value="3"]').trigger('click');
	$fields.find(':checkbox[value="5"]').trigger('click');

	var $modal_almacen = $('#modal_almacen');
	var $form_almacen = $('#form_almacen');

	$form_almacen.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_almacen.find('[data-cancelar]').on('click', function () {
		$modal_almacen.modal('hide');
	});

	$modal_almacen.find('[data-aceptar]').on('click', function () {
		var almacenes = [];

		$('[data-seleccion]:checked').each(function () {
			almacenes.push($(this).attr('data-seleccion'));
		});

		almacenes = almacenes.join('-');
		$modal_almacen.modal('hide');
		
		if (almacenes != '') {
			window.location = '?/inventarios/listar/' + almacenes;
		} else {
			window.location = '?/inventarios/listar';
		}
		
	});

	$('[data-cambiar]').on('click', function () {
		$modal_almacen.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-sidebar'); ?>