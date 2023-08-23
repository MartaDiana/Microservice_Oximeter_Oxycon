<?php

require('lib/phpMQTT.php');
require_once 'MongoDB.php';
$mg = new MongoDB('patientdocument');

// mqtt requirement
$server = '192.168.41.187';        // change if necessary MONGODB SERVER
$port      = 1883;                      // change if necessary
$username  = '';                    // set your username
$password  = '';                    // set your password
$client_id = 'phpMQTT-subscriber'; // make sure this is unique for connecting to sever - you could use uniqid()
$prevtime  = 0;

$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
if(!$mqtt->connect(true, NULL, $username, $password)) {
	exit(1);
}

$mqtt->debug = true;

$topics['oxycon/oximeterdev/sensorpub'] = array('qos' => 0, 'function' => 'procMsg');
$mqtt->subscribe($topics, 0);

while($mqtt->proc()) {

}
// end of mqtt requirement

function procMsg($topic, $msg){
	    global $mg;

	    // MongoDB requirement
		$client     = new MongoDB\Client;
		$db         = $client->Oxycon;
		$collection = $db->patientdocument;
		$waktusekarang = microtime();

		$pasiendoc  = $collection->find();
		// end of MongoDB requirement

		// echo 'Msg Recieved: ' . date('r') . "\n";
		echo "Topic: {$topic}\n\n";
		echo "Data: $msg\n\n";

		//json parsing data form mqtt
		$decoded_json = json_decode($msg, true);
		$kodealat = $decoded_json["kodealat"];
		$index = $decoded_json["index"];
		$bpm = $decoded_json["bpm"];
		$spo = $decoded_json["spo"];
		// end of json parsing data from mqtt

		// echo data
		echo "Kode Alat: {$kodealat} \n";
		echo "Index: {$index} \n";
		echo "Bpm: {$bpm} \n";
		echo "Spo: {$spo} \n";
		// end of echo data

		$obj = (object)[
			'kodealat' => $kodealat,
			'ppm' => $bpm,
			'spO' => $spo,
			'time' => $waktusekarang,
			'index' => $index
		];

		$store = json_encode($obj);
		$dirmongo = 'session/';
		$dirmqtt = 'session_mqtt/';
		$fileData = $kodealat.'.txt';

		// data yang berhasil diterima oleh mqtt
		$myfile = fopen($dirmqtt.$fileData, "a") or die("Unable to open file!");
		fwrite($myfile, "$store\n");
		fclose($myfile);
		// end of data yang berhasil diterima oleh mqtt
		

		if ($bpm != NULL && $spo != NULL && $kodealat != NULL && $index != NULL ) {
		// membuat directory untuk dump data
		echo "Found File \n";
		print_r($mg->updateSensor($kodealat, [
				'Index' => $index,
				'Bpm' => $bpm,
				'SpO' => $spo
		]));
			 
		print_r($mg->docHistory($kodealat, [
				'index' => $index,
				'Bpm' => $bpm,
				'SpO' => $spo,
				'Time' => $waktusekarang 
		]));

		$myfile = fopen($dirmongo.$fileData, "a") or die("Unable to open file!");
		fwrite($myfile, "$store\n");
		fclose($myfile);
		sleep(5);
	}
}

$mqtt->close();