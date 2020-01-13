<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Captcha extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}

  public function index(){
    $this->img();
  }

  public function img(){
    $string = '';

    for ($i = 0; $i < 5; $i++) {
    	$string .= chr(rand(97, 122));
    }

    $_SESSION['random_number'] = $string;

    $image = imagecreatetruecolor(165, 50);

    $font = "assets/fonts/Roboto-Black.ttf";

    $color1 = rand(10,200);
    $color2 = rand(10,200);
    $color3 = rand(10,200);

    $color = imagecolorallocate($image, $color1, $color2, $color3);// color

    $white = imagecolorallocate($image, 255, 255, 255); // background color white
    imagefilledrectangle($image,0,0,399,99,$white);

    imagettftext ($image, 30, 0, 10, 40, $color, $font, $_SESSION['random_number']);

    header("Content-type: image/png");
    imagepng($image);
  }

}
