<?php

namespace Drupal\cache_assignment\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'Category Articles' Block.
 */

#[Block(
  id: "cache_category_articles_block",
  admin_label: new TranslatableMarkup("Category Articles Block"),
  category: new TranslatableMarkup("Category Articles Block"),
)]

class CacheCategoryArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the current user ID safely
    $current_user_id = \Drupal::currentUser()->id();

     // Load the user entity with access check
    $user = $this->entityTypeManager->getStorage('user')
    ->load($current_user_id);

    if ($user && $user->hasField('field_preferred_category')) {
        $category_field = $user->get('field_preferred_category');

        // Check if the field has a value
        if (!$category_field->isEmpty()) {
          $category_id = $category_field->target_id;
          // Use $category_id here
        } else {
          // Handle empty field case
          \Drupal::logger('cache_assignment')->warning('User @uid has no preferred category set.',
            ['@uid' => $current_user_id]);
        }
    }

    if (!$category_id) {
      return [];
    }

    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'article')
      ->condition('field_category.target_id', $category_id)
      ->sort('created', 'DESC')
      ->range(0, 5)
      ->accessCheck(TRUE);

    $nids = $query->execute();

    if (empty($nids)) {
      return [];
    }

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $build = [
      '#theme' => 'item_list',
      '#items' => [],
      '#cache' => [
        'contexts' => ['custom_category'],
      ],
    ];
    foreach ($nodes as $node) {
      $build['#items'][] = $node->toLink()->toString();
    }

    return $build;
  }

}
