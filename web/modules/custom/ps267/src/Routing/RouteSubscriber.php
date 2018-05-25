<?php

namespace Drupal\ps267\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
//    if ($route = $collection->get('system.admin_content')) {
//      $info = [
//        'defaults' => $route->getDefaults(),
//        'options' =>  $route->getOptions(),
//        'reqs' => $route->getRequirements(),
//        'condition' => $route->getCondition(),
//        'schemes' => $route->getSchemes(),
//        'path' => $route->getPath(),
//      ];
//      var_dump($info);
//      $route->setPath('/admin/content/advanced');
//    }
  }
}

