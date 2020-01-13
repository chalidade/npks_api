<?php
/**
 * Sanitizing Output
 */
function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
    );

    $replace = array(
        '>',
        '<',
        '\\1'
    );

    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

ob_start("sanitize_output");

/**
 * Layout mini version
 */
$L	= $width * $height;
$s = 13;
$s_h = 13;
$s_h_white = 13;
$grid_width = ($s*$width)+8; // 8 fix value
$grid_height = ($s_h*$height)+8;

$border_color = '#8C92AC';
?>

<style type="text/css">

#center_content_<?=$tab_id?> {
    margin-left: 0px;
    padding-top: 20px;
    position: relative;
}

/* Yard module */
#selectable_<?=$tab_id?> .ui-selecting { background: #FECA40; }
#selectable_<?=$tab_id?> .ui-selected { background: #F39814; color: white; }
#selectable_<?=$tab_id?> { list-style-type: none; margin: 0; padding: 0; }
#selectable_<?=$tab_id?> li {
	float: left;
	width: <?php echo $s."px"?>;
	height: <?php echo $s."px"?>;
	font-size: 2em;
	text-align: center;
}
div.grid_<?=$tab_id?> {
	width:  <?php echo $grid_width."px"?>;
	height: <?php echo $grid_height."px"?>;
	font-size: 5px;
	position: absolute;
  background-image: url('<?= MAIN_DOMAIN ?>/../assets/tanah2.jpg');
}

.div_cell_block{
	float: left;
	width: <?php echo $s."px"?>;
	height: <?php echo $s_h."px"?>;
	border-top: 1px solid <?=$border_color?>;
	border-left: 1px solid <?=$border_color?>;
  border-bottom: 1px solid <?=$border_color?>;
	/*border: 1px solid #e8edff;*/
    font-size: 2em;
    text-align: center;
}

.div_cell_block_40{
	float: left;
	width: <?php echo ($s*2)."px"?>;
	height: <?php echo $s_h."px"?>;
	border-top: 1px solid <?=$border_color?>;
	border-left: 1px solid <?=$border_color?>;
	/*border: 1px solid #e8edff;*/
    font-size: 2em;
    text-align: center;
}

.notexist { background: #e8edff; }
.dummy { background: #008B8B; }

.cell_block_right{
    border-left: 1px solid <?=$border_color?>;
}

.cell_block_bottom{
    border-bottom: 1px solid <?=$border_color?>;
}

.div_cell_block_zero{
	float: left;
	width: 0px;
	height: <?php echo $s_h."px"?>;
    font-size: 2em;
    text-align: center;
}

.exist:hover{
	background: #FFBF00;/*#d0dafd;*/
	cursor: pointer;
}

.copy_cont:hover{
	cursor: copy;
}

.div_cell_block_whitespace_<?=$tab_id?>{
	float: left;
	width: <?php echo $s."px"?>;
	height: <?php echo $s_h_white."px"?>;
    font-size: 1.2em;
    text-align: center;
}

/* equipment group */
#equipment-group {
	position: absolute;
	top:0px;
	left:3px;
}

.equip-rtg {
	position:absolute;
}

.equip-rtg img {
	opacity:0.8;
}

.equip-rs {
	position:absolute;
}

.equip-rs img {
	opacity:0.8;
}

.equip-name {
	font-size: 6pt;
    position: absolute;
    width: 150px;
    color: #fff;
}

.equip-rtg-name {
    top: -11px;
    left: 0;
}

.equip-rs-name {
	top: 61px;
	left: 0;
}

.equip-block-name {
	float: left;
    padding: 0 2px;
}

.equip-block-act {
	float: left;
	margin-left: 2px;
    padding: 0 2px;
}

/** legend **/
.my-legend-group {

}
.my-legend {
	background: none repeat scroll 0 0 #eeeeee;
    border: 1px solid #cdcdcd;
    border-radius: 5px;
    margin-left: 5px;
    margin-top: 20px;
    padding: 10px 15px;
    width: 200px;
    float: left;
}
.my-legend .legend-title {
    text-align: left;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 90%;
}
.my-legend .legend-scale ul {
    margin: 0;
    margin-bottom: 5px;
    padding: 0;
    float: left;
    list-style: none;
}
.my-legend .legend-scale ul li {
    font-size: 80%;
    list-style: none;
    margin-left: 0;
    line-height: 18px;
    margin-bottom: 2px;
}
.my-legend ul.legend-labels li span {
    display: block;
    float: left;
    height: 16px;
    width: 30px;
    margin-right: 5px;
    margin-left: 0;
    border: 1px solid #999;
}
.my-legend .legend-source {
    font-size: 70%;
    color: #999;
    clear: both;
}
.my-legend a {
    color: #777;
}

/******* berth ******/

.berth {
	display: inline-block;
	background-image: url('<?= IMG_ ?>assets/background-shading-2.png');
    /*float: left;*/
    margin: 10px;
    text-align: center;
    padding: 3px;
    border: 1px solid;
    border-radius: 5px;
}

.yard_header {
  height: 53px;
  background-image: url('<?= MAIN_DOMAIN ?>/../assets/sea4.png');
  margin-top: -30px;
}

.copy_cont {cursor: copy;}

</style>

<center id="center_content_<?=$tab_id?>" class='mainmon'>
<!-- yard -->
<div class="grid_<?=$tab_id?>">
  <div class="yard_header"></div>
	<table border="0" width="100%">
		<tr align="center" valign="top">
			<td align="center" valign="middle" style="padding-left: 2px; padding-right: 2px;">
				<div id="selectable_<?=$tab_id?>">
                    <!-- HATI HATI menghapus setiap karakter di sini -->
					<?php
						$count_block = 0;
						$j = 1; $p = 0; $l = 0;
                        // $p is INDEX in data Monitoring

						$block_array = array();
						$flag_40f = false;
                        $flagging_border_right = false;
            $labelVariable = array();
						for($i = 0; $i <= $L; $i++){

							$block_flag = 0;
							$m = ($width*$j) + 1;

							if($i  == @$index[$p]) {
								if (!in_array($block_name[$p],$block_array)) {
									$block_array[] = $block_name[$p];

									$count_block += 1;
									$block_flag = 1;
								}
                $index_block_name = ($i)-((1+(2*$width)));

                if(!in_array($block_name[$p],$labelVariable)){
                  $labelVariable[] = $block_name[$p];
					?>

					<script>

						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").eq(0).css("font-weight", "bold");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").eq(0).css("font-size", "10px");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").eq(0).css("white-space", "nowrap");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").eq(0).text('<?=$block_name[$p]?>');

					</script>
              <?php
                }
              ?>
								<div class="<?php

                                        $flagging_border_right = true;
										echo "div_cell_block cont_size_".$cont_size[$p]." ";

									if($placement[$p]>0){
										echo "exist tier" .$placement[$p]. " ";
                     //if($cont_size_real[$p] == 40 ){echo "fourtyfeet "; }
									} else {
                      if($block_dummy[$p] == 'N'){
                          echo "notexist ";
                      }
                      else{
                        echo "dummy";
                      }
                  }


									?> <?php if($cont_size[$p] == 40 && $placement[$p] == '') {echo 'fourtyfeet_background'; } ?>"

									title="<?php echo $owner[$p] ?>"
                  owner="<?php echo $owner[$p] ?>"

									<?php
									if ($block_flag==1)
										echo "block-name-id='block_name_".$tab_id."_".$count_block."'";
									?>

									class="ui-stacking-default"
                                    data-index="<?=$i?>"
                                    data-slot="<?=$slot_[$p]?>"
                                    data-block_id="<?=$block_id[$p]?>"
									data-row="<?=$row_[$p]?>"
                                    data-max_row="<?=$max_row[$p]?>"
                                    data-orientation="<?=$orientation[$p]?>"
                                    data-placement="<?=$placement[$p]?>"
                                    data-ybc_id="<?=$ybc_id[$p]?>"
                                    id="ybc_<?=$ybc_id[$p]?>"
                                    data-yard_id = "<?=$id_yard?>"


									<?php
									if (($i%$m) == 0){
										$j++;
										echo "style=\"clear: both;\"";
									}
									?>

								>
                <div id="ybc40_<?=$ybc_id[$p]?>" <?php if($cont_size_real[$p] == 40 ){echo 'class="fourtyfeet"'; }?> ></div>
								</div>
                        <?php
								$p++;
							}
							else
							{
                        ?>
							<div class="div_cell_block_whitespace_<?=$tab_id?>
                                <?php if($flagging_border_right){
                                    echo "cell_block_right";
                                    $flagging_border_right = false;
                                }?>"
                                data-index="<?=$i?>"
                                <?php if (($i%$m) == 0){ $j++;	?>style="clear: both;"<?php }?>>
																	<?php
																		if (in_array($i,$label)){
																			$key = array_search($i,$label);
																			echo '<div style="margin-top:0px; font-size:8.5px; font-weight: bold;">'.$label_text[$key].'</div>';
																		}
																	?>
							</div>
                    <?php
							}
						}
                    ?>
                    <!-- End HATI HATI -->
				</div>
			</td>
		</tr>
	</table>
</div>
</center>

<div style="clear:both"></div>

<?php
ob_end_flush();
?>

<div id='container_in_yard_view<?=$tab_id?>'></div>
<script type="text/javascript">
/**
 * Global Variables
 */

var ux_singlestackview;
var ux_tabpanelstackview;
var counter_id_ssv = 1;
$(".fourtyfeet").parent().css("overflow", "hidden");

$(".exist").unbind('click').on('click', function(event){
  //console.log(Globals.getContYardView());
  //if(Globals.getContYardView() == false){
  var indexClick = $(this).data('index');
  var yardClick = '<?=$id_yard?>';
  var blockClick = $(this).data('block_id');
  var slotClick = $(this).data('slot');
  var sybcClick = $(this).data('ybc_id');

  var myMask = new Ext.LoadMask({
  msg: 'Mohon Tunggu....',
  target: Ext.getCmp('panel-<?=$tab_id?>')
});
  myMask.show();

  Ext.Ajax.request({
        url: Contants.getAppRoot() + 'yard/yard_monitoring/get_yard_stacking/'
            +yardClick+'/'+blockClick+'/'+slotClick+'/'+sybcClick,
        method: 'POST',
        scope:this,
        success: function(response, request){
          myMask.hide();
          var resData = Ext.decode(response.responseText);

          // var filterContent = "Block: <select class='filter_ciy filter_ciy_<?=$tab_id?> filter_block' disabled>";
          //     for(var keyBlock in resData.filter_block){
          //         var case_selected = ''; if (keyBlock == blockClick) { case_selected = 'selected'; }
          //         filterContent += "<option value='"+resData.filter_block[keyBlock].ID_BLOCK+"'"+case_selected
          //             + " data-slot=" + resData.filter_block[keyBlock].SLOT
          //             + " >"+resData.filter_block[keyBlock].NAME_BLOCK+"</option> ";
          //     }
          //     filterContent += "</select>";
          //
          //     filterContent += " Slot: <select class='filter_ciy filter_ciy_<?=$tab_id?> filter_slot' disabled>";
          //     // console.log(Number(resData.filter_block[blockClick].SLOT));
          //     for(var idx=1;idx<=Number(resData.filter_block[blockClick].SLOT);idx++){
          //         var case_selected = ''; if (idx == slotClick) { case_selected = 'selected'; }
          //         filterContent += "<option value='"+idx+"'"+case_selected+">"+idx+"</option> ";
          //     }
          //     filterContent += "</select>";

          var filterContent = '<table><tr>';
          filterContent += "<td><b>Block : " + resData.block_data.BLOCK_NAME + " | Slot : "+slotClick+" |</b></td>";
          filterContent += '<td style="padding-left:5px;"><div style="background-color:#A1CAF1; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">20\'\</td>';
          filterContent += '<td><div style="background-color:#ccff33; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">40\'\</td>';
          filterContent += '<td><div style="background-color:#d896ff; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">Muatan Kapal</td>';
          filterContent += '<td><div style="background-color:#ff967d; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">SP2</td>';
          filterContent += '</tr></table><br>';

              var switchContent = generateSwitchContent(resData, blockClick, slotClick);
              var bodyContent = generateBodyContent(resData, blockClick, slotClick);

              var htmlContent = "<div style='overflow:scroll;' id='ciy_container_"+counter_id_ssv+"' class='ciy_container'><div class='ciy_tab_switch'>"
                      + "<div style='float:left; margin-left:2px;' data-idwin="+counter_id_ssv+">" + filterContent + "</div>"
                      // + "<div style='' class='ciy_button_switch'>" + switchContent + "</div>"
                  + "</div>"
                  + bodyContent
              + "</div>";

              var tabComponent = {
                  itemId: 'tabpanel-' + counter_id_ssv,
                  title: 'Single Stack View '.concat(generateTitlePostfix(counter_id_ssv)),
                  html: htmlContent,
                  closable: true
              };

              var w = resData.configs.MAX_ROW * 45;
              var w1 = (parseInt(resData.configs.MAX_TIER) * 2);
              var widthWindow = ($(window).width());
              //alert(w);
              if(w < 350){
                w = 350;
              }
              else if(widthWindow < w){
                w = "80%";
              }
              if(w1 < 283){
                w1 = 283;
              }

              // console.log(resData.configs.MAX_TIER);
              // console.log(w1);

              if (!ux_singlestackview){
                  ux_tabpanelstackview = Ext.create('Ext.tab.Panel', {
                      tabPosition: 'top',
                      items: [tabComponent]
                  });
                  ux_singlestackview = Ext.create('Ext.Window', {
                      title: 'Container in Yard View',
                      autoScroll: true,
                      scrollable: true,
                      width: w,
                      //maxWidth: '80%',
                      minHeight: 283,
                      resizable: false,
                      y: 120,
                      plain: true,
                      headerPosition: 'top',
                      renderTo: 'container_in_yard_view<?=$tab_id?>',
                      listeners:{
                          afterlayout: function() {
                              var height = Ext.getBody().getViewSize().height;

                              var clipboard = new ClipboardJS('.copy_cont');
                              clipboard.on('success', function(e) {
                                  e.clearSelection();

                                  // console.info('Action:', e.action);
                                  // console.info('Text:', e.text);
                                  // console.info('Trigger:', e.trigger);
                                  //showTooltip(e.trigger, e.text);
                              });
                              clipboard.on('error', function(e) {
                                  // console.error('Action:', e.action);
                                  // console.error('Trigger:', e.trigger);
                                  // showTooltip(e.trigger, fallbackMessage(e.action));
                              });

                              // function showTooltip(elem, cont) {
                              //   Ext.toast(cont + ' Copied!');
                              // }

                          },
                          close:function(){
                              ux_singlestackview = null;
                              counter_id_ssv = 1;
                                  // @todo Not found another way.
                                  // Weakness: "zombie" Window component may be exist
                          },
                          scope:this
                      },
                      items: [ux_tabpanelstackview]
                  }).show();

                  // var myMask = new Ext.LoadMask(ux_tabpanelstackview, {msg:"Please wait..."});
                  // myMask.show();
                  counter_id_ssv++;
              } else {
                  ux_tabpanelstackview.add(tabComponent);
                  ux_tabpanelstackview.setActiveTab('tabpanel-' + counter_id_ssv).show();
                  counter_id_ssv++;
              }

              // insert container content
              fillBodyContent(resData)
          },
          failure: function(response, request){
              Ext.MessageBox.alert('Error', 'Please try again. ' + response.status);
          }
      });
    // }
  });

  /********* HTML Generate Manipulation ********/
  function generateTitlePostfix(counter_id_ssv){
      if (counter_id_ssv != 1){ return "("+counter_id_ssv+")"; } else { return ""; }
  }

  function generateSwitchContent(resData, blockClick, slotClick){
      var switchContent = ""; var i=0;
      for(var y =0;y<resData.data_idx.length;y++){
          if (i==0){
              switchContent += "<span data-refer='" +resData.data_idx[y]+ "'>" +resData.data_idx[y]+ "</span>";
              //switchContent += "<span data-refer='" +resData.data_idx[y]+ "' class='passive'>" +resData.data_idx[y]+ "</span>";
          } else {
              switchContent += "<span data-refer='" +resData.data_idx[y]+ "' class='passive'>" +resData.data_idx[y]+ "</span>";
          }
          i++;
      }
      return switchContent
  }

  function generateBodyContent(resData, blockClick, slotClick){
      var headerContent = "<table class='ciy_header'><tr><td colspan='"+Number(resData.configs.MAX_ROW)+"' style='font-weight:bold; font-size:12px'>Row</td><tr>";
      for(var i=1;i<=Number(resData.configs.MAX_ROW);i++){
          headerContent += "<td>" +i+ "</td>";
      }
      headerContent += "<td></td></tr></table>";
      var bodyContent = ""; var z=0;
      for(var y =0;y<resData.data_idx.length;y++){
          if (z>0){ var class_css = 'ciy_table hide';} else { class_css = 'ciy_table';}
          bodyContent += "<table class='"+class_css+"' data-slot='"+resData.data_idx[y]+"'><tr class='ciy_data'>";
          for(var j=Number(resData.configs.MAX_TIER);j>=1;j--){
              bodyContent += "<tr class='ciy_data'>";
              for(var k=1;k<=Number(resData.configs.MAX_ROW);k++){
                  bodyContent += "<td data-block='"+blockClick+"' data-slot='"+slotClick+"' data-row='" +k+ "' data-tier='" +j+ "' class='data CellWithComment'></td>";
              }
              if(j == resData.configs.MAX_TIER){
                bodyContent += "<td class='right_header'>" +j+ "</td><td class='right_header' rowspan='"+Number(resData.configs.MAX_TIER)+"' style='font-size:12px; font-weight:bold;'>Tier</td></tr>";
              }
              else{
                bodyContent += "<td class='right_header'>" +j+ "</td></tr>";
              }
          }
          bodyContent += "</table>";
          z++;
      }

      return (headerContent + bodyContent);
  }

  function fillBodyContent(resData){
      for(var key in resData.data){
          for(var i=0;i<resData.data[key].length;i++){
              if(resData.data[key][i].CONT_SIZE == 20){
                if(resData.data[key][i].DELIVERY_TO == 'DEPO'){
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('delivery_luar copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
                else if(resData.data[key][i].DELIVERY_TO == 'TPK'){
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('delivery_tpk copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
                else{
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('stacking copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
              }
              else{
                if(resData.data[key][i].DELIVERY_TO == 'DEPO'){
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('delivery_luar copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
                else if(resData.data[key][i].DELIVERY_TO == 'TPK'){
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('delivery_tpk copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
                else{
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                      generateDataContainer(resData.data[key][i])
                  ).addClass('stacking40 copy_cont').css({"position":"relative"} );
                  $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").attr('data-clipboard-text',resData.data[key][i].NO_CONTAINER);
                }
              }
          }
      }
  }

  function generateDataContainer(dataContainer){
      var cont = dataContainer.NO_CONTAINER;
      var size = dataContainer.CONT_SIZE;
      var type = dataContainer.CONT_TYPE;
      var status = dataContainer.CONT_STATUS;
      var owner = dataContainer.CONT_OWNER;
      var header = cont.substring(0, 4);
      var dtl = cont.substring(4);

      var htmlDiv = '';
      if(size == 40){
        htmlDiv = '<div class="fourtyfeet_dialog"></div>';
      }
      var data = htmlDiv + header + '<br />'+dtl+ '<span class="CellComment">No Container : '+cont+'<br> Size : '+size+'<br> Type : '+type+'<br> Status : '+status+'<br> Owner : '+owner+'</span>';
      return data;
  }

  var count_socket = '';
  if(Globals.getSocketYardMon() == 0){
    Globals.setSocketYardMon(1);
    var socket = io.connect(Contants.getNodeServer(), { forceNew: true });
    function tierMonitoring(branch, ybc_id, tier, cont, loc, ybc_id40, size, ybc_id_old, ybc_id_old40, tier_old, tier_old40, detail, delivery_to){
      if (branch == Ext.util.Cookies.get('branch')) {
        //count_socket = count_socket + 1;
        // var cont_ = cont + " | "+ size + "'";
        // toastr.info(loc,cont_, {timeOut: 8000});
        // console.log('ini params branch : ' + branch);
        // console.log('ini params ybc id : ' + ybc_id);
        // console.log('ini params ybc id 40 : ' + ybc_id40);
        // console.log('ini params tier yard : ' + tier);
        // console.log('ini params cont : ' + cont);
        // console.log('ini params cont size : ' + size);
        // console.log('ini params loc : ' + loc);
        // console.log('ini params ybc id old : ' + ybc_id_old);
        // console.log('ini params ybc id old 40 : ' + ybc_id_old40);
        // console.log('ini params tier old : ' + tier_old);
        // console.log('ini params tier old 40 : ' + tier_old40);
        // console.log(socket.connected);
        // console.log(socket.id);
        // console.log(count_socket);
        var detail = JSON.parse(detail);
        console.log(detail);
        console.log('delivery_to : '+ delivery_to);
        console.log('------------------------------------');

        if(ybc_id == 0){
          return false;
        }

        var cont_hdr = cont.substr(0, 4);
        var cont_dtl = cont.substr(4, 11);
        var cont_dtl = cont_hdr + '<br>' + cont_dtl;

        if(detail.cont_detail.detail_actve == 1){
          // cont_dtl = cont_dtl + '<span class="CellComment">No Container : '+detail.cont_detail.detail.CONT+'<br> Size : '+detail.cont_detail.detail.SIZE+'<br> Type : '+detail.cont_detail.detail.TYPE+'<br> Status : '+detail.cont_detail.detail.STATUS+'</span>';
          cont_dtl = cont_dtl + '<span class="CellComment">No Container : '+detail.cont_detail.detail.CONT+'<br> Size : '+detail.cont_detail.detail.SIZE+'<br> Type : '+detail.cont_detail.detail.TYPE+'<br> Status : '+detail.cont_detail.detail.STATUS+' <br> Owner : '+detail.cont_detail.OWNER+'</span>';
        }

        console.log(cont_dtl);
        if(ybc_id_old != 0){
          $("#ybc_"+ybc_id_old).removeClass();
          $('td[data-block='+detail.old_loc.BLOCK_ID+'][data-slot='+detail.old_loc.YBC_SLOT+'][data-row='+detail.old_loc.YBC_ROW+'][data-tier='+detail.tier_old+']').removeClass('stacking stacking40 delivery_luar delivery_tpk copy_cont');
          $('td[data-block='+detail.old_loc.BLOCK_ID+'][data-slot='+detail.old_loc.YBC_SLOT+'][data-row='+detail.old_loc.YBC_ROW+'][data-tier='+detail.tier_old+']').html("");
          if(tier_old > 0){
            $("#ybc_"+ybc_id_old).addClass("div_cell_block cont_size_ exist socket"+count_socket+" tier"+tier_old);
          }
          else{
            $("#ybc_"+ybc_id_old).addClass("div_cell_block cont_size_ notexist ");
            if(size == 40){
              $("#ybc40_"+ybc_id_old).removeClass();
            }
          }
        }

        $("#ybc_"+ybc_id).removeClass();
        $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+detail.tier+']').removeClass('stacking stacking40 delivery_luar delivery_tpk copy_cont');
        $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+detail.tier+']').html("");
        if(tier > 0){
          $("#ybc_"+ybc_id).addClass("div_cell_block cont_size_ exist socket"+count_socket+" tier"+tier);
          if(size == 40){
            if(delivery_to == 'TPK'){
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_tpk copy_cont');
            }
            else if(delivery_to == 'DEPO'){
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_luar copy_cont');
            }
            else{
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('stacking40 copy_cont');
            }
          }
          else{
            if(delivery_to == 'TPK'){
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_tpk copy_cont');
            }
            else if(delivery_to == 'DEPO'){
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_luar copy_cont');
            }
            else{
              $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').addClass('stacking copy_cont');
            }
          }
          $('td[data-block='+detail.new_loc.BLOCK_ID+'][data-slot='+detail.new_loc.YBC_SLOT+'][data-row='+detail.new_loc.YBC_ROW+'][data-tier='+tier+']').html(cont_dtl);
        }
        else{
          $("#ybc_"+ybc_id).addClass("div_cell_block cont_size_ notexist ");
        }

        if(size == 40){
          if(ybc_id_old40 > 0){
            $("#ybc_"+ybc_id_old40).removeClass();
            $('td[data-block='+detail.old_loc40.BLOCK_ID+'][data-slot='+detail.old_loc40.YBC_SLOT+'][data-row='+detail.old_loc40.YBC_ROW+'][data-tier='+detail.tier_old+']').removeClass('stacking stacking40 delivery_luar delivery_tpk');
            $('td[data-block='+detail.old_loc40.BLOCK_ID+'][data-slot='+detail.old_loc40.YBC_SLOT+'][data-row='+detail.old_loc40.YBC_ROW+'][data-tier='+detail.tier_old+']').html("");
            $("#ybc40_"+ybc_id_old40).removeClass();
            if(tier_old40 > 0){
              $("#ybc_"+ybc_id_old40).addClass("div_cell_block cont_size_ exist socket"+count_socket+" tier"+tier_old40);
              $("#ybc40_"+ybc_id_old40).addClass("fourtyfeet");
            }
            else{
              $("#ybc_"+ybc_id_old40).addClass("div_cell_block cont_size_ notexist ");
            }
          }

          $("#ybc_"+ybc_id40).removeClass();
          $("#ybc40_"+ybc_id40).removeClass();
          $("#ybc40_"+ybc_id).removeClass();
          $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+detail.tier+']').removeClass('stacking stacking40 delivery_luar delivery_tpk');
          $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+detail.tier+']').html("");
          if(tier > 0){
            $("#ybc_"+ybc_id40).addClass("div_cell_block cont_size_ exist socket"+count_socket+" tier"+tier);
            $("#ybc40_"+ybc_id40).addClass("fourtyfeet");
            $("#ybc40_"+ybc_id).addClass("fourtyfeet");
            if(size == 40){
              if(delivery_to == 'TPK'){
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_tpk');
              }
              else if(delivery_to == 'DEPO'){
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_luar');
              }
              else{
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('stacking40');
              }
            }
            else{
              if(delivery_to == 'TPK'){
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_tpk');
              }
              else if(delivery_to == 'DEPO'){
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('delivery_luar');
              }
              else{
                $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').addClass('stacking');
              }
            }
            $('td[data-block='+detail.new_loc40.BLOCK_ID+'][data-slot='+detail.new_loc40.YBC_SLOT+'][data-row='+detail.new_loc40.YBC_ROW+'][data-tier='+tier+']').html(cont_dtl);
          }
          else{
            $("#ybc_"+ybc_id40).addClass("div_cell_block cont_size_ notexist ");
          }
        }

        Globals.setSocketYardMon(socket.id);
        Globals.setContYardView(true);

        var ux_singlestackview_;
        var ux_tabpanelstackview_;
        var counter_id_ssv_ = 1;
        $(".exist.socket"+count_socket).unbind('click').on('click', function(event){
          Globals.setContYardView(true);
          console.log(Globals.getContYardView());
          var indexClick = $(this).data('index');
          var yardClick = $(this).data('yard_id');
          var blockClick = $(this).data('block_id');
          var slotClick = $(this).data('slot');
          var sybcClick = $(this).data('ybc_id');

          var myMask = new Ext.LoadMask({
          msg: 'Mohon tunggu....',
          target: Ext.getCmp('panel-<?=$tab_id?>')
        });
          myMask.show();

          Ext.Ajax.request({
                url: Contants.getAppRoot() + 'yard/yard_monitoring/get_yard_stacking/'
                    +yardClick+'/'+blockClick+'/'+slotClick+'/'+sybcClick,
                method: 'POST',
                scope:this,
                success: function(response, request){
                  myMask.hide();
                  var resData = Ext.decode(response.responseText);
                  var filterContent = '<table><tr>';
                  filterContent += "<td><b>Block : " + resData.block_data.BLOCK_NAME + " | Slot : "+slotClick+" |</b></td>";
                  filterContent += '<td style="padding-left:5px;"><div style="background-color:#A1CAF1; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">20\'\</td>';
                  filterContent += '<td><div style="background-color:#ccff33; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">40\'\</td>';
                  filterContent += '<td><div style="background-color:#ae63e4; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">TPK</td>';
                  filterContent += '<td><div style="background-color:#ff967d; width:15px; height:15px;"></div></td><td style="padding-left: 2px; padding-right: 10px;">LUAR</td>';
                  filterContent += '</tr></table><br>';

                      var switchContent = generateSwitchContent(resData, blockClick, slotClick);
                      var bodyContent = generateBodyContent(resData, blockClick, slotClick);

                      var htmlContent = "<div style='overflow:scroll;' id='ciy_container_"+counter_id_ssv_+"' class='ciy_container'><div class='ciy_tab_switch'>"
                              + "<div style='float:left; margin-left:2px;' data-idwin="+counter_id_ssv_+">" + filterContent + "</div>"
                              // + "<div style='' class='ciy_button_switch'>" + switchContent + "</div>"
                          + "</div>"
                          + bodyContent
                      + "</div>";

                      var tabComponent_ = {
                          itemId: 'tabpanel-' + counter_id_ssv_,
                          title: 'Single Stack View '.concat(generateTitlePostfix(counter_id_ssv_)),
                          html: htmlContent,
                          closable: true
                      };

                      var w = resData.configs.MAX_ROW * 45;
                      var w1 = (parseInt(resData.configs.MAX_TIER) * 2);
                      var widthWindow = ($(window).width());
                      //alert(w);
                      if(w < 350){
                        w = 350;
                      }
                      else if(widthWindow < w){
                        w = "80%";
                      }
                      if(w1 < 283){
                        w1 = 283;
                      }

                      // console.log(resData.configs.MAX_TIER);
                      // console.log(w1);

                      if (!ux_singlestackview_){
                          ux_tabpanelstackview_ = Ext.create('Ext.tab.Panel', {
                              tabPosition: 'top',
                              items: [tabComponent_]
                          });
                          ux_singlestackview_ = Ext.create('Ext.Window', {
                              title: 'Container in Yard View',
                              autoScroll: true,
                              scrollable: true,
                              width: w,
                              //maxWidth: '80%',
                              minHeight: 283,
                              resizable: false,
                              y: 120,
                              plain: true,
                              headerPosition: 'top',
                              renderTo: 'container_in_yard_view<?=$tab_id?>',
                              listeners:{
                                  afterlayout: function() {
                                      var height = Ext.getBody().getViewSize().height;

                                      var clipboard = new ClipboardJS('.copy_cont');
                                      clipboard.on('success', function(e) {
                                          e.clearSelection();

                                          // console.info('Action:', e.action);
                                          // console.info('Text:', e.text);
                                          // console.info('Trigger:', e.trigger);
                                          // showTooltip(e.trigger, e.text);
                                      });
                                      clipboard.on('error', function(e) {
                                          // console.error('Action:', e.action);
                                          // console.error('Trigger:', e.trigger);
                                          // showTooltip(e.trigger, fallbackMessage(e.action));
                                      });

                                      // function showTooltip(elem, cont) {
                                      //   //Ext.toast(cont + ' Copied!');
                                      // }

                                  },
                                  close:function(){
                                      ux_singlestackview_ = null;
                                      counter_id_ssv_ = 1;
                                          // @todo Not found another way.
                                          // Weakness: "zombie" Window component may be exist
                                  },
                                  scope:this
                              },
                              items: [ux_tabpanelstackview_]
                          }).show();

                          // var myMask = new Ext.LoadMask(ux_tabpanelstackview, {msg:"Please wait..."});
                          // myMask.show();
                          counter_id_ssv_++;
                      } else {
                          ux_tabpanelstackview_.add(tabComponent_);
                          ux_tabpanelstackview_.setActiveTab('tabpanel-' + counter_id_ssv_).show();
                          counter_id_ssv_++;
                      }

                      // insert container content
                      fillBodyContent(resData)
                  },
                  failure: function(response, request){
                      Ext.MessageBox.alert('Error', 'Please try again. ' + response.status);
                  }
              });
          });

      }
    };
    socket.on('updateTierMonitor', tierMonitoring);
    //socket.removeAllListeners('updateTierMonitor');
   }

</script>
