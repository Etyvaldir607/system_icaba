<?php


$id_almacen = $_POST['id_almacen'];
$fecha_inicial = ( isset(  $_POST['fecha_inicial'] ) ) ?  $_POST['fecha_inicial']: date('Y-m-d');
$fecha_final = ( isset(  $_POST['fecha_final'] ) ) ?  $_POST['fecha_final']: date('Y-m-d');

$table = <<<EOT
 (
	select 
		row_number() over(order by e.id_egreso) as nro,
		e.id_egreso,
		CONCAT ( 
			DATE_FORMAT(e.fecha_egreso, "%d-%m-%Y")," ",
			TIME_FORMAT(e.hora_egreso, "%H:%i:%s" )
		) AS fecha_hora_egreso,
		e.fecha_egreso,
		e.hora_egreso,
		e.tipo,
		e.descripcion,
		e.monto_total,
		e.nro_registros,
		concat( em.nombres, " ", em.paterno, " ", em.materno) as usuario,
		a.almacen, 
		a.principal
	from inv_egresos e
	left join inv_almacenes a on e.almacen_id = a.id_almacen
	left join sys_empleados em on e.empleado_id = em.id_empleado
	where e.fecha_egreso between '$fecha_inicial' and '$fecha_final' and e.almacen_id = '$id_almacen'
 ) temp
EOT;


$primaryKey = 'id_egreso';



$columns = array(
    
    array('db' => 'nro',                'dt' => 0),
	/** botones que se agregarán en el front end */
	array('db' => 'fecha_hora_egreso',  'dt' => 2),
	array('db' => 'tipo',          		'dt' => 3),
	array('db' => 'descripcion',        'dt' => 4),
    array('db' => 'monto_total',        'dt' => 5),
	array('db' => 'nro_registros',      'dt' => 6),
	array('db' => 'almacen',        	'dt' => 7),
    array('db' => 'usuario',          	'dt' => 8),
    
    array('db' => 'id_egreso',        	'dt' => 'id_egreso'),
    array('db' => 'fecha_egreso',  	'dt' => 'fecha_egreso'),
    array('db' => 'hora_egreso',  		'dt' => 'hora_egreso'),
	
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
