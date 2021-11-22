<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Instancia el objeto
	
	// Obtiene los productos
	$empleados = $db->query("
	SELECT 
		e.id_empleado,
		u.username,
		upper(concat(e.nombres ,' ', e.paterno))  as empleado
	FROM sys_empleados e
	LEFT JOIN inv_almacen_empleados ae on ae.empleado_id = e.id_empleado
	LEFT JOIN sys_users u on u.persona_id = e.id_empleado
	LEFT JOIN sys_roles r ON r.id_rol = u.rol_id AND r.id_rol != 1 AND r.id_rol != 2
	WHERE
		ae.id is null
		AND u.persona_id is not null
		AND u.active = 1
	ORDER BY e.nombres
	")->fetch();
		
	// Envia respuesta
	echo json_encode([
		'empleados'   => $empleados,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}
