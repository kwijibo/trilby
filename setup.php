<?php
ini_set('upload_max_filesize', '30M');
set_time_limit(0);
define('RAFFLES_ROOT', __DIR__.'/../Raffles/');
require '../Raffles/lib/rafflesstore.php';
include 'vendor/autoload.php';

require 'helpers.php';

function savePostToConfig(){
  $config = array(
    'name' => $_POST['name'],
    'license' => $_POST['license'],
    'prefixes' => array(),
    'username' => $_POST['Username'],
  );
  $Password = trim($_POST['Password']);
  if(!empty($Password)){
    $config['password'] = md5($_POST['Password']);
  } else {
    $existingConfig = getConfig(0);
    $config['password'] = $existingConfig->password;
    
  }

   if(isset($_POST['prefixes'])){
    foreach($_POST['prefixes'] as $vocab){
      $config['prefixes'][$vocab['prefix']] = $vocab['namespace'];
    }
  }
  if(isset($_POST['add-vocab-field'])){
    $config['prefixes']['prefix']='';
  }

  return file_put_contents(CONFIG_JSON_FILE, json_encode($config));
}

function uploadData(){
  if( ( isset($_POST['upload']) ) && isset($_FILES['data-file'])){
    if($_FILES['data-file']['error']){
    //handle error
      switch($_FILES['data-file']['error']){
        case 1: 
        case 2:
          $errorMessage = 'The file uploaded is bigger than the upload_max_filesize directive in your php.ini file. Try making the value larger.';
          break;
        case 3:
          $errorMessage='The uploaded file was only partially uploaded. ';
          break;
        case 4:
          $errorMessage='No file was uploaded';
          break;
        case 5:
        case 6:
          $errorMessage='There is no temporary folder to write the file to.';
          break;
        case 7:
          $errorMessage='Failed to write file to disk.';
          break;
        case 8:
          $errorMessage='A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.';
          break;
      }
      echo $errorMessage;

    } else {
      $Store = new \Raffles\RafflesStore(RAFFLES_DATA_DIR);
      $Store->reset();
      $Store->indexPredicates = false;
      $extension = false;
      if(strpos($_FILES['data-file']['name'],'.')){
        $extension = array_pop(explode('.', $_FILES['data-file']['name']));
      }
      $Store->loadDataFile($_FILES['data-file']['tmp_name'], $extension);
      
    }
  }
}

$Config = getConfig(0);
/* authenticate*/
if(
  !empty($Config->password) && !empty($Config->username)
  && (
  (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
  ||
  (
    $Config->username !=$_SERVER['PHP_AUTH_USER'] ||
    $Config->password !=md5($_SERVER['PHP_AUTH_PW'])
  )
  )
){
      header('WWW-Authenticate: Basic realm="My Realm"');
      header('HTTP/1.0 401 Unauthorized');
      echo 'You need to log in to access the setup page. If you have forgotton your password, delete "config.json" from your Trilby installation folder.';
      exit;
}
if($_SERVER['REQUEST_METHOD']=='POST'){
  savePostToConfig();
  uploadData();
  redirect('_setup');
} else {
   $maxfileSize = ini_get('upload_max_filesize');
   $Config = getConfig(false);
   $innerTemplate='setup.html';
   $showMap = false;
   $title = 'Setup';
   $site_name = 'Trilby';
   $licenses = getLicenses();
   require 'templates/outer.html';
}

?>