<?php

$table = <<<EOT
 (
	select
		row_number() over(order by p_u.id_producto) as nro,
		p_u.id_producto,
		p_u.codigo,
		p_u.nombre,
		p_u.nombre_factura,
		p_u.ubicacion,
		p_u.descripcion,
		p_u.imagen,
		ifnull(p_u.categoria,'') as categoria,
		group_concat(p_u.id_unidad order by p_u.id_asignacion separator '|' ) as arr_id_unidad,
		group_concat(p_u.id_asignacion order by p_u.id_asignacion separator '|' ) as arr_id_asignacion,
		group_concat(p_u.unidad order by p_u.id_asignacion separator '|' ) as arr_unidad,
		group_concat(p_u.tamanio order by p_u.id_asignacion separator '|' ) as arr_tamanio,
		group_concat(p_u.precio_actual order by p_u.id_asignacion separator '|' ) as arr_precio_actual
		
	/*producto con su asignacion de unidades y categoria lo obtenemos para despues ordenarlos segun asignacion id*/
	from (
		select 
		p.id_producto,
		p.codigo,
		p.nombre,
		p.nombre_factura,
		p.ubicacion,
		p.descripcion,
		p.imagen,
		c.categoria,
		a.id_asignacion,
		a.precio_actual,
		u.id_unidad,
		u.tamanio,
		u.unidad
		from inv_asignaciones as a
			left join inv_productos as p on p.id_producto = a.producto_id
			left join inv_unidades as u on u.id_unidad = a.unidad_id 
			left join inv_categorias as c on p.categoria_id = c.id_categoria
			where a.visible = "s"
			and p.id_producto is not null
	) as p_u
	/*finalmente agrupamos por el id producto*/
	group by p_u.id_producto
 ) temp
EOT;


$primaryKey = 'id_producto';

//id_producto
//imagen
//nombre
//nombre_factura
//categoria
//ubicacion
//descripcion

$columns = array(
    
    array('db' => 'nro',                'dt' => 0),
    //array('db' => 'button-yottabm',   'dt' => 1),
    array('db' => 'codigo',             'dt' => 2),
    array('db' => 'nombre',             'dt' => 3),
    array('db' => 'nombre_factura',     'dt' => 4),
    //array('db' => 'unidad-yottabm',   'dt' => 5),
    array('db' => 'categoria',          'dt' => 6),
    array('db' => 'ubicacion',          'dt' => 7),
    array('db' => 'descripcion',        'dt' => 8),
    array('db' => 'imagen',             'dt' => 9),

    //yottabm enviamos la data solo para uso en el front nota ocultarlo en el front
    array('db' => 'id_producto',        'dt' => 'id_producto'),
	array('db' => 'arr_id_unidad',  	'dt' => 'arr_id_unidad'),
    array('db' => 'arr_id_asignacion',  'dt' => 'arr_id_asignacion'),
    array('db' => 'arr_unidad',         'dt' => 'arr_unidad'),
	array('db' => 'arr_tamanio',        'dt' => 'arr_tamanio'),
    array('db' => 'arr_precio_actual',  'dt' => 'arr_precio_actual'),
	array('db' => 'nombre',  			'dt' => 'nombre'),

	
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
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
