<?php
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Obtiene los empleados
	$empleados = $db->query("
		SELECT 
			e.id_empleado,
			u.username,
			UPPER(CONCAT(e.nombres ,' ', e.paterno, ' ', e.materno))  AS empleado
		FROM sys_empleados e
		LEFT JOIN inv_almacen_empleados ae ON ae.empleado_id = e.id_empleado
		LEFT JOIN sys_users u ON u.persona_id = e.id_empleado
		LEFT JOIN sys_roles r ON r.id_rol = u.rol_id
		WHERE ae.id is null
			AND u.persona_id is not null
			AND u.active = 1
			AND r.id_rol !=1 AND r.id_rol !=2
		ORDER BY e.nombres
	")->fetch();
		
	// Envia respuesta
	echo json_encode($empleados);
} else {
	// Error 404
	require_once not_found();
	exit;
}
