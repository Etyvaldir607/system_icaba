<?php
$dat=date("Y-m-d");
$houur=date("H:i:s");

	$ventas_hoy222= $db->query("SELECT *
								FROM sys_users
								LEFT JOIN sys_roles ON id_rol=rol_id
								WHERE id_user='".$_user['id_user']."' AND vista_home='a'"
						)->fetch_first();

	//echo $_user['id_user'];
	//var_dump($ventas_hoy222);

	//calculo ventas y minutos
	$ventas_hoy=  $db->query("SELECT count(id_egreso) countt, MAX(hora_egreso) hourr
							FROM inv_egresos
							WHERE fecha_egreso='$dat' AND tipo='venta'"
						)->fetch_first();

	//calculo factuta y minutos
	$facturas_hoy=  $db->query("SELECT count(id_egreso) countt
							FROM inv_egresos
							WHERE tipo='venta' AND codigo_control!='' AND provisionado='N'
							ORDER BY fecha_egreso DESC, hora_egreso DESC"
						)->fetch_first();

	$facturas_hoy2=  $db->query("SELECT hora_egreso as hourr
							FROM inv_egresos
							WHERE tipo='venta' AND codigo_control!='' AND provisionado='N' AND fecha_egreso='$dat'
							ORDER BY hora_egreso DESC
							LIMIT 1 "
						)->fetch();

	$compras_hoy=  $db->query("SELECT count(id_ingreso) countt, MAX(hora_ingreso) hourr
							FROM inv_ingresos
							WHERE fecha_ingreso='$dat' AND tipo='compra' 
							ORDER BY fecha_ingreso DESC, hora_ingreso DESC"
						)->fetch_first();

	$clientes_hoy=  $db->query("SELECT count(id_egreso) countt, MAX(hora_egreso) hourr
							FROM inv_egresos
							WHERE fecha_egreso='$dat' AND nit_ci NOT IN (
								SELECT nit_ci
								FROM inv_egresos
								WHERE fecha_egreso<'$dat' 
							)
							"
						)->fetch_first();

	$fecha_actual = date("d-m-Y");

	$DD[0]= date("Y-m-d",strtotime($fecha_actual."- 0 days")); 

	$DD[1]= date("Y-m-d",strtotime($fecha_actual."- 1 days")); 

	$DD[2]= date("Y-m-d",strtotime($fecha_actual."- 2 days")); 

	$DD[3]= date("Y-m-d",strtotime($fecha_actual."- 3 days")); 

	$DD[4]= date("Y-m-d",strtotime($fecha_actual."- 4 days")); 

	$DD[5]= date("Y-m-d",strtotime($fecha_actual."- 5 days")); 

	$DD[6]= date("Y-m-d",strtotime($fecha_actual."- 6 days")); 

	$DD[7]= date("Y-m-d",strtotime($fecha_actual."- 7 days")); 



	$DD2[0]= date("d/m/Y",strtotime($fecha_actual."- 0 days")); 

	$DD2[1]= date("d/m/Y",strtotime($fecha_actual."- 1 days")); 

	$DD2[2]= date("d/m/Y",strtotime($fecha_actual."- 2 days")); 

	$DD2[3]= date("d/m/Y",strtotime($fecha_actual."- 3 days")); 

	$DD2[4]= date("d/m/Y",strtotime($fecha_actual."- 4 days")); 

	$DD2[5]= date("d/m/Y",strtotime($fecha_actual."- 5 days")); 

	$DD2[6]= date("d/m/Y",strtotime($fecha_actual."- 6 days")); 

	$DD2[7]= date("d/m/Y",strtotime($fecha_actual."- 7 days")); 

	function dif_Hour($h1, $h2){
		$v1=explode(":",$h1);	   
		$v2=explode(":",$h2);

		if(count($v1)>2 && count($v2)>2){
			$minuto=$v2[1]-$v1[1];
			$hora=$v2[0]-$v1[0];
			if($minuto<0){
				$minuto=$minuto+60;
				$hora=$hora-1;
			}
			if($hora!=0){
				if($hora==1){
					return "Hace ".$hora." hora";
				}
				else{
					return "Hace ".$hora." horas";
				}
			}
			else{
				if($minuto==0){
					return "En este momento";
				}
				else{
					if($minuto==1){
						return "Hace ".$minuto." minuto";
					}
					else{
						return "Hace ".$minuto." minutos";
					}
				}
			}
			return $hora.":".$minuto;
		}
		else{
			return " Hace más de 1 dia";
		}	    
	}

	$productos =  $db->query("SELECT *, SUM(d.cantidad*tamanio) as costomax
							from inv_productos p
							INNER join inv_egresos_detalles d ON p.id_producto=d.producto_id
							INNER join inv_asignaciones a ON a.id_asignacion=d.asignacion_id
							INNER join inv_unidades u ON u.id_unidad=a.unidad_id
							
							group by id_producto

							order by costomax DESC

							limit 5"

						)->fetch();



	$empleados =  $db->query("SELECT *, SUM(i.monto_total*(1-i.descuento/100) ) as costomax

							from sys_empleados e

							INNER join inv_egresos i ON empleado_id=id_empleado

							INNER join inv_egresos_detalles d ON i.id_egreso=d.egreso_id

							group by id_empleado

							order by costomax DESC

							limit 5"

						)->fetch();



	$clientes =  $db->query("SELECT *, (SUM(i.monto_total*(1-i.descuento/100) )-SUM(x.monto2))as costomax, SUM(i.monto_total*(1-i.descuento/100) )as costomax1, SUM(x.monto2)as costomax2

							FROM inv_egresos i 

							

							INNER join (

											SELECT *, SUM(monto) as monto2

											FROM inv_pagos p 

											INNER join inv_pagos_detalles pd ON pd.pago_id=p.id_pago AND pd.estado='1'

											WHERE p.tipo='egreso'         

	                            			GROUP BY pago_id

							)as x on x.movimiento_id=i.id_egreso				

							WHERE plan_de_pagos='si' 

							group by nombre_cliente, NIT_CI

							order by costomax DESC"

						)->fetch();



	$proveedores =  $db->query("SELECT *, (SUM(i.monto_total)-SUM(x.monto2))as costomax, SUM(i.monto_total)as costomax1, SUM(x.monto2)as costomax2

							FROM inv_ingresos i 

							

							INNER join (

											SELECT *, SUM(monto) as monto2

											FROM inv_pagos p 

											INNER join inv_pagos_detalles pd ON pd.pago_id=p.id_pago AND pd.estado='1'

											WHERE p.tipo='ingreso'         

	                            			GROUP BY pago_id

							)as x on x.movimiento_id=i.id_ingreso				

							WHERE plan_de_pagos='si' 

							group by nombre_proveedor

							order by costomax DESC"

						)->fetch();



	$sucursal_ventas = $db->query("SELECT sucursal, SUM(i.monto_total*(1-i.descuento/100) )as costomax					

							FROM inv_egresos i 

							INNER JOIN inv_sucursal s ON id_sucursal=sucursal_id 						

							group by sucursal

							order by id_sucursal ASC"

						)->fetch();



	//Textos completos	id_egreso	fecha_egreso	hora_egreso	tipo	provisionado	descripcion	nro_factura	nro_autorizacion	codigo_control	fecha_limite	monto_total	nombre_cliente	nit_ci	nro_registros	dosificacion_id	almacen_id	empleado_id	plan_de_pagos	telefono	direccion	observacion	descuento	estado	tipo_de_pago

		
