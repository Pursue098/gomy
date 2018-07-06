<?php
/**
 * Created by PhpStorm.
 * User: MAzam
 * Date: 4/30/2018
 * Time: 5:45 PM
 */

return [
    'client_id' => env('PAYPAL_CLIENT_ID','AZ9cH82Uzb0xMWdrAf1Nv-uvHCaOi14SnbrARfIdtI-mI4IXwb8Poz9xfi-8Z1YkkhO1AuV0Iab5h8_M'),
    'secret' => env('PAYPAL_SECRET','EGBineYhSFwMDM0JCEC4FpqQ9dfKcw7DVJpRXsVuRLLQfphph702sruN7n8mV9bCJRF8E_l1u1vpY03-'),
    'settings' => array(
        'mode' => env('PAYPAL_MODE','sandbox'),
        'http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => storage_path() . '/logs/paypal.log',
        'log.LogLevel' => 'ERROR'
    ),
];