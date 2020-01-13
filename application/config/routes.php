<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['discharge']            = 'bm_convensional/get_discharge_plan_hdr';

$route['default_controller']   = 'welcome';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;


//POST
$route['api/v1/basic']        = 'api/home/basic';
$route['api/v1/bearer']       = 'api/home/bearer';
$route['api/v1/apikey']       = 'api/home/api';
$route['api/v1/getreceiving'] = 'api/home/getReceiving';
$route['api/v1/getdelivery']  = 'api/home/getDelivery';
$route['api/v1/stuffing']     = 'api/home/getStuffing';
$route['api/v1/stripping']    = 'api/home/getStripping';

//GET
$route['api/v1/get/(:any)']   = 'api/home/index/$1';

//DELETE
$route['api/v1/delete']       = 'api/home/del';
