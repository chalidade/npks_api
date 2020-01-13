<!DOCTYPE html>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		$(document).ready(function() {
			$("button").click(function() {
				$.ajax({
					url: "http://10.88.48.33/npks-api/index.php/menu/get_all_menu_pda_list_user",
					success: function(result) {
						$("#div1").html(result);
					}
				});
			});
		});

			function ajaxCall(){
				$.ajax({
					url: "http://10.88.48.33/npks-api/index.php/menu/get_all_menu_pda_list_user",
					success: function(result) {
						$("#div1").html(result);
					}
				});
			}

			setInterval(ajaxCall, 2000);

	</script>

</head>
<body>
<div id="div1">
<h2>Let jQuery AJAX Change This Text</h2>
</div>
<button>Get External Content</button>
</body>
</html>

​
