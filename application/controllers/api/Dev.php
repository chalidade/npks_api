<?php
// Home.php
public function getStuffing_post() {
  $this->auth_basic();
  $repo     = $this->reponpks;
  $header   = $this->post("header");
  $detail   = $this->post("detail");

  $query    = $repo->where('STUFF_ID', $header["STUFF_ID"])->where("STUFF_ID", $header["STUFF_ID"])->get('TX_REQ_STUFF_HDR');
  $cek      = $query->result();

  $head     = $repo->set($header)->get_compiled_insert('TX_REQ_STUFF_HDR');
  $this->reponpks->query($head);

  foreach ($detail as $detail) {
    $det    = $repo->set($detail)->get_compiled_insert('TX_REQ_STUFF_DTL');
    $this->reponpks->query($det);
  }
}
 ?>
