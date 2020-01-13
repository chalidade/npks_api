<?php
$id_yard = $_POST['id_yard'];
$tab_id = $_POST['tab_id'];
?>
<script type="text/javascript">
	Ext.onReady(function(){
		Ext.create('Ext.data.Store', {
			storeId: 'orientation',
			fields: ['value', 'name'],
			data: [{
				"value": "TL",
				"name": "Top-Left"
			}, {
				"value": "TR",
				"name": "Top-Right"
			}, {
				"value": "BL",
				"name": "Bottom-Left"
			}, {
				"value": "BR",
				"name": "Bottom-Right"
			}]
		});

		var block_list_store = Ext.create('Ext.data.Store', {
			fields:['BLOCK_ID', 'BLOCK_NAME', 'BLOCK_CAPACITY', 'BLOCK_SLOT', 'BLOCK_ROW', 'BLOCK_TIER', 'BLOCK_ORIENTATION'],
			autoLoad: true,
			proxy: {
				type: 'jsonp',
        url: Contants.getAppRoot() + 'yard/yard_editor/get_block_list/',
				extraParams: {
					id_yard: '<?=$id_yard?>'
				},
				reader: {
					type: 'json'
				}
			}
		});

		var rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
			clicksToMoveEditor: 1,
			autoCancel: false,
			listeners: {
				edit: function(editor, e, opt){
					var record = e.record.getChanges();
					var array = $.map(record, function(value, index) {
						return [value];
					});
					if (array.length>0){
						// loadmask.show();
						Ext.Ajax.request({
							url: Contants.getAppRoot() + 'yard/yard_editor/update_yard_block/' + <?=$id_yard?> + '/' + e.record.data.BLOCK_ID,
							params: {
                BLOCK_TIER: e.record.data.BLOCK_TIER,
                BLOCK_ORIENTATION: e.record.data.BLOCK_ORIENTATION
              },
							success: function(response){
								var text = response.responseText;
								if (text=='1'){
									Ext.MessageBox.show({
										title: 'Success',
										msg: 'Changes saved successfully.',
										buttons: Ext.MessageBox.OK
									});
								}else if (text=='2'){
									Ext.MessageBox.show({
										title: 'Error',
										msg: 'Please delete yard plan and equipment plan first!',
										buttons: Ext.MessageBox.OK
									});
									e.record.reject();
								}else{
									Ext.MessageBox.show({
										title: 'Error',
										msg: 'Failed to save changes.',
										buttons: Ext.MessageBox.OK
									});
									e.record.reject();
								}
								// loadmask.hide();
							}
						});
					}
				}
			}
		});

		var grid = Ext.create('Ext.grid.Panel', {
			id: 'block_list_grid_<?=$tab_id?>',
			store: block_list_store,
			loadMask: true,
			width: 740,
			columns: [
				{ dataIndex: 'BLOCK_ID', hidden: true, hideable: false},
				{ text: 'Block Name', dataIndex: 'BLOCK_NAME', width: 140},
				{ text: 'Capacity', dataIndex: 'BLOCK_CAPACITY', width: 100},
				{ text: '# of Slot', dataIndex: 'BLOCK_SLOT', width: 100},
				{ text: '# of Row', dataIndex: 'BLOCK_ROW', width: 100},
				{ text: '# of Tier', dataIndex: 'BLOCK_TIER', width: 100,
					editor: {
						xtype: 'numberfield',
						allowDecimals: false,
						allowBlank: false
					}
				},
				{ text: 'Slot-Row Orientation', dataIndex: 'BLOCK_ORIENTATION', width: 200,
					editor: {
						xtype: 'combo',
						store: 'orientation',
						queryMode: 'local',
						displayField: 'name',
						valueField: 'value',
						allowBlank: false
					}
				}
			],
			plugins: [rowEditing],
			emptyText: 'No Data Found'
		});

		grid.render('block_list_<?=$tab_id?>');
	});
</script>

<div id="block_list_<?=$tab_id?>"></div>
