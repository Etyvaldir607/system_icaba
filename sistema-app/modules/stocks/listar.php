<?php
// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// @etysoft  obtiene el id del usuario actual
$id_user_current = $_user['id_user'];



// Verifica si hay parametros
if ($id_almacen == 0) {
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();
	$t_ajustar = $db->query("select*from tmp_ajustar")->fetch();
	$condicion_ids = '';
} else {
	// Obtiene los id_almacen
	$id_almacen = explode('-', $id_almacen);
	
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $id_almacen)->order_by('id_almacen')->fetch();
	$t_ajustar = $db->from('tmp_ajustar')->where_in('almacen_id', $id_almacen)->fetch();
	
	$ids = '';
	$sw = true;
	foreach($id_almacen as $nro){
	    if($sw){
	        $ids .= $nro;
	        $sw = false;
	    }else{
	        $ids .= ','.$nro;
	    }
	}
	$condicion_ids = 'AND ta.almacen_id in (';
	$condicion_ids .= $ids.')';
// 	var_dump($condicion_ids);die();
}

// Verifica si existen almacenes
if (!$almacenes) {
	// Error 404
	require_once not_found();
	exit;
}


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los ubicaciones
$ubicaciones = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ajustar = in_array('ajustar', $permisos);
$permiso_ver_stock = true;


// Genera la consulta

$select = "select p.id_producto, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, ifnull(ta.producto_id, 'No') as modificado, p.precio_actual, u.unidad, u.sigla, c.categoria";
$from = " from inv_productos p left join inv_unidades u on u.id_unidad = p.unidad_id
left join inv_categorias c on c.id_categoria = p.categoria_id

LEFT JOIN tmp_ajustar ta ON ta.producto_id = p.id_producto $condicion_ids";
$join = "";

// recorre los almacenes

foreach ($almacenes as $nro => $almacen) {
	$id = $almacen['id_almacen'];
	$select = $select . ", ifnull(e$id.ingresos$id, 0) as ingresos$id, ifnull(s$id.egresos$id, 0) as egresos$id";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad*u.tamanio) as ingresos$id from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id LEFT JOIN inv_asignaciones asig ON
                d.asignacion_id=asig.id_asignacion
            LEFT JOIN inv_unidades u ON
                u.id_unidad = asig.unidad_id where i.almacen_id = $id and i.transitorio = 0 group by d.producto_id) as e$id on e$id.producto_id = p.id_producto";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad*u.tamanio) as egresos$id from inv_egresos_detalles d left join inv_egresos e on (e.id_egreso = d.egreso_id AND estado='V') LEFT JOIN inv_asignaciones asig ON
                d.asignacion_id=asig.id_asignacion
            LEFT JOIN inv_unidades u ON
                u.id_unidad = asig.unidad_id where e.almacen_id = $id group by d.producto_id) as s$id on s$id.producto_id = p.id_producto";
}



// Arma la consulta
$query = $select . $from . $join . " GROUP BY p.id_producto";

// Obtiene las lista de productos y los stocks en cada almacen
$productos = $db->query($query)->fetch();

?>
<?php require_once show_template('header-sidebar-yottabm'); ?>


<style>
	button.btn.btn-primary.apply, button.btn.btn-secondary.apply {
    	margin-top: 2.4rem;
	}

	button.btn-link.apply {
		padding-left: 0;
		padding-right: 0;
	}


	span.dt-down-arrow {
		color: #fff !important;
	}

	.content-space-between {
		display: flex;
		justify-content: space-between;
	}

	.content-space-right {
		display: flex;
		justify-content: right;
	}

	.row.no-wrap.content-space-between.pl-4.pr-4 {
		flex-wrap: nowrap;
	}

	.td-bold {
		font-weight: bold
	}

	.btn-group,
	.btn-group-vertical {
		margin-bottom: 15px;
		float: right;
	}

	.modal {
		text-align: center;
		padding: 0 !important;
	}

	.modal:before {
		content: '';
		display: inline-block;
		height: 100%;
		vertical-align: middle;
		margin-right: -4px;
	}

	.modal-dialog {
		display: inline-block;
		text-align: left;
		vertical-align: middle;
	}
