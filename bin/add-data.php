#!/usr/bin/php
<?php
   if(empty($argv[1]) OR !is_file($argv[1])){
      echo "\nUsage: php add-data.php mydata.ttl\n";
      exit;
   }
   if(!is_readable($argv[1])){
      echo "\nYou need to change the permissions on {$argv[1]} to make it readable\n";
   }
   require 'helpers.php'; 
   set_time_limit(0);
   define('RAFFLES_ROOT', __DIR__.'/vendor/kwijibo/raffles/');
   require RAFFLES_ROOT . '/lib/rafflesstore.php';
   $Store = new \Raffles\RafflesStore(RAFFLES_DATA_DIR);
   $Store->loadDataFile($argv[1]);
?>
