<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['discharge']              = 'bm_convensional/get_discharge_plan_hdr';

$route['default_controller']     = 'welcome';
$route['404_override']           = '';
$route['translate_uri_dashes']   = FALSE;


//POST
$route['api/v1/basic']           = 'api/home/basic';
$route['api/v1/bearer']          = 'api/home/bearer';
$route['api/v1/apikey']          = 'api/home/api';

// Univeral
$route['api/v1/repoPost']        = 'api/store/repo';
$route['api/v1/repoGet']         = 'api/view/repo';

//GET
$route['api/v1/get/(:any)']      = 'api/home/index/$1';

//DELETE
$route['api/v1/delete']          = 'api/home/del';
