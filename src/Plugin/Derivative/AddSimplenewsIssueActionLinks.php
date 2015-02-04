<?php

/**
 * @file
 * Contains \Drupal\simplenews\Plugin\Derivative\AddSimplenewsIssueActionLinks.
 */

namespace Drupal\simplenews\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\node\Entity\NodeType;

/**
 * Provides dynamic link actions for simplenews content types.
 */
class AddSimplenewsIssueActionLinks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $node_types = simplenews_get_content_types();
    foreach ($node_types as $node_type) {
      $label = NodeType::load($node_type)->label();
      $this->derivatives[$node_type] = $base_plugin_definition;
      $this->derivatives[$node_type]['title'] = 'Add @label';
      $this->derivatives[$node_type]['title_arguments'] = array(
        '@label' => $label,
      );
      $this->derivatives[$node_type]['route_parameters'] = array(
        'node_type' => $node_type,
      );
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }
}
