<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Nusoap_library
{
	function __construct()
	{
		require_once('nusoap'.EXT);
		require_once('xml2array'.EXT);
	}
}
?>