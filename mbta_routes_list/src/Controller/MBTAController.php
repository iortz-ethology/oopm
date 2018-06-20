<?php

namespace Drupal\mbta_routes_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
 * Class ReachInfluenceController.
 */
class MBTAController extends ControllerBase {

  public function mainPage() {
    $routes = static::getRoutes();
    $routes_sorted = static::sortRoutes($routes->data);
    $route_type_tables = [];
    foreach ($routes_sorted as $name =>$route_type) {
      $table_name = str_replace('_', ' ', $name);
      $table_name = ucwords($table_name);

      $route_type_table = [
        '#type' => 'table',
        '#header' => [
          ['data' => ['#markup' => '<strong>' . $table_name . '</strong>']],
        ],
      ];
      foreach ($route_type as $route) {
        $route_name = (!empty($route->attributes->long_name)) ? $route->attributes->long_name: $route->id;
        $route_url = Url::fromUserInput($route->links->self);

        $attr = [
          'style' => 'background-color:#' . $route->attributes->color,
          'class' => array('color-' . $route->attributes->color),
        ];
        $route_url->setOptions($attr);
        $link = Link::fromTextAndUrl($route_name, $route_url);
        $route_type_table['#rows'][] = [
            $link,
            '#attributes' => $attr,
        ];
      }

      $route_type_tables[] =$route_type_table;
    }
    return $route_type_tables;
  }

  public function routeInfoPage($route_id = 0) {
    $path = \Drupal::service('path.current')->getPath();
    $args = explode('/', $path);
    $route_info = static::getRoutesInfo($args[2]);
    return ['#markup' => $route_info];
  }

  public static function getRoutes() {
    $mbta_url = 'https://api-v3.mbta.com/routes';
    $options = [
    ];
    $client = \Drupal::httpClient();
    $response = $client->get($mbta_url);
    $data = $response->getBody();
    $data_content= $data->getContents();

    return json_decode($data_content);
  }

  public static function getRoutesInfo($route_id) {
    $mbta_url = 'https://api-v3.mbta.com/routes/' . $route_id;
    $options = [
    ];
    $client = \Drupal::httpClient();
    $response = $client->get($mbta_url);
    $data = $response->getBody();
    $data_content= $data->getContents();

    return ($data_content);
  }

  public static function sortRoutes(array $routes) {
    $sorted = [];
    foreach ($routes as $key => $route) {
      $type_name = $route->attributes->description;
      $type_machine_name = str_replace(' ', '_', $type_name);
      $type_machine_name = strtolower($type_machine_name);
      $sorted[$type_machine_name][] = $route;
    }

    return $sorted;
  }
}
