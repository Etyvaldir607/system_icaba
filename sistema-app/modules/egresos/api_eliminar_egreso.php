<?php
if (is_post()) {
    if (isset($_POST['id_egreso'])) {
        // Obtiene el id_egreso
        $id_egreso = $_POST['id_egreso'];

        // Obtiene el egreso
        $egreso = $db->from('inv_egresos')
                    ->where('id_egreso', $id_egreso)
                    ->fetch_first();

        // Obtiene el id_almacen
        $id_almacen = $egreso["almacen_id"];

        // obtiene el detalles del egreso actual 
        $arr_detalle_egreso = $db->query("
                                SELECT de.*,
                                (de.cantidad * u.tamanio) AS sum_cantidad
                                FROM inv_egresos_detalles de
                                LEFT JOIN inv_egresos e ON e.id_egreso = de.egreso_id
                                LEFT JOIN inv_asignaciones a ON a.id_asignacion = de.asignacion_id
                                LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                                WHERE e.id_egreso = $id_egreso
                            ")->fetch();
        
        // @etysoft Forma desglosa los detalles en array individuales para cada caso
        foreach ($arr_detalle_egreso as $nro => $elemento) {
            $arr_producto_id[$nro]      = $elemento['producto_id'];
            $arr_asignacion_id[$nro]    = $elemento['asignacion_id'] ;
            $arr_cantidad[$nro]         = $elemento['cantidad'] ;
            $arr_precio[$nro]           = $elemento['precio'] ;
            $arr_sum_cantidad[$nro]     = $elemento['sum_cantidad'] ;
        }

        // @etysoft verificamos el stock actual, en caso de error de validacion retorna una respuesta
        $validar = check_stock( $db, $arr_producto_id, $arr_asignacion_id, $arr_cantidad, $id_almacen);
        if($validar['control'] > 0){
            echo json_encode([
                "status" => 400, //status 201 creacion
                "title" => "¡Error!",
                "productos" => $validar['agotados'],
                "type" => "warning", //info danger
                "icon" => "glyphicon glyphicon-alert", //"glyphicon glyphicon-info-sign",
                "message" => 'El stock es insuficiente verifica y vuelve a intentarlo'
            ]);
            exit;
        }


        // Elimina el egreso
        $db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();

        // Elimina los detalles
        $db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();


        // Envia respuesta
        echo json_encode([
            "status" => 201, //status 201 creacion
            "title" => "¡Eliminación exitosa!",
            "type" => "success", //info  warning
            "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
            "message" => "El registro del egreso y su detalle se han eliminado."
        ]);

    }else{
        // Error 400
        require_once bad_request();
        exit; 
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}

// @etysoft validar el stock para la venta
function check_stock( $db, Array $productos, Array $asignacion, Array $sum_cantidades, $almacen_id ){
    // agregando validacion de stock actual del producto por unidad
    $productos_val= $productos;
    $cantidades_val = $sum_cantidades;
    $id_almacen_val = $almacen_id;
    $control = 0;
    $agotados= [];
    // Recorre los productos
    foreach ($productos_val as $nro => $elemento) {
        $id_producto_val = $productos_val[$nro];
        $stock_val = $db->query("
            SELECT
                p.id_producto,
                p.nombre,
                p.codigo,
                I.unidad,
                IFNULL((IFNULL(I.cantidad_ingresos, 0) - IFNULL(E.cantidad_egresos, 0)), 0 ) AS stock
            FROM
                inv_productos p
                LEFT JOIN (
                    SELECT
                        u.unidad,
                        d.producto_id,
                        SUM(d.cantidad * u.tamanio) AS cantidad_ingresos
                    FROM
                        inv_ingresos_detalles d
                        LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                        LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                    WHERE
                        i.almacen_id = $almacen_id 
                    GROUP BY
                        d.producto_id
                ) I ON I.producto_id = p.id_producto
                LEFT JOIN (
                    SELECT
                        d.producto_id,
                        SUM(d.cantidad * u.tamanio) as cantidad_egresos
                    FROM
                        inv_egresos_detalles d
                        LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id AND e.estado='V'
                        LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
                    WHERE
                        e.almacen_id = $id_almacen_val
                    GROUP BY
                        d.producto_id
                ) E ON E.producto_id = p.id_producto
                WHERE
            p.id_producto = $id_producto_val		
        ")->fetch_first();

        if($stock_val['stock'] < $cantidades_val[$nro] ){
            $agotados[$control]= array(
                'codigo'=>$stock_val['codigo'],
                'stock'=>(int)($stock_val['stock']),
                'solicitado' => $cantidades_val[$nro],
                'unidad'=>$stock_val['unidad'],
                'nombre'=>$stock_val['nombre']
            );
            $control++;
        }
    }
    return array('control'=>$control, 'agotados' =>$agotados);
}
