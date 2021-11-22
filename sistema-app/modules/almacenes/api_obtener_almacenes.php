<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Instancia el objeto
	
	// Obtiene los productos
	$almacenes = $db->query("
	SELECT
		a.*,
		IFNULL (CONCAT(da.id ),'' ) AS empleado_almacen_id,
		IFNULL (CONCAT(da.id_empleado ),'' ) AS empleado_id,
		IFNULL (CONCAT(da.nombres, ' ', da.paterno, ''),'' ) AS encargado
	FROM
	inv_almacenes a 
	left JOIN inv_almacen_empleados ae ON ae.almacen_id = a.id_almacen
	left JOIN sys_empleados e ON e.id_empleado = ae.empleado_id
	left JOIN 
	(
		SELECT
			a.id_almacen,
			a.almacen,
			ae.id ,
			e.id_empleado,
			e.nombres,
			e.paterno,
			r.id_rol,
			r.rol
		FROM
			inv_almacenes a 
			JOIN inv_almacen_empleados ae ON ae.almacen_id = a.id_almacen 
			JOIN sys_empleados e ON e.id_empleado = ae.empleado_id 
			JOIN sys_users u ON u.persona_id = e.id_empleado
			JOIN sys_roles r ON r.id_rol = u.rol_id AND r.id_rol != 1 AND r.id_rol != 2
		ORDER BY a.id_almacen
	) da
	ON da.id_almacen = a.id_almacen
	GROUP BY a.id_almacen
	")->fetch();
		
	// Envia respuesta
	echo json_encode([
		'almacenes'   => $almacenes,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}