</style>

<style>
.select2-results__option .wrap:before{
    font-family:fontAwesome;
    color:#999;
    content:"\f096" !important;
    width:35px;
    height:35px;
    padding-right: 15px;
    
}
.select2-results__option[aria-selected=true] .wrap:before{
    content:"\f14a" !important;
}

</style>

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Stock general de productos</strong>
	</h3>
</div>



<div class="panel-body">
	<?php $borrar_aux = 0 ?>
	<div class="row">
		<div class="col-12 col-md-12 col-lg-6 d-block pt-2 pb-2">
			<button type="button" class="btn btn-secondary apply" onclick="borrar_tmp(<?= $borrar_aux?>)">
				<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
				<span >Reestablecer todos los almacenes</span>
			</button>
		</div>

		<!-- @etysoft seleccionar almacen a visualizar -->
		<div class="col-6 col-md-9 col-lg-4 d-block pt-2 pb-2">
			<label for="almacen" class="control-label">Almacen:</label>
			<div id="change_almacen">
			</div>
		</div>

		<!-- @etysoft seleccionar almacen a visualizar -->
		<div class="col-6 col-md-3 col-lg-2 d-block pt-2 pb-2">
			<button type="button" class="btn btn-primary apply btn-block" onclick="set_almacen_select();">
				<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
				<span>Aplicar</span>
			</button>
		</div>
	</div>
	<hr>
	<div class="clearfix" style="margin-bottom: 10px;">
		<div class="pull-right tableTools-container"></div>
	</div>
	<table id="table_list_stockES" class="table table-striped table-bordered nowrap display"></table>

</div>


<!-- Fin modal almacen-->

<div id="modal_ajuste" class="modal fade">
	<div class="modal-dialog">
		<form id="form_ajuste" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Ajuste de inventario</h4>
			</div>
			<form id="form_ajuste">	
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div class="well">
								<h4 class="margin-none"><u>Producto</u></h4>
								<dl class="margin-none">
									<dt>Producto:</dt>
									<dd data-producto="true"></dd>
									<input type="hidden" name="producto" id="producto">
								</dl>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="well">
								<h4 class="margin-none"><u>Almacén</u></h4>
								<dl class="margin-none">
									<dt>Almacén:</dt>
									<dd data-almacen="true"></dd>
									<input type="hidden" name="almacen" id="almacen">
								</dl>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
					            <label for="stock">Stock</label>
					            <!--<input type="text" class="form-control" name="stock_nuevo" id="stock_nuevo" data-validation="required number" data-validation-allowing="range[1;100000]">-->
					            <input type="text" class="form-control" name="stock_nuevo" id="stock_nuevo" data-validation-allowing="range[1;100000]">
					            <small class="form-text text-muted">Tienes un stock actual de <span id="stock_actual"></span></small>
					            <input type="hidden" name="stock" id="stock">
					          </div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" data-aceptar="true">
						<span class="glyphicon glyphicon-ok"></span>
						<span>Aceptar</span>
					</button>
					<button type="button" class="btn btn-default" data-cancelar="true" id="btn_cancelar">
						<span class="glyphicon glyphicon-remove"></span>
						<span>Cancelar</span>
					</button>
				</div>
			</form>
			<div id="loader_ajuste" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script>
