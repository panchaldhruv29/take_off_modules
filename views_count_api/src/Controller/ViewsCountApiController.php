<?php
namespace Drupal\views_count_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\Query\Sql\Query;

/**
 * An example controller.
 */
class ViewsCountApiController extends ControllerBase {
  /**
   * Returns a render-able array for a test page.
   */
  public function views_count_api() {
	$get_views_count_url = \Drupal::request()->getRequestUri();
	$explode_views_count_url = explode('/',$get_views_count_url);
	$sort_type = $explode_views_count_url[4];
    $offset_limit = explode(',',$explode_views_count_url[5]);
    $offset = $offset_limit[0];
    $limit = $offset_limit[1];
	$database = \Drupal::database();
	if($sort_type == 'count_desc'){

			$result = $database->select('views_count', 'c')
							   ->fields('c', ['package_id', 'package_name', 'count'])
							   ->orderBy('c.count', 'DESC')
							   ->range($offset,$limit)
							   ->execute()
							   ->fetchAll();
	}elseif($sort_type == 'count_asc'){

			$result = $database->select('views_count', 'c')
							   ->fields('c', ['package_id', 'package_name', 'count'])
							   ->orderBy('c.count', 'ASC')
							   ->range($offset,$limit)
							   ->execute()
							   ->fetchAll();
	}
	print_r(json_encode($result));
	exit;
  }
}
