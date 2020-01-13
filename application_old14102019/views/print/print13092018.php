<style type="text/css">
@page *{
    margin-top: 1.0mm;
    margin-bottom: 2.54mm;
    margin-left: 3.175mm;
    margin-right: 3.175mm;
}
.tg  {border-collapse:collapse;border-spacing:0;border:none;}
.tg td{font-family:Arial, sans-serif;font-size:9px;padding:4px 10px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;}
.tg th{font-family:Arial, sans-serif;font-size:9px;font-weight:normal;padding:4px 10px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;}
.tg .tg-sn89{font-size:9px;font-family:Verdana, Geneva, sans-serif !important;;vertical-align:top; border-color:#330001;}
.tg .tg-hnao{font-size:9px;font-family:Verdana, Geneva, sans-serif !important;;text-align:center; border-color:#330001;}
div.a {
    font-size: 8px;
    margin-left:8;
    margin-top:10;
}
</style>



<?php
 $z = 0;
 $arrCount = sizeof($gate);
 if($arrCount >=3){
   $x = 1;
 }
 else{
   $x = 0;
 }
for ($i=0; $i <=$x; $i++) {
  $data = explode(",",$gate[$i]); ?>
  <img src="<?=DIR?>/assets/logo.png" width="100" height="40" style="margin-top:-40px; margin-left:-45px;">
  <div style="margin-left:-45px;margin-right:-45px;"><hr></div>
  <div style="margin-left:-55px; margin-right:-55px">
  <table class="tg">
    <tr>
      <td class="tg-sn89">Truck-In Time<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[3]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Request No<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[4]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Activity</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[0]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Container No<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[2]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Seal No</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[8]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Origin</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[5]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Size<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[9]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Type</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[13]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Status</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[11]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Yard Name</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[14]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Yard Position</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89">Block: <?=$data[1]?>, Slot: <?=$data[10]?>, Row: <?=$data[7]?>, Tier: <?=$data[15]?> </td>
    </tr>
    <tr>
      <td class="tg-sn89">Truck No<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[12]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Remark<br></td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=$data[6]?></td>
    </tr>
    <tr>
      <td class="tg-sn89">Date/Time</td>
      <td class="tg-sn89">:</td>
      <td class="tg-sn89"><?=date('d-m-Y H:i:s')?></td>
    </tr>
  </table>
  <div class="a"><i>This document is generated by NPKS USTER <?=strtoupper($branch)?></i></div>
  </div>
<?php } ?>
