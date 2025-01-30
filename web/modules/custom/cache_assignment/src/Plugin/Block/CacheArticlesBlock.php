<?php

namespace Drupal\cache_assignment\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'Articles' Block.
 */

#[Block(
  id: "cache_articles_block",
  admin_label: new TranslatableMarkup("Articles Block"),
  category: new TranslatableMarkup("Articles"),
)]

class CacheArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LastThreeArticlesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string
   * @param $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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

  public function build() {

    $nids = $this->getLatestArticleIds();

    $nodes = Node::loadMultiple($nids);
    $titles = [];

    foreach ($nodes as $node) {
      $titles[] = $node->getTitle();
    }

    return [
      '#theme' => 'item_list',
      '#items' => $titles,
    ];
  }

  /**
   * Get the node IDs.
   *
   * @return array
   *   An array of node IDs.
   */
  protected function getLatestArticleIds() {
    // Load the latest 3 article nodes.
    $nids = $this->entityTypeManager->getStorage('node')
    ->getQuery()
    ->condition('type', 'article')
    ->sort('created', 'DESC')
    ->range(0, 3)
    ->accessCheck(FALSE)
    ->execute();

    return $nids;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];

    $nids = $this->getLatestArticleIds();

    if (!empty($nids)) {
      foreach ($nids as $nid) {
        $tags[] = 'node:' . $nid;
      }
    }
    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

}
