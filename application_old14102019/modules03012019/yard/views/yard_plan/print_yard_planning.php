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
        <td><img src="<?=DIR?>/../assets/logo.png" width="130" height="60"></td>
        <td style="width:80%; text-align:center;"><div class="header">PLANNING YARD</div></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td>:</td>
        <td><?php echo $branch ?></td>
      </tr>
      <tr>
        <td>Yard</td>
        <td>:</td>
        <td><?php echo $YARD_NAME ?></td>
      </tr>
    </table>
    <table class="tg" style="width:200mm">
  <tr>
    <td class="tg-hgcj">Grup</td>
    <td class="tg-hgcj">Yard</td>
    <td class="tg-hgcj">Block</td>
    <td class="tg-hgcj">Slot</td>
    <td class="tg-hgcj">Row</td>
    <td class="tg-hgcj" width="110">Size</td>
    <td class="tg-hgcj" width="110">Type</td>
    <td class="tg-hgcj" width="110">Owner</td>
    <td class="tg-hgcj" width="110">Capacity</td>
  </tr>
      <?php foreach ($data as $val) { ?>
          <tr>
            <td><?=$val['GROUP_NAME']?></td>
            <td><?=$val['YARD_NAME']?></td>
            <td><?=$val['BLOCK_NAME']?></td>
            <td><?=$val['SLOT_RANGE']?></td>
            <td><?=$val['ROW_RANGE']?></td>
            <td><?=$val['CAT_DTL_CONT_SIZE']?></td>
            <td><?=$val['CAT_DTL_CONT_TYPE']?></td>
            <td><?=$val['OWNER']?></td>
            <td><?=$val['CAPACITY']?></td>
          </tr>
      <?php } ?>
    </table>
  </body>
</html>
