<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('vendor/autoload.php');

class M_pdf {

    public $pdf;
    public $export;

    public function __construct()
    {
        $this->pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80,110],
            'orientation' => 'P'
        ]);

        $this->export = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [210,297],
            'orientation' => 'P'
        ]);
    }
}
