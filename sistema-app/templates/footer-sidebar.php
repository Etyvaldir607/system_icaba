						<hr>
						<p class="m-0"><?= credits; ?></p>
					</div>
				</div>
			</div>
		</div>
		<div id="modal_ayudar" class="modal fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content spinner-wrapper">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Ayuda</h4>
					</div>
					<div class="modal-body">
						<p class="text-decoration-underline">Atajos de teclado</p>
						<table class="table table-bordered table-condensed">
							<tbody>
								<tr>
									<td class="text-nowrap text-middle column-collapse">
										<code>Alt + H</code>
									</td>
									<td class="text-middle">Muestra el cuadro de ayuda.</td>
								</tr>
								<tr>
									<td class="text-nowrap text-middle column-collapse">
										<code>Alt + A</code>
									</td>
									<td class="text-middle">Selecciona todos los elementos.</td>
								</tr>
								<tr>
									<td class="text-nowrap text-middle column-collapse">
										<code>Alt + C</code>
									</td>
									<td class="text-middle">Crea un nuevo elemento.</td>
								</tr>
								<tr>
									<td class="text-nowrap text-middle column-collapse">
										<code>Alt + U</code>
									</td>
									<td class="text-middle">Modifica un elemento.</td>
								</tr>
								<tr>
									<td class="text-nowrap text-middle column-collapse">
										<code>Alt + D</code>
									</td>
									<td class="text-middle">Elimina un elemento o una selección de elementos.</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id="spinner_ayudar" class="spinner-wrapper-backdrop">
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>
		<script>
		$(function () {
			
			var date, time, hours, minutes, seconds, tseconds, tminutes, thours;

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/home/obtener'
			}).done(function (datetime) {

				date = datetime.date;
				hours = parseInt(datetime.hours);
				minutes = parseInt(datetime.minutes);
				seconds = parseInt(datetime.seconds);

			}).fail(function () {

				date = moment().format('YYYY-MM-DD');
				hours = parseInt(moment().format('H'));
				minutes = parseInt(moment().format('mm'));
				seconds = parseInt(moment().format('ss'));

			});

			setInterval(function () {

				if (seconds < 59) {
					seconds = seconds + 1;
				} else {
					seconds = 0;
					if (minutes < 59) {
						minutes = minutes + 1;
					} else {
						minutes = 0;
						if (hours < 23) {
							hours = hours + 1;
						} else {
							hours = 0;
						}
					}
				}

				tseconds = (seconds < 10) ? '0' + seconds : seconds;
				tminutes = (minutes < 10) ? '0' + minutes : minutes;
				thours = (hours < 10) ? '0' + hours : hours;
				time = thours + ':' + tminutes + ':' + tseconds

				$('[data-datetime="date"]').text(date);
				$('[data-datetime="time"]').text(time);

			}, 1000);
			
			var $wrapper = $('.wrapper'), $wrapper_aside = $('.wrapper-aside:first'), $navbar_toggle = $('.navbar-toggle:first'), $object, $element, $icon;
			
			$navbar_toggle.on('click', function (e) {
				e.preventDefault();
				if ($(document).outerWidth() < 768) {
					$wrapper_aside.stop().animate({
						height: 'toggle'
					}, 200);
				} else {
					$wrapper_aside.stop().animate({
						width: 'toggle'
					}, 200, function () {
						$wrapper_aside.scrollTop(0);
					});
				}
			});
			
			$wrapper_aside.find('.sidebar-nav:first').metisMenu({
				toggle: true
			}).on('show.metisMenu', function (e) {
				$object = $(e.target);
				$element = $object.parent();
				$icon = $object.prev().find('.glyphicon:last');
				$element.addClass('active');
				$icon.removeClass('glyphicon-menu-right');
				$icon.addClass('glyphicon-menu-down');
			}).on('hide.metisMenu', function (e) {
				$object = $(e.target);
				$element = $object.parent();
				$icon = $object.prev().find('.glyphicon:last');
				$element.removeClass('active');
				$icon.removeClass('glyphicon-menu-down');
				$icon.addClass('glyphicon-menu-right');
			});
			
			if (is_dark($wrapper.css('background-color'))) {
				$wrapper.removeClass('wrapper-dark');
				$wrapper.addClass('wrapper-light');
			} else {
				$wrapper.removeClass('wrapper-light');
				$wrapper.addClass('wrapper-dark');
			}
			
			var $modal_ayudar = $('#modal_ayudar'), $spinner_ayudar = $('#spinner_ayudar');
			
			$modal_ayudar.on('hidden.bs.modal', function () {
				$spinner_ayudar.show();
			}).on('shown.bs.modal', function () {
				$spinner_ayudar.hide();
			});
			
			<?php if (isset($_SESSION[temporary])) { ?>
			$.notify({
				message: "<?= $_SESSION[temporary]['message']; ?>"
			},{
				type: "<?= $_SESSION[temporary]['alert']; ?>"
			});
			<?php unset($_SESSION[temporary]); ?>
			<?php } ?>
			
			<?php if (environment == 'production') : ?>
			$(document).on('contextmenu selectstart dragstart', function (e) { e.preventDefault(); });
			
			$('body').css({ cursor: 'default' });
			
			$('[data-spinner]:first').hide();
			
			setTimeout(console.log.bind(console, '%c\n¡Detente!', 'font-family:sans-serif;font-size:50px;font-weight:bold;color:#f00;-webkit-text-stroke:1px #000;-moz-text-stroke:1px #000;text-stroke:1px #000;'));
			setTimeout(console.log.bind(console, '%c\nEsta función del navegador está pensada para desarrolladores, todas las operaciones que realices dentro de este sitio están siendo monitoreadas por los administradores.', 'font-family:sans-serif;font-size:20px;'));
			setTimeout(console.log.bind(console, '%c\nConsulta https://www.checkcode.bo para obtener mas información.', 'font-family:sans-serif;font-size:20px;'));
			<?php endif ?>
			
		});
		</script>
	</body>
</html>