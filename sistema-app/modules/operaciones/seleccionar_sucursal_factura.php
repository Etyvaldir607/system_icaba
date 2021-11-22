<?php

// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_almacen = in_array('permiso_sucursal', $permisos);
// Obtiene los permisos
// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

// if(!$permiso_almacen){
// 	header("Location: ?/notas/crear");
// }

?>
<?php require_once show_template('header-sidebar'); ?>

<style>
.panel-heading h2{
	text-align: center;
}
.panel-heading h2 span{
	font-size: 40px;
}
.panel-heading h2 a{
}
										
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
				<a href="?/notas/mostrar" class="btn btn-warning">Mis notas de remision</a>
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
					$almacen = $db->from('inv_sucursal')
								->fetch();
				
					foreach($almacen as $nro => $almacenX){
					?>
					<div class="col-md-4">
						<a class="seleccionarAlmacen" href="?/operaciones/proformas_facturar/<?php echo $almacenX["id_sucursal"]; ?>/<?= $id_proforma; ?>">
							<div class="panel panel-default">
							<div class="panel-heading">
								<h2 class="panel-title">
									<span class="glyphicon glyphicon-list"></span>
									<br>
									<br>
									<?php echo $almacenX["sucursal"]; ?>
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
	$(document).ready(function(){
		$('.seleccionarAlmacen').hover(
			function(){ $(this).children('div').addClass("panel-primary"); $(this).children('div').removeClass("panel-default"); },
			function(){ $(this).children('div').addClass("panel-default"); $(this).children('div').removeClass("panel-primary"); }
		);							
	});
</script>
<?php require_once show_template('footer-sidebar'); ?>