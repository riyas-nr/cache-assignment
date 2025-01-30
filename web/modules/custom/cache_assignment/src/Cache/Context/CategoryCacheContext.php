<?php

namespace Drupal\cache_assignment\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class CategoryCacheContext implements CacheContextInterface {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Category cache context');
  }

  public function getContext() {
    $user = $this->entityTypeManager->getStorage('user')->load(\Drupal::currentUser()->id());
    $category_id = $user->get('field_preferred_category')->target_id;
    return $category_id ? 'category_' . $category_id : 'none';
  }

  public function getCacheableMetadata() {

    $cacheableMetadata = new CacheableMetadata();

    $cacheableMetadata->addCacheTags(['user:' . \Drupal::currentUser()->id()]);
    return $cacheableMetadata;
  }
}
