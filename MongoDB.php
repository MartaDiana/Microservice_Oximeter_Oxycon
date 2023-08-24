<?php

require 'vendor/autoload.php';


class MongoDB 
{
    protected $db;
    protected $name;
    protected $client;

    public function __construct(string $collection) {
      $this->db = new MongoDB\Client;
      $this->name = $collection;
    }

    // getcollection
    public function updateSensor(string $kodealat, array $dataFlow = []) {
        try {
          $koleksi = $this->name;
          $this->db->Oxycon->$koleksi->updateOne(
            ['kodealat' => $kodealat],
            ['$set' => 
            $dataFlow
            ]
          ); 
        return "OK UPDATE \n";
        }  catch (Exception $exception) {
          return $exception->getMessage();
      }
    }

    public function docHistory(string $kodealat, array $data) {
      try {
        $koleksi = $this->name;
        $this->db->Oxycon->$koleksi->updateOne(
          ['kodealat' => $kodealat],
          ['$push' => 
          ['DataHistoryOximeter' => 
            $data
          ] 
          ]
        ); 
      return "OK";
      }  catch (Exception $exception) {
        return $exception->getMessage();
    }
  }
}
?>