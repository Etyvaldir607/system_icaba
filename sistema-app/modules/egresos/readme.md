/* notas de remision a la fecha*/
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 10
and e.tipo = 'Venta'
and e.codigo_control = ''
and e.provisionado = 'S'

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc
1335 registros


/* manuales */
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 14
and e.tipo = 'Venta'
and e.codigo_control = ''
and e.provisionado = 'N'

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc
0 registro

/* electronicas */
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 14
and e.tipo = 'Venta'
and e.codigo_control != ''
and e.provisionado = 'N'

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc

0 registro

/* trapasos */
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 10
and e.tipo = 'Traspaso'
and e.codigo_control = ''
and e.provisionado = 'N'

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc
4 regitros


/* bajas */
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 10
and e.tipo = 'Baja'
and e.codigo_control = ''
and e.provisionado = 'N'

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc


203 registros





/* obtener total por empleado */
select e.*,
		a.almacen, 
		a.principal, 
		em.nombres, 
		em.paterno, 
		em.materno
from inv_egresos e
left join inv_almacenes a on a.id_almacen = e.almacen_id
left join sys_empleados em on em.id_empleado = e.empleado_id
/* parametro $_user['persona_id'] */
where e.empleado_id = 10

/* parametro $fecha_inicial */
/*where e.fecha_egreso >=  $fecha_inicial*/
/* parametro $fecha_final */
/*where e.fecha_egreso <= $fecha_final*/
order by e.fecha_egreso desc, e.hora_egreso desc

1542 registros


