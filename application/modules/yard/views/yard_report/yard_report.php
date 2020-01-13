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
    <table style="margin-left: 10px;">
      <?php
      $z = 0;
      $c = 0;
      $y = 0;
      $row2 = round($block_data['MAX_ROW'] / 2);
      $rows = '';
          for ($i=1; $i <= $block_data['MAX_SLOT']; $i++) {
            if($i == $slot){
            echo '<tr>';
            echo '<td valign="top">';

            echo '<table>';
          for ($x=1; $x <= $row2; $x++) {
            echo '<tr>';
            echo '<td>';
            $z++;
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
      <?php
      echo '</td>';
      echo '</tr>';
      }
      echo '</table>';

      echo '</td>';

      echo '<td>';
      echo '&nbsp &nbsp &nbsp';
      echo '</td>';

      echo '<td valign="top">';

      echo '<table>';
    for ($x=$row2+1; $x <= $block_data['MAX_ROW']; $x++) {
      echo '<tr>';
      echo '<td>';
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
    <?php
    echo '</td>';
    echo '</tr>';
    }
    echo '</table>';
    }
   } ?>
 </table>
  </body>
</html>
