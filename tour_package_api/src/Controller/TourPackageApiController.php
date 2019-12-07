<?php
namespace Drupal\tour_package_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\Query\Sql\Query;

/**
 * An example controller.
 */
class TourPackageApiController extends ControllerBase {
  /**
   * Returns a render-able array for a test page.
   */
  public function tour_package_api() {

  $current_uri = \Drupal::request()->getRequestUri();
  $explode_current_path = explode('/',$current_uri);
  $sort_path = $explode_current_path[4];
  $offset_limit = explode(',',$explode_current_path[5]);
  $offset = $offset_limit[0];
  $limit = $offset_limit[1];
  if($sort_path == 'list'){
  	$tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->sort('created', 'desc')
       ->range($offset,$limit); 
  }elseif($sort_path == 'price_high_to_low_bd'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->sort('field_package_price_bhd_adult', 'desc')
       ->range($offset,$limit);
  }elseif($sort_path == 'price_low_to_high_bd'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->sort('field_package_price_bhd_adult', 'asc')
       ->range($offset,$limit);
  }elseif($sort_path == 'price_high_to_low_sar'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->sort('field_package_price_sar_adult', 'desc')
       ->range($offset,$limit);
  }elseif($sort_path == 'price_low_to_high_sar'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->sort('field_package_price_sar_adult', 'asc')
       ->range($offset,$limit);
  }elseif($sort_path == 'offers'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->condition('field_offer', '1')
       ->range($offset,$limit);
  }elseif($sort_path == 'normal_packages'){
    $tour_package_query = \Drupal::entityQuery('node')
       ->condition('type', 'tour_package')
       ->condition('status', 1)
       ->condition('field_offer', '0')
        ->range($offset,$limit);
  }else{
    $return_value = 'No data found';
    return $return_value;
  }

    $tour_package_data = $tour_package_query->execute();
    $create_main = [];
    foreach ($tour_package_data as $key => $value) {
      $node_data = \Drupal\node\Entity\Node::load($value);
      $node_owner = $node_data->getOwnerId();
      $node_owner_load = \Drupal\user\Entity\User::load($node_owner);
      $node_owner_name = $node_owner_load->name->value;
      $node_owner_mail = $node_owner_load->mail->value;
      $node_owner_person_name = $node_owner_load->field_person_name->value;
      $node_owner_phone_number = $node_owner_load->field_phone->value;

      // $node_owner_logo_url = $node_owner_load->get('node_owner_logo')->entity->getFileUri();
      // if ($wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($node_owner_logo_url)) {
      //   $field_logo_avatar = $wrapper->getExternalUrl();
      // }
      $node_owner_agency_name = $node_owner_load->field_agency_name->value;
      $field_nid = $node_data->id();
      $field_name = $node_data->getTitle();
      $field_ar_body = $node_data->field_ar_body->value;

      $field_ar_guide = $node_data->field_ar_guide->value;

      $field_ar_title = $node_data->field_ar_title->value;
      $body = $node_data->body->value;
      $field_category = $node_data->field_category->referencedEntities();

        foreach ($field_category as $field_category_value) {
          $field_value_array_create_6 = $field_category_value->tid->value.','.$field_category_value->name->value.','.$field_category_value->field_ar_title->value;
          $array_merge_6[] = explode(',',$field_value_array_create_6);
        }

      $field_country = $node_data->field_country->referencedEntities();

        foreach ($field_country as $field_country_value) {
          $field_value_array_create_5 = $field_country_value->tid->value.','.$field_country_value->name->value.','.$field_country_value->field_ar_title->value;
          $array_merge_5['country_term'][] = explode(',',$field_value_array_create_5);
        }

      $field_hotel = $node_data->field_hotel->referencedEntities();

        foreach ($field_hotel as $field_hotel_value) {
          $field_value_array_create_4 = $field_hotel_value->tid->value.','.$field_hotel_value->name->value.','.$field_hotel_value->field_ar_title->value;
          $array_merge_4['hotel_term'][] = explode(',',$field_value_array_create_4);
        }

      $field_guide = $node_data->field_guide->value;
      $field_package_image = $node_data->get('field_package_image')->entity->getFileUri();
      if ($wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($field_package_image)) {
        $field_package_image_avatar = $wrapper->getExternalUrl();
      }
      
      $field_guide_photo = $node_data->get('field_guide_photo')->isEmpty();

      if(empty($field_category_data)){
        $field_guide_photo_data = 'null';
      }else{
        $field_guide_photo_data = $node_data->get('field_guide_photo')->entity->getFileUri();
        if ($wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($field_guide_photo_data)) {
        $avatar = $wrapper->getExternalUrl();
         }
      }

      $field_package_price_bhd_infant = $node_data->field_package_price_bhd_infant->value;
      $field_package_price_sar_infant = $node_data->field_package_price_sar_infant->value;
      $field_package_price_bhd_child = $node_data->field_package_price_bhd_child->value;
      $field_package_price_sar_child = $node_data->field_package_price_sar_child->value;
      $field_number_of_days = $node_data->field_number_of_days->value;
      $field_offer = $node_data->field_offer->value;
      $field_package_reference_data = $node_data->field_package_reference_->value;
      
      $field_package_status = $node_data->field_package_status->referencedEntities();

        foreach ($field_package_status as $field_package_status_value) {
          $field_value_array_create_3 = $field_package_status_value->tid->value.','.$field_package_status_value->name->value.','.$field_package_status_value->field_ar_title->value;
          $array_merge_3[] = explode(',',$field_value_array_create_3);
        }

      $field_package_price_bhd_adult = $node_data->field_package_price_bhd_adult->value;
      $field_package_price_sar_adult = $node_data->field_package_price_sar_adult->value;
      $field_search_terms = $node_data->field_search_terms->referencedEntities();

      foreach ($field_search_terms as $field_search_terms_value) {

        $field_value_array_create_2 = $field_search_terms_value->tid->value.','.$field_search_terms_value->name->value.','.$field_search_terms_value->field_ar_title->value;
          $array_merge_2['search_term'][] = explode(',',$field_value_array_create_2);    
         }

      $field_package_strike_price_bhd = $node_data->field_package_strike_price_bhd->value;

      $field_package_strike_price_sar = $node_data->field_package_strike_price_sar->value;

      $field_package_tags = $node_data->field_package_tags->referencedEntities();

        foreach ($field_package_tags as $field_package_tags_value) {
          $field_value_array_create = $field_package_tags_value->tid->value.','.$field_package_tags_value->name->value.','.$field_package_tags_value->field_ar_title->value;
          $array_merge[] = explode(',',$field_value_array_create);    
        }

      $field_travel_date_from_to = $node_data->field_travel_date_from_to->value;
      $field_travel_type = $node_data->field_travel_type->referencedEntities();

        foreach ($field_travel_type as $field_travel_type_value) {
          $field_value_array_create_travel = $field_travel_type_value->tid->value.','.$field_travel_type_value->name->value.','.$field_travel_type_value->field_ar_title->value;
          $array_merge_travel_type['travel_type'][] = explode(',',$field_value_array_create_travel);    
        }

      $create_array = [];
      $create_array['id'] = $field_nid;
      $create_array['en_title'] = $field_name; 
      $create_array['ar_title '] = $field_ar_title;
      $create_array['en_body'] = $body;
      //$create_array['ar_body'] = $field_ar_body;
      $create_array['categories'] = $array_merge_6;
      $create_array['tags'] = $array_merge;
      $create_array['adult_price_bd'] = $field_package_price_bhd_adult;
      $create_array['adult_price_sar'] = $field_package_price_sar_adult;
      $create_array['child_price_bd'] = $field_package_price_bhd_child;
      $create_array['child_price_sar'] = $field_package_price_sar_child;
      $create_array['infant_price_bd'] = $field_package_price_bhd_infant;      
      $create_array['infant_price_sar'] = $field_package_price_sar_infant;
      $create_array['offer'] = $field_offer;
      $create_array['number_of_days'] = $field_number_of_days;
      $create_array['package_status'] = $array_merge_3;
      $create_array['person_created'] = $node_owner_name;
      $create_array['agency_mail'] = $node_owner_mail;
      $create_array['agency_person_name'] = $node_owner_person_name;
      $create_array['agency_phone'] = $node_owner_phone_number;
      $create_array['photo_url'] = $field_package_image_avatar;
      //$create_array['agency_logo'] = $field_logo_avatar;
      $create_array['agency_name'] = $node_owner_agency_name;
      array_push($create_main,$create_array);
      //$JsonResponse = new JsonResponse($create_array);
      //ech
      //var_dump($JsonResponse);
      
    }
    $data = [];
    $data['data']= $create_main;

    echo json_encode($data);
    exit;
    //return array($JsonResponse);
  }
}