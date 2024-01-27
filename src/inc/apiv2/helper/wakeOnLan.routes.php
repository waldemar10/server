<?php
use DBA\Factory;
use DBA\Agent;
use DBA\AgentStat;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class WakeOnLanHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/wol";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array
  {
    return [Agent::PERM_WOL, AgentStat::PERM_WOL];
  }

  public function getFormFields(): array {
    return  [ 
      'agentIds' => ['type' => 'string']
    ];
  }

  private function createPackage($macAddressHexadecimal, $broadcastAddress)
  {
    $macAddressHexadecimal = str_replace([':','-'], '', $macAddressHexadecimal);

    // check if $macAddress is a valid mac address
    if (!ctype_xdigit($macAddressHexadecimal)) {
      return 'Mac address invalid, only 0-9 and a-f are allowed';
    }

    $macAddressBinary = pack('H12', $macAddressHexadecimal);

    $magicPacket = str_repeat(chr(0xff), 6).str_repeat($macAddressBinary, 16);

    if (!$fp = fsockopen('udp://' . $broadcastAddress, 7, $errno, $errstr, 2)) {
      return "Cannot open UDP socket: {$errstr}, $errno";
    }
    fputs($fp, $magicPacket);
    fclose($fp);

    return;
  }

  private function compareAgentsByIP($a, $b) {
    return ip2long($a->ip) - ip2long($b->ip);
  }

  public function actionPost(array $data): array|null {
    $agentIds = $data['agentIds'];
    $agentIdsArray = explode(",", $agentIds);
    $agentIdsArray = array_map('intval', $agentIdsArray);
    $selectedAgents = [];

    foreach ($agentIdsArray as $id) {
      $selectedAgents[] = Factory::getAgentFactory()->get($id);
    }

    //Sorts agents by ip
    usort($selectedAgents, array($this, 'compareAgentsByIP'));
   
    foreach ($selectedAgents as $agent){
      $error = $this->createPackage($agent->getMac(), $agent->getLastIp());
      usleep(100000); // 0.1 seconds delay
      if($error)
        break;
    }
   
    return ['error' => $error];
  }  
}

WakeOnLanHelperAPI::register($app);
