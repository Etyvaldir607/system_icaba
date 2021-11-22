<?php


$id_almacen = $_POST['id_almacen'];
$fecha_inicial = ( isset(  $_POST['fecha_inicial'] ) ) ?  $_POST['fecha_inicial']: date('Y-m-d');
$fecha_final = ( isset(  $_POST['fecha_final'] ) ) ?  $_POST['fecha_final']: date('Y-m-d');

$table = <<<EOT
 (
	select 
		row_number() over(order by i.id_ingreso) as nro,
		i.id_ingreso,
		i.fecha_ingreso,
		i.hora_ingreso,
		i.tipo, 
		i.monto_total,
		i.nro_registros,
		concat( e.nombres, " ", e.paterno ) as usuario,
		i.transitorio,
		/*if( i.transitorio = '0', 'en inventario', 'no esta en inventario') as transitorio,*/
		i.des_transitorio,
		a.almacen, 
		a.principal

	from inv_ingresos i
	left join inv_almacenes a on i.almacen_id = a.id_almacen
	left join sys_empleados e on i.empleado_id = e.id_empleado
	where i.fecha_ingreso between '$fecha_inicial' and '$fecha_final'
 ) temp
EOT;


$primaryKey = 'id_ingreso';



$columns = array(
    
    array('db' => 'nro',                'dt' => 0),
	//array('db' => 'button-yottabm',   'dt' => 1), // se crea en el fronted
	//array('db' => 'fecha hora', 		'dt' => 2), // se crea en el fronted
	array('db' => 'tipo',          		'dt' => 3),
    array('db' => 'monto_total',        'dt' => 4),
	array('db' => 'nro_registros',      'dt' => 5),
	array('db' => 'almacen',        	'dt' => 6),
    array('db' => 'usuario',          	'dt' => 7),
	array('db' => 'transitorio',        'dt' => 8),
	array('db' => 'des_transitorio',    'dt' => 9),
    
    //yottabm enviamos la data solo para uso en el front nota ocultarlo en el front
    array('db' => 'id_ingreso',        	'dt' => 'id_ingreso'),
    array('db' => 'fecha_ingreso',  	'dt' => 'fecha_ingreso'),
    array('db' => 'hora_ingreso',  		'dt' => 'hora_ingreso'),

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
