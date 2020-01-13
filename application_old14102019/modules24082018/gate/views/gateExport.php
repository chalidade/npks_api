<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0; margin-left:-40px;margin-right:-40px;}
    .tg td{font-family:Arial, sans-serif;font-size:12px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg th{font-family:Arial, sans-serif;font-size:12px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg .tg-s6z2{text-align:center}
    .tg .tg-baqh{text-align:center;vertical-align:top}
    .tg .tg-hgcj{font-weight:bold;text-align:center}
    .tz {margin-left:-45px;}
    .tz td{font-family:Arial, sans-serif;font-size:12px;padding:0 0 0 5px;font-weight:bold;}
    .header {font-family:Arial, sans-serif; font-size: 20px; font-weight:bold;}
    </style>
  </head>
  <body>
    <table style="margin-left:-40;">
      <tr>
        <td><img src="<?=DIR?>/assets/logo.png" width="130" height="60"></td>
        <td style="width:80%; text-align:center;"><div class="header">GATE REPORT</div></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td>:</td>
        <td><?=$branch?></td>
      </tr>
      <tr>
        <td>Activity</td>
        <td>:</td>
        <td><?=$activity?></td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td><?=$date1?> - <?=$date2?></td>
      </tr>
    </table>
    <table class="tg" style="width:200mm">
  <tr>
    <td class="tg-hgcj">Request No<br></td>
    <td class="tg-hgcj">Container No</td>
    <td class="tg-hgcj">Activity</td>
    <td class="tg-hgcj">Consignee</td>
    <td class="tg-hgcj">Truck No</td>
    <td class="tg-hgcj">Origin</td>
    <td class="tg-hgcj" width="110">Gate In</td>
    <td class="tg-hgcj" width="110">Gate Out</td>
  </tr>
      <?php foreach ($data as $val) { ?>
          <tr>
            <td><?=$val['REQUEST_NO']?></td>
            <td><?=$val['REQUEST_DTL_CONT']?></td>
            <td><?=$val['ACTIVITY']?></td>
            <td><?=$val['CONSIGNEE_NAME']?></td>
            <td><?=$val['GATE_TRUCK_NO']?></td>
            <td><?=$val['GATE_ORIGIN']?></td>
            <td><?=$val['GATE_IN']?></td>
            <td><?=$val['GATE_OUT']?></td>
          </tr>
      <?php } ?>
    </table>
  </body>
</html>
