<?php

//echo json_encode($_POST);
//exit;
// Verifica si es una peticion post
if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (
        //egresoss   
        isset($_POST['monto_total_confirmation']) &&      
        isset($_POST['nombre_cliente']) &&   
        isset($_POST['nit_ci']) &&           
        isset($_POST['nro_registros']) &&    
        isset($_POST['almacen_id']) &&     
        isset($_POST['plan_de_pagos']) &&    
        isset($_POST['telefono']) &&         
        isset($_POST['direccion']) &&
        isset($_POST['observacion']) &&         
        isset($_POST['descuento']) &&         
        isset($_POST['tipo_pago']) &&  
        isset($_POST['sucursal_id']) &&      
        isset($_POST['cliente_id']) &&       
        
        //egresos_detalles
        isset($_POST['arr_producto_id']) &&
        isset($_POST['arr_asignacion_id']) &&
        isset($_POST['arr_cantidad']) &&
        isset($_POST['arr_precio'])

    ) 
    {
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */
        //@yottabm check_stock() verificamos el stock actual recibe el parametro de la conexion de la base de datos en caso de error de validacion retorna una respuesta
		check_stock($db);
        // obtiene los datos para la tabla egresos
        $fecha_egreso           = date('Y-m-d');
        $hora_egreso            = date('H:i:s');
        $tipo                   = 'Nota';
        $provisionado           = 'S';
        $descripcion            = 'Nota de remision';
        $nro_factura            = 0;
        $nro_autorizacion       = 0;
        $codigo_control         = 0;
        $fecha_limite           = '0000-00-00';
        $monto_total            = trim($_POST['monto_total_confirmation']);
        $nombre_cliente         = trim($_POST['nombre_cliente']);
        $nit_ci                 = trim($_POST['nit_ci']);
        $nro_registros          = trim($_POST['nro_registros']);
        $dosificacion_id        = 0;
        $almacen_id             = trim($_POST['almacen_id']);
        $empleado_id            = $_user['persona_id']; // usuario sesion actual
        $plan_de_pagos          = trim($_POST['plan_de_pagos']);
        $telefono               = trim($_POST['telefono']);
        $direccion              = trim($_POST['direccion']);
        $observacion            = trim($_POST['observacion']);
        $descuento              = trim($_POST['descuento']);
        $estado                 = 'V';
        $tipo_pago              = trim($_POST['tipo_pago']);
        $sucursal_id            = trim($_POST['sucursal_id']);
        $responsable_id         = 0; //no se usa por el momento
        $conductor_id           = 0; //no se usa por el momento
        $cliente_id             = trim($_POST['cliente_id']);
  


         // Instancia el egreso
        $egreso = array(
            'fecha_egreso'           => $fecha_egreso,           
            'hora_egreso'            => $hora_egreso,            
            'tipo'                   => $tipo,                   
            'provisionado'           => $provisionado,           
            'descripcion'            => $descripcion,            
            'nro_factura'            => $nro_factura,            
            'nro_autorizacion'       => $nro_autorizacion,       
            'codigo_control'         => $codigo_control,         
            'fecha_limite'           => $fecha_limite,           
            'monto_total'            => $monto_total,            
            'nombre_cliente'         => $nombre_cliente,         
            'nit_ci'                 => $nit_ci,                 
            'nro_registros'          => $nro_registros,          
            'dosificacion_id'        => $dosificacion_id,        
            'almacen_id'             => $almacen_id,             
            'empleado_id'            => $empleado_id,            
            'plan_de_pagos'          => $plan_de_pagos,          
            'telefono'               => $telefono,               
            'direccion'              => $direccion,              
            'observacion'            => $observacion,            
            'descuento'              => $descuento,              
            'estado'                 => $estado,                 
            'tipo_de_pago'           => $tipo_pago,              
            'sucursal_id'            => $sucursal_id,            
            'responsable_id'         => $responsable_id,         
            'conductor_id'           => $conductor_id,           
            'cliente_id'             => $cliente_id,
        );

         // Guarda la informacion del ingreso y obtiene el id_ingreso
        $id_egreso = $db->insert('inv_egresos', $egreso);
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */
         // obtiene los datos para la tabla egresos_detalles
        $egreso_id              = isset($id_egreso) ?  $id_egreso : 0;
        $arr_producto_id        = $_POST['arr_producto_id'] ;
        $arr_asignacion_id      = $_POST['arr_asignacion_id'] ;
        $arr_cantidad           = $_POST['arr_cantidad'] ;
        $arr_precio             = $_POST['arr_precio'] ;

        // recoore los productos del array para ingresos_detalles
        foreach ($arr_producto_id as $nro => $elemento) {
            // Forma el detalle
            $egreso_detalle = array(
                'egreso_id'     => $egreso_id,
                'producto_id'   => $arr_producto_id[$nro],
                'asignacion_id' => $arr_asignacion_id[$nro],
                'cantidad'      => $arr_cantidad[$nro],
                'precio'    => $arr_precio[$nro], 
            );
            // Guarda la informacion
            $db->insert('inv_egresos_detalles', $egreso_detalle);
        }
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */
        //si solo si el cliente_id es 0 se crea uno nuevo
        if(
            $cliente_id == 0 &&
            isset($_POST['nombre_cliente']) &&
            isset($_POST['nit_ci']) &&
            isset($_POST['categoria_cliente_id']) 
            
        ){
            //obtiene los datos para la tabla clientes
            $nombre_cliente         = trim($_POST['nombre_cliente']);
            $nit_ci                 = trim($_POST['nit_ci']);
            $telefono               = trim($_POST['telefono']);
            $escalafon              = '';
            $imagen                 = '';
            $categoria_cliente_id   = trim($_POST['categoria_cliente_id']);

            $cliente = array(
                'nombre_cliente'             => $nombre_cliente,                
                'nit_ci'                     => $nit_ci,                 
                'telefono'                   => $telefono,               
                'escalafon'                  => $escalafon,              
                'imagen'                     => $imagen,                 
                'categoria_cliente_id'       => $categoria_cliente_id,   
            );
            $cliente_id = $db->insert('inv_clientes', $cliente);
        }
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */
        //si solo el plan de pagos es si
        if(
            $plan_de_pagos == 'si' &&
            isset($_POST['arr_nro_cuota']) &&
            isset($_POST['arr_fecha_pago']) &&
            isset($_POST['arr_monto'])
        )
        {
            // obtiene los datos para la tabla ingresos
            $movimiento_id  = $id_egreso;
            $interes_pago   = 0;
            $tipo           = 'Egreso';
            // Instancia el pago
            $pago = array(
                'movimiento_id' => $movimiento_id,
                'interes_pago'  => $interes_pago,
                'tipo'          => $tipo,
            );
            $id_pago = $db->insert('inv_pagos', $pago);

            //obtiene los datos para la tabla pagos detalles
            $pago_id            = isset($id_pago)?  $id_pago : 0;
            $fecha              = date('Y-m-d');
            $arr_nro_cuota      = $_POST['arr_nro_cuota'] ;
            $arr_fecha_pago     = $_POST['arr_fecha_pago'] ;
            $arr_monto          = $_POST['arr_monto'] ;
            $estado             = 1;
            $tipo_pago          = trim($_POST['tipo_pago']);
            $empleado_id        = $_user['persona_id'];
            // recoore los productos del array para ingresos_detalles
            foreach ($arr_nro_cuota as $nro => $elemento) {
                // Forma el detalle
                $pago_detalle = array(
                    'pago_id'       => $id_pago,
                    'fecha'         => $fecha,
                    'nro_cuota'     => $arr_nro_cuota[$nro],
                    'fecha_pago'    => $arr_fecha_pago[$nro],
                    'monto'         => $arr_monto[$nro],
                    'estado'        => $estado, 
                    'tipo_pago'     => $tipo_pago, 
                    'empleado_id'   => $empleado_id, 
                );
                // Guarda la informacion
                $db->insert('inv_pagos_detalles', $pago_detalle);
            }
            // Envia respuesta
            echo json_encode([
                "status" => 201, //status 201 creacion
                "title" => "Venta Exitosa !",
                "type" => "success", //info  warning
                "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
                "message" => "La nota de remision y los planes de pagos se han registrado correctamente"
            ]);
            exit;

        }
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */

        // Envia respuesta
        echo json_encode([
            "status" => 201, //status 201 creacion
            "title" => "Venta Exitosa !",
            "type" => "success", //info  warning
            "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
            "message" => "La nota de remision se han registrado correctamente"
        ]);

    
    } else {
        // Error 400
        require_once bad_request();
        exit;
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}


//@yotabm funcion validar stock mediante la peticion requerida
function check_stock($db)
{	
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */

	//@yottabm parametros de post contiene array de lo solicitado 
	$almacen_id = trim($_POST['almacen_id']);

    $arr_id_producto = array_unique($_POST['arr_producto_id']); //eliminamos campos repetidos
	//@yottabm generamos un string con el id de los productos para la consulta
	$str_id_productos =	implode(',',$arr_id_producto);

	//@yottabm array productos_post almacena los productos enviados con su cantidad ya sumado
	$productos_post = [];
	foreach ($arr_id_producto as $nro => $elemento) {
		//@yottabm unimos los arrays id_producto y cantidadespara su facil manipulacion a continuacion

        $id = $arr_id_producto[$nro];
        $cantidad = trim($_POST['sum_cantidad_'.$id]);

		$productos_post [] = array(
			'id_producto' => $arr_id_producto[$nro],
			'cantidad' => $cantidad,
		);
	}

//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */

	//obtiene los productos con el stock actual
	$productos_current = $db->query("
        select 
            p.id_producto, 
            p.codigo, 
            p.nombre,


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
                where i.almacen_id = $almacen_id
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
                where e.almacen_id = $almacen_id
                and e.estado = 'v'
                group by edt.producto_id
            ) as egre on egre.producto_id = p.id_producto
            
            where p.id_producto in ($str_id_productos)
            /*agrupamos por el tipo de producto*/
            group by p.id_producto
		")->fetch();

//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */


	//@yottabm array respuesta almacena los productos con cantidad insuficiente
	$respuesta = [];
	//@yottabm variable de validacion de stock
	$check_stock = true;
	foreach ($productos_current as $i => $elemento) {

		foreach ($productos_post as $j => $el) {
			//@yottabm verificamos que el id_producto se igual al del producto enviado 
			if($productos_current[$i]['id_producto'] == $productos_post[$j]['id_producto'] )
			{	//@yottabm verificamos que la cantidad enviada sea mayor si es mayor lo almacenamos en el array respuesta 
				if( $productos_current[$i]['stock'] < $productos_post[$j]['cantidad']  ){
					//@yottabm almacenamos los productos que no cumplen con el stock requerido en el array respuesta
					$respuesta [] = array(
						'id_producto' => $productos_current[$i]['id_producto'],
						'codigo' => $productos_current[$i]['codigo'],
						'nombre' => $productos_current[$i]['nombre'],
						'cantidad_requerido' => $productos_post[$j]['cantidad'],
						'cantidad_actual' => $productos_current[$i]['stock'],
						'check' => false,
					);
					$check_stock = false;
				}
			}
		}
	}
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */

	//@yottabm si la variable cambio a false retornamos la respuesta con el array de los productos que no tienen suficiente stock
	if(!$check_stock){
        echo json_encode([
            "status" => 400, //status 201 creacion
            "title" => "Error !",
            "productos" => $respuesta,
            "type" => "danger", //info  warning
            "icon" => "glyphicon glyphicon-ok", //"glyphicon glyphicon-info-sign",
            "message" => 'El stock es insuficiente verifica y vuelve a intentarlo'
        ]);
        exit;

	}
//******************************************************************************************************************************************************************************************* */
//******************************************************************************************************************************************************************************************* */


}

