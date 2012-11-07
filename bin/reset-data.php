<?php
   include 'vendor/autoload.php';
   require 'helpers.php'; 
   set_time_limit(0);
   define('RAFFLES_ROOT', __DIR__.'/../vendor/kwijibo/raffles/');
   require RAFFLES_ROOT . '/lib/rafflesstore.php';
   $Store = new \Raffles\RafflesStore(RAFFLES_DATA_DIR);
   $Store->reset();
?>
