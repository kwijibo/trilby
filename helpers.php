<?php

define('CONFIG_JSON_FILE', 'config.json');
define('RAFFLES_DATA_DIR', 'raffles-data');

class Config {
  var $name='';
  var $license = '';
  var $prefixes = array();
}

function getConfig($redirectIfNotExists=true){
  if(is_readable(CONFIG_JSON_FILE)){
    return json_decode(file_get_contents(CONFIG_JSON_FILE));
  } else if($redirectIfNotExists) {
    redirect('_setup');
  } else {
    return new Config();
  }
}

function redirect($page){
      $host  = $_SERVER['HTTP_HOST'];
      $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
      header("Location: http://$host$uri/$page");
      exit;
}

function getPrefixes(&$Config,&$Store){
    if($prefixes = $Config->prefixes){
      foreach($prefixes as $prefix => $ns){
        $Store->addPrefix($prefix, $ns);
      }
    }
    return $Store->prefixes;
}

  function get_etag(){
    return time();
  }

  function get_if_none_match(){
    $headers = apache_request_headers();
    if(isset($headers["If-None-Match"])){
     return $headers["If-None-Match"]; 
    }
  }

  function plural($word){
    if($word=='Person'){
      return 'People';
    } else {
      return $word.'s';
    }
  }
  function curie($uri){
    global $namespaces;
    if(preg_match('/^(.+[\/#])([^\/#]+)$/', $uri, $m)){
    $ns = $m[1];
    $local = $m[2];
    return isset($namespaces[$ns])? $namespaces[$ns].':'.$local : $uri;
    } else {
      return $uri;
    }
  }

  function curie_to_uri($curie){
    global $prefixes;
    list($prefix, $local) = explode(":", $curie);
    return $prefixes[$prefix].$local;
  }

  function local($uri){
    global $namespaces;
    if(preg_match('/([^:\/#]+)$/', $uri, $m)){
      $local = $m[1];
      $local = str_replace('_', ' ', urldecode($local));
      return ucwords(preg_replace('/([a-z])([A-Z])/','$1 $2', $local));
    }  else {
      return $uri;
    }
  }

  function pathescape($o){
    if($o['type']=='uri'){
      $v = curie($o['value']);
    } else {
      $v = $o['value'];
    }
    return urlencode($v);
  }


function label($props, $uri='Something'){
  global $prefixes;
  extract($prefixes);
  $labelPs = array($dct.'title', $foaf.'name', $rdfs.'label');
  foreach($labelPs as $p){
    if(isset($props[$p])) return $props[$p][0]['value'];
  } 
  $type = 'Thing';
  if(isset($props[$rdf.'type'])){
    $type = $props[$rdf.'type'][0]['value'];
  }
  return "A ".curie($type);
}

function getQuery(){
  $get = $_GET;
  unset($get['_page']);
  unset($get['_related']);
  unset($get['_uri']);
  unset($get['_dataset']);
  unset($get['_near']);
  unset($get['_reload']);
  unset($get['_search']);
  $q=array();
  foreach($get as $k => $v) $q[]="{$k}=".($v);
  return implode('&',$q);
}

class AcceptHeader {

    function getAcceptHeader(){
        if(isset($_SERVER['HTTP_ACCEPT'])) return trim($_SERVER['HTTP_ACCEPT']);
        else return null;
    }
    
    function getAcceptTypes($defaultTypes = array()){
        $header = self::getAcceptHeader();
        $mimes = explode(',',$header);
    	$accept_mimetypes = array();

        foreach($mimes as $mime){
        $mime = trim($mime);
    		$parts = explode(';q=', $mime);
    		if(count($parts)>1){
    			$accept_mimetypes[$parts[0]]=strval($parts[1]);
    		}
    		else {
    			$accept_mimetypes[$mime]=1;
    		}
    	}
  /* prefer html, then xhtml, then anything in the default array, to mimetypes with the same value. this is because WebKit browsers (Chrome, Safari, Android) currently prefer xml and even image/png to html */
  $defaultTypes = array_merge(array('text/html', 'application/xhtml+xml'), $defaultTypes);
	foreach($defaultTypes as $defaultType){
		if(isset($accept_mimetypes[$defaultType])){	
			$count_values = array_count_values($accept_mimetypes);
			$defaultVal = $accept_mimetypes[$defaultType];
			if($count_values[$defaultVal] > 1){
				$accept_mimetypes[$defaultType]=strval(0.001+$accept_mimetypes[$defaultType]);
			}
		}
  }
    	arsort($accept_mimetypes);
    	return array_keys($accept_mimetypes);
    }
    
    function hasAcceptTypes(){
        $acceptheader = $this->getAcceptHeader();
        if(empty($acceptheader)){
           return false; 
        } else {
            return true;
        }
    }

}
