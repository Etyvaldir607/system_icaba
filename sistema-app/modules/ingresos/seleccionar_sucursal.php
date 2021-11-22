<?php



// Obtiene los permisos

$permisos = explode(',', permits);



// Almacena los permisos en variables

$permiso_sucursal = in_array('permiso_sucursal', $permisos);



if(!$permiso_sucursal){

	//header("Location: ?/ingresos/crear");

}

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


?>

<?php require_once show_template('header-sidebar'); ?>
<style>
.panel-heading h2{
	text-align: center;
}

.panel-heading h2 span{
	font-size: 40px;
}


</style>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Seleccionar almacen</strong>
				</h3>
			</div>

			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de ingresos</span></a>
					</div>
				</div>
				<hr>
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Advertencia!</strong>
					<ul>
						<li>Elija el almacen desde el cual hara la compra.</li>
					</ul>
				</div>
					<?php

					foreach($almacenes as $nro => $almacenX){
					?>

					<div class="col-md-4">
						<a class="seleccionarAlmacen" href="?/ingresos/crear/<?= $almacenX["id_almacen"]; ?>">
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
					<?php } ?>
			</div>
		</div>
	</div>	
</div>

<script>

	$(document).ready(function(){
		$('.seleccionarAlmacen').hover(
			function(){ $(this).children('div').addClass("panel-primary"); $(this).children('div').removeClass("panel-default"); },
			function(){ $(this).children('div').addClass("panel-default"); $(this).children('div').removeClass("panel-primary"); }
		);
	});
</script>

<?php require_once show_template('footer-sidebar'); ?>