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
$licenses = getLicenses();
$page = 1;
$pageSize = !empty($_GET['_pageSize'])? $_GET['_pageSize'] : 10;
$offset = (isset($_GET['_page']) && $page = $_GET['_page'])? ($_GET['_page']-1)*$pageSize : 0;


if($query = getQuery()){
  $data = $Store->query($query, $pageSize, $offset);
} else if(!empty($_GET['_uri'])){
  $data = $Store->get($_GET['_uri']);
} else if(!empty($_GET['_related'])){
  $data = $Store->get(null,null,$_GET['_related'], $pageSize, $offset);
} else if(!empty($_GET['_search'])){
  $data = $Store->search($_GET['_search'], false, $pageSize, $offset);
}

$facets = $Store->getFacetsForLastQuery();
$resultCount = $Store->getResultsCountForLastQuery();
$showMap= (strpos($query,'_near')!==false || isset($_GET['_near']))? true : false ;


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
