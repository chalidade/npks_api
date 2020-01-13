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
        <td style="width:80%; text-align:center;"><div class="header">Stay Period Container</div></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td>:</td>
        <td><?php echo $branch ?></td>
      </tr>      
    </table>
    <table class="tg" style="width:200mm">
  <tr>
    <td class="tg-hgcj">Container Number</td>
    <td class="tg-hgcj">Owner</td>
    <td class="tg-hgcj">Ship</td>
    <td class="tg-hgcj">Container Size</td>
    <td class="tg-hgcj">Container Type</td>
    <td class="tg-hgcj" width="110">Status</td>
    <td class="tg-hgcj" width="110">Location</td>
    <td class="tg-hgcj" width="110">Activity</td>
    <td class="tg-hgcj" width="110">Start Stack</td>
    <td class="tg-hgcj" width="110">Stacking Duration</td>
    

  </tr>
      <?php foreach ($data as $val) { ?>
          <tr>
            <td><?=$val['NO_CONT']?></td>
            <td><?=$val['PEMILIK']?></td>
            <td><?=$val['KAPAL']?></td>
            <td><?=$val['CONTAINER_SIZE']?></td>
            <td><?=$val['CONTAINER_TYPE']?></td>
            <td><?=$val['STATUS']?></td>
            <td><?=$val['LOKASI']?></td>
            <td><?=$val['KEGIATAN']?></td>
            <td><?=$val['START_STACK']?></td>
            <td><?=$val['DURASI_STACKING']?></td>
            
          </tr>
      <?php } ?>
    </table>
  </body>
</html>
