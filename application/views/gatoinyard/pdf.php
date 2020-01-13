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
    .tz {margin-left:-37px;}
    .tz td{font-family:Arial, sans-serif;font-size:11px;padding:0 0 0 5px;font-weight:bold;}
    .header {font-family:Arial, sans-serif; font-size: 15px; font-weight:bold;}
    </style>
  </head>
  <body>
    <table style="margin-left:-34;">
      <tr>
        <td><img src="<?=DIR?>/../assets/logo.png" width="130" height="60"></td>
        <td style="width:80%; text-align:center;"><div class="header">REPORT REPO TPK</div></td>
      </tr>
    </table>
    <table class="tg" style="width:200mm">
  <tr>
    <td class="tg-hgcj">Request No<br></td>
    <td class="tg-hgcj">Container No</td>
    <td class="tg-hgcj">Status</td>
    <td class="tg-hgcj">Gate Out</td>
  </tr>
      <?php foreach ($data as $val) { ?>
          <tr>
            <td><?=$val->NO_REQUEST?></td>
            <td><?=$val->CONT_NO?></td>
            <td><?=$val->STATUS?></td>
            <td><?=$val->TGL_UPDATE?></td>
          </tr>
      <?php } ?>
    </table>
  </body>
</html>
