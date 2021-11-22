<?php

// @etysoft  obtiene el id del usuario actual
$id_user_current = $_user['id_user'];
// @etysoft  obtiene el almacen asignado al usuario actual
$almacenes = $db->query("
	SELECT
		a.id_almacen,
		a.almacen,
		s.id_sucursal,
		s.sucursal
	FROM inv_almacen_empleados ae
	JOIN inv_almacenes a ON a.id_almacen = ae.almacen_id
	JOIN inv_almacen_sucursales asu ON asu.almacen_id = a.id_almacen 
	JOIN inv_sucursal s ON s.id_sucursal = asu.sucursal_id
	JOIN sys_users u  ON u.persona_id = ae.empleado_id
	WHERE u.id_user = $id_user_current
")->fetch();
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_almacen = in_array('permiso_sucursal', $permisos);
$permiso_mostrar = in_array('mostrar', $permisos);

if (!$permiso_almacen) {
	header("Location: ?/manuales/crear");
}

?>
<?php require_once show_template('header-sidebar'); ?>

<style>
	.panel-heading h2 {
		text-align: center;
	}

	.panel-heading h2 span {
		font-size: 40px;
	}

	.panel-heading h2 a {}
</style>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Seleccionar el punto de venta</strong>
				</h3>
			</div>

			<?php if ($permiso_mostrar) : ?>
				<br>
				<p class="text-right">
					<a href="?/manuales/mostrar" class="btn btn-warning">Mis ventas manuales</a>
				</p>
			<?php endif ?>

			<div class="panel-body">
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Advertencia!</strong>
					<ul>
						<li>Elija el punto de venta desde el cual hara la compra.</li>
					</ul>
				</div>
				<?php

				foreach ($almacenes as $nro => $almacenX) {
				?>
					<div class="col-md-4">
						<a class="seleccionarAlmacen" href="?/manuales/crear/<?php echo $almacenX["id_sucursal"]; ?>">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title d-block text-center">
										<strong><?= $almacenX["sucursal"]; ?></strong>
										<br>
										<span class="glyphicon glyphicon-list pt-2 pb-3"></span>
										<br>
										<?= $almacenX["almacen"]; ?>
									</h2>
								</div>
							</div>
						</a>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function() {
		$('.seleccionarAlmacen').hover(
			function() {
				$(this).children('div').addClass("panel-primary");
				$(this).children('div').removeClass("panel-default");
			},
			function() {
				$(this).children('div').addClass("panel-default");
				$(this).children('div').removeClass("panel-primary");
			}
		);
	});
</script>
<?php require_once show_template('footer-sidebar'); ?>