<style type="text/css">
	ul#list_menu{
		list-style: none;
		padding-left: 35px;
	}
	ul#list_menu li input{
		margin-right: 8px;
		margin-bottom: 8px;
	}
	ul#list_menu:first-child{
		padding-left: 0;
		margin-top: 10px;
	}
</style>

<script type="text/javascript">
	$(window).on('click',function(){
		if($("input[name=ROLE_ID]").val() && $("input[name=BRANCH_ID]").val() && $('input#roleaccess').is(':checked')){
			$('#btnSubmit').removeClass("x-item-disabled x-btn-disabled");
		}
		else{
			$('#btnSubmit').addClass("x-item-disabled x-btn-disabled");
		}
	});

	$('#btnReset').on('click',function(){
		var idForm			= '<?php echo $form_id; ?>';
		var form 			= Ext.getCmp(idForm).getForm();
		
		$('input#roleaccess').prop('checked',false);
		form.reset();
	});

	$('#btnSubmit').addClass("x-item-disabled x-btn-disabled");
	
	$('input#roleaccess').on('click',function(){
		var allParents = $(this).parents('li');
		var parent = $(this).parent('li');

		if ($(this).is(':checked')){
			
			for(x = 0; x < allParents.length; x++){				

				allParents.children('#roleaccess').prop('checked',true);

			}
		}
		else{
			allParents.each(function() {
			    var counterChecked = 0;
			    var me = this;

			    $(this).siblings().each(function() {

			    	if(!$(this).children('#roleaccess').is(':checked') && !$(me).children('#roleaccess').is(':checked')){

			    		counterChecked++;

					}

			    });
			    
			    if($(this).siblings().children('#roleaccess').length  == counterChecked){
			    	
					$(this).parents('li').eq(0).children('#roleaccess').prop('checked',false);

				}
			});
			
			parent.find('input#roleaccess').prop('checked',false);
		}
		
	});

	$('#btnSubmit').on('click',function(){
		var idForm			= '<?php echo $form_id; ?>';
		var gridID			= '<?php echo $grid_id; ?>';
		var windowID		= '<?php echo $win_id; ?>';
		
		var form 			= Ext.getCmp(idForm).getForm();
		var ROLE_MENU_ID 	= $('[name=ROLE_MENU_ID]').val();		
		var roleaccess 		= [];
		var counter 		= 0;

		for(x = 0; x < $('input#roleaccess').length; x++){
			
			if($('input#roleaccess').eq(x).prop('checked') == true){
				
				roleaccess[counter] =  $('input#roleaccess').eq(x).attr('data-value');
				counter++;
			}
		}

		
		var myMask = new Ext.LoadMask({
					            msg: 'Saving....',
					            target: Ext.getCmp('<?php echo $form_id; ?>')
					          });
		myMask.show();

		Ext.Ajax.request({
	        url: Contants.getAppRoot() + 'menu/add_role_access',
	        headers: {
				auth: Ext.util.Cookies.get('auth')
			},
	        params :{
	        	
	        	ROLE_ID : form.findField('ROLE_ID').getValue(),
	        	BRANCH_ID : form.findField('BRANCH_ID').getValue(),
	        	roleaccess : JSON.stringify(roleaccess)
	        },
	        success: function(response, opts) {
				var responseText = JSON.parse(response.responseText);
				myMask.hide();
				Ext.Msg.alert('Info', responseText.message);
				Ext.getCmp(gridID).getStore().reload();
				Ext.getCmp(windowID).close();
			},
			failure: function(response, opts) {
				var responseText = JSON.parse(response.responseText);
				myMask.hide();
				Ext.Msg.alert('Info', responseText.message);
				Ext.getCmp(gridID).getStore().reload();
				//Ext.getCmp(windowID).close();
			}
	   	});
	});
	// x-item-disabled x-btn-disabled
</script>
<?php
	echo $dataAllHtmlMenu;
?>