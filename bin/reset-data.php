<?php
   require 'helpers.php'; 
   define('RAFFLES_ROOT', __DIR__.'/vendor/kwijibo/raffles/');
   require RAFFLES_ROOT . '/lib/rafflesstore.php';
   $Store = new \Raffles\RafflesStore(RAFFLES_DATA_DIR);
   $Store->reset();
?>
