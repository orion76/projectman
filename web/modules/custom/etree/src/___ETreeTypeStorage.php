<?php

namespace Drupal\etree;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the storage handler class for ETree entities.
 *
 * This extends the base storage class, adding required special handling for
 * ETree entities.
 *
 * @ingroup etree
 */
class ETreeTypeStorage extends ConfigEntityStorage implements ETreeTypeStorageInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;


  /**
   * The deleted fields repository.
   *
   * @var \Drupal\Core\Field\DeletedFieldsRepositoryInterface
   */


  /**
   * ETreeTypeStorage constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   */
  public function __construct(EntityTypeInterface $entity_type,
                              ConfigFactoryInterface $config_factory,
                              UuidInterface $uuid_service,
                              LanguageManagerInterface $language_manager,
                              EntityManagerInterface $entity_manager,
                              MemoryCacheInterface $memory_cache) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);
    $this->entityManager = $entity_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity.manager'),
      $container->get('entity.memory_cache')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function importDelete($name, Config $new_config, Config $old_config) {
    if (!$old_config->get()) {
      return TRUE;
    }
    return parent::importDelete($name, $new_config, $old_config);
  }


  public function loadRootItems() {
    return $this->loadByProperties(['is_root' => TRUE]);
  }

}
