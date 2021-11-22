<?php
	if (isset($_POST['imagen']) && isset($_POST['id_producto'])) {
	    
	    $datos = base64_decode(
	      preg_replace('/^[^,]*,/', '', $_POST['imagen'])
	    );

	    $id_producto=$_POST['id_producto'];


		$strix="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$codix="";
		for($i=0; $i<=29 ;$i++){
			$r=rand(0,35);
			$codix.=substr($strix,$r,1);
		}
		
		$imgg=$codix.".jpg";
		$asignacion = array('imagen'=> $imgg);
		$condicion = array('id_producto' => $id_producto);
		$db->where($condicion)->update('inv_productos', $asignacion);
		
	    file_put_contents('../sistema-app/files/productos/'.$codix.'.jpg', $datos);
	    
	    echo $codix.".jpg";
	}
?>