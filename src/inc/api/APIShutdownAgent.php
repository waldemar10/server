<?php

class APIShutdownAgent extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetHealthCheck::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SHUTDOWN , "Invalid shutdown query!");
    }
    $this->checkToken(PActions::SHUTDOWN  , $QUERY);
  
    $directory = dirname(dirname(dirname(__DIR__))) . '/src/';
    $filename = 'shutdown.txt';
    $filepath = $directory . $filename;

    if (file_exists($filepath)) {
      $file = fopen($filepath, 'r');

      if ($file) {
        $fileContent = [];

        while (($line = fgets($file)) !== false) {
            $fileContent[] = trim($line);
        }

        fclose($file);

        $isValidAgent = $this->validateShutdownCommand($fileContent, $this->agent);
        
      } else {
        $this->sendErrorResponse(PActions::GET_FILE, "Unable to open the file '$filepath' for reading.");
      }
    } else {
      $this->sendErrorResponse(PActions::GET_FILE, "File '$filepath' does not exist.");
    }
    
    if($isValidAgent){
      $this->updateAgent(PActions::SHUTDOWN );
      $this->sendResponse([PResponseGetShutdown::RESPONSE => PResponseGetShutdown::SHUTDOWN]);
    }
  }

  private function validateShutdownCommand($data, $agent) {
    $timestamp = (int)$data[0];
    $currentTime = time();
    $agentId = $agent->getId();

    if (($currentTime - $timestamp) <= 10000) {
      for ($i = 1; $i < count($data); $i++) {
        if ($data[$i] == $agentId) {
          return true;
        }
      }
    }
    
    return false;
  }
}