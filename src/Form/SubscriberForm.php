<?php

/**
 * @file
 * Definition of Drupal\simplenews\Form\SubscriberForm.
 */

namespace Drupal\simplenews\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the subscriber edit forms.
 */
class SubscriberForm extends ContentEntityForm {
  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /* @var \Drupal\simplenews\SubscriberInterface $subscriber */
    $subscriber = $this->entity;
    $uid = $subscriber->getUserId();

    $form['#title'] = $this->t('Edit subscriber @mail', array('@mail' => $subscriber->getMail()));
    if ($uid > 0) {
      $form['mail']['#disabled'] = 'disabled';
    }

    $options = array();
    $default_value = $subscriber ? $subscriber->getSubscribedNewsletterIds() : array();

    // Get newsletters for subscription form checkboxes.
    // Newsletters with opt-in/out method 'hidden' will not be listed.
    foreach (simplenews_newsletter_get_visible() as $newsletter) {
      $options[$newsletter->id()] = String::checkPlain($newsletter->name);
    }

    $form['subscriptions'] = array(
      '#type' => 'fieldset',
      '#description' => t('Select your newsletter subscriptions.'),
    );
    $form['subscriptions']['newsletters'] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $default_value,
    );

    $form['subscriptions']['#title'] = t('Current newsletter subscriptions');

    $form['activated'] = array(
      '#title' => t('Status'),
      '#type' => 'fieldset',
      '#description' => t('Active or inactive account.'),
      '#weight' => 15,
    );
    $form['activated']['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Active'),
      '#default_value' => $subscriber->getStatus(),
    );

    $language_manager = \Drupal::languageManager();
    if ($language_manager->isMultilingual()) {
      $languages = $language_manager->getLanguages();
      foreach ($languages as $langcode => $language) {
        $language_options[$langcode] = $language->getName();
      }
      $form['language'] = array(
        '#type' => 'fieldset',
        '#title' => t('Preferred language'),
        '#description' => t('The e-mails will be localized in language chosen. Real users have their preference in account settings.'),
        '#disabled' => FALSE,
      );
      if ($subscriber->getUserId()) {
        // Fallback if user has not defined a language.
        $form['language']['langcode'] = array(
          '#type' => 'item',
          '#title' => t('User language'),
          '#markup' => $subscriber->language()->getName(),
        );
      }
      else {
        $form['language']['langcode'] = array(
          '#type' => 'select',
          '#default_value' => $subscriber->language()->id,
          '#options' => $language_options,
          '#required' => TRUE,
        );
      }
    }

    return $form;
  }

  public function buildEntity(array $form, FormStateInterface $form_state) {
    $subscriber =  parent::buildEntity($form, $form_state);
    // We first subscribe, then unsubscribe. This prevents deletion of subscriptions
    // when unsubscribed from the
    arsort($form_state->getValue('newsletters'), SORT_NUMERIC);
    foreach ($form_state->getValue('newsletters') as $newsletter_id => $checked) {
      if ($checked) {
        $subscriber->subscribe($newsletter_id, SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED, 'website');
      }
      else {
        $subscriber->subscribe($newsletter_id, SIMPLENEWS_SUBSCRIPTION_STATUS_UNSUBSCRIBED, 'website');
      }
    }
    return $subscriber;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $subscriber = $this->entity;
    $status = $subscriber->save();

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('Subscriber %label has been updated.', array('%label' => $subscriber->label())));
    }
    else {
      drupal_set_message(t('Subscriber %label has been added.', array('%label' => $subscriber->label())));
    }

    $form_state->setRedirect('simplenews.subscriber_edit', array('simplenews_subscriber' => $subscriber->id()));
  }
}
