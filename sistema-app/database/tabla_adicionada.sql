-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.2.6-MariaDB-log - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;



USE `icaba_lp`;

-- Volcando estructura para tabla icaba_20102021.inv_almacen_empleados
CREATE TABLE IF NOT EXISTS `inv_almacen_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla icaba_20102021.inv_almacen_empleados: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_almacen_empleados` DISABLE KEYS */;
INSERT INTO `inv_almacen_empleados` (`id`,`empleado_id`, `almacen_id`) VALUES
 (1,14, 1),
 (2,14, 24),
 (3,14, 6),
 (4,14, 7),
 (5,14, 9),
 (6,14, 12),
 (7,14, 14),
 (8,14, 15),
 (9,14, 17),
 (10,14, 22),
 (11,14, 18),
 (12,14, 25);
/*!40000 ALTER TABLE `inv_almacen_empleados` ENABLE KEYS */;

-- Volcando estructura para tabla icaba_lp.inv_almacen_sucursales
CREATE TABLE IF NOT EXISTS `inv_almacen_sucursales` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `almacen_id` int(11) NOT NULL,
  `sucursal_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



ALTER TABLE `inv_egresos`
	CHANGE COLUMN `tipo` `tipo` ENUM('Venta','Traspaso','Baja','Ajuste','Nota','Electronica','Manual') NOT NULL COLLATE 'utf8_unicode_ci' AFTER `hora_egreso`;

-- Volcando datos para la tabla icaba_lp.inv_almacen_sucursales: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_almacen_sucursales` DISABLE KEYS */;
INSERT INTO `inv_almacen_sucursales` (`id`, `almacen_id`, `sucursal_id`) VALUES
	(1, 1, 2),
	(4, 18, 9),
	(5, 22, 3),
	(6, 24, 6),
	(7, 15, 10),
	(8, 14, 7);
/*!40000 ALTER TABLE `inv_almacen_sucursales` ENABLE KEYS */;

-- Volcando datos para la tabla icaba_lp.sys_permisos: 135 rows
/*!40000 ALTER TABLE `sys_permisos` DISABLE KEYS */;
TRUNCATE TABLE `sys_permisos`;

