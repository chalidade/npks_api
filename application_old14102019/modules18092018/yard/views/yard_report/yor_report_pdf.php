<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:12px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:12px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg .tg-hyn2{font-weight:bold;font-size:12px;border-color:inherit;text-align:center}
    .tg .tg-s6z2{text-align:center}
    .tg .tg-d3l3{font-weight:bold;font-size:12px;text-align:center}
    .tg .tg-hgcj{font-weight:bold;text-align:center}
    .tg .tg-xocx{font-size:12px;border-color:inherit;text-align:center}
    .tg .tg-gozu{font-size:12px;text-align:center}
    .tg .tg-h4ka{font-size:10px;background-color:#f8ff00;border-color:inherit;text-align:center}
    .tz {margin-left:-8px;}
    .tz td{font-family:Arial, sans-serif;font-size:10px;padding:0 0 0 5px;font-weight:bold;}
    .tz2 td{font-family:Arial, sans-serif;font-size:14px;padding:0 0 0 5px;font-weight:bold;}
    </style>
  </head>
  <body>
    <table >
      <tr>
        <td><img src="<?=DIR?>/assets/logo.png" width="130" height="60"></td>
        <td style="width:77%; text-align:center;"><b>LAPORAN POSISI PETIKEMAS<br><?=$date2?></b></td>
      </tr>
    </table>
    <table class="tz">
      <tr>
        <td>Cabang</td>
        <td colspan="7">: <?=$branch?></td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td colspan="7">: <?=$date1?></td>
      </tr>
    </table>
    <table class="tg">
      <tr>
          <th class="tg-hyn2" rowspan="4">TANGGAL<br></th>
          <th class="tg-hyn2" colspan="6">MASUK</th>
          <th class="tg-hyn2" colspan="10">KELUAR</th>
          <th class="tg-hyn2" colspan="2" rowspan="2">POSISI<br>CONTAINER</th>
          <th class="tg-hyn2" rowspan="3">Y.O.R<br>%</th>
          <th class="tg-hyn2" rowspan="3">YTP<br></th>
        </tr>
        <tr>
          <td class="tg-hyn2" colspan="4">RECEIVING<br></td>
          <td class="tg-hyn2" colspan="2">JUMLAH</td>
          <td class="tg-hyn2" colspan="4">TPK</td>
          <td class="tg-hyn2" colspan="4">DELIVERY</td>
          <td class="tg-hyn2" colspan="2">JUMLAH</td>
        </tr>
        <tr>
          <td class="tg-hyn2" colspan="2">FL</td>
          <td class="tg-hyn2" colspan="2">MT</td>
          <td class="tg-hyn2" rowspan="2">BOX</td>
          <td class="tg-hyn2" rowspan="2">TEUS</td>
          <td class="tg-hyn2" colspan="2">FL</td>
          <td class="tg-hyn2" colspan="2">MT</td>
          <td class="tg-hyn2" colspan="2">FL</td>
          <td class="tg-hyn2" colspan="2">MT<br></td>
          <td class="tg-hyn2" rowspan="2">BOX</td>
          <td class="tg-hyn2" rowspan="2">TEUS</td>
          <td class="tg-hyn2" rowspan="2">BOX</td>
          <td class="tg-hyn2" rowspan="2">TEUS</td>
        </tr>
        <tr>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"<br></td>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"</td>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"</td>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"</td>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"</td>
          <td class="tg-hyn2">20"</td>
          <td class="tg-hyn2">40"</td>
          <td class="tg-hgcj"></td>
          <td class="tg-hgcj"></td>
        </tr>
        <tr>
          <td class="tg-h4ka">O2<br></td>
          <td class="tg-h4ka">O3</td>
          <td class="tg-h4ka">O4</td>
          <td class="tg-h4ka">O5</td>
          <td class="tg-h4ka">O6</td>
          <td class="tg-h4ka">O9</td>
          <td class="tg-h4ka">O10</td>
          <td class="tg-h4ka">O11</td>
          <td class="tg-h4ka">O12</td>
          <td class="tg-h4ka">O13</td>
          <td class="tg-h4ka">O14</td>
          <td class="tg-h4ka">O15</td>
          <td class="tg-h4ka">O16</td>
          <td class="tg-h4ka">O17</td>
          <td class="tg-h4ka">O18</td>
          <td class="tg-h4ka">O19</td>
          <td class="tg-h4ka">O20</td>
          <td class="tg-h4ka">O21</td>
          <td class="tg-h4ka">O22</td>
          <td class="tg-h4ka">O23</td>
          <td class="tg-h4ka">O24</td>
        </tr>
      <?php foreach ($data['DATA'] as $val) { ?>
      <tr>
        <td class="tg-xocx"><?=$val['YOR_DATE2']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_FCL20']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_FCL40']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_MTY20']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_MTY40']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_BOX']?></td>
        <td class="tg-xocx"><?=$val['YOR_REC_TEUS']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_TPK_FCL20']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_TPK_FCL40']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_TPK_MTY20']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_TPK_MTY40']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_DEPO_FCL20']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_DEPO_FCL40']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_DEPO_MTY20']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_DEPO_MTY40']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_BOX']?></td>
        <td class="tg-xocx"><?=$val['YOR_DEL_TEUS']?></td>
        <td class="tg-xocx"><?=$val['BOX']?></td>
        <td class="tg-xocx"><?=$val['TEUS']?></td>
        <td class="tg-xocx"><?=$val['YOR']?></td>
        <td class="tg-xocx"><?=$val['YTP']?></td>
      </tr>
        <?php } ?>
        <tr>
          <td class="tg-hyn2">Jumlah</td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_rec_fl_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_rec_fl_40']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_rec_mty_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_rec_mty_40']?></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_box_rec']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_teus_rec']?></b></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_tpk_fl_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_tpk_fl_40']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_tpk_mty_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_tpk_mty_40']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_depo_fl_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_depo_fl_40']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_depo_mty_20']?></td>
          <td class="tg-xocx"><?=$data['TOTAL']['tot_del_depo_mty_40']?></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_box_del']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_teus_del']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_box']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_teus']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_yor']?></b></td>
          <td class="tg-xocx"><b><?=$data['TOTAL']['tot_ytp']?></b></td>
        </tr>
        <tr>
          <td class="tg-hyn2">Average</td>
          <td class="tg-xocx"><?=$data['AVG']['avg_rec_fl_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_rec_fl_40']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_rec_mty_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_rec_mty_40']?></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_box_rec']?><b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_teus_rec']?><b></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_tpk_fl_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_tpk_fl_40']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_tpk_mty_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_tpk_mty_40']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_depo_fl_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_depo_fl_40']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_depo_mty_20']?></td>
          <td class="tg-xocx"><?=$data['AVG']['avg_del_depo_mty_40']?></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_box_del']?></b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_teus_del']?></b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_box']?></b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_teus']?></b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_yor']?></b></td>
          <td class="tg-xocx"><b><?=$data['AVG']['avg_ytp']?></b></td>
        </tr>
    </table>
  </body>
</html>
