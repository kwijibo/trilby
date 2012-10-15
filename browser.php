<?php
require '../Raffles/lib/rafflesstore.php';
include 'vendor/autoload.php';
require 'helpers.php';


$Config = getConfig();
$Store = new \Raffles\RafflesStore(RAFFLES_DATA_DIR);

$site_name = $Config->name;
$title='';
$showMap=false;
$types = $Store->getTypes();
$prefixes = getPrefixes($Config,$Store);
$namespaces = array_flip($prefixes);
$page = 1;

if($query = getQuery()){
  $offset = (isset($_GET['_page']) && $page = $_GET['_page'])? ($_GET['_page']-1)*10 : 0;
  $showMap= (strpos($query,'_near')!==false || isset($_GET['_near']))? true : false ;
  $data = $Store->query($query, 10, $offset);
  $facets = $Store->getFacetsForLastQuery();
}


$acceptTypes = AcceptHeader::getAcceptTypes();
foreach($acceptTypes as $mimetype){
  switch($mimetype){
  
    case 'text/html':
    case '*/*':
    $innerTemplate = 'browser.html';
    require 'templates/outer.html';
    exit;

    case 'application/json';
     echo json_encode($data);
    exit;
  }
}


?>
