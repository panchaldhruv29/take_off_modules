<?php

namespace Drupal\modifyrestexport\Plugin\views\style;

use Drupal\file\Entity\File;
use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "wishlist_serializer",
 *   title = @Translation("Wishlist serializer"),
 *   help = @Translation("Serializes views row data and pager using the Serializer component."),
 *   display_types = {"data"}
 * )
 */
class WishlistSerializer extends Serializer {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    $user = \Drupal::service('current_user');
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;

      $liked_id = 0;
      // \Drupal::cache('data')->invalidateAll();
      if ($user->id() != 0) {
        $database = \Drupal::database();
        $query = $database->select('votingapi_vote', 'votes');
        $query
          ->condition('user_id', $user->id())
          ->condition('entity_id', $row->_entity->getVotedEntityId())
          ->condition('type', 'like')
          ->fields('votes', ['id']);
        $liked_id = $query->execute()->fetchField(0);
      }

      $object = [
        "content" => $row->_relationship_entities['entity_id'],
        "relation" => [
          "uid" => $row->_relationship_entities['uid'],
          "node_vote_result_vote_sum_like" => $row->_relationship_entities['node_vote_result_vote_sum_like'],
          "profile" => $row->_relationship_entities['profile'],
        ],
        "like" => $liked_id == '' ? [] : [
          "nid" => [["value" => $liked_id]],
        ],
        "wish" => [
          "nid" => $row->_entity->id,
        ],
      ];

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($row->_entity->getVotedEntityId());

      $paragraph_fields = ['field_ar_itinerary_information', 'field_itinerary_information'];
      foreach ($paragraph_fields as $field) {
        $object['relation'][$field] = $this->getParagraphDetails($node, $field);
      }

      $taxonomy = [
        'field_hotel', 'field_category', 'field_country', 'field_package_status',
        'field_search_terms', 'field_package_tags', 'field_travel_type', 'field_facilities',
      ];
      foreach ($taxonomy as $field) {
        $object['taxonomy'][$field] = $this->getTaxonomyDetail($row->_relationship_entities['entity_id'], $field);
      }

      $rows[] = $object;
    }
    unset($this->view->row_index);

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }

    $pager = $this->view->pager;
    $class = get_class($pager);
    $current_page = !empty($rows) ? $pager->getCurrentPage() : 0;
    $items_per_page = !empty($rows) ? $pager->getItemsPerPage() : 0;
    $total_items = !empty($rows) ? $pager->getTotalItems() : 0;
    $total_pages = 0;
    if (!in_array($class, ['Drupal\views\Plugin\views\pager\None', 'Drupal\views\Plugin\views\pager\Some'])) {
      $total_pages = !empty($rows) ? $pager->getPagerTotal() : 0;
    }

    $result = [
      'rows' => $rows,
      'pager' => [
        'current_page' => $current_page,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'items_per_page' => $items_per_page,
      ],
    ];
    return $this->serializer->serialize($result, $content_type, ['views_style_plugin' => $this]);
  }

  /**
   * Get Paragraph details.
   *
   * @param object $node
   *   Node object to get paragraph details.
   * @param string $field
   *   Field to retrieve paragraph detil from node.
   *
   * @return object
   *   To get result as array.
   */
  public function getParagraphDetails($node, $field = 'field_itinerary_information') {
    $result = [];
    if ($node->hasField($field)) {
      $paragraph = $node->$field->getValue();
      // Loop through the result set.
      foreach ($paragraph as $element) {
        $p = Paragraph::load($element['target_id']);
        if (!empty($p)) {
          $result[] = $p->toArray();
        }
      }
    }
    return $result;
  }

  /**
   * To get taxonomy, use getTaxonomyDetail function.
   *
   * @param object $row
   *   Object of row params.
   * @param string $field
   *   Field name.
   *
   * @return object
   *   To get result as array.
   */
  public function getTaxonomyDetail($row, $field) {
    $result = [];
    if ($row->hasField($field)) {
      $package = $row->get($field)->getString();
      $package_tags = explode(',', str_replace(" ", "", $package));
      $i = 0;
      foreach ($package_tags as $package_tag) {
        $term = Term::load($package_tag);
        if (!empty($term)) {
          $name = $term->getName();
          $term_ar_title = $term->get('field_ar_title')->getValue();
          $array['id'] = $term->id();
          $array['name'] = $name;
          $array['field_ar_title'] = $term_ar_title[0]['value'];

          if ($field == 'field_facilities') {
            $file = $term->get('field_facility_icon')->getValue();
            if (!empty($file)) {
              $fieldata = File::load($file[0]['target_id']);
              $array['field_icon'] = $fieldata->url();
            }
          }

          $parentObj = $term->get('parent')->getValue();
          if (!empty($parentObj) && $parentObj[0]['target_id'] != 0) {
            $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term->id());
            $parent = reset($parent);
            $array['parent']['id'] = $parent->id();
            $array['parent']['name'] = $parent->getName();
          }
        }
        $result[$i] = $array;
        $i++;
      }
    }
    return $result;
  }

}