require_once show_template('header-sidebar');

if($ventas_hoy222){
	?>

	<div class="row">
        <!--<div class="col-md-12">-->
        <!--    <div class="alert alert-danger">-->
        <!--        <b>Señores usuarios:</b> <br>-->
        <!--        <p>-->
        <!--            SE LES INFORMA QUE ESTA COPIA DEL SISTEMA ES SOLAMENTE PARA PRUEBAS<br>-->
        <!--            <b>NOTA:</b> Aquí puede hacer pruebas de las adiciones y correcciones antes de ser actualizados en el sistema oficial. Cada cierto tiempo la base de datos de esta copia será borrada y/o reemplazada, tomar precauciones. -->
        <!--        </p>-->
        <!--    </div>-->
        <!--</div>-->
		<div class="col-6 col-lg-4 col-xl-3">

			<div data-enlace="?/reportes/ventas_generales" class="alert alert-info cursor-pointer">

				<div class="row align-items-center">

					<div class="col-auto pr-0">

						<span class="h1">

							<span class="glyphicon glyphicon-user"></span>

						</span>

					</div>

					<div class="col">

						<b>Nuevas ventas</b>

						<br>

						<em><?= dif_Hour($ventas_hoy['hourr'], $houur) ?></em>

						<p class="h1 m-0"><?= $ventas_hoy['countt'] ?></p>

					</div>

				</div>

			</div>

		</div>

		<div class="col-6 col-lg-4 col-xl-3">

			<div class="alert alert-warning">

				<div class="row align-items-center">

					<div class="col-auto pr-0">

						<span class="h1">

							<span class="glyphicon glyphicon-star"></span>

						</span>

					</div>

					<div class="col">

						<b>Nuevos clientes</b>

						<br>

						<em><?= dif_Hour($clientes_hoy['hourr'], $houur) ?></em>

						<p class="h1 m-0"><?= $clientes_hoy['countt'] ?></p>

					</div>

				</div>

			</div>

		</div>

		<div class="col-6 col-lg-4 col-xl-3">

			<div data-enlace="?/operaciones/facturas_listar" class="alert alert-success cursor-pointer">

				<div class="row align-items-center">

					<div class="col-auto pr-0">

						<span class="h1">

							<span class="glyphicon glyphicon-star"></span>

						</span>

					</div>

					<div class="col">

						<b>Facturas emitidas</b>

						<br>

						<em><?php 

							$x=0;

							foreach ($facturas_hoy2 as $facturas_hoy222){

								echo dif_Hour($facturas_hoy222['hourr'], $houur);

								$x++;

							}

							if($x==0){

								echo " Hace más de 1 dia";

							}

						?></em>

						<p class="h1 m-0"><?= $facturas_hoy['countt'] ?></p>

					</div>

				</div>

			</div>

		</div>

		<div class="col-6 col-lg-4 col-xl-3">

			<div class="alert alert-danger">

				<div class="row align-items-center">

					<div class="col-auto pr-0">

						<span class="h1">

							<span class="glyphicon glyphicon-star"></span>

						</span>

					</div>

					<div class="col">

						<b>Nuevas compras</b>

						<br>

						<em><?= dif_Hour($compras_hoy['hourr'], $houur) ?></em>

						<p class="h1 m-0"><?= $compras_hoy['countt'] ?></p>

					</div>

				</div>

			</div>

		</div>

	</div>

	<div class="row">

		<div class="col-lg-6">

			<div id="estadisticas_ventas">

				<table class="table table-bordered table-condensed d-none">

					<thead>

						<tr>

							<th>Fecha</th>

							<th>Ventas</th>

						</tr>

					</thead>

					<tbody>

						<?php 

							for($i=7;$i>=0;$i--){

								$venta_dia =  $db->query("SELECT *, SUM(i.monto_total*(1-i.descuento/100) )as costomax

											FROM inv_egresos i 

											WHERE fecha_egreso='".$DD[$i]."' 

											"

										)->fetch_first();

							?>

								<tr>

									<th><?= $DD2[$i]; ?></th>

									<td><?= $venta_dia['costomax'] ?></td>

								</tr>

							<?php

							}

						?>



					</tbody>

				</table>

				<p class="lead">Monto alcanzado semanalmente</p>

				<div class="well">

					<canvas></canvas>

				</div>

			</div>

		</div>

		<div class="col-lg-6">

			<div id="estadisticas_sucursales">

				<table class="table table-bordered table-condensed d-none">

					<thead>

						<tr>

							<th>Sucursal</th>

							<th>Ventas</th>

						</tr>

					</thead>

					<tbody>

						<tr>

							<th></th>

							<td></td>

						</tr>

						<?php

						foreach ($sucursal_ventas as $sucursal){

						?>	

						<tr>

							<th><?= $sucursal['sucursal'] ?></th>

							<td><?= $sucursal['costomax'] ?></td>

						</tr>

						<?php } ?>

						<tr>

							<th></th>

							<td></td>

						</tr>

					</tbody>

				</table>

				<p class="lead">Monto máximo alcanzado en ventas por sucursal</p>

				<div class="well">

					<canvas></canvas>

				</div>

			</div>

		</div>

	</div>

	<div class="row">

		<div class="col-lg-4">

			<p class="lead">Ranking de montos alcanzados por empleado</p>

			<ul class="list-group">

				<?php 

				$max=0;

				foreach ($empleados as $empleado): 

					if($max==0){

						$max=$empleado['costomax'];

					}

					?>

					<li class="list-group-item py-2">

						<div class="row align-items-center">

							<div class="col-auto pr-0">

								<img src="<?= imgs; ?>/avatar-default.jpg" class="rounded-circle" height="48" data-toggle="lightbox" data-lightbox-size="md" data-lightbox-content="<?= escape($cliente['nombre_cliente']); ?>">

							</div>

							<div class="col pull-right-container">

								<div class="text-primary"><?= escape($empleado['nombres']." ".$empleado['paterno']." ".$empleado['materno']); ?></div>

								<div class="h3 m-0"><?= number_format(escape($empleado['costomax']),2,'.',''); ?></div>

								<span class="pull-right">

									<?php 

									$limit=round(($empleado['costomax']/$max)*5);

									for($i=1;$i<=$limit;$i++){

									?>

										<span class="glyphicon glyphicon-star text-danger"></span>

									<?php

									}

									for($i=$limit+1;$i<=5;$i++){

									?>

										<span class="glyphicon glyphicon-star"></span>

									<?php

									}

									?>

								</span>

							</div>

						</div>

					</li>

				<?php endforeach ?>

			</ul>

		</div>

		<div class="col-lg-4">
		    
            <?php if($clientes[1] !=''){?>
    			<p class="lead">Resumen de saldo de clientes</p>
    
    			<ul class="list-group">
    
    				<?php
    
    				foreach ($clientes as $cliente){
    
    				?>
    
    				<li class="list-group-item list-group-item-success pull-right-container">
    
    					<span><?= $cliente["nombre_cliente"]; ?></span>
    
    					<span class="pull-right lead"><?= number_format($cliente["costomax"],2,'.',''); ?> </span>
    
    				</li>
    
    				<?php } ?>
    
    			</ul>
            <?php } else {?>
                <h5> no existen ventas a clientes </h5>
            <?php } ?>
			
            <?php if($proveedores[1] !=''){?>
                <p class="lead">Resumen de saldo a proveedores</p>
                <ul class="list-group">
    				<?php
    				foreach ($proveedores as $proveedor){
    				?>
    				<li class="list-group-item list-group-item-success pull-right-container">
    					<span><?= $proveedor["nombre_proveedor"]; ?></span>
    					<span class="pull-right lead"><?= number_format($proveedor["costomax"],2,'.',''); ?> </span>
    				</li>
    				<?php } ?>
    
    			</ul>		
                
            <?php }else{?>
                
                <h5></h5>
            <?php } ?>
			

		</div>

		<div class="col-lg-4">

			<p class="lead">Ranking de productos más vendidos</p>

			<ul class="list-group">

				<?php 

				$max=0;

				foreach ($productos as $producto): 

					if($max==0){

						$max=$producto['costomax'];

					}

				?>

				

				<li class="list-group-item py-2">

					<div class="row align-items-center">

						<div class="col-auto pr-0">

							<img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" class="img-rounded" height="64" data-toggle="lightbox" data-lightbox-size="md" data-lightbox-content="<div class='text-center'><p class='lead m-0'><?= escape($producto['nombre']); ?></p><p class='m-0'><?= escape($producto['nombre']); ?></p><p class='h1 m-0'>55 Bs.</p></div>">

						</div>

						<div class="col pull-right-container">

							<div class="text-primary"><?= escape($producto['nombre']); ?></div>

							<div class="h6 m-0"><?= escape($producto['codigo']); ?></div>

							<span class="pull-right">

									<?php 

									$limit=round(($producto['costomax']/$max)*5);

									for($i=1;$i<=$limit;$i++){

									?>

										<span class="glyphicon glyphicon-star text-danger"></span>

									<?php

									}

									for($i=$limit+1;$i<=5;$i++){

									?>

										<span class="glyphicon glyphicon-star"></span>

									<?php

									}

									?>

							</span>

						</div>

					</div>

				</li>

				<?php endforeach ?>

			</ul>

		</div>

	</div>



	<script src="<?= js; ?>/Chart.min.js"></script>

	<script>

	$(function () {



		var $estadisticas_ventas = $('#estadisticas_ventas'), $fila, contexto, grafico, nombre, valor, nombres = [], valores = [];



		$estadisticas_ventas.find('div').css('height', 256);



		nombre = $.trim($($estadisticas_ventas.find('table thead tr').children().get(0)).text());

		valor = $.trim($($estadisticas_ventas.find('table thead tr').children().get(1)).text());



		$estadisticas_ventas.find('table tbody tr').each(function (i) {

			$fila = $(this);

			nombres.push($.trim($($fila.children().get(0)).text()));

			valores.push($.trim($($fila.children().get(1)).text()));

		});



		contexto = $estadisticas_ventas.find('canvas').get(0).getContext('2d');



		grafico = new Chart(contexto, {

			type: 'line',

			data: {

				labels: nombres,

				datasets: [{

					label: valor,

					data: valores,

					borderColor: 'rgba(217, 83, 79, 1)',

					backgroundColor: 'rgba(217, 83, 79, 0.2)',

					borderWidth: 2,

					pointRadius: 1,

					pointHoverRadius: 1,

					fill: true,

					lineTension: 0.2

				}]

			},

			options: {

				responsive: true,

				maintainAspectRatio: false,

				scales: {

					xAxes: [{

						scaleLabel: {

							display: false,

							labelString: nombre

						}

					}],

					yAxes: [{

						scaleLabel: {

							display: false,

							labelString: valor

						}

					}]

				}

			}

		});



		var $estadisticas_sucursales = $('#estadisticas_sucursales'), $fila, contexto, grafico, nombre, valor, nombres = [], valores = [];



		$estadisticas_sucursales.find('div').css('height', 256);



		nombre = $.trim($($estadisticas_sucursales.find('table thead tr').children().get(0)).text());

		valor = $.trim($($estadisticas_sucursales.find('table thead tr').children().get(1)).text());



		$estadisticas_sucursales.find('table tbody tr').each(function (i) {

			$fila = $(this);

			nombres.push($.trim($($fila.children().get(0)).text()));

			valores.push($.trim($($fila.children().get(1)).text()));

		});



		contexto = $estadisticas_sucursales.find('canvas').get(0).getContext('2d');



		grafico = new Chart(contexto, {

			type: 'line',

			data: {

				labels: nombres,

				datasets: [{

					label: valor,

					data: valores,

					borderColor: 'rgba(2, 117, 216, 1)',

					backgroundColor: 'rgba(2, 117, 216, 0.2)',

					borderWidth: 2,

					pointRadius: 1,

					pointHoverRadius: 1,

					fill: true,

					lineTension: 0.2

				}]

			},

			options: {

				responsive: true,

				maintainAspectRatio: false,

				scales: {

					xAxes: [{

						scaleLabel: {

							display: false,

							labelString: nombre

						}

					}],

					yAxes: [{

						scaleLabel: {

							display: false,

							labelString: valor

						}

					}]

				}

			}

		});



		$('[data-enlace]').on('click', function (e) {

			e.preventDefault();

			var enlace = $(this).attr('data-enlace');

			window.location = enlace;

		});

	});

	</script>

<?php 
}else{
?>
	<table style="width: 100%;"><tr><td style="text-align: center;">
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<img src="../sistema-app/files/institucion/9c581d2a1e197d331e7d7db6242276e7.jpg">
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
	</td></tr></table>
<?php
}
require_once show_template('footer-sidebar'); 
?>