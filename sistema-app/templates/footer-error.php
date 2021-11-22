		</div>
		<script>
		$(function () {

			document.title = ($('[data-header]:first').size() > 0) ? (($.trim($('[data-header]:first').text()) == '') ? document.title : $.trim($('[data-header]:first').text())) : document.title;

		});
		</script>		
	</body>
</html>