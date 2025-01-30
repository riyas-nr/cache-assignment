<?php

namespace Drupal\cache_assignment\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'Current User' Block.
 */

#[Block(
  id: "cache_user_block",
  admin_label: new TranslatableMarkup("Current User Block"),
  category: new TranslatableMarkup("Current User Block"),
)]

class CacheUserBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new User block object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string
   * @param $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }



  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    if ($this->currentUser->isAuthenticated()) {
      $user = User::load($this->currentUser->id());
      $email = $user->getEmail();
      $build = [
        '#markup' => $this->t('Your email address is: @email', ['@email' => $email]),
      ];
    }
    else {
      $build = [
        '#markup' => $this->t('You are not logged in.'),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
