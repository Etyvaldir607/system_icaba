<?php
	// var_dump($_POST);die();
	if (isset($_POST['imagen']) && isset($_POST['id_cliente'])) {
	    
	    $datos = base64_decode(
	      preg_replace('/^[^,]*,/', '', $_POST['imagen'])
	    );

	    $id_cliente=$_POST['id_cliente'];


		$strix="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$codix="";
		for($i=0; $i<=29 ;$i++){
			$r=rand(0,35);
			$codix.=substr($strix,$r,1);
		}
		
		$imgg=$codix.".jpg";
		$asignacion = array('imagen'=> $imgg);
		$condicion = array('id_cliente' => $id_cliente);
		$db->where($condicion)->update('inv_clientes', $asignacion);
		
	    file_put_contents('../sistema-app/files/clientes/'.$codix.'.jpg', $datos);
	    
	    echo $codix.".jpg";
	}
?>