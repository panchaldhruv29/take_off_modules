<?php
/**
 * @file
 * Contains \Drupal\sub_admin_dashboard_links\Plugin\Block\SubAdminLinksBlock.
 */
namespace Drupal\sub_admin_dashboard_links\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "sub_admin_dashboard_links_menu",
 *   admin_label = @Translation("Sub Admin Menu block"),
 *   category = @Translation("Custom sub Admin Menu block")
 * )
 */
class SubAdminLinksBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    //$host = \Drupal::request()->getSchemeAndHttpHost();
    $menu_links_custom = '<ul class="sidebar-menu">
				<li><a href="/take-off/web/node/add/tour_package">Add New Package</a></li>
				<li><a href="/take-off/web/package-management-list">Package Management</a></li>
				<li><a href="/take-off/web/offer-management-list">Offers Management</a></li>
				<li><a href="/take-off/web/agency-management">Agency Management</a></li>
				<li><a href="/take-off/web/admin/structure/taxonomy">Master Entries</a></li>
        <li><a href="/take-off/web/admin/people">Users</a></li>
        <li><a href="/take-off/web/list-of-banners">Banners</a></li>
        <li><a href="/take-off/web/admin/content/moderated">Package Waiting Approval</a></li>
        <li><a href="/take-off/web/statistics">Statistics</a></li>
       <!-- <li><a href="/take-off/web/customfirebase/settings">Notification</a></li> -->
        <li><a href="/take-off/web/push-notify">Notify</a></li>

        

			</ul>';
	return array(
      '#type' => 'markup',
      '#markup' => $menu_links_custom,
    );
  }
}

