<?php

if (isset($_GET['server'])) {

	echo file_get_contents(__DIR__.'/server_path.txt');

}

if (isset($_POST['server'])) {

	echo file_put_contents(__DIR__.'/server_path.txt', json_encode($_POST) );

}


if (isset($_GET['client'])) {

	echo file_get_contents(__DIR__.'/client_path.txt');

}

if (isset($_POST['client'])) {

	echo file_put_contents(__DIR__.'/client_path.txt', json_encode($_POST) );

}