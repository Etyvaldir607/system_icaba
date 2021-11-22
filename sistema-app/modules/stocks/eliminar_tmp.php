<?php

if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id'])) {
	    
        $almacen_id	= isset($_POST['almacen_id']) ? trim($_POST['almacen_id']) : 0;
        // var_dump('llegó id_almacen: >>'.$almacen_id.'<<');die();
        
        if($almacen_id!=0){
            $db->query("DELETE FROM tmp_ajustar WHERE almacen_id = '$almacen_id'")->execute();    
            // var_dump('ELIMINÓ SOLO SUCURSAL '.$almacen_id);
            
        }else
        {
            $db->query('delete from tmp_ajustar')->execute();
            // var_dump('ELIMINÓ LA TABLA COMPLETA');
            // header('Location: ?/stocks/listar');
            // exit;
            
        }
        echo json_encode($almacen_id);


	} else {
		// Envia respuesta
		echo 'Parámetros indefinidos!';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}
?>