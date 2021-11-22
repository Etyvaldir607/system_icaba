<?php
//echo "llega punto 000";

if (isset($_POST['nro'])) {

//echo "llega punto 111";

	$nro=$_POST['nro'];
	if (isset($_POST['tipo'.$nro]) && isset($_POST['monto'.$nro]) && isset($_POST['f0'.$nro]) && isset($_POST['pago']) && isset($_POST['fx'.$nro])) {

		//echo "llega punto 222";

		$id=$_POST['f0'.$nro];
		$f4=$_POST['monto'.$nro];
		$id_pago=$_POST['pago'];
		$nro_cuota=$_POST['fx'.$nro];
		$estado=1;

		$persona_id=$_POST['persona_id'];

		if(isset($_POST['inicial_fecha_'.$nro])){
			$f1=$_POST['inicial_fecha_'.$nro];
		} else {
			$f1="";			
		}

		if(isset($_POST['pago_fecha_'.$nro])){
			$f2=$_POST['pago_fecha_'.$nro];
		} else {
			$f2="";			
		}
		$f3 = $_POST['tipo'.$nro];
		
		if($f1==""){				$estado=0;	}
		if($f2==""){				$estado=0;	}
		if($f3=="" || $f3=="-"){	$estado=0;	$f3=="";	}

		$resultado = $db->select('*')
						->from('inv_pagos_detalles i')
						->where('id_pago_detalle', $id)
						->fetch_first();

		if($resultado) {
			$ingreso = array(
				'nro_cuota' 	=> $nro_cuota,
				'fecha' 		=> date_encode($f1),
				'fecha_pago'	=> date_encode($f2),
				'tipo_pago' 	=> $f3,
				'monto' 		=> $f4,			
				'estado' 		=> $estado,
				'empleado_id' 	=> $persona_id
			);

			$condicion = array('id_pago_detalle' => $id);			
			$db->where($condicion)->update('inv_pagos_detalles', $ingreso);
			echo "1|".$id."|".$estado;	
		} else {
			$detallePlan = array(
					'pago_id'	=>$id_pago,
					'nro_cuota'	=>$nro_cuota,
					'fecha' 	=> date_encode($f1),
					'fecha_pago'=> date_encode($f2),
					'tipo_pago' => $f3,
					'monto' 	=> $f4,			
					'estado' 	=> $estado,
					'empleado_id' => $persona_id
				);
				// Guarda la informacion
			$id=$db->insert('inv_pagos_detalles', $detallePlan);
			echo "1|".$id."|".$estado;	
		}
	}
	else{
		echo "0".$nro;
	}
}
else{
	echo "00000";
}
?>


