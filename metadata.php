<?php
namespace Trilby;

define('void_ns', 'http://rdfs.org/ns/void#');
define('rdf_ns', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('dcterms_ns', 'http://purl.org/dc/terms/');

class Graph {

  var $_index = array();
  var $uri=null;

  function __construct($uri, &$index){
    $this->_index =& $index;
    $this->uri = $uri;
  }

  function setLiteral($prop, $val){
    $this->_index[$this->uri][$prop][]=array(
      'value' => $val,
      'type' => 'literal'
    );
    return $this;
  }
  
  function setResource($prop, $val){
    $this->_index[$this->uri][$prop][]=array(
      'value' => $val,
      'type' => 'uri'
    );
    $graph = new Graph($val, $this->_index);
    return $graph;
  }

  function getIndex(){
    return $this->_index;
  }

}



function addMetadata($data, &$Config, $types, $facets, $namespaces){
  $datasetUri = 'http://' .$_SERVER['HTTP_HOST']. dirname($_SERVER['SCRIPT_NAME']).'/';
  $documentUri = 'http://' .$_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
  $documentUri = array_shift(explode('?', $documentUri));
  if($query = getQuery()){
    $documentUri.='?'.$query; 
  }
  if(isset($data[$documentUri])){
    $documentUri.='&_output=turtle';
  }
  $DocumentGraph = new Graph($documentUri, $data);
  $count = 1;
  foreach($data as $uri => $props){
    $prop = rdf_ns.'_'.$count++;
    $DocumentGraph->setResource($prop,$uri);
  }
  if($documentUri!=$datasetUri){
    $DocumentGraph->setResource(void_ns.'inDataset', $datasetUri);
  } else {
    $DocumentGraph->setResource(rdf_ns.'type', void_ns.'Dataset');
    foreach($types as $type => $entities){
      $classPartition = $DocumentGraph->setResource(void_ns.'classPartition', $datasetUri.'?rdf:type='.curie($type));
      $classPartition->setResource(void_ns.'class', $type);
      $classPartition->setLiteral(void_ns.'entities', $entities);
    }

    foreach($namespaces as   $ns => $n){
        $vocabUri = preg_replace('@#$@', '',$ns);
        $DocumentGraph->setResource(void_ns.'vocabulary', $vocabUri);
    }

  }
  if(!empty($Config->license)){
    $DocumentGraph->setResource(dcterms_ns.'license', $Config->license);
  }
  if(!empty($Config->name)){
    $DocumentGraph->setLiteral(dcterms_ns.'title', $Config->name);
  }

  return $DocumentGraph->getIndex();
}

?>
