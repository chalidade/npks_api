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
    .noborder {border-color:#000000;}
    </style>
  </head>
  <body>
    <table style="margin-left:-40;">
      <tr>
        <td colspan="10"><img src="<?=DIR?>/../assets/logo.png" width="130" height="60"></td>
      </tr>
      <tr>
          <td colspan="10" style="width:80%; text-align:center;"><div class="header">SHUFFLING REPORT</div></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td colspan="9">: <?=$branch?></td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td colspan="9">: <?=$date1?> - <?=$date2?></td>
      </tr>
    </table>
    <table class="tg" style="width:200mm">
      <tr>
        <td class="tg-hgcj">Container No</td>
        <td class="tg-hgcj">Size<br></td>
        <td class="tg-hgcj">Type<br></td>
        <td class="tg-hgcj">Status<br></td>
        <td class="tg-hgcj" width="150">Location Before</td>
        <td class="tg-hgcj" width="150">Location After</td>
        <td class="tg-hgcj" width="130">Date</td>
        <td class="tg-hgcj">User</td>
      </tr>
          <?php foreach ($data as $val) { ?>
              <tr>
                <td><?=@$val['CONT_BEFORE']?></td>
                <td><?=@$val['CONT_SIZE']?></td>
                <td><?=@$val['CONT_TYPE']?></td>
                <td><?=@$val['CONT_STATUS_BEFORE']?></td>
                <td><?=@$val['LOCATION_BEFORE']?></td>
                <td><?=@$val['LOCATION_AFTER']?></td>
                <td><?=@$val['DATE_AFTER']?></td>
                <td><?=@$val['USER']?></td>
              </tr>
          <?php } ?>
        </table>
  </body>
</html>
