<?php
namespace Drupal\package_views_hit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\Query\Sql\Query;


/**
 * An example controller.
 */
class PackageViewsHitController extends ControllerBase {
  /**
   * Returns a render-able array for a test page.
   */
  public function package_views_hit_api() {
	$get_package_views_hit_url = \Drupal::request()->getRequestUri();
	$explode_get_package_views_hit_url = explode('/',$get_package_views_hit_url);
	$get_nid_from_url = $explode_get_package_views_hit_url[4];
	$get_package_name_url = $explode_get_package_views_hit_url[5];
	$database = \Drupal::database();
	$result_package = $database->select('views_count', 'c')
					   ->fields('c', ['package_id', 'package_name', 'count'])
					   ->condition('c.package_id', $get_nid_from_url, '=')
					   ->execute()
					   ->fetchAll();	
	if(empty($result_package)){
		$count = '1';
		$database->insert('views_count')
							->fields([
								'package_id',
								'package_name',
								'count',
							])
							->values(array(
								$get_nid_from_url,
								$get_package_name_url,
								$count,
							))
							->execute();
		echo 'Record Inserted';

	}else{
		foreach($result_package as $result_package_array){
			$package_id_inner = $result_package_array->package_id;
			$package_count_inner = $result_package_array->count;
			$count_increase = $package_count_inner + 1;
			$database->update('views_count')
								->condition('package_id' , $package_id_inner)
								->fields([
									'count' => $count_increase,
								])
								->execute();
		    echo 'Record Updated';
		}

	}

	exit;
  }
}