$(function () {	
	<?php if ($productos) { ?>

	var $fields = $('#fields_0');
	$fields.find(':checkbox[value="3"]').trigger('click');
	$fields.find(':checkbox[value="5"]').trigger('click');

	var $modal_almacen = $('#modal_almacen');
	var $form_almacen = $('#form_almacen');

	$form_almacen.on('submit', function (e) {
		e.preventDefault();
	});

	$('[data-cambiar]').on('click', function () {
		$modal_almacen.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>


	$.validate({
		form: '#form_ajuste',
		modules: 'basic',
		onSuccess: function (form) {
			guardar_stock(form);
		}
	});

	function guardar_stock(form){
		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/stocks/ajustar',
			data: $(form).serialize()
		}).done(function(respuesta) {
			if (respuesta.success) {
				location.reload();
				/*var $row 	= $("[data-cantidad='" + respuesta.producto +'-'+ respuesta.almacen+"']");
				var stock 	= $row.children().data('stock');
				var stocks 	= stock.split('|');
				
				$row.children().attr('data-stock',respuesta.stock+'|sdfsdf'+stocks[1]+'|'+stocks[2]+'|'+stocks[3]+'|'+stocks[4]);
				$row.find('strong').text(respuesta.stock);
				var html = $("[data-cantidad='" + respuesta.producto +'-'+ respuesta.almacen+"']").html();
				var cell = table.cell($row);
				
				cell.data(html).draw();*/
				$("#modal_ajuste").modal('hide');
			}
		}).fail( function() {
    		$.notify({
				message: "Verifique que tenga permisos"
			},{
				type: "danger"
			});
		});
	}

	$('#btn_cancelar').on('click',function(){
		$('#modal_ajuste').modal('hide');
	});

});

function borrar_tmp(id_almacen){
    var almacen_id = id_almacen;
        if(almacen_id == 0){
            var mensaje = '¿Está seguro que desea reestablecer todos los almacenes?'
        }else{
            var mensaje = '¿Está seguro que desea reestablecer este almacen?'
        }
        bootbox.confirm(mensaje, function (result) {
    	    if(result){
                $.ajax({
                	type: 'post',
                	dataType: 'json',
                	url: '?/stocks/eliminar_tmp',
                	data: {almacen_id: almacen_id}
            	}).done(function (id) {
                    window.location = '?/stocks/listar';
                	if (id) {
                	   // alert('id de vuelta: '+id);
                		location.reload();
                		$.notify({
        		            message: 'Se reestableció correctamente.'
        		        }, {
        		            type: 'success'
        		        });
                	} else {
        		        $('#loader').fadeOut(100);
        		        $.notify({
        		            message: 'Ocurrió un al devolver id_almacen durante el reestablecimiento de los colores'
        		        }, {
        		            type: 'danger'
        		        });
        		    	
        		    }
                	
            	}).fail(function (e) {
                    window.location = '?/stocks/listar';
        		    $.notify({
        		        message: 'Ocurrió un problema al reestablecer colores, verifique su conexion a internet y si el problema perciste contactese con Sitemas.'
        		    }, {
        		        type: 'danger'
        		    });

        		});
    	    }
    	});
        
    
}

// function borrar_tmp_completo(aux) {
// 	window.location = '?/stocks/eliminar_tmp';
// }
</script>


<!-- data table js required  -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap.min.js"></script>

<!--buttons css export-->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css">
<!--buttons css export-->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.min.css">
 
<!-- buttons js export-->
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap.min.js"></script>
<!-- buttons js export-->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.bootstrap.min.js"></script>

<!-- cabecera para datatable -->
<script src="https://cdn.datatables.net/fixedheader/3.2.0/js/dataTables.fixedHeader.min.js"></script>

<!-- rederizar botones en html5 -->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>

<!-- visulaización de columnas  -->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>
<!-- copiar -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<!-- pdf - make  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<!-- imprimir  -->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>

<!-- iconos font awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />




<!--  @etysoft importando la libreria de axios  -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!--  @etysoft iniciando la instancia de axios  -->
<script>
	axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
<!-- sweetalert2 js export-->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- select2 css export -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- select2 js export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<!-- @etysoft datable -->
<script type="text/javascript">
	let moneda = "<?= $moneda; ?>";
	
	// cargar el datatable
	async function loadDataTable(arr_id_almacen){

		if (arr_id_almacen.length != 0) {
			// console.log(arr_id_almacen)
			var table_list_stockES = $('#table_list_stockES').DataTable({
				"order": [
					[0, "desc"]
				],
				//"lengthMenu": [ [10, 25, 50,100,200, -1], [10, 25, 50,100,200, "Todos"] ],
				"lengthMenu": [
					[15, 25, 50, 100, 200, 500],
					[15, 25, 50, 100, 200, 500]
				],
				"processing": true,
				"serverSide": true,
				"info": true, // control table information display field
				"stateSave": true, //restore table state on page reload,
				//"deferLoading": 2,

				"scrollX": true,
				"scrollY": '57vh',
				"scrollCollapse": true,

				//"scrollCollapse": true,

				"language": {
					//"url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"

					"sProcessing": "Procesando...",
					"sLengthMenu": "_MENU_",
					"sZeroRecords": "No se encontraron resultados",
					"sEmptyTable": "Ningún dato disponible en esta tabla",
					"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
					"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
					"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix": "",
					"sSearch": "",
					"sUrl": "",
					"sInfoThousands": ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst": "Primero",
						"sLast": "Último",
						"sNext": ">",
						"sPrevious": "<"
					},
					"oAria": {
						"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}

				},

				"ajax": {
					"url": "?/stocks/api_obtener_productos_almacen",
					"type": "POST",
					"data": {
						"arr_id_almacen": `${arr_id_almacen}`,
					},
				},
				"deferRender": true,
				//"responsive": true,
				"colReorder": true,
				"columnDefs": columnDefsSet(),
			});

			$.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';

			new $.fn.dataTable.Buttons(table_list_stockES, {
				buttons: [{
					"extend": "colvis",
					"text": "<i class='fa fa-search'></i> <span></span>",
					"className": "btn btn-white btn-primary",
					columns: ':not(:first):not(:last)'
				}, {
					"extend": "copy",
					"text": "<i class='fa fa-copy'></i> <span>Copiar</span>",
					"className": "btn btn-white btn-primary",
				}, {
					"extend": "excel",
					"text": "<i class='fa fa-file-excel-o'></i> <span>Excel</span>",
					"className": "btn btn-white btn-primary"
				}, {
					"extend": "pdf",
					"text": "<i class='fa fa-file-pdf-o'></i> <span>PDF</span>",
					"className": "btn btn-white btn-primary"
				}, {
					"extend": "print",
					"text": "<i class='fa fa-print'></i> <span>Imprimir</span>",
					"className": "btn btn-white btn-primary",
					autoPrint: true,
					message: 'This print was produced using the Print button for DataTables'
				}]
			});

			table_list_stockES.buttons().container().appendTo($('.tableTools-container'));

			setTimeout(function() {
				$($('.tableTools-container')).find('a.dt-button').each(function() {
					var div = $(this).find(' > div').first();
					if (div.length == 1) div.tooltip({
						container: 'body',
						title: div.parent().text()
					});
					else $(this).tooltip({
						container: 'body',
						title: $(this).text()
					});
				});
			}, 500);

		} else {
			$.notify({
				title: '¡Alerta!',
				icon: 'glyphicon glyphicon-info-sign',
				message: `<strong>Debe seleccionar al menos un almacen, para visualizar</strong>`
			}, {
				type: 'warning',
				animate: {
					enter: 'animated fadeInUp',
					exit: 'animated fadeOutRight'
				},
				placement: {
					from: "bottom", //from: "bottom" //top,
					align: "center" //align: "left" right
				},
				offset: 10,
				spacing: 10,
				z_index: 1031,
			});
		}
	}


	let dataTable_columns
	/** renderiza las columnas por separado */
	function columnDefsSet(data){
		dataTable_columns = [
			{
				"className": "td-bold",
				"targets": [0],
				"title": '#',
				"width": "3%",
				"searchable": false,
			},
			{
				"targets": [1],
				"title": 'Código',
				"width": "7%",
				"visible": false
			},
			{
				"targets": [2],
				"title": 'Nombre de producto',
				"width": "8%",
			},
			{
				"targets": [3],
				"title": 'Categoria',
				"width": "7%",
				"visible": false
			},
			{
				"targets": [4],
				"title": `Costo ${moneda}`,
				"width": "5%",
			},
			{
				"targets": [5],
				"title": 'Cantidad Mínima',
				"width": "5%",
			},
			{
				"targets": [6],
				"title": 'Modificado',
				"width": "5%",
			},		
		];

		// capturamos el target inicial de que contiene stock por almacen
		let cont_add = 7;
		// generamos las columnas de stock por almacen/producto
		for (let i = cont_add; i < (arr_id_almacen.length + cont_add); i++) {
			// console.log(arr_nombre_almacen)
			dataTable_columns.push(
				{
					"className": "td-bold td-center",
					"targets": [i],
					"title": `
					<span role="button" data-toggle="tooltip" title="${arr_nombre_almacen[i - cont_add]}">
						Stock ${[i + 1 - cont_add]}
					</span>
					
					`,
					"width": "8%",
					"visible": true,
					"data": function(data, type, row, meta) {
						let column = {
							data_col:data,
							cont_col:i
						}
						return column;
					},
					"render": setColumnStock,
					"searchable": false,
				}
			);
		}

		// generamos la columna que suma todas columnas de stock por producto
		dataTable_columns.push(
			{
				"className": "td-bold td-center",
				"targets": [parseInt(arr_id_almacen.length + cont_add)],
				"title": `Stock Total`,
				"width": "7%",
				"visible": true,
				"data": function(data, type, row, meta) {
					return data;
				},
				"render": sumAllColumnsOfStock,
				"searchable": false,
				"orderable": false,
			}
		)
		return dataTable_columns;
	}

	// @etysoft inserta el stock para cada producto por almacen
	function setColumnStock(column){
		// console.log(column.data_col, column.cont_col);
		// contador interno
		let cont_render_col = column.cont_col;
		// recupera la columa
		let data_render = column.data_col;

		let arr_almacen_value_producto = data_render[`${cont_render_col}`].split('|');

		let stock_almacen_actual = arr_almacen_value_producto[2];
		let id_almacen_actual = arr_almacen_value_producto[0];
		let nombre_almacen_actual = arr_almacen_value_producto[1];
		let id_producto_actual = data_render.id_producto;
		let producto_actual = data_render[2];	
 
		let template = '';

		const btn_shop = createButtons(id_almacen_actual,nombre_almacen_actual,stock_almacen_actual, id_producto_actual, producto_actual);
		template += `
			<div class="content-space-between" style="padding-bottom: 3px;">
				<span> ${(stock_almacen_actual) } </span>
				<span>
					${(btn_shop)}
				</span>
			</div>`;
		return template;
	}


	// @etysoft calcula la sumatoria del stock de los almacenes
	function sumAllColumnsOfStock(data){
		// contador interno
		let cont_render = 7;
		let sum_render = 0;
		let limit = arr_id_almacen.length + cont_render;
		// recorre la cantidad de columnas
		for (let i = cont_render; i < limit; i++) {
			// console.log(data[i]);
			let arr_almacen_value_producto = data[i].split('|');
			// recorre el array de la petición
			sum_render = sum_render + parseInt(arr_almacen_value_producto[2]);
		}
		return sum_render;
	}


	// visualiza los permisos
	let permiso_ajustar = "<?= $permiso_ajustar; ?>" || false;
	let permiso_ver_stock = "<?= $permiso_ver_stock; ?>" || false;

	// @etysoft crea los botones de opcion de cada almacen por producto
	function createButtons(id_almacen_actual,nombre_almacen_actual,stock_almacen_actual, id_producto_actual, producto_actual) {
		// console.log(id_almacen_actual,nombre_almacen_actual,stock_almacen_actual, id_producto_actual, producto_actual)
		const btn_adjust = permiso_ajustar ?
			`<button class="btn-link apply" data-toggle="tooltip" title="Ajustar stock en ${nombre_almacen_actual}"
				onclick="
					adjust_stock_producto({
						'id_almacen': ${id_almacen_actual},
						'nombre_almacen':'${nombre_almacen_actual }',
						'stock_almacen':'${stock_almacen_actual}',
						'id_producto':${id_producto_actual},
						'nombre_producto':'${producto_actual}'
					})">
				<span class="glyphicon glyphicon-refresh"></span>
			</button>` : '';
		const btn_ver = permiso_ver_stock ?
			`<button class="btn-link apply" data-toggle="tooltip" title="Ver detalle ${nombre_almacen_actual}"
				onclick="show_stock_producto(${id_almacen_actual},${id_producto_actual})">
				<span class="glyphicon glyphicon-book"></span>
			</button>` : '';
		const btn_all = ( permiso_ver_stock || permiso_ajustar) ?
			`<div class="no-wrap">
				${btn_adjust}${btn_ver}
			</div>` : '';
		return btn_all;
	}

</script>


<!-- @etysoft redirecciones y funcion de botones ajustar stock y ver detalle de movimientos -->
<script type="text/javascript">
	// @etysoft llama a ajustar el stock por producto / almacen 
	function adjust_stock_producto(objeto) {
		//@etysoft destructuramos el objeto recibido y lo almacenamos en variables
		const {
			id_almacen,
			nombre_almacen,
			stock_almacen, 
			id_producto, 
			nombre_producto
		} = objeto
		let front_template =
							`<div class="row">
								<div class="col-sm-6 p-5 d-block">
									<label class="margin-none d-flex">Almacén:</label>
									<strong class="form-text text-muted">
										${nombre_almacen}
									</strong>
								</div>
								<div class="col-sm-6 p-5 d-block">
									<label class="margin-none d-flex">Producto:</label>
									<strong class="form-text text-muted">
										${nombre_producto}
									</strong>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12 pl-5 pr-5">
									<div class="form-group">
										<strong class="form-text text-muted">Tienes un stock actual de: ${stock_almacen}</strong>
									</div>
								</div>
							</div>`;

		bootbox.prompt({
			closeButton: false,
			title: `<div class="row">
						<div class="col-sm-12 pt-3 text-center">
							<h3><strong class="form-text text-muted">Ajuste de inventario</strong></h3>
						</div>
					</div>`+
					front_template,
			inputType: 'number',
			callback: function (stock){
				confirm_adjust_stock_producto({
					"id_almacen": id_almacen,
					"id_producto": id_producto,
					"stock_actual": stock_almacen,
					"stock_nuevo": stock
				})
			}
		});
	
	}

	// @etysoft confirmar el ajuste del stock de producto
	function confirm_adjust_stock_producto(objeto_stock){
		const {
			id_almacen,
			id_producto, 
			stock_actual,
			stock_nuevo
		} = objeto_stock
		console.log(objeto_stock)
		let formData = new FormData();
        formData.append("id_almacen", id_almacen);
		formData.append("id_producto", id_producto);
		formData.append("stock_actual", stock_actual);
		formData.append("stock_nuevo", stock_nuevo);

		axios({
			method: "post",
			url: `?/stocks/api_ajustar_stock`,
			data : formData
		}).then(({
			data
		}) => {
			if (data.status && data.status === 201 ) {
				//@etysoft recargamos el datatble
				$('#table_list_stockES').DataTable().ajax.reload(null, false);
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: data.icon,
					message: data.message
				}, {
					type: data.type,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom" //top,
						align: "center" //align: "left" right
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}

			if (data && data.status == 400) {
				// console.log(data)
				$.notify({
					title: `<strong>${data.title}</strong>`,
					icon: data.icon,
					message: data.message
				}, {
					type: data.type,
					animate: {
						enter: 'animated fadeInUp',
						exit: 'animated fadeOutRight'
					},
					placement: {
						from: "bottom", //from: "bottom" //top,
						align: "center" //align: "left" right
					},
					offset: 10,
					spacing: 10,
					z_index: 1031,
				});
			}
		});

	}


	// @etysoft ver egreso
	function show_stock_producto(id_almacen, id_producto){
		window.open(`?/stocks/mostrar/${id_almacen}/${id_producto}`);
	}
</script>


<!-- @etysoft renderiza solo el almacen asignados por empleado, en caso de super usuario o rol asignado renderiza un select -->
<script type="text/javascript">
	/** recuperamos id_alamcen como variable global */
	let arr_id_almacen = [];
	let arr_nombre_almacen = [];
	// @etysoft renderiza almacenes
	render_almacenes();
	//@etysoft funcion para obtener alamacenes 
	async function render_almacenes() {

		// obteniene el id del usuario que esta en sesión
		let id_rol_current_user = "<?= $_user['rol_id']; ?>";
		let id_current_user = "<?= $id_user_current; ?>";
		// console.log(id_rol_current_user)
		let $id_form = document.getElementById("change_almacen");

		axios.post(`?/stocks/api_obtener_almacen_empleado`).then(({
			data
		}) => {
			const arr_almacenes = data;
			let template =``;

			if ((id_rol_current_user == 1 || id_rol_current_user == 2 ) && arr_almacenes.length > 1 ) {
				const arr_almacenes = data;
				template +=`<select id="id_almacen_select" class="form-control" multiple="multiple"></select>`;
				$id_form.innerHTML = template;
				handle_almacenes(arr_almacenes);
				set_almacen_init();
			}else{
				template += `
					<input type="text" class="form-control" value="${(arr_almacenes[0]['id_almacen'] === 0)  ? '' : arr_almacenes[0]['almacen'] }" readonly>	
					<input id="almacen" type="hidden" name="almacen" value="${arr_almacenes[0]['id_almacen']}">
				`;
				$id_form.innerHTML = template;
				// set_almacen();
			}
		})
		.catch((err) => {
			// console.error(err);
		});
	}

	function handle_almacenes(arr_almacenes){
		let id_almacen_select = document.getElementById('id_almacen_select');
		// define el campo de con de tipo select2MultiCheckboxes
		$.fn.select2.defaults.set( "theme", "bootstrap" );
		$("#id_almacen_select").select2MultiCheckboxes({
			tags: true,
			//placeholder: "agregar contenedor",
			allowClear: true,
			templateSelection: function(selected, total) {
				return "Seleccionado " + selected.length + " de " + total;
			}
		});

		let options = ``;
			// options = `<option value="0" selected>Todos</option>`;
		arr_almacenes.forEach( (item) => {
			options += `<option value="${item.id_almacen}" >${item.almacen}</option>`
		});
		id_almacen_select.innerHTML = `${options}`;
	}

	// @etysoft agrega item en el array de parametros de: id_almacen y nombre_almacen
	function removeToArray(item){
		let position = arr_id_almacen.indexOf(parseInt(item.id_almacen));
		arr_id_almacen.splice( position, 1 );
		arr_nombre_almacen.splice( position, 1 );
		// console.log(arr_id_almacen, arr_nombre_almacen);
	}

	// @etysoft elimina item en el array de parametros de: id_almacen y nombre_almacen
	function addToArray(item){
		arr_id_almacen.push(parseInt(item.id_almacen));
		arr_nombre_almacen.push(item.almacen);
		// console.log(arr_id_almacen, arr_nombre_almacen)
	}

	// @etysoft limpia el array de parametros de: id_almacen y nombre_almacen
	function clearArray(){
		arr_id_almacen.splice(0, arr_id_almacen.length);
		arr_nombre_almacen.splice(0, arr_nombre_almacen.length);
		return true;// console.log(arr_id_almacen, arr_nombre_almacen);
	}


	// @etysoft cargamos el datatable inicial
	function set_almacen_init(){
		// recuperamos la lista de opciones
		let lista = document.getElementById('id_almacen_select');
		// recorre las opciones para capturar los datos
		Array.from(lista.options).forEach(function(item){
			let id_almacen_selected = item.value;
			let almacen_selected = item.text;
			item.selected = true;
			// agrega al array de almacenes
			addToArray({
						'id_almacen':`${id_almacen_selected}`,
						'almacen':`${almacen_selected}`,
					});
			// console.log(id_almacen_selected, almacen_selected)
		});
		// cargamos el datatable inicial
		loadDataTable(arr_id_almacen);
	}

	// @etysoft select - habilitado solo para roles ['Super Usuario', 'Administrador']
	function set_almacen_select(){
		// recuperamos la lista de opciones
		let lista = document.getElementById('id_almacen_select');
		// limpia el array de almacenes
		clearArray();
		// recorre las opciones para capturar los datos
		Array.from(lista.options).forEach(function(item){
			let id_almacen_selected = item.value;
			let almacen_selected = item.text;
			//console.log(id_almacen_selected, almacen_selected)
			if(item.selected === true){
				// agrega al array de almacenes
				addToArray({
							'id_almacen':`${id_almacen_selected}`,
							'almacen':`${almacen_selected}`,
						});
				
			}
			
		});
		// reiniciamos datatable
		reloadDataTable();
	}

	// @etysoft recarga el tabla de productos mediante ajax y lo actualiza
	function reloadDataTable() {
		// detruimos el datatable actual
		destroyDataTable();
		// cargamos el datatable inicial
		loadDataTable(arr_id_almacen);
	};

	// @etysoft limpia la tabla funcion de datatable
	function destroyDataTable() {
		$('#table_list_stockES').DataTable().clear();
		$('#table_list_stockES').DataTable().destroy();
		$('#table_list_stockES').empty();
		dataTable_columns = [];
		cont_add = 7;
	}

	// @etysoft select - habilitado solo para empleados
	function set_almacen(){
		let elemento = document.getElementById('almacen');
		arr_id_almacen.push(elemento.value);
		loadDataTable(arr_id_almacen);
	}

</script>


<!-- plugin para seleccion multiple -->
<script>
	(function(){
		var S2MultiCheckboxes = function(options, element) {
		var self = this;
		self.options = options;
		self.$element = $(element);
		var values = self.$element.val();
		self.$element.removeAttr('multiple');
		self.select2 = self.$element.select2({
		allowClear: true,
		minimumResultsForSearch: options.minimumResultsForSearch,
		placeholder: options.placeholder,
		closeOnSelect: false,
		templateSelection: function() {
			return self.options.templateSelection(self.$element.val() || [], $('option', self.$element).length);
		},
		templateResult: function(result) {
			if (result.loading !== undefined)
			return result.text;
			return $('<div>').text(result.text).addClass(self.options.wrapClass);
		},
		matcher: function(params, data) {
			var original_matcher = $.fn.select2.defaults.defaults.matcher;
			var result = original_matcher(params, data);
			if (result && self.options.searchMatchOptGroups && data.children && result.children && data.children.length != result.children.length) {
			result.children = data.children;
			}
			return result;
		}
	}).data('select2');

	self.select2.$results.off("mouseup").on("mouseup", ".select2-results__option[aria-selected]", (function(self) {
		return function(evt) {
			var $this = $(this);
			const Utils = $.fn.select2.amd.require('select2/utils');
			var data = Utils.GetData(this, 'data');
			// console.log(data);

			if ($this.attr('aria-selected') === 'true') {
				self.trigger('unselect', {
					originalEvent: evt,
					data: data
				});
				/*
				removeToArray({
							'id_almacen':`${data.id}`,
							'almacen':`${data.text}`,
						});
				console.log("ejecuta des")
				*/
				return;
			}
				/*
				addToArray({
							'id_almacen':`${data.id}`,
							'almacen':`${data.text}`,
						});	
				console.log("ejecuta mar")
				*/
			

			self.trigger('select', {
				originalEvent: evt,
				data: data
			});
		}
		})(self.select2));
		self.$element.attr('multiple', 'multiple').val(values).trigger('change.select2');

	}

	$.fn.extend({
		select2MultiCheckboxes: function() {
			var options = $.extend({
				placeholder: 'Choose elements',
				templateSelection: function(selected, total) {
				return selected.length + ' > ' + total + ' total';
				},
				wrapClass: 'wrap',
				minimumResultsForSearch: -1,
				searchMatchOptGroups: true
			}, arguments[0]);

			this.each(function() {
				new S2MultiCheckboxes(options, this);
			});
		}
	});

	}());


</script>







<?php require_once show_template('footer-sidebar'); ?>