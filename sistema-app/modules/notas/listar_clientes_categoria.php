<?php

if (is_post()) {
        
    if(isset($_POST['id_categoria'])):
        $id_proveedor=trim($_POST['id_categoria']);

        $clientes = $db->query("SELECT id_egreso, c.nombre_cliente, c.nit_ci, c.telefono, c.escalafon, GROUP_CONCAT(b.estado SEPARATOR '|') AS pago 
						FROM inv_clientes c
						LEFT JOIN inv_egresos e ON c.id_cliente = e.cliente_id
						LEFT JOIN inv_pagos a ON e.id_egreso = a.movimiento_id
						LEFT JOIN inv_pagos_detalles b ON a.id_pago = b.pago_id
						WHERE categoria_cliente_id = '$id_proveedor'
						GROUP BY c.nombre_cliente ASC, c.nit_ci 
						ORDER BY c.nombre_cliente ASC, c.nit_ci ASC 
						")->fetch();
						
						$res = '';
						foreach ($clientes as $cliente) {
                                    $pago = explode('|',$cliente['pago']);
                                    if(in_array('0', $pago, true)){
                                        $cliente['pago'] = 0;
                                    } else {
                                        $cliente['pago'] = 1;
                                    }
                                     
								$res .= "<option value=" . $cliente['nit_ci'] . "|" . $cliente['nombre_cliente'] . "|" . $cliente['telefono'] . "|" . $cliente['escalafon'] . "|" . $cliente['pago'] . ">" . $cliente['nit_ci'] . " &mdash; " . $cliente['nombre_cliente'] ."</option>";
								}

						
		//crear array con pago 1 2
		
        // $Fecha=date('Y-m-d');

        // $clientes .=  " ORDER BY p.codigo ASC LIMIT 10";
        // $Consulta=$db->query($clientes)->fetch();
        $res1 .= "<option>BUSCAR</option>" . $res;
        echo $res1;
        
    else:
        require_once not_found();
	    die;
    endif;
}else{
    require_once not_found();
    die;
}