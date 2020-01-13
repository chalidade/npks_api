<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Yard Report</title>
    <style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;align:center;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg .tg-s6z2{text-align:center}
    .tg .tg-xyyz{font-size:40px;font-family:Impact, Charcoal, sans-serif !important;;text-align:center}
    .tg .tg-hgcj{font-weight:bold;text-align:center}
    .tg .tg-obcv{border-color:#000000;text-align:center}
    </style>
  </head>
  <body>
    <table style="margin-left:-40;">
      <tr>
        <td colspan="6"><img src="<?=DIR?>/../assets/logo.png" width="130" height="60"></td>
      </tr>
      <tr>
          <td colspan="6" style="width:80%; text-align:left; font-weight:bold"><div class="header">YARD REPORT</div></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td colspan="5">: <?=$branch?></td>
      </tr>
      <tr>
        <td>Yard</td>
        <td colspan="5">: <?=$yard_name?></td>
      </tr>
      <tr>
        <td>Block</td>
        <td colspan="5">: <?=$block_name?></td>
      </tr>
      <tr>
        <td></td>
      </tr>
    </table>
    <table>
      <tr>
        <?php
        $z = 0;
        $c = 0;
        $rows = '';
            for ($i=1; $i <= $block_data['MAX_SLOT']; $i++) {
                if($z > 3){
                  echo '<tr>';
                }
                else{
                $z = $z+1;
              }
                echo '<td class=".tg-obcv" style="padding:7px 7px 7px 30px">';
                echo '<div style="font-weight: 900; font-size: 14px;">SLOT: '.$i.'</div>';
                for ($x=1; $x <= $block_data['MAX_ROW']; $x++) {
         ?>
        <table class="tg">
          <tr>
            <th class="tg-hgcj">TIER<br></th>
            <th class="tg-hgcj" width="150">NO CONTAINER</th>
            <th class="tg-hgcj" width="70">SIZE</th>
            <th class="tg-hgcj" width="70">TYPE</th>
            <th class="tg-hgcj" width="70">STATUS</th>
            <th class="tg-hgcj" width="70">ROW</th>
          </tr>
          <?php

          $REAL_YARD_CONT = '';
          $REAL_YARD_CONT_SIZE = '';
          $REAL_YARD_CONT_TYPE = '';
          $REAL_YARD_CONT_STATUS = '';

          for ($y=1; $y <= $block_data['MAX_TIER'] ; $y++) {

            foreach ($cont_data as $data) {

                if($data['YBC_SLOT'] == $i AND $data['YBC_ROW'] == $x AND $data['REAL_YARD_TIER'] == $y){

                    $REAL_YARD_CONT = $data['REAL_YARD_CONT'];
                    $REAL_YARD_CONT_SIZE = $data['REAL_YARD_CONT_SIZE'];
                    $REAL_YARD_CONT_TYPE = $data['REAL_YARD_CONT_TYPE'];
                    $REAL_YARD_CONT_STATUS = $data['REAL_YARD_CONT_STATUS'];
                    break;

                }
                else{
                  $REAL_YARD_CONT = '';
                  $REAL_YARD_CONT_SIZE = '';
                  $REAL_YARD_CONT_TYPE = '';
                  $REAL_YARD_CONT_STATUS = '';
                }

            }


            ?>

            <tr>
              <td class="tg-s6z2"><?=$y?></td>
              <td class="tg-s6z2"><?= $REAL_YARD_CONT ?> </td>
              <td class="tg-s6z2"><?= ($REAL_YARD_CONT_SIZE)?></td>
              <td class="tg-s6z2"><?= ($REAL_YARD_CONT_TYPE)?></td>
              <td class="tg-s6z2"><?= ($REAL_YARD_CONT_STATUS)?></td>
              <?php if($y == 1){ ?>
              <td class="tg-xyyz" rowspan="<?=$block_data['MAX_TIER']?>"><?=$x?></td>
            <?php } ?>
            </tr>

          <?php


          } ?>
        </table>
        <BR>
      <?php } echo '</td>';
      if($z == 3){
        echo '</tr>';
        $z = 0;
      }
     } ?>
    </tr>
    </table>
  </body>
</html>
