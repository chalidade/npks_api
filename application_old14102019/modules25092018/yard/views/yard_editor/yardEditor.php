<?php
	$L	= $width * $height;
	$sWidth = 15;
	$sHeight = 15;
	$grid_width = ($sWidth*$width)+8;
	$grid_height = ($sWidth*$height)+8;
?>

<style>
#feedback { font-size: 1.4em; }
#selectable_<?=$tab_id?> .ui-selecting { background: #FECA40; }
#selectable_<?=$tab_id?> .ui-selected { background: #F39814; color: white; }
#selectable_<?=$tab_id?> { list-style-type: none; margin: 0; padding: 0; }
#selectable_<?=$tab_id?> li {float: left; width: <?php echo $sWidth."px"?>; height: <?php echo $sHeight."px"?>; font-size: 4em; text-align: center; }
div.grid_<?=$tab_id?> {
	width:  <?php echo $grid_width."px"?>;
	height: <?php echo $grid_height."px"?>;
}
</style>

<script>
var cellxy = new Array();
var block_name_<?=$tab_id?> 		= new Array();
var block_tier_<?=$tab_id?> 		= new Array();
var block_position_<?=$tab_id?> 	= new Array();
var block_orientation_<?=$tab_id?> 	= new Array();
var block_height_<?=$tab_id?> 		= new Array();
var block_width_<?=$tab_id?> 		= new Array();
var block_color_<?=$tab_id?>		= new Array();
var block_status_<?=$tab_id?>		= new Array();
var block_id_<?=$tab_id?>		= new Array();

var count_block_<?=$tab_id?> 	= <?php echo $block_sum;?>;
var slot_<?=$tab_id?>  			= <?php echo $width;?>;
var row_<?=$tab_id?>	 		= <?php echo $height;?>;

var total_<?=$tab_id?>			= row_<?=$tab_id?>*slot_<?=$tab_id?>;

$(function() {
	for (var i = 0; i < total_<?=$tab_id?>; i++){
		cellxy[i] = new Object();
	}

	$( "#selectable_<?=$tab_id?>" ).selectable({
		start: function( event, ui ) {
				var result = $( "#select-result_<?=$tab_id?>" ).empty();
		},
		selected: function(event, ui) {
			// console.log($(ui.selected).attr('index'));
			$( "#select-result_<?=$tab_id?>" ).append(
				$(ui.selected).attr('index')+","
			);
			// console.log($(ui.selected).attr('block_id'));
			$("#select-block_<?=$tab_id?>").append(
				$(ui.selected).attr('block_id')+","
			);
		},
		stop: function( event, ui ) {
			var str = $( "#select-result_<?=$tab_id?>").html();
			var list_cell = str.split(",");
			var width=0;
			var height=0;
			for(i=0;i<(list_cell.length)-1;i++){
				if (i==0){
					width=1;
					height=1;
				}else{
					if (list_cell[i]-1 == list_cell[i-1]){
						width = width+1;
					}else{
						width = width+1;
						height = height+1;
					}
				}
			}
			width = width/height;
			if (width > slot_<?=$tab_id?>){
				height = width/slot_<?=$tab_id?>;
				width = slot_<?=$tab_id?>;
			}
			$("#selected_width_<?=$tab_id?>").val(width);
			$("#selected_height_<?=$tab_id?>").val(height);
			Ext.getCmp('width_<?=$tab_id?>').setValue(width);
			Ext.getCmp('height_<?=$tab_id?>').setValue(height);
		}
	});

	$.contextMenu({
		selector: "#selectable_<?=$tab_id?> .ui-selected",
		items: {
			"set": {
				name: "Set Block",
				icon: "edit",
				callback: function(key, options) {
					$("#set_block_pop_up_<?=$tab_id?>").click();
				}
			},
			"unset": {
				name: "Unset Block",
				icon: "delete",
				callback: function(key, options) {
					$("#unblock_<?=$tab_id?>").click();
				}
			},
			"sep1": "---------",
			"quit": {
				name: "Quit",
				icon: "quit",
				callback: function(key, options) {
					$(this).contextMenu("hide");
				}
			}
		}
	});

	Ext.create('Ext.data.Store', {
		storeId: 'position',
		fields: ['value', 'name'],
		data: [{
			"value": "H",
			"name": "Horizontal"
		}, {
			"value": "V",
			"name": "Vertical"
		}]
	});

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
});

function RenderBlockEdit_<?=$tab_id?>(index,block_,cell_block,cell_block_id){
	block_ = eval(block_);
	cell_block = eval(cell_block);
	cell_block_id = eval(cell_block_id);
	block_id_<?=$tab_id?>[index] 	= block_.BLOCK_ID;
	block_name_<?=$tab_id?>[index] 	= block_.BLOCK_NAME;
	block_tier_<?=$tab_id?>[index]	= block_.TIER;
	block_position_<?=$tab_id?>[index]	= block_.POSITION;
	block_orientation_<?=$tab_id?>[index]	= block_.ORIENTATION;
	block_height_<?=$tab_id?>[index]	= block_.HEIGHT;
	block_width_<?=$tab_id?>[index]	= block_.WIDTH;
	if (block_.COLOR!=''){
		block_color_<?=$tab_id?>[index]	= block_.COLOR;
	}else{
		block_color_<?=$tab_id?>[index]	= 'BLACK';
	}
	// console.log(block_);
	// console.log(block_name_<?=$tab_id?>[index]);
	// console.log(block_tier_<?=$tab_id?>[index]);
	// console.log(block_position_<?=$tab_id?>[index]);
	// console.log(block_color_<?=$tab_id?>[index]);
	for (var h = 0; h < cell_block.length; h++){
		var style = $("#selectable_<?=$tab_id?> li").eq(parseInt(cell_block[h])).attr( "style");
		style = typeof style !== 'undefined' ? style : "";

		cellxy[parseInt(cell_block[h])].block = block_name_<?=$tab_id?>[index];
		$("#selectable_<?=$tab_id?> li").eq(parseInt(cell_block[h])).attr( "style", style+"  border: 1px solid "+block_color_<?=$tab_id?>[index]+"; " );
		$("#selectable_<?=$tab_id?> li").eq(parseInt(cell_block[h])).attr( "title", "  Blok "+block_name_<?=$tab_id?>[index] );
		$("#selectable_<?=$tab_id?> li").eq(parseInt(cell_block[h])).attr( "block_id", block_id_<?=$tab_id?>[index] );

	}
}

var v = 0;

$("#set_block_pop_up_<?=$tab_id?>").click(function(event){
	var win = Ext.create({
		xtype: 'window',
		requires: [
			'Ext.form.Panel',
			'Ext.form.field.Number',
			'Ext.form.field.ComboBox'
		],

		width: 400,
		title: 'Save Block',
		autoLoad: true,

		items: [{
			xtype: 'form',
			bodyPadding: 10,
			header: false,
			title: 'My Form',
			items: [{
					xtype: 'textfield',
					name: 'block_name',
					anchor: '100%',
					fieldLabel: 'Block Name ',
					labelWidth: 130,
					maskRe: /[\w\s\-]/,
					regex: /[\w\s\-]/,
				},
				{
					xtype: 'numberfield',
					anchor: '100%',
					name: 'num_tier',
					fieldLabel: 'Number Of Tier',
					labelWidth: 130,
					value: 1,
					minValue: 1,
					allowBlank: false
				},
				{
					xtype: 'combobox',
					anchor: '100%',
					fieldLabel: 'Block Position',
					labelWidth: 130,
					name: 'block_position',
					store: 'position',
					queryMode: 'local',
					displayField: 'name',
					valueField: 'value',
					allowBlank: false
				},
				{
					xtype: 'combobox',
					anchor: '100%',
					fieldLabel: 'Slot-Row Orientation',
					labelWidth: 130,
					name: 'orientation',
					store: 'orientation',
					queryMode: 'local',
					displayField: 'name',
					valueField: 'value',
					allowBlank: false
				},
				{
					xtype: 'hidden',
					name: 'block_color',
					fieldLabel: 'Color',
					value: ''
				}
			],
			buttons: [{
				text: 'Save Block',
				formBind: true,
				handler: function() {
					if (this.up('form').getForm().isValid()){
						if (SetBlock_<?=$tab_id?>(this.up('form').getForm().findField("block_name").getValue(),this.up('form').getForm().findField("num_tier").getValue(),this.up('form').getForm().findField("block_position").getValue(),this.up('form').getForm().findField("orientation").getValue(),this.up('form').getForm().findField("block_color").getValue())){
							win.close();
						}
					}
				}
			},{
        text: 'Cancel',
        handler: function() {
          win.close();
        }
      }]
    }]
  });
  win.show();
});

	var updateYard = new Ext.Window({
		layout: 'fit',
		modal: true,
		title: 'Update Yard',
		closable: false,
		items: Ext.create('Ext.form.Panel', {
			frame: true,
			autoScroll: true,
			bodyPadding: 5,
			fieldDefaults: {
				labelAlign: 'left',
				labelWidth: 90,
				anchor: '100%'
			},
			items: [{
				xtype: 'textfield',
				name: 'yard_name',
				fieldLabel: 'Yard Name',
				maskRe: /[\w\s\-]/,
				regex: /[\w\s\-]/,
				value: '<?=$name?>',
				allowBlank: false
			}],
			buttons: [{
				text: 'Update Yard',
				formBind: true,
				handler: function() {
					if (this.up('form').getForm().isValid()){
						if (UpdateYard_<?=$tab_id?>(this.up('form').getForm().findField("yard_name").getValue())){
							updateYard.hide();
						}
					}
				}
			},{
				text: 'Cancel',
				handler: function() {
					updateYard.hide();
				}
			}]
		})
	});

function SetBlock_<?=$tab_id?>(name, tier, position, orientation, color){
	// event.preventDefault();
	// alert($("#result").html());
	var selected = $("#select-result_<?=$tab_id?>").html();
	//console.log(selected);
	var array_s  = selected.split(",");
	//console.log(array_s);

	var p = 0;
	var idx = -1;
	for (var i = 0; i < count_block_<?=$tab_id?>; i++){
		if(block_name_<?=$tab_id?>[i] == name){
			p = 1;
			idx = i;
		}
	}

	console.log(name);

	if (color==""){
		color = 'BLACK';
	}

	// console.log(cell.length);
	// console.log(array_s.length);
	var height=1;
	var width=1;
	var max_width=1;
	for (var i = 0; i < (array_s.length-1); i++){
		if (i==0){
			height=1;
			width=1;
		}else{
			if ((array_s[i]-1) == array_s[i-1]){
				width += 1;
			}else{
				height += 1;
				max_width = width;
				width=1;
			}
		}
		// console.log(array_s[i]);
		cellxy[array_s[i]].block = name;
		// console.log("--"+cell[array_s[i]].block+"--");
		// console.log("--"+cell[array_s[i]].stack+"--");
		cellxy[array_s[i]].stack = 1;

		var style = $("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "style" );
		style = typeof style !== 'undefined' ? style : "";

		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "class", "ui-stacking-default");
		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "style", style + "  border: 1px solid "+color+"; " );
		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "title", "Blok "+name );
	}

	if (p == 0){
		block_status_<?=$tab_id?>[count_block_<?=$tab_id?>]	 = 'ADD';
		block_name_<?=$tab_id?>[count_block_<?=$tab_id?>]	 = name;
		block_tier_<?=$tab_id?>[count_block_<?=$tab_id?>] = tier;
		block_position_<?=$tab_id?>[count_block_<?=$tab_id?>] = position;
		block_orientation_<?=$tab_id?>[count_block_<?=$tab_id?>] = orientation;
		block_color_<?=$tab_id?>[count_block_<?=$tab_id?>] = color;
		block_height_<?=$tab_id?>[count_block_<?=$tab_id?>] = Ext.getCmp('height_<?=$tab_id?>').getValue();
		block_width_<?=$tab_id?>[count_block_<?=$tab_id?>] = Ext.getCmp('width_<?=$tab_id?>').getValue();
		count_block_<?=$tab_id?>++;
	}else{
		block_status_<?=$tab_id?>[idx]	 = 'EDIT';
		block_name_<?=$tab_id?>[idx]	 = name;
		block_tier_<?=$tab_id?>[idx] = tier;
		block_position_<?=$tab_id?>[idx] = position;
		block_orientation_<?=$tab_id?>[idx] = orientation;
		block_color_<?=$tab_id?>[idx] = color;
		block_height_<?=$tab_id?>[idx] = Ext.getCmp('height_<?=$tab_id?>').getValue();
		block_width_<?=$tab_id?>[idx] = Ext.getCmp('width_<?=$tab_id?>').getValue();
	}

	$("#selected_width_<?=$tab_id?>").val("");
	$("#selected_height_<?=$tab_id?>").val("");
	Ext.getCmp('width_<?=$tab_id?>').setValue('');
	Ext.getCmp('height_<?=$tab_id?>').setValue('');
	return 1;
}

$("#unblock_<?=$tab_id?>").click(function(event) {
	// event.preventDefault();
	//alert($("#result").html());
	var selected = $("#select-result_<?=$tab_id?>").html();
	var selected_block =  $("#select-block_<?=$tab_id?>").html();
	var array_s  = selected.split(",");
	var block_id_s = selected_block.split(",").filter(v=>v!='').sort();
	// console.log(block_id_s);
	var block_id_merge = [];
	$.each(block_id_s, function(i, el){
	    if($.inArray(el, block_id_merge) === -1) block_id_merge.push(el);
	});
	for( var i = block_id_merge.length; i--;){
		if ( block_id_merge[i] === 'undefined') block_id_merge.splice(i, 1);
	}
	// console.log(block_id_merge);
	var unset_block = block_id_merge.toString();
	// console.log(unset_block);
	$("#unset_block_<?=$tab_id?>").html(unset_block);
	// var color 	 = $("#block_color").val();
	// var name 	 = $("#block_name").val();
	//console.log("++"+selected+"++");
	for (var i = 0; i < (array_s.length-1); i++){
		cellxy[array_s[i]].block = "";
		cellxy[array_s[i]].stack = 0;

		var style = $("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "style" );
		style = typeof style !== 'undefined' ? style : "";

		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "class", "ui-state-default");
		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "style", style + "  border: 1px solid #ffffff; " );
		$("#selectable_<?=$tab_id?> li").eq(array_s[i]).attr( "title", "" );
	}

	$("#selected_width_<?=$tab_id?>").val("");
	$("#selected_height_<?=$tab_id?>").val("");
	Ext.getCmp('width_<?=$tab_id?>').setValue('');
	Ext.getCmp('height_<?=$tab_id?>').setValue('');
});

function UpdateYard_<?=$tab_id?>(yard_name_){
// event.preventDefault();
//build width and height
var unset_block = $("#unset_block_<?=$tab_id?>").html();
// console.log('unset block');
// console.log(unset_block);
var width_str 	= "<width>"+slot_<?=$tab_id?>+"</width>";
var height_str	= "<height>"+row_<?=$tab_id?>+"</height>";

//build array of stacking area
var j = 0;
var index_stack = new Array();
for (var i = 0; i < total_<?=$tab_id?>; i++){
	if(cellxy[i].stack == 1){
		index_stack[j] = i;
		j++;
	}
}
var stack_ 		= index_stack.join(",");
var stack_str	= "<index>"+stack_+"</index>";
var unset_str = "<unset>"+unset_block+"</unset>";
// console.log("=="+stack_str+"==");
// console.log('celly');
// console.log(cellxy);
//build array of blocking area
var index_block = new Array();
var p = 0;
for (var j = 0; j < count_block_<?=$tab_id?>; j++){
	index_block[j] = new Array();
	for (var i = 0; i < total_<?=$tab_id?>; i++){
		if(cellxy[i].block == block_name_<?=$tab_id?>[j]){
			index_block[j][p] = i;
			p++;
		}
	}
	p = 0;
}

var block_str = "";
for (var j = 0; j < count_block_<?=$tab_id?>; j++){
	if (index_block[j].length>0){
		block_str += "<block><status>"+block_status_<?=$tab_id?>[j]+"</status><name>"+block_name_<?=$tab_id?>[j]+"</name><color>"+block_color_<?=$tab_id?>[j]+"</color><tier>"+block_tier_<?=$tab_id?>[j]+"</tier><position>"+block_position_<?=$tab_id?>[j]+"</position><orientation>"+block_orientation_<?=$tab_id?>[j]+"</orientation><height>"+block_height_<?=$tab_id?>[j]+"</height><width>"+block_width_<?=$tab_id?>[j]+"</width><cell>"+index_block[j].join(",")+"</cell></block>";
	}
}

//complete xml string
var xml_str = "\<\?xml version=\"1.0\" encoding=\"UTF-8\"\?\><yard>"+width_str+height_str+stack_str+unset_str+block_str+"</yard>";

var url = Contants.getAppRoot() + "yard/yard_editor/update_yard?id_yard=<?=$id_yard?>";
// var yard_name_ = $("#yard_name").val();

//loadmask.show();
$.post( url, { xml_: xml_str, yard_name : yard_name_}, function(data) {
	if (data=="1"){
		//loadmask.hide();
		Ext.Msg.alert('Success', 'Yard Updated');
		// Ext.getCmp('<?=$tab_id?>').close();
	}
});
return true;
}

var builder_<?=$tab_id?> = Ext.create('Ext.form.FieldSet',{
  requires: [
    'Ext.form.FieldContainer',
    'Ext.form.field.Text',
    'Ext.button.Button'
  ],

  controller: 'yardbuilder',
  anchor: '100%',
  margin: 5,

  items: [{
      xtype: 'fieldcontainer',
      fieldLabel: '',
      layout: {
        type: 'hbox',
        align: 'middle',
        pack: 'center'
      },
      items: [{
          xtype: 'textfield',
          margin: 5,
          id: 'width_<?=$tab_id?>',
          fieldLabel: 'Selection Width',
          labelAlign: 'top'
        },
        {
          xtype: 'textfield',
          margin: 5,
          id: 'height_<?=$tab_id?>',
          fieldLabel: 'Selection Height',
          labelAlign: 'top'
        }
      ]
    },
    {
      xtype: 'fieldcontainer',
      fieldLabel: '',
      layout: {
        type: 'hbox',
        align: 'middle',
        pack: 'center'
      },
      items: [{
        xtype: 'button',
        text: 'Save Yard',
        handler: function(){
            updateYard.show();
        }
      }]
    }
  ]

});
builder_<?=$tab_id?>.render('YardBuilder_<?=$tab_id?>');

<?php
	foreach ($block as $block_)
	{
		// print_r($block_);
		$cell_block	= explode(",",$block_->CELL);
		//$block_id = explode(",",$block_->BLOCK_ID);
?>
			RenderBlockEdit_<?=$tab_id?>(v,<?php echo json_encode((array) $block_)?>,<?php echo json_encode ($cell_block)?>,<?php echo json_encode ($block_->BLOCK_ID[0])?>);
			v++;
<?php
	}
?>
</script>

<center>
	<input type="button" id="set_block_pop_up_<?=$tab_id?>" class="button_set_block_pop_up" value=" Set Block " name="set_block_pop_up" style="display:none;"/>
	<input type="button" id="unblock_<?=$tab_id?>" value=" UnSet Block " class="unblock" name="unblock" style="display:none;"/>
	<div id="YardBuilder_<?=$tab_id?>"></div>
</center>
<br/>
<br/>

<span id="select-result_<?=$tab_id?>" style="display: none;"></span>
<span id="select-block_<?=$tab_id?>" style="display: none;"></span>
<span id="result_<?=$tab_id?>"></span>
<span id="unset_block_<?=$tab_id?>" style="display: none;"></span>

<center>
<div class="grid_<?=$tab_id?>">
	<table border="0" width="100%">
		<tr align="center" valign="top">
			<td align="center" valign="middle" style="padding-left: 2px; padding-right: 2px;">
				<ol id="selectable_<?=$tab_id?>">
					<div id="menu"></div>
					<?php
						$j = 1;
						$p = 0;
						for($i = 1; $i <= $L; $i++)
						{
							$m = ($width*$j) + 1;
							$cell_idx = $i - 1;

							if($cell_idx == @$index[$p])
							{
					?>
								<!-- block_id="<?=@$block_id[$p]?>" -->
								<li class="ui-stacking-default" index="<?=$i-1?>"  <?php if (($i%$m) == 0){ $j++;	?>style="clear: both;"<?php }?>></li>
					<?php
								$p++;
							}
							else
							{
					?>
							<li class="ui-state-default"  index="<?=$i-1?>" <?php if (($i%$m) == 0){ $j++;	?>style="clear: both;"<?php }?>></li>
					<?php
							}
						}

					?>
				</ol>
			</td>
		</tr>
	</table>
</div>

</center>
