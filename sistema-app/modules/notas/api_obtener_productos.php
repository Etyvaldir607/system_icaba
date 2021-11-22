<?php


$id_almacen = $_POST['id_almacen'];
//$id_almacen = 1;
$table = <<<EOT
 (
	select 
		row_number() over(order by p.id_producto) as nro,
		p.id_producto, 
		p.codigo, 
		p.nombre,
		substring(p.nombre , 1, 30) as sb_nombre,
		p.descripcion, 
		p.cantidad_minima,
		c.categoria,
		
		group_concat(u.id_unidad order by  a.id_asignacion separator '|') as arr_unidad_id,
		group_concat(u.unidad order by  a.id_asignacion separator '|') as arr_unidad,
		group_concat(u.tamanio order by  a.id_asignacion separator '|') as arr_tamanio,
		group_concat(a.id_asignacion order by  a.id_asignacion separator '|') as arr_asignacion_id,
		group_concat(a.precio_actual order by  a.id_asignacion separator '|') as arr_precio_actual,
		group_concat(a.costo_actual order by  a.id_asignacion separator '|') as arr_costo_actual,
		
		ifnull(ingre.cantidad_ingresos, 0) - ifnull(egre.cantidad_egresos, 0) as stock

		from inv_asignaciones a
		join inv_productos p  on p.id_producto = a.producto_id
		join inv_unidades u on u.id_unidad = a.unidad_id 
		join inv_categorias c on c.id_categoria = p.categoria_id
		
		/*ingresos*/ 
		left join
		(
		select
			idt.producto_id,
			sum(idt.cantidad * u.tamanio) as cantidad_ingresos
			from inv_ingresos_detalles idt
			join inv_ingresos i on i.id_ingreso = idt.ingreso_id
			join inv_asignaciones a on a.id_asignacion = idt.asignacion_id
			join inv_unidades u on u.id_unidad = a.unidad_id 
			where i.almacen_id = $id_almacen
			and i.transitorio = 0
			group by idt.producto_id
		) as ingre on ingre.producto_id = p.id_producto

		/*egresos*/
		left join
		(
		select 
			edt.producto_id,
			sum(edt.cantidad * u.tamanio) as cantidad_egresos
			from inv_egresos_detalles edt 
			join inv_egresos e on e.id_egreso = edt.egreso_id
			join inv_asignaciones a on a.id_asignacion = edt.asignacion_id
			join inv_unidades u on u.id_unidad = a.unidad_id
			where e.almacen_id = $id_almacen
			and e.estado = 'V'
			group by edt.producto_id
		) as egre on egre.producto_id = p.id_producto
		
		/*agrupamos por el tipo de producto*/
		group by p.id_producto
 ) temp
EOT;


$primaryKey = 'id_producto';



$columns = array(
    
    array('db' => 'nro',                'dt' => 0),
	//array('db' => 'precios_actuales', 'dt' => 1), // se crea en el fronted
    //array('db' => 'button-yottabm',   'dt' => 2), // se crea en el fronted
	array('db' => 'stock',          	'dt' => 2),
    array('db' => 'codigo',             'dt' => 3),
	array('db' => 'nombre',             'dt' => 4),
	array('db' => 'descripcion',        'dt' => 5),
    array('db' => 'categoria',          'dt' => 6),
    
    

    //yottabm enviamos la data solo para uso en el front nota ocultarlo en el front
	array('db' => 'codigo',        			'dt' => 'codigo'),
	array('db' => 'nombre',        			'dt' => 'nombre'),
	array('db' => 'stock',        			'dt' => 'stock'),

    array('db' => 'id_producto',        	'dt' => 'id_producto'),
    array('db' => 'arr_unidad_id',  		'dt' => 'arr_unidad_id'),
    array('db' => 'arr_unidad',         	'dt' => 'arr_unidad'),
	array('db' => 'arr_tamanio',        	'dt' => 'arr_tamanio'),

    array('db' => 'arr_asignacion_id',  	'dt' => 'arr_asignacion_id'),
	array('db' => 'arr_precio_actual', 	 	'dt' => 'arr_precio_actual'),
	array('db' => 'arr_costo_actual',  		'dt' => 'arr_costo_actual'),
);

// SQL server connection information
$sql_details = array(
    'user' => username,
    'pass' => password,
    'db'   => database,
    'host' => host,
    'charset' => 'utf8' // collation utf8_unicode_ci
);


require_once libraries . '/data-table-class/ssp.class.php';

echo json_encode(
	//$_POST
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
