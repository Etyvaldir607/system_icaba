informe (26 - 10 - 2021)
permisos en Registro de sucursal
api_almacenes_habilitados

validar la asignacion de empleado
distinto de checkcode
y almacen debe estar asigando a sucursal previamente.

adición - mostrar sucursales asignadas notas
adición - mostrar sucursales asignadas manuales
adición - mostrar sucursales asignadas electronicas
adición - mostrar sucursales asignadas proformas

adición - api obtener listado de sucursales
adición - api obtener empleados disponibles
adición - api eliminar asignacion de empleado a almacen
adición - api guardar asignacion de empleado a almacen
adición - vista listar sucursales

permisos en Registro de sucursal
back
api_almacenes_habilitados,api_eliminar_asignacion,api_guardar_almacen_empleado,api_obtener_empleados,api_obtener_sucursales,
front
asignar

informe (27 - 10 - 2021)
adición - validación por rol en api_obtener_sucursales
adición - validación por rol en api_obtener_sucursales
adición - validación por rol en api_eliminar_asignacion
depuración - alineación de notificación en listar
adición - asignación inicial para empleados en inv_almacen_empleados

modificación - sucursales por usuario en seleccionar sucursal de ingresos
modificación - sucursales por usuario en seleccionar sucursal de notas
modificación - sucursales por usuario en seleccionar sucursal de electronicas
modificación - sucursales por usuario en seleccionar sucursal de manuales
modificación - sucursales por usuario en seleccionar sucursal de proformas

informe (28 - 10 - 2021)
adición - validación existencia de movimientos en eliminar productos
adición - validación existencia de asignaciones en eliminar unidad
adición obtener api obtener unidades
adición obtener api eliminar unidades
modificación listas de unidades


informe (28 - 10 - 2021)
adición - api obtener empleados
adición - pai obtener almacenes (menos el actual)
adición - cargar empleados y almacenes
validación stock


informe (08-11-2021)
modificación api guardar sucursal
modificación api editar sucursal
modificación api obtener almacenes habilitados
modificación obtener sucursales
modificación lista sucursales
modificación eliminar asignación
modificación api guardar asignación de empleado
modificación api obtener almacenes 
modificación lista almacenes 
modificación seleccionar sucursal


permiso en el front (cambiar_rango)

## @etysoft
informe - resumen de modulos trabajados
tablas agregadas:
## inv_almacen_empleados
## inv_almacen_sucursales


campos actualizados en tabla egresos:
## tipo: se agrego ['Electronica','Nota','Manual']

modulos corregidos:
## módulo configuración
## - unidades - adición de API's y validaciones
## - almacenes - adición de API's y validaciones (es posible asignar un empleado)
## - sucursales - adición de API's y validaciones (es obligatorio seleccionar un almacen para crear la sucursal)

## módulo inventarios
## - catalogo de productos - adición de API's y validaciones
## - compras - adición de API's y validaciones
## - egresos - adición de API's y validaciones

## módulo ventas
## - notas de remisión - adición de API's y validaciones


informe (11-11-2021)
modificación formulario cambiar fechas 
prueba para segmentación
adición - validación syock del backend en guardar egreso
adicióm - funciones para redireccion en listar egresos
adición - validación por fechas 
modificación filtrado de egresos por fechas


informe (12-11-2021)
corrección lista de egresos
adición - api - obtener productos por almacen stock de productos
adicióm - inicio - entorno listado por almacen stotck de productos

informe (13-11-2021)
correción - consulta por almacen
adición - api - obtener productos por almacen para cada item de tabla
adicióm - entorno listado por almacen stock de productos agregando columnas por almacen




informe (18-11-2021)
carga css de cdn a local
carga js de cdn a local

revisión - navegación en hosting
## administración
## |--> configuración general
## |--|--> información de la empresa [OK]
## |--|--> ajustes sobre los reportes [OK]
## |--|--> ajustes sobre la fecha [OK]
## |--|--> apariencia del sistema [OK]

## |--> registro de roles [OK]
## |--> registro de permisos [OK]
## |--> registro de empleados [OK,EDITAR->RESTABLECER]
## |--> registro de usuarios [OK]

## configuración
## |--> registro de unidades [OK]
## |--> registro de categorias cliente [OK]
## |--> edición de precios  [Revisar]-(aparentemente desarrollado para cumplir con asignaciones de descuento por rol, pero no se puede probar que cumple con el objetivo)

## |--> registro de sucursales [OK,EDITAR->RESTABLECER]
## |--> registro de almacenes [OK,PERMISO][asignar]
## |--> registro de categoria producto [OK]
## |--> registro de monedas [OK]
## |--> personas
## |--|--> lista de proveedores [OK,EDITAR->404]
## |--|--> lista de clientes [OK,LISTA->CARGA-LENTA]



## módulo inventarios
## |--> catalogo de productos [OK,NO-ESTA-CARGADO-EN-EL-SERVIDOR]
## módulo ventas
## módulo facturación
## módulo cajas
## módulo cuentas


informe (19-11-2021)

adición estructura ajustar stock
mesaje ajustar stock
api ajustar stock
funciones ajustar stock y confirmar ajuste