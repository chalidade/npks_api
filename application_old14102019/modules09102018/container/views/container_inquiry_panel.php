<script type="text/javascript">
	var container_inquiry_point_<?=$tab_id?> = Ext.create('Ext.data.Store', {
		fields:['POINT'],
		proxy: {
			type: 'ajax',
			url: '<?=controller_?>container_inquiry/list_of_point/',
			reader: {
				type: 'json'
			}
		},
		autoLoad: true
	});
	
	Ext.create('Ext.form.Panel', {
		id: "container_data_form_<?=$tab_id?>",
		bodyPadding: 5,
		fieldDefaults: {
			labelAlign: 'left',
			labelWidth: 100
		},
		items: [
			{
			xtype: 'fieldset',
			title: 'Status',
			items: [{
				xtype: 'displayfield',
				fieldLabel: 'Status',
				name: 'ID_OP_STATUS'
			},{
				xtype: 'displayfield',
				fieldLabel: 'Status Code',
				name: 'OP_STATUS_DESC'
			},{
				xtype: 'displayfield',
				fieldLabel: 'Location',
				name: 'CONT_LOCATION'
			},{
				xtype: 'displayfield',
				fieldLabel: 'Yard Position',
				name: 'YARD_POS'
			}]
		},{
			id: "no_container",
			xtype: "hidden",
			name: "NO_CONTAINER"
		},{
			id: "point",
			xtype: "hidden",
			name: "POINT"
		}]
	}).render('container_data_<?=$tab_id?>');
	
	
</script>
<div id="container_search_<?=$tab_id?>"></div>
<div id="container_data_<?=$tab_id?>"></div>
<div id="popup_script_<?=$tab_id?>"></div>