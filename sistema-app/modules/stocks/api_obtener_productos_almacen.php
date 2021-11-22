<?php
    $arr_id_almacen = (isset($_POST['arr_id_almacen'])) ? $_POST['arr_id_almacen']: array();
    $arr_id_almacen_res = explode(",",$arr_id_almacen);
    
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $arr_id_almacen_res)->order_by('id_almacen')->fetch();

	$temporal_ajustar = $db->from('tmp_ajustar')->where_in('almacen_id', $arr_id_almacen_res)->fetch();
	
	$ids = '';
	$sw = true;
	foreach($arr_id_almacen_res as $nro){
	    if($sw){
	        $ids .= $nro;
	        $sw = false;
	    }else{
	        $ids .= ','.$nro;
	    }
	}
	$condicion_ids = 'AND ta.almacen_id in (';
	$condicion_ids .= $ids.')';
    

    $columna_stock = [];
    $select = "
        select 
        row_number() over(order by p.id_producto) as nro,
        p.id_producto, 
        p.codigo, 
        p.nombre,
        substring(p.nombre , 1, 30) as sb_nombre,
        p.descripcion, 
        p.cantidad_minima,
        c.categoria,
        a.precio_actual,
        if(ta.producto_id,'Si','No') as modificado,
        group_concat(u.id_unidad order by  a.id_asignacion separator '|') as arr_unidad_id,
        group_concat(u.unidad order by  a.id_asignacion separator '|') as arr_unidad,
        group_concat(u.tamanio order by  a.id_asignacion separator '|') as arr_tamanio,
        group_concat(a.id_asignacion order by  a.id_asignacion separator '|') as arr_asignacion_id,
        group_concat(a.precio_actual order by  a.id_asignacion separator '|') as arr_precio_actual,
        group_concat(a.costo_actual order by  a.id_asignacion separator '|') as arr_costo_actual";

    $from ="
        from inv_asignaciones a
        join inv_productos p  on p.id_producto = a.producto_id
        join inv_unidades u on u.id_unidad = a.unidad_id 
        join inv_categorias c on c.id_categoria = p.categoria_id
        left join tmp_ajustar ta ON ta.producto_id = p.id_producto $condicion_ids";
    $join = "";

    // recorre los almacenes
    for ($i=0; $i < count( $arr_id_almacen_res) ; $i++) {
        $id_almacen = $arr_id_almacen_res[$i];
        $select = $select.
        ",ifnull(ingre$id_almacen.cantidad_ingresos, 0) - ifnull(egre$id_almacen.cantidad_egresos, 0) as stock$id_almacen 
        ,concat(
            alma$id_almacen.id_almacen,'|',alma$id_almacen.almacen,'|', ifnull(ingre$id_almacen.cantidad_ingresos, 0) - ifnull(egre$id_almacen.cantidad_egresos, 0) 
        ) as arr_val_stock$id_almacen
        ";
        $join = $join . "left join
                        (
                        select
                            idt.producto_id,
                            sum(idt.cantidad * u.tamanio) as cantidad_ingresos
                            from inv_ingresos_detalles idt
                            join inv_ingresos i on i.id_ingreso = idt.ingreso_id
                            left join inv_almacenes al on al.id_almacen = $id_almacen
                            join inv_asignaciones a on a.id_asignacion = idt.asignacion_id
                            join inv_unidades u on u.id_unidad = a.unidad_id 
                            where i.almacen_id = $id_almacen
                            and i.transitorio = 0
                            group by idt.producto_id
                        ) as ingre$id_almacen on ingre$id_almacen.producto_id = p.id_producto ";

        $join = $join . "left join
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
                        ) as egre$id_almacen on egre$id_almacen.producto_id = p.id_producto ";
        $join = $join . "left join
                        (
                        select 
                            al.id_almacen,
                            al.almacen
                            from inv_almacenes al 
                        ) as alma$id_almacen on alma$id_almacen.id_almacen = $id_almacen ";
    }

    $query = $select.$from.$join."GROUP BY p.id_producto";

    $table =  <<<EOT
    (
        $query
    ) temp
    EOT;


$primaryKey = 'id_producto';



$columns = array(
    
    array('db' => 'nro',                'dt' => 0),

    array('db' => 'codigo',             'dt' => 1),
	array('db' => 'nombre',             'dt' => 2),
    array('db' => 'categoria',          'dt' => 3),
    array('db' => 'precio_actual', 	 	'dt' => 4),
    array('db' => 'cantidad_minima', 	 	'dt' => 5),
    array('db' => 'modificado', 	 	    'dt' => 6),

	array('db' => 'codigo',        			'dt' => 'codigo'),
	array('db' => 'nombre',        			'dt' => 'nombre'),

    array('db' => 'id_producto',        	'dt' => 'id_producto'),
    array('db' => 'arr_unidad_id',  		'dt' => 'arr_unidad_id'),
    array('db' => 'arr_unidad',         	'dt' => 'arr_unidad'),
	array('db' => 'arr_tamanio',        	'dt' => 'arr_tamanio'),

    array('db' => 'arr_asignacion_id',  	'dt' => 'arr_asignacion_id'),
	array('db' => 'arr_precio_actual', 	 	'dt' => 'arr_precio_actual'),
	array('db' => 'arr_costo_actual',  		'dt' => 'arr_costo_actual'),

);


foreach ($arr_id_almacen_res as $key => $element ) {
    $columna_stock[$key] = array('db' => 'arr_val_stock'.$element, 'dt' => 'arr_val_stock'.$element);
}

/*
$cont = 7;
foreach ($arr_id_almacen_res as $key => $element ) {
    $columna_stock_col[$key] = array('db' => 'stock'.$element, 'dt' => $cont);
    $cont++;
}
*/

$cont = 7;
foreach ($arr_id_almacen_res as $key => $element ) {
    $columna_stock_col[$key] = array('db' => 'arr_val_stock'.$element, 'dt' => $cont);
    $cont++;
}

$columns = array_merge($columns , $columna_stock, $columna_stock_col);



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
