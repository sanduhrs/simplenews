<?php

/**
 * @file
 * Contains \Drupal\simplenews\Plugin\views\field\LinkDelete.
 */

namespace Drupal\simplenews\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link subscriber edit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("subscriber_link_delete")
 */
class LinkDelete extends Link {

  /**
   * Prepares the link to the subscriber.
   *
   * @param \Drupal\simplenews\Entity\SubscriberInterface $subscriber
   *   The subscriber entity this field belongs to.
   * @param ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($subscriber, ResultRow $values) {

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "admin/people/simplenews/delete/" . $subscriber->id();
    $this->options['alter']['query'] = drupal_get_destination();

    $text = !empty($this->options['text']) ? $this->options['text'] : $this->t('Delete');
    return $text;
  }

}
