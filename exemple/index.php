<?php
require "../vendor/autoload.php";

use Colis\StreamFactory;
use Colis\RequestFactory;
use Colis\ResponseFactory;
use Colis\ServerRequestFactory;

/* ===== Stream ===== */

$stfac = new StreamFactory();
$tempst = $stfac->createStream();
$tempst->write('hello world');
$stfac->copyTo($tempst, $stfac->createOutputStream());
//*/

/* ===== Request ===== */

$reqfac = new RequestFactory();
$request = $reqfac->createRequest('GET', 'http://api.geonames.org/astergdemJSON?formatted=true&lat=50.01&lng=10.2&username=demo&style=full'); //So long
//*/

/* ===== Response ===== */

$resfac = new ResponseFactory();
$response = $resfac->createResponse()
->withStatus(201, 'Banana')
->withHeader('Content-Type', 'text/plain')
->withBody($tempst);
//*/

/* ===== ServerRequest ===== */

$sreqfac = new ServerRequestFactory();
$srequest = $sreqfac->createServerRequest($_SERVER);
var_dump($srequest);
//*/
