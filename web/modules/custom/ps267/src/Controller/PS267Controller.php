<?php
/**
 * @file
 * Contains \Drupal\ps267\Controller\PS267ManageContent.
 */

namespace Drupal\ps267\Controller;
use Drupal\Core\Controller\ControllerBase;
/**
 * An example controller.
 */
class PS267Controller extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function adminDashboard() {
    $build = [];
    $build['#attached']['library'][] = 'ps267/ps267-admin';
    $build['add_content'] = $this->getBlock('ps267_add_content_block');
    $build['manage_content'] = $this->getBlock('manage_content_block');
    return $build;
  }

  /**
   * @param string $block_name
   *   The id of a block, or in the case of a view, the display id.
   *
   * @return array
   */
  private function getBlock($block_name) {
    $block = [];

    switch ($block_name) {
      // Plugin defined blocks.
      case "ps267_add_content_block":
        $block_manager = \Drupal::service('plugin.manager.block');

        $config = [];
        $plugin_block = $block_manager->createInstance($block_name, $config);

        $access_result = $plugin_block->access(\Drupal::currentUser());
        if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
          return [];
        }
        $block = $plugin_block->build();
        break;

      // View defined blocks.
      case 'manage_content_block':
        $view = "content";
        $block = views_embed_view($view, $block_name);
        break;
    }
    return $block;
  }
}
