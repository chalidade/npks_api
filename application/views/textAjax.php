<!DOCTYPE html>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		$(document).ready(function() {
			$("button").click(function() {
				$.ajax({
					url: "http://npks.indonesiaport.co.id/npks-api/index.php/menu/get_all_menu_pda_list_user",
					success: function(result) {
						$("#div1").html(result);
					}
				});
			});

			function ajaxCall(){
				$.ajax({
					url: "http://npks.indonesiaport.co.id/npks-api/index.php/menu/get_all_menu_pda_list_user",
					success: function(result) {
						$("#div1").html(result);
					}
				});
			}

			setInterval(ajaxCall, 2000);
		});
	</script>

</head>
<body>
<button>Get External Content</button>
<br><br>
<div id="div1">
<h2>Let jQuery AJAX Change This Text</h2>
</div>
<h2><?php echo date('H:i:s'); ?></h2>
</body>
</html>

​
