<?php

namespace Drupal\ps267\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Url;

/**
 * Provides a 'Add New Content' block.
 *
 * @Block(
 *   id = "ps267_add_content_block",
 *   admin_label = @Translation("Add New Content Links")
 * )
 */
class PS267AddContentBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $links = [];
    $content_types = $this->getContentTypes();
    foreach ($content_types as $content_type) {
      $links[$content_type->id()] = [
        'title' => t($content_type->label()),
        'url' => Url::fromRoute('node.add', ['node_type' => $content_type->id()]),
        'attributes' => ['class' => ['add-link-' . $content_type->id()]]
      ];
    }

    return [
      '#theme' => 'container',
      '#attributes' => ['class' => ['create-content-links']],
      '#children' => [
      'header' => [
        '#theme' => 'markup',
        '#markup' => '<h2>' . t('Create Content') . '</h2>',
      ],
      'links' => [
        '#theme' => 'links',
        '#links' => $links,
      ]
        ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access administration pages');
  }

  /**
   * Gets a list of node types.
   *
   * @return array
   */
  private function getContentTypes() {
    return NodeType::loadMultiple();
  }
}