INSERT INTO `sys_permisos` (`rol_id`, `menu_id`, `archivos`) VALUES
	(8, 5, ''),
	(8, 3, ''),
	(8, 9, 'imprimir,capturar,validar,listar,subir,eliminar,ver,asignar,guardar,crear,editar,actualizar,activar'),
	(3, 23, 'listar,listar1,mostrar'),
	(3, 32, 'imprimir,permiso_traspaso,listar,ver,historiar,crear2,guardar,crear,suprimir,crear,actualizar,activar'),
	(3, 30, ''),
	(3, 48, 'obtener,imprimir,termico,buscar,permiso_sucursal,eliminar,crear_,ver,historiar,mostrar,seleccionar_sucursal,crear2,guardar,crear,crear3,facturar,guardar_,actualizar,modificar,XimprimirX,listar_clientes_categoria'),
	(4, 43, 'xproformas_imprimir,notas_ver,proformas_obtener,notas_anular,facturas_listar,proformas_editar,proformas_facturar,manuales_listar,proformas_imprimir,historiar,seleccionar_sucursal,notas_obtener,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener,'),
	(3, 44, 'obtener,imprimir,gastos_listar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,ingresos_listar,mostrar,egresos_imprimir,balance_caja,gastos_guardar,gastos_crear,caja,cerrar,egresos_crear,ingresos_imprimir,imprimir_general,ingresos_crear'),
	(7, 33, 'obtener,imprimir,imprimir_simple,buscar,permiso_sucursalr,eliminar,cantidad_sucursales,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,facturar,actualizar,modificar'),
	(7, 48, 'obtener,imprimir,termico,buscar,permiso_sucursal,notas_anular,eliminar,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,crear3,facturar,actualizar,ximprimirx'),
	(7, 20, 'imprimir,validar,listar,generarbc,subir,saltar,ver,generar,asignar,quitar,'),
	(7, 25, 'listar'),
	(7, 30, ''),
	(5, 32, ''),
	(5, 30, ''),
	(5, 33, 'obtener,buscar,permiso_sucursal,eliminar,cantidad_sucursales,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,facturar,actualizar,modificar'),
	(1, 62, ''),
	(1, 76, ''),
	(1, 78, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,reporte_cuentas_cobrar,imprimir_comprobante,mostrar,imprimir_factura,guardar_plan_pagos,guardar_pago,eliminar_pago'),
	(1, 77, 'pagar,eliminar,imprimir_comprobante,ver,mostrar,guardar_plan_pagos,reporte_proveedores,utilidad,guardar_pago,reporte_cuentas_pagar,plan_pagos,crear,eliminar_pago,reporte_proveedores_detalle,ver-ultimaversionymejor,listar_pagos'),
	(1, 63, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,reporte_cuentas_cobrar,imprimir_comprobante,mostrar,imprimir_factura,guardar_plan_pagos,guardar_pago,eliminar_pago'),
	(1, 59, 'pagar,eliminar,imprimir_comprobante,ver,mostrar,guardar_plan_pagos,reporte_proveedores,utilidad,guardar_pago,reporte_cuentas_pagar,plan_pagos,crear,eliminar_pago,reporte_proveedores_detalle,ver-ultimaversionymejor,listar_pagos'),
	(1, 61, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,eliminar,delete,guardar_pagos,ver,cronograma,guardar,reporte_cuentas_pagar,plan_pagos,crear'),
	(4, 44, ''),
	(4, 62, ''),
	(4, 63, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,reporte_cuentas_cobrar,imprimir_comprobante,mostrar,imprimir_factura,guardar_plan_pagos,guardar_pago,eliminar_pago'),
	(5, 25, 'listar'),
	(1, 67, 'pagar,eliminar,imprimir_comprobante,ver,mostrar,guardar_plan_pagos,reporte_proveedores,utilidad,guardar_pago,reporte_cuentas_pagar,plan_pagos,crear,eliminar_pago,reporte_proveedores_detalle,ver-ultimaversionymejor,listar_pagos'),
	(1, 45, 'obtener,imprimir,gastos_listar,egresos_eliminar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,gastos_modificar,ingresos_listar,mostrar,egresos_imprimir,egresos_modificar,balance_caja,gastos_guardar,gastos_crear,caja,gastos_eliminar,cerrar,egresos_crear,ingresos_eliminar,ingresos_imprimir,imprimir_general,ingresos_modificar,ingresos_crear'),
	(1, 44, 'obtener,imprimir,gastos_listar,egresos_eliminar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,gastos_modificar,ingresos_listar,mostrar,egresos_imprimir,egresos_modificar,balance_caja,gastos_guardar,gastos_crear,caja,gastos_eliminar,cerrar,egresos_crear,ingresos_eliminar,ingresos_imprimir,imprimir_general,ingresos_modificar,ingresos_crear'),
	(3, 19, ''),
	(3, 20, 'validar,listar,generarbc,subir,saltar,ver,img,fijar,cambiar,editar,procesos,guardar'),
	(4, 45, 'obtener,imprimir,gastos_listar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,ingresos_listar,mostrar,egresos_imprimir,balance_caja,gastos_guardar,gastos_crear,caja,cerrar,egresos_crear,ingresos_imprimir,imprimir_general,ingresos_crear'),
	(5, 23, 'listar,listar1,mostrar'),
	(1, 56, 'diario,imprimir,utilidades_dia,ventas_notas,header_footer,ventas_generales,utilidades,ventas_electronicas,ventas_manuales,ventas_personales'),
	(1, 27, ''),
	(1, 57, 'verificar'),
	(1, 29, 'imprimir,listar,eliminar,ver,bloquear,guardar,crear,editar'),
	(1, 28, 'imprimir,listar,eliminar,ver,guardar,descargar,crear,editar'),
	(1, 24, ''),
	(1, 52, ''),
	(1, 51, 'obtener,imprimir,gastos_listar,egresos_eliminar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,gastos_modificar,ingresos_listar,mostrar,egresos_imprimir,egresos_modificar,balance_caja,gastos_guardar,gastos_crear,caja,gastos_eliminar,cerrar,egresos_crear,ingresos_eliminar,ingresos_imprimir,imprimir_general,ingresos_modificar,ingresos_crear'),
	(1, 46, 'obtener,imprimir,gastos_listar,egresos_eliminar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,gastos_modificar,ingresos_listar,mostrar,egresos_imprimir,egresos_modificar,balance_caja,gastos_guardar,gastos_crear,caja,gastos_eliminar,cerrar,egresos_crear,ingresos_eliminar,ingresos_imprimir,imprimir_general,ingresos_modificar,ingresos_crear'),
	(1, 35, 'diario,imprimir,utilidades_dia,ventas_notas,header_footer,ventas_generales,utilidades,ventas_electronicas,ventas_manuales,ventas_personales'),
	(1, 55, 'diario,imprimir,utilidades_dia,ventas_notas,header_footer,ventas_generales,utilidades,ventas_electronicas,ventas_manuales,ventas_personales'),
	(1, 39, 'diario,imprimir,utilidades_dia,ventas_notas,header_footer,ventas_generales,utilidades,ventas_electronicas,ventas_manuales,ventas_personales'),
	(8, 2, ''),
	(4, 32, 'imprimir,permiso_traspaso,listar,ver,historiar,crear2,guardar,crear,suprimir,crear,editar,actualizar'),
	(4, 30, ''),
	(4, 48, 'obtener,imprimir,termico,buscar,permiso_sucursal,notas_anular,eliminar,crear_,ver,historiar,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,crear3,facturar,guardar_,actualizar,notas_modificar,modificar,XimprimirX'),
	(8, 4, ''),
	(8, 6, ''),
	(2, 19, ''),
	(2, 31, ''),
	(2, 20, ''),
	(2, 21, ''),
	(2, 22, ''),
	(2, 23, 'listar,listar1,mostrar,ajustar'),
	(2, 53, ''),
	(2, 25, ''),
	(2, 32, ''),
	(7, 24, ''),
	(7, 51, ''),
	(7, 46, ''),
	(7, 45, ''),
	(7, 44, ''),
	(4, 33, 'obtener,imprimir,imprimir_simple,buscar,permiso_sucursal,eliminar,cantidad_sucursales,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,facturar,actualizar,modificar'),
	(3, 51, ''),
	(3, 46, ''),
	(3, 45, ''),
	(3, 43, 'xproformas_imprimir,notas_ver,proformas_obtener,notas_anular,facturas_listar,proformas_editar,proformas_facturar,manuales_listar,proformas_imprimir,historiar,seleccionar_sucursal,notas_obtener,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(3, 24, ''),
	(3, 33, 'obtener,imprimir,imprimir_simple,buscar,permiso_sucursal,cantidad_sucursales,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,facturar,actualizar,modificar'),
	(3, 54, 'xproformas_imprimir,notas_ver,proformas_obtener,facturas_listar,proformas_facturar,manuales_listar,proformas_imprimir,historiar,seleccionar_sucursal,notas_obtener,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_listar,proformas_remision,facturas_obtener'),
	(1, 33, 'obtener,imprimir,imprimir_simple,buscar,permiso_sucursal,eliminar,cantidad_sucursales,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,facturar,actualizar,modificar'),
	(1, 41, ''),
	(1, 74, 'proformas_eliminar,xproformas_imprimir,notas_ver,buscar,proformas_obtener,notas_anular,facturas_listar,proformas_editar,guardar_facturar,proformas_facturar,manuales_listar,proformas_imprimir,seleccionar_sucursal_factura,historiar,seleccionar_sucursal,selecciones_sucursal_factura,notas_obtener,guardar,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_eliminar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(1, 73, 'proformas_eliminar,xproformas_imprimir,notas_ver,buscar,proformas_obtener,notas_anular,facturas_listar,proformas_editar,guardar_facturar,proformas_facturar,manuales_listar,proformas_imprimir,seleccionar_sucursal_factura,historiar,seleccionar_sucursal,selecciones_sucursal_factura,notas_obtener,guardar,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_eliminar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(1, 54, 'proformas_eliminar,xproformas_imprimir,notas_ver,buscar,proformas_obtener,notas_anular,facturas_listar,proformas_editar,guardar_facturar,proformas_facturar,manuales_listar,proformas_imprimir,seleccionar_sucursal_factura,historiar,seleccionar_sucursal,selecciones_sucursal_factura,notas_obtener,guardar,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_eliminar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(1, 26, ''),
	(1, 69, 'diario,imprimir,utilidades_dia,ventas_notas,header_footer,ventas_generales,utilidades,ventas_electronicas,ventas_manuales,ventas_personales'),
	(4, 24, ''),
	(4, 51, 'obtener,imprimir,gastos_listar,ingresos_guardar,abrir_caja,cerrar_caja,egresos_guardar,gastos_imprimir,egresos_listar,ingresos_listar,mostrar,egresos_imprimir,balance_caja,gastos_guardar,gastos_crear,caja,cerrar,egresos_crear,ingresos_imprimir,imprimir_general,ingresos_crear'),
	(4, 46, ''),
	(4, 54, 'xproformas_imprimir,notas_ver,proformas_obtener,notas_anular,facturas_listar,proformas_editar,proformas_facturar,manuales_listar,proformas_imprimir,historiar,seleccionar_sucursal,notas_obtener,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(8, 24, ''),
	(8, 52, 'obtener,imprimir,gastos_listar,egresos_eliminar'),
	(8, 51, ''),
	(8, 46, ''),
	(8, 45, ''),
	(8, 44, ''),
	(1, 66, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,reporte_cuentas_cobrar,imprimir_comprobante,mostrar,imprimir_factura,guardar_plan_pagos,guardar_pago,eliminar_pago'),
	(1, 43, 'proformas_eliminar,xproformas_imprimir,notas_ver,buscar,proformas_obtener,notas_anular,facturas_listar,proformas_editar,guardar_facturar,proformas_facturar,manuales_listar,proformas_imprimir,seleccionar_sucursal_factura,historiar,seleccionar_sucursal,selecciones_sucursal_factura,notas_obtener,guardar,facturas_imprimir,proformas_ver,proformas_listar,facturas_ver,guardar_remision,notas_modificar,notas_eliminar,notas_listar,facturas_editar,proformas_remision,proformas_modificar,facturas_obtener'),
	(1, 71, 'obtener,imprimir,buscar,permiso_sucursal,guardar_facturar,eliminar,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,crear3,facturar,actualizar,imprimir___simple,XimprimirX'),
	(1, 48, 'actualizar,api_guardar_nota,api_obtener_productos,buscar,crear,crear_original,editar,eliminar,facturar,historiar,imprimir,listar,listar_clientes_categoria,modificar,mostrar,notas_anular,notas_modificar,obtener,permiso_sucursal,seleccionar_sucursal,termico,ver'),
	(3, 62, ''),
	(3, 63, 'reporte_clientes_detalle,notas_ver,listar,pagar,reporte_clientes,reporte_cuentas_cobrar,imprimir_comprobante,mostrar,imprimir_factura,guardar_plan_pagos,guardar_pago,eliminar_pago'),
	(4, 19, ''),
	(4, 20, 'validar, listar,generarbc,subir,saltar,ver,img,fijar,cambiar,editar,procesos,guardar'),
	(4, 23, 'listar,listar1,mostrar'),
	(1, 30, ''),
	(1, 72, 'obtener,imprimir,buscar,permiso_sucursal,eliminar,ver,mostrar,seleccionar_sucursal,crear2,guardar,crear,editar,crear3,facturar,actualizar,XimprimirX'),
	(1, 23, 'listar,listar1,mostrar,eliminar_tmp,ajustar'),
	(1, 25, 'listar'),
	(1, 53, 'imprimir,listar,imprimir____simple,imprimir2,detallar3,detallar,detallar2'),
	(1, 31, 'activar,api_guardar_compra,api_obtener_ingresos,api_obtener_productos,crear,editar,eliminar,guardar,historiar,imprimir,listar,listar_nuevo,listar_original,permiso_sucursal,seleccionar_sucursal,suprimir,ver'),
	(1, 32, 'activar,actualizar,api_almacenes_menos_actual,api_guardar_egreso,api_obtener_almacen_empleado,api_obtener_egresos,api_obtener_empleados,api_obtener_productos,Baja_Extraoficial,crear,crear2,editar,eliminar,guardar,historiar,imprimir,listar,permiso_baja,permiso_traspaso,seleccionar_sucursal,suprimir,ver,cambiar_rango'),
	(1, 17, 'imprimir,listar,guardar,modificar'),
	(1, 16, 'imprimir,listar,subir,eliminar,saltar,guardar,suprimir,crear,img,modificar'),
	(1, 19, ''),
	(1, 20, 'api_actualizar_precio_asignacion,api_editar_producto,api_eliminar_asignacion_unidad,api_eliminar_producto,api_guardar_asignacion_unidad,api_guardar_producto,api_obtener_productos,api_obtener_unidades,crear,editar,generar,generarbc,img,imprimir,listar,saltar,subir,suprimir,unidad,validar,validar_barras,validar_barras_editar,ver,editar,eliminar'),
	(1, 22, 'imprimir,listar,eliminar,utilidades,ver,asignar,quitar,unidad,actualizar,cambiar,procesos'),
	(1, 21, 'listar'),
	(1, 15, ''),
	(1, 12, 'api_eliminar_asignacion,api_guardar_almacen_empleado,api_obtener_almacenes,api_obtener_empleados,crear,editar,eliminar,guardar,imprimir,listar,ver,asignar'),
	(1, 13, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 14, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 75, 'listar,guardar,editar'),
	(1, 70, 'api_almacenes_habilitados,api_editar_sucursal,api_eliminar_asignacion,api_guardar_almacen_empleado,api_guardar_sucursal,api_obtener_empleados,api_obtener_sucursales,crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 11, ''),
	(1, 18, 'api_eliminar_unidades,api_obtener_unidades,crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 79, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 9, 'imprimir,capturar,validar,listar,subir,eliminar,ver,asignar,guardar,crear,editar,actualizar,activar'),
	(1, 1, ''),
	(1, 2, ''),
	(1, 4, 'reportes_guardar,institucion_editar,apariencia_guardar,institucion,reportes_editar,preferencias_editar,reportes,preferencias,preferencias_guardar,institucion_guardar,apariencia'),
	(1, 6, 'reportes_guardar,institucion_editar,apariencia_guardar,institucion,reportes_editar,preferencias_editar,reportes,preferencias,preferencias_guardar,institucion_guardar,apariencia'),
	(1, 5, 'reportes_guardar,institucion_editar,apariencia_guardar,institucion,reportes_editar,preferencias_editar,reportes,preferencias,preferencias_guardar,institucion_guardar,apariencia'),
	(1, 10, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 8, 'listar,asignar,guardar'),
	(1, 7, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 3, 'reportes_guardar,institucion_editar,apariencia_guardar,institucion,reportes_editar,preferencias_editar,reportes,preferencias,preferencias_guardar,institucion_guardar,apariencia'),
	(5, 19, ''),
	(5, 20, 'listar');
/*!40000 ALTER TABLE `sys_permisos` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;


