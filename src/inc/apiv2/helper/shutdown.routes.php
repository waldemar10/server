<?php

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class RunCommandHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/shutdown";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array
  {
    return [];
  }

  public function getFormFields(): array {
    return  [ 
      'timestamp' => ['type' => 'int'],
      'agentIds' => ['type' => 'string']
    ];
  }

  private function createFile($timestamp, $agentIds){
    $directory = dirname(dirname(dirname(dirname(__DIR__)))) . '/src/';
    $filename = 'shutdown.txt';
    $mode = 'w';
    $filepath = $directory . $filename;

    $file = fopen($filepath, $mode);

    if ($file) {
      fwrite($file, $timestamp . PHP_EOL);

      $agentIdsArray = explode(',', $agentIds);

      foreach ($agentIdsArray as $agentId) {
        fwrite($file, $agentId . PHP_EOL);
      }

      return;
    } else {
      return "Unable to read the shutdown file";
    }
  }

  public function actionPost(array $data): array|null {
    $timestamp = $data['timestamp'];
    $agentIds = $data['agentIds'];

    $output = $this->createFile($timestamp, $agentIds);
   
    return ['error' => $output];
  }  
}

RunCommandHelperAPI::register($app);
