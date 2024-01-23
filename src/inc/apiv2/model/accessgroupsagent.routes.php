<?php
use DBA\Factory;

use DBA\AccessGroupAgent;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

class AccessGroupAgentAPI extends AbstractModelAPI {
  
  public static function getBaseUri(): string {
    return "/api/v2/ui/accessgroupsagent";
  }
  public static function getDBAclass(): string {
    return AccessGroupAgent::class;
  }
  public static function getAvailableMethods(): array {
    return ['POST','GET', 'PATCH', 'DELETE'];
  }
  protected function createObject(array $data): int {

    $agentId = $data[AccessGroupAgent::AGENT_ID];
    $accessGroupId = $data[AccessGroupAgent::ACCESS_GROUP_ID];
    $object = AccessGroupUtils::addAgent($agentId, $accessGroupId);

    return $object->getId();
  }
  public function updateObject(object $object, array $data, array $processed = []): void {
    assert(False, "AccessGroupsAgent cannot be updated via API");
  }
  protected function deleteObject(object $object): void {
    Factory::getAccessGroupAgentFactory()->delete($object);
  }
}

AccessGroupAgentAPI::register($app);