<?php
	$L	= $width * $height;
	$s1 = 15;
	$s = 15;
	$grid_width = ($s1*$width)+8;
	$grid_height = ($s*$height)+8;
?>

<style>
#selectable_<?=$tab_id?> .ui-selecting { background: #FECA40; }
#selectable_<?=$tab_id?> .ui-selected { background: #F39814; color: white; }
#selectable_<?=$tab_id?> { list-style-type: none; margin: 0; padding: 0; }
#selectable_<?=$tab_id?> li {float: left; width: <?php echo $s1."px"?>; height: <?php echo $s."px"?>; font-size: 2em; text-align: center; }
div.grid_<?=$tab_id?> {
	width:  <?php echo $grid_width."px"?>;
	height: <?php echo $grid_height."px"?>;
	font-size: 5px;
}
</style>

<script>
var plan_ = '<?=$_POST['plan']?>';
var capacity_ = '<?=$_POST['capacity']?>';
// console.log('<?=$xmlData?>');
$(function() {
	$( "#selectable_<?=$tab_id?>" ).selectable({
		filter: ".ui-stacking-default",
		start: function( event, ui ) {
			$( "#select-result_<?=$tab_id?>" ).empty();
			$( "#result_<?=$tab_id?>" ).empty();
			$( "#block_id_<?=$tab_id?>" ).empty();
			$( "#ybc_id_<?=$tab_id?>" ).empty();
		},
		selected: function(event, ui) {
			//console.log($(ui.selected).attr('index'));
			if ($( "#select-result_<?=$tab_id?>" ).html()!=""){
				$( "#select-result_<?=$tab_id?>" ).append(",");
			}
			if ($( "#result_<?=$tab_id?>" ).html()!=""){
				$( "#result_<?=$tab_id?>" ).append(",");
			}
			$( "#select-result_<?=$tab_id?>" ).append(
				$(ui.selected).attr('title')+"^"+$(ui.selected).attr('slot')+"^"+$(ui.selected).attr('row')+"^"+$(ui.selected).attr('tier')
			);
			$( "#result_<?=$tab_id?>" ).append(
				$(ui.selected).attr('index')
			);
			if ($( "#block_id_<?=$tab_id?>" ).html()==""){
				$( "#block_id_<?=$tab_id?>" ).append(
					$(ui.selected).attr('block_id')
				);
			}
			if ($( "#ybc_id_<?=$tab_id?>" ).html()!=""){
				$( "#ybc_id_<?=$tab_id?>" ).append(",");
			}
			$( "#ybc_id_<?=$tab_id?>" ).append(
				$(ui.selected).attr('ybc_id')
			);
		},
		stop: function( event, ui ) {
			var str = $( "#select-result_<?=$tab_id?>").html();
			var list_plan = str.split(",");
			var block = "";
			var tier = 0;
			var min_slot=0;
			var max_slot=0;
			var min_row=0;
			var max_row=0;
			var flag = 1;
			for(i=0;i<list_plan.length;i++){
				var temp = list_plan[i].split("^");
				// console.log(temp);
				if (i==0){
					if (temp[1] == undefined){
						flag = 0;
						break;
					}
					block = temp[0];
					tier = temp[3];
					min_slot = temp[1];
					max_slot = temp[1];
					min_row = temp[2];
					max_row = temp[2];
				}
				if (parseInt(temp[1])>max_slot){
					max_slot = temp[1];
				}
				if (parseInt(temp[1])<min_slot){
					min_slot = parseInt(temp[1]);
				}
				if (parseInt(temp[2])>max_row){
					max_row = parseInt(temp[2]);
				}
				if (parseInt(temp[2])<min_row){
					min_row = parseInt(temp[2]);
				}
			}

			var str_plan = "";
			var capacity = "";

			if (flag){
				var str_plan = "Blok: "+block+";";
				str_plan += "Slot: "+min_slot+"-"+max_slot+";";
				str_plan += "Row: "+min_row+"-"+max_row+";";

				var capacity = (max_slot-min_slot+1)*(max_row-min_row+1)*tier;
			}

			Ext.getCmp(plan_).setValue(str_plan);
			Ext.getCmp(capacity_).setValue(capacity);
			$("#selected_plan_").val(str_plan);
			$("#selected_capacity_").val(capacity);
		}
	});

	$.contextMenu({
		selector: "#selectable_<?=$tab_id?> .ui-selected",
		items: {
			"plan_new": {
				name: "Plan with New Category",
				icon: "edit",
				callback: function(key, options) {
					Ext.create({
						xtype: 'newcategoryplan'
					});
				}
			},
			"plan_existing": {
				name: "Plan with Existing Category",
				icon: "edit",
				callback: function(key, options) {
					Ext.create({
						xtype: 'existingcategoryplan'
					});
				}
			},
			"sep1": "---------",
			"quit": {
				name: "Cancel",
				icon: "quit",
				callback: function(key, options) {
					// $(this).contextMenu("hide");
					PlanYard_(20);

				}
			}
		}
	});
});

// function PlanYard_(category_id){
// 	var block_str = "";
// 	block_str += "<block_id>"+$("#block_id_<?=$tab_id?>").html()+"</block_id>";
// 	block_str += "<index>"+$("#result_<?=$tab_id?>").html()+"</index>";
// 	block_str += "<ybc_id>"+$("#ybc_id_<?=$tab_id?>").html()+"</ybc_id>";
//
// 	var xml_str = "\<\?xml version=\"1.0\" encoding=\"UTF-8\"\?\><plan><block>"+block_str+"</block><category_id>"+category_id+"</category_id></plan>";
//
// 	var url = Contants.getAppRoot() +'yard/yard_plan/plan_yard';
// 	console.log('xml yard plan');
// 	console.log(xml_str);
// 	// loadmask.show();
// 	$.post( url+"?id_yard=<?=$id_yard?>", { xml_: xml_str}, function(data) {
// 		// console.log(data);
// 		if (data=='1'){
// 			// loadmask.hide();
// 			Ext.Msg.alert('Success', 'Plan Inserted');
// 			// $("#list_yard_<?=$tab_id?>").change();
// 		}
// 	});
// 	return true;
// }

Ext.define('PlanYard__', {
  singleton: true,
	setPlan: function(category_id){
		var block_str = "";
		block_str += "<block_id>"+$("#block_id_<?=$tab_id?>").html()+"</block_id>";
		block_str += "<index>"+$("#result_<?=$tab_id?>").html()+"</index>";
		block_str += "<ybc_id>"+$("#ybc_id_<?=$tab_id?>").html()+"</ybc_id>";

		var xml_str = "\<\?xml version=\"1.0\" encoding=\"UTF-8\"\?\><plan><block>"+block_str+"</block><category_id>"+category_id+"</category_id></plan>";

		var url = Contants.getAppRoot() +'yard/yard_plan/plan_yard';
		// loadmask.show();
		$.post( url+"?id_yard=<?=$id_yard?>", { xml_: xml_str}, function(data) {
			// console.log(data);
			if (data=='1'){
				// loadmask.hide();
				Ext.Msg.alert('Success', 'Plan Inserted');
				// $("#list_yard_<?=$tab_id?>").change();
			}
		});
		return [plan_,capacity_];
  }
});
</script>

<span id="select-result_<?=$tab_id?>" style="display: none;"></span>
<span id="result_<?=$tab_id?>" style="display: none;"></span>
<span id="block_id_<?=$tab_id?>" style="display: none;"></span>
<span id="ybc_id_<?=$tab_id?>" style="display: none;"></span>
<div id="popup_script_<?=$tab_id?>"></div>

<center>

<div class="grid_<?=$tab_id?>">
	<table border="0" width="100%">
		<tr align="center" valign="top">
			<td align="center" valign="middle" style="padding-left: 2px; padding-right: 2px;">
				<ol id="selectable_<?=$tab_id?>">
					<?php
						$j = 1;
						$p = 0;
						$l = 0;
						$coen = 0;
						$block_array = array();
						for($i = 0; $i <= $L; $i++){
							$m = ($width*$j) + 1;
							$cell_idx = $i;

							if($cell_idx == @$index[$p]){
								if (!in_array($title[$p],$block_array)) {
									$block_array[] = $title[$p];
									$index_block_name = ($i)-(1+(2*$width));
								?>
								<script>
									$("#selectable_<?=$tab_id?> li[index='"+<?=$index_block_name?>+"']").css("font-weight", "bold");
									$("#selectable_<?=$tab_id?> li[index='"+<?=$index_block_name?>+"']").css("font-size", "12px");
									$("#selectable_<?=$tab_id?> li[index='"+<?=$index_block_name?>+"']").css("white-space", "nowrap");
									$("#selectable_<?=$tab_id?> li[index='"+<?=$index_block_name?>+"']").text('<?=$title[$p]?>');
								</script>
								<?php
								}
								if ($cell_idx == @$plan[$coen]){
					?>
								<li class="ui-plan-default" ybc_id="<?=$ybc_id[$p]?>" index="<?=$i?>" slot="<?=$slot_[$p]?>" row="<?=$row_[$p]?>" tier="<?=$tier_[$p]?>" title="<?=$title[$p]?>" block_id="<?=$block_id[$p]?>" orientation="<?=$orientation[$p]?>" position="<?=$position[$p]?>"
								<?php if (($i%$m) == 0){ $j++;?>
								style="clear: both; border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
								<?php }else{ ?>
								style="border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
              <?php } ?>> <div style="margin-top:-3px;"></div></li>
					<?php
									$coen++;
								}else{
					?>
								<li class="ui-stacking-default" ybc_id="<?=$ybc_id[$p]?>" index="<?=$i?>" slot="<?=$slot_[$p]?>" row="<?=$row_[$p]?>" tier="<?=$tier_[$p]?>" title="<?=$title[$p]?>" block_id="<?=$block_id[$p]?>" orientation="<?=$orientation[$p]?>" position="<?=$position[$p]?>"
								<?php if (($i%$m) == 0){ $j++;?>
								style="clear: both; border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
								<?php }else{ ?>
								style="border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
              <?php } ?>><?php if($placement[$p]>0){echo $placement[$p];}?></li>
					<?php
								}
					?>
					<?php
								$p++;
							}
							else
							{
					?>
							<li index="<?=$i?>"<?php if (($i%$m) == 0){ $j++;	?>style=""<?php }?> >
								<?php
									if (in_array($i,$label)){
										$key = array_search($i,$label);
										echo $label_text[$key];
									}
								?>
							</li>
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
