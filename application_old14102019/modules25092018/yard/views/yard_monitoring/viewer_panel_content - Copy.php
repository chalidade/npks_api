<?php
	$L	= $width * $height;
	$s1 = 28;
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
.tier1{ background: #adcf60; }
.tier2{ background: #8db438; }
.tier3{ background: #fde74c; }
.tier4{ background: #fa7921; }
.tier5{ background: #e46e1e; }
.tier6{ background: #e55934; }
</style>

<script>
function test(){
	alert('ok');
};
var plan_ = "";
var capacity_ = "";
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

			// Ext.getCmp(plan_).setValue(str_plan);
			// Ext.getCmp(capacity_).setValue(capacity);
			// $("#selected_plan_").val(str_plan);
			// $("#selected_capacity_").val(capacity);
		}
	});

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
									if($placement[$p]>0){

					?>
								<li class="ui-stacking-default" ybc_id="<?=$ybc_id[$p]?>" index="<?=$i?>" slot="<?=$slot_[$p]?>" row="<?=$row_[$p]?>" tier="<?=$tier_[$p]?>" title="<?=$title[$p]?>" block_id="<?=$block_id[$p]?>" orientation="<?=$orientation[$p]?>" position="<?=$position[$p]?>"
								<?php if (($i%$m) == 0){ $j++;?>
								style="clear: both; border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
								<?php }else{ ?>
								style="border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
              <?php } ?>><a href="javascript:void(0);"><div style="margin-top:-2.5px;"><?php if($placement[$p]>0){echo $placement[$p];}?></div></a></li>
					<?php

				} else { ?>

					<li class="ui-stacking-default" ybc_id="<?=$ybc_id[$p]?>" index="<?=$i?>" slot="<?=$slot_[$p]?>" row="<?=$row_[$p]?>" tier="<?=$tier_[$p]?>" title="<?=$title[$p]?>" block_id="<?=$block_id[$p]?>" orientation="<?=$orientation[$p]?>" position="<?=$position[$p]?>"
					<?php if (($i%$m) == 0){ $j++;?>
					style="clear: both; border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
					<?php }else{ ?>
					style="border-bottom: solid #6dadd6; border-right: solid #6dadd6;border-width:0.15em;"
				<?php } ?>><?php if($placement[$p]>0){echo $placement[$p];}?></li>

				<?php		}

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
