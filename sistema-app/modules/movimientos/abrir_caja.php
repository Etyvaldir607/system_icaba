<?php 

$estado = $_POST['estado'];
$fecha = date_encode($_POST['fecha']);


$datos = array('fecha' => date_encode($fecha),
	'hora_caja' => date('H:i:s'),
	'total_ingresos' => 0,
	'total_egresos' => 0,
	'total_saldo' => 0,
	'total_total' => 0,
	'estado' => $estado );

$id = $db->insert('inv_caja',$datos);

$_SESSION[temporary] = array(
	'alert' => 'success',
	'title' => 'Abrir Caja satisfactoriamente!',
	'message' => 'Se abrio la caja de forma satisfactoria.'
);
// Redirecciona a la pagina principal
echo json_encode($id);

?>