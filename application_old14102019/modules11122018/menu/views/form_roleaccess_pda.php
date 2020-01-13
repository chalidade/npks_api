<style type="text/css">
	ul#list_menu{
		list-style: none;
		padding:0;
		margin:20px 0 0;
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
		if($("input[name=ROLE_ID]").val() && $("input[name=BRANCH_ID]").val() && $('input#roleaccesspda').is(':checked')){
			$('#btnSubmitPDA').removeClass("x-item-disabled x-btn-disabled");
		}
		else{
			$('#btnSubmitPDA').addClass("x-item-disabled x-btn-disabled");
		}
	});
	$('#btnResetPDA').on('click',function(){
		var idForm			= '<?php echo $form_id; ?>';
		var form 			= Ext.getCmp(idForm).getForm();
		
		$('input#roleaccesspda').prop('checked',false);
		form.reset();
	});
	$('#btnSubmitPDA').addClass("x-item-disabled x-btn-disabled");	
	

	$('#btnSubmitPDA').on('click',function(){
		var idForm			= '<?php echo $form_id; ?>';
		var gridID			= '<?php echo $grid_id; ?>';
		var windowID		= '<?php echo $win_id; ?>';
		
		var form 			= Ext.getCmp(idForm).getForm();
		var ROLE_MENU_ID 	= $('[name=ROLE_MENU_ID]').val();		
		var roleaccess 		= [];
		var counter 		= 0;

		for(x = 0; x < $('input#roleaccesspda').length; x++){
			
			if($('input#roleaccesspda').eq(x).prop('checked') == true){
				
				roleaccess[counter] =  $('input#roleaccesspda').eq(x).attr('data-value');
				counter++;
			}
		}

		
		var myMask = new Ext.LoadMask({
					            msg: 'Saving....',
					            target: Ext.getCmp('<?php echo $form_id; ?>')
					          });
		myMask.show();

		Ext.Ajax.request({
	        url: Contants.getAppRoot() + 'menu/add_role_access_pda',
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
<ul id="list_menu">
<?php
	foreach ($dataAllMenu as $dataMenu) {
		?>		
			<li>
				<input id="roleaccesspda" type="checkbox" data-value="<?php echo $dataMenu->MENU_PDA_ID ?>" name="roleaccesspda[]"/>
				<?php echo $dataMenu->MENU_PDA_TEXT; ?>
			</li>
		<?php
	}
?>
</ul>