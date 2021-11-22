<?php
	$id_producto = (isset($params[0])) ? $params[0] : 0;

	$productos = $db->query("SELECT
                                    p.id_producto,
                                    p.nombre,
                                    p.nombre_factura
                            FROM inv_productos p
                            WHERE p.id_producto='$id_producto'
                            ")->fetch_first();

	echo'
		<div class="modal-header">
			<h4 class="modal-title">
				STOCK EN LOS ALMACENES
				"<I>'.$productos['nombre'].'</I>"
			</h4>
		</div>
		<div class="modal-body">
			<table id="tabla_sucursal_x" class="table table-bordered table-condensed table-striped table-hover margin-none">
				<thead>
					<tr class="active">
						<th class="text-nowrap text-center">ALMACEN</th>
						<th class="text-nowrap text-center">CANTIDAD</th>						
					</tr>
				</thead>
				<tbody>
				';

	$almacenes = $db->query("SELECT * FROM inv_almacenes")->fetch();    
							
	foreach ($almacenes as $almacen) { 
		$id_almacen=$almacen['id_almacen'];

		$productos = $db->query("SELECT
                                    p.id_producto,
                                    p.imagen,
                                    p.codigo,
                                    p.codigo,
                                    p.nombre,
                                    p.nombre_factura,
                                    p.cantidad_minima,
                                    p.descripcion,
                                    IFNULL(I.cantidad_ingresos,0) AS cantidad_ingresos,
                                    IFNULL(E.cantidad_egresos,0) AS cantidad_egresos,
                                    c.categoria,
                                    z.id_asignacion, z.unidad_id, z.tamanio, p.bonificacion, z.unidad_descripcion
                                FROM inv_productos p
                                LEFT JOIN (SELECT
                                                d.producto_id,
                                                almacen_id,
                                                SUM(d.cantidad*u.tamanio) AS cantidad_ingresos
                                            FROM
                                                inv_ingresos_detalles d
                                            LEFT JOIN inv_ingresos i ON
                                                i.id_ingreso = d.ingreso_id
                                            LEFT JOIN inv_asignaciones a ON
                                                a.id_asignacion = d.asignacion_id
                                            LEFT JOIN inv_unidades u ON
                                                u.id_unidad = a.unidad_id
                                            WHERE almacen_id='$id_almacen'
                                            GROUP BY
                                                d.producto_id, almacen_id
                                          ) I ON  I.producto_id = p.id_producto
                                LEFT JOIN ( SELECT
                                                d.producto_id,
                                                almacen_id,
                                                SUM(d.cantidad*u.tamanio) as cantidad_egresos
                                            FROM inv_egresos_detalles d
                                            LEFT JOIN inv_egresos e ON
                                                (e.id_egreso = d.egreso_id AND estado='V')
                                            LEFT JOIN inv_asignaciones a ON
                                                a.id_asignacion = d.asignacion_id
                                            LEFT JOIN inv_unidades u ON
                                                u.id_unidad = a.unidad_id
                                                WHERE almacen_id='$id_almacen'
                                                GROUP BY d.producto_id, almacen_id
                                            ) E ON E.producto_id = p.id_producto
                                                               
                                LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id 
                                LEFT JOIN ( SELECT
                                                w.producto_id,
                                                GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion,
                                                GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id,
                                                GROUP_CONCAT(
                                                    w.unidad,
                                                    ':',
                                                    w.precio_actual SEPARATOR '&'
                                                ) AS unidad_descripcion,
                                                GROUP_CONCAT(w.tamanio SEPARATOR '|') AS tamanio
                                            FROM
                                                (SELECT
                                                        *
                                                    FROM
                                                        inv_asignaciones q
                                                    LEFT JOIN inv_unidades u ON
                                                        q.unidad_id = u.id_unidad
                                                    ORDER BY
                                                        u.unidad
                                                    DESC
                                                ) w GROUP BY w.producto_id 

                                          ) z ON p.id_producto=z.producto_id
                                WHERE p.id_producto='$id_producto'
                                ORDER BY p.nombre ASC")->fetch_first();

		echo '<tr>';
		echo '<td>'.$almacen['almacen'].'</td>';
		echo '<td>'.($productos['cantidad_ingresos']-$productos['cantidad_egresos']).'</td>';
		echo '</tr>';

	}
	
	echo		'
				</tbody>
			</table>
		</div>		
	';	
?>