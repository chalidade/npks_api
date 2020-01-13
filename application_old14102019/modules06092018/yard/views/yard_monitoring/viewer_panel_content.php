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

/* Vessel berthing module */
.vessel_selector{
    position: absolute;
    padding: 5px;
    cursor: pointer;
    -webkit-border-radius: 20px;
    -moz-border-radius: 20px;
    border-radius: 20px;
    border:1px solid #EFE4DE;
    background-color:#FFFFFF;
    -webkit-box-shadow: #b3b3b3 8px 8px 8px;
    -moz-box-shadow: #b3b3b3 8px 8px 8px;
    box-shadow: #b3b3b3 8px 8px 8px;
    padding: 10px;

    left: 600px;
    top: 250px;
}

.vessel_selector figcaption {
    margin: 10px 0 0 0;
    font-variant: small-caps;
    font-family: Arial;
    font-weight: bold;
}

.vessel_selector img:hover {
    transform: scale(1.1);
    -ms-transform: scale(1.1);
    -webkit-transform: scale(1.1);
    -moz-transform: scale(1.1);
    -o-transform: scale(1.1);
}

.vessel_selector img {
    transition: transform 0.2s;
    -webkit-transition: -webkit-transform 0.2s;
    -moz-transition: -moz-transform 0.2s;
    -o-transition: -o-transform 0.2s;
}

#left_container {
	position: relative;
	width: 60%;
	float: left;
	height: 300px;
	padding: 10px;
}

#right_container {
	position: relative;
	width: 40%;
	float: left;
	height: 300px;
	padding: 10px;
}

#bottom_container {
	clear: both;
	position: relative;
	width: 100%;
	padding: 10px;
}

.dl {
	font-weight: bold;
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
}

.div_cell_block_<?=$tab_id?>{
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

/******* coloring filter ******/
/*.tier1{ background: #A1CAF1; }
.tier2{ background: #6DA9E3; }
.tier3{ background: #297CCC; }
.tier4{ background: #014585; }
.tier5{ background: #014585; }
.tier6{ background: #014585; }*/

.tier1{ background: #adcf60; }
.tier2{ background: #8db438; }
.tier3{ background: #fde74c; }
.tier4{ background: #fa7921; }
/* .tier5{ background: #e46e1e; } */
.tier5{ background: #f92121; }
/* .tier6{ background: #e55934; } */
.tier6{ background: #c11919; }

/******* Container in Yard View ******/
.ciy_tab_switch {
    height: 40px;
    margin-top: 20px;
    text-align: center;
    width: 495px; /*@todo*/
}
.ciy_tab_switch span {
    display: inline-block;
    width: 50px;
    height: 35px;
    margin-left: 5px;
    margin-right: 5px;
    padding: 10px;
    border-radius: 5px;
    color: #214FC6;
    text-align: center;
    background-color: #A1CAF1;
    font-weight: bold;
}
.ciy_tab_switch .passive {
    background-color: #007FFF;
    cursor: pointer;
}
.ciy_header td {
    border: none;
    min-width: 40px;
    padding: 5px;
    text-align: center;
    font-family: helvetica, arial, verdana, sans-serif;
    font-size: 9px;
}
.ciy_table table, .ciy_table th, .ciy_table td {
   border: 1px solid black;
}
.ciy_table {
    font-family: helvetica, arial, verdana, sans-serif;
    font-size: 9px;
}
.ciy_table table {
    width:40px;
    padding:.5em;
}
.ciy_data tr {}
.ciy_data .data {
    min-width: 40px;
    height: 40px;
    padding: 3px;
    background: #e8edff;
}
.ciy_data .stacking {
    background: #A1CAF1;
    font-size: 8px;
}
.ciy_data .right_header {
    border: none;
    min-width: 10px;
    background: #FFFFFF;
    padding-left: 10px;
}
.hide {display: none;}

/**** Filter Container in Yard ****/
@media screen and (-webkit-min-device-pixel-ratio:0) {  /*safari and chrome*/
    .filter_ciy {
        height:20px;
        line-height:32px;
        background:#f4f4f4;
    }
}
.filter_ciy::-moz-focus-inner { /*Remove button padding in FF*/
    border: 0;
    padding: 0;
}
@-moz-document url-prefix() { /* targets Firefox only */
    .filter_ciy {
        padding: 16px 0!important;
    }
}
@media screen\0 { /* IE Hacks: targets IE 8, 9 and 10 */
    .filter_ciy {
        height:32px;
        line-height:30px;
    }
}
</style>

<center id="center_content_<?=$tab_id?>" class='mainmon'>
<!-- yard -->
<div class="grid_<?=$tab_id?>">
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

						for($i = 0; $i <= $L; $i++){

							$block_flag = 0;
							$m = ($width*$j) + 1;

							if($i  == @$index[$p]) {
								if (!in_array($block_name[$p],$block_array)) {
									$block_array[] = $block_name[$p];
									$index_block_name = ($i)-((1+(2*$width)));
									$count_block += 1;
									$block_flag = 1;
								}

					?>

					<script>
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").css("font-weight", "bold");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").css("font-size", "10px");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").css("white-space", "nowrap");
						$(".div_cell_block_whitespace_<?=$tab_id?>[data-index='"+<?=$index_block_name?>+"']").text('<?=$block_name[$p]?>');
					</script>

								<div class="<?php

                                        $flagging_border_right = true;
										echo "div_cell_block_".$tab_id." ";

									if($placement[$p]>0){
										echo "exist tier" .$placement[$p]. " ";
									} else {
                                        echo "notexist ";
                                    }


									?>"

									title="<?php echo $block_name[$p] ?>"

									<?php
									if ($block_flag==1)
										echo "id='block_name_".$tab_id."_".$count_block."'";
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

									<?php
									if (($i%$m) == 0){
										$j++;
										echo "style=\"clear: both;\"";
									}
									?>
								>
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
																			echo '<div style="margin-top:0px; font-size:8px;">'.$label_text[$key].'</div>';
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

$(".exist").on('click', function(event){
  var indexClick = $(this).data('index');
  var yardClick = '<?=$id_yard?>';
  var blockClick = $(this).data('block_id');
  var slotClick = $(this).data('slot');
  var sybcClick = $(this).data('ybc_id');

  var myMask = new Ext.LoadMask({
  msg: 'Saving....',
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
          var filterContent = "Block: <select class='filter_ciy filter_ciy_<?=$tab_id?> filter_block'>";
              for(var keyBlock in resData.filter_block){
                  var case_selected = ''; if (keyBlock == blockClick) { case_selected = 'selected'; }
                  filterContent += "<option value='"+resData.filter_block[keyBlock].ID_BLOCK+"'"+case_selected
                      + " data-slot=" + resData.filter_block[keyBlock].SLOT
                      + " >"+resData.filter_block[keyBlock].NAME_BLOCK+"</option> ";
              }
              filterContent += "</select>";

              filterContent += " Slot: <select class='filter_ciy filter_ciy_<?=$tab_id?> filter_slot'>";
              // console.log(Number(resData.filter_block[blockClick].SLOT));
              for(var idx=1;idx<=Number(resData.filter_block[blockClick].SLOT);idx++){
                  var case_selected = ''; if (idx == slotClick) { case_selected = 'selected'; }
                  filterContent += "<option value='"+idx+"'"+case_selected+">"+idx+"</option> ";
              }
              filterContent += "</select>";

              var switchContent = generateSwitchContent(resData, blockClick, slotClick);
              var bodyContent = generateBodyContent(resData, blockClick, slotClick);

              var htmlContent = "<div id='ciy_container_"+counter_id_ssv+"' class='ciy_container'><div class='ciy_tab_switch'>"
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
              if(w < 350){
                w = 350;
              }
              if(w1 < 283){
                w1 = 283;
              }

              console.log(resData.configs.MAX_TIER);
              console.log(w1);

              if (!ux_singlestackview){
                  ux_tabpanelstackview = Ext.create('Ext.tab.Panel', {
                      // id: 'tabpanel_singlestack<?=$tab_id?>',
                      tabPosition: 'top',
                      items: [tabComponent]
                  });
                  ux_singlestackview = Ext.create('Ext.Window', {
                      // id: 'win_ciy_view<?=$tab_id?>',
                      title: 'Container in Yard View',
                      autoScroll: true,
                      scrollable: true,
                      width: w,
                      // maxWidth: 800,
                      minHeight: 283,
                      resizable: false,
                      y: 120,
                      plain: true,
                      headerPosition: 'top',
                      renderTo: 'container_in_yard_view<?=$tab_id?>',
                      listeners:{
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
      var headerContent = "<table class='ciy_header'><tr>";
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
                  bodyContent += "<td data-row='" +k+ "' data-tier='" +j+ "' class='data'></td>";
              }
              bodyContent += "<td class='right_header'>" +j+ "</td></tr>";
          }
          bodyContent += "</table>";
          z++;
      }

      return (headerContent + bodyContent);
  }

  function fillBodyContent(resData){
      for(var key in resData.data){
          for(var i=0;i<resData.data[key].length;i++){
              $(".ciy_table[data-slot="+key+"] td[data-row="+resData.data[key][i].YD_ROW+"][data-tier="+resData.data[key][i].YD_TIER+"]").html(
                  generateDataContainer(resData.data[key][i])
              ).addClass('stacking');
          }
      }
  }

  function generateDataContainer(dataContainer){
      var cont = dataContainer.NO_CONTAINER;
      var header = cont.substring(0, 4);
      var dtl = cont.substring(4);
      return header + '<br />'+dtl;
  }

</script>
