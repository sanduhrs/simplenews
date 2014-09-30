<?php
/**
 * @file
 * Contains \Drupal\simplenews\Tests\SimplenewsPersonalizationFormsTest.
 */

namespace Drupal\simplenews\Tests;
use Drupal\simplenews\Entity\Newsletter;
use Drupal\simplenews\Entity\Subscriber;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Tests crucial aspects of Subscriber fieldability and User field sync.
 *
 * @group simplenews
 */
class SimplenewsPersonalizationFormsTest extends SimplenewsTestBase {
  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->addField('string', 'field_shared', 'user');
    $this->addField('string', 'field_shared', 'simplenews_subscriber');

    Role::load('anonymous')
      ->grantPermission('subscribe to newsletters')
      ->grantPermission('access user profiles')
      ->save();
    Role::load('authenticated')
      ->grantPermission('subscribe to newsletters')
      ->save();
  }

  /**
   * Subscribe then register: fields updated, subscription remains unconfirmed.
   */
  public function testSynchronizeSubscribeRegister() {
    $email = $this->randomEmail();

    // Subscribe.
    $this->subscribe('default', $email, array('field_shared[0][value]' => $this->randomString(10)));

    // Register.
    $new_value = $this->randomString(20);
    $uid = $this->registerUser($email, array('field_shared[0][value]' => $new_value));

    // Assert fields are updated.
    $this->drupalGet("user/$uid");
    $this->assertText($new_value);

    // Assert subscription remains unconfirmed.
    $snids = \Drupal::entityQuery('simplenews_subscriber')
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->execute();
    $subscriber = Subscriber::load(array_shift($snids));
    $this->assertEqual($subscriber->subscriptions->get(0)->status, SIMPLENEWS_SUBSCRIPTION_STATUS_UNCONFIRMED);
  }

  /**
   * Register then subscribe: require login, fields updated.
   */
  public function testSynchronizeRegisterSubscribe() {
    $email = $this->randomEmail();

    // Register.
    $uid = $this->registerUser($email, array('field_shared[0][value]' => $this->randomString(10)));
    $user = User::load($uid);

    // Attempt subscribe and assert login message.
    $this->subscribe('default', $email, array(), t('Subscribe'), 'newsletter/subscriptions', 403);
    $this->assertRaw(t('There is an account registered for the e-mail address %mail. Please log in to manage your newsletter subscriptions', array('%mail' => $email)));

    // Visit a confirm link and assert login message.
    $subscriber = Subscriber::create(array('email' => $email));
    $subscriber->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_UNCONFIRMED);
    $subscriber->save();
    $data = array(
      'simplenews_subscriber' => $subscriber,
      'newsletter' => Newsletter::load('default'),
    );
    $this->drupalGet(\Drupal::token()->replace('[simplenews-subscriber:subscribe-url]', $data));
    $this->assertRaw(t('There is an account registered for the e-mail address %mail. Please log in to manage your newsletter subscriptions', array('%mail' => $email)));

    // Login.
    $timestamp = REQUEST_TIME;
    $hash = user_pass_rehash($user->getPassword(), $timestamp, $user->getLastLoginTime());
    $this->drupalPostForm("/user/reset/$uid/$timestamp/$hash", array(), t('Log in'));

    // Subscribe.
    $new_value = $this->randomString(20);
    $this->subscribe('default', NULL, array('field_shared[0][value]' => $new_value));

    // Assert fields are updated.
    $this->drupalGet("user/$uid");
    $this->assertText($new_value);
  }

  /**
   * Subscribe, request password: "name is not recognized".
   * /
  public function testSubscribeRequestPassword() {
    // Subscribe.
    // Request new password.
    // Assert the email is not recognized as an account.
  }

  /**
   * Disable account, subscriptions inactive.
   * /
  public function testDisableAccount() {
    // Register account.
    // Subscribe.
    // Disable account.
    // Assert subscriptions are inactive.
  }

  /**
   * Delete account, subscriptions deleted.
   * /
  public function testDeleteAccount() {
    // Register account.
    // Subscribe.
    // Delete account.
    // Assert subscriptions are deleted.
  }

  /**
   * Blocked account subscribes, display message.
   * /
  public function testBlockedSubscribe() {
    // Register account.
    // Block account.
    // Attempt subscribe and assert "blocked" message.
  }
   */

}
