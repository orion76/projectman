<?php

namespace Drupal\etree\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Link;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\Url;
use Drupal\etree\drulib\entity\EntityFull;
use Drupal\etree\ETreeHierarchyData;
use Drupal\etree\ETreeStorageHierarchy;
use Drupal\etree\ETreeStorageInterface;
use Drupal\etree\Event\ETreeEventSave;
use Drupal\etree\exception\ETreeHierarchyDataException;
use function str_replace;


/**
 * Defines the ETree entity.
 *
 * @ingroup etree
 *
 * @ContentEntityType(
 *   id = "etree",
 *   label = @Translation("ETree"),
 *   bundle_label = @Translation("ETree type"),
 *   handlers = {
 *     "storage" = "Drupal\etree\ETreeStorage",
 *     "storage_schema" = "Drupal\etree\ETreeStorageSchema",
 *     "view_builder" = "Drupal\etree\ViewBuilder\ETreeViewBuilder",
 *     "list_builder" = "Drupal\etree\ETreeListBuilder",
 *     "translation" = "Drupal\etree\ETreeTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\etree\Form\ETreeForm",
 *       "add" = "Drupal\etree\Form\ETreeForm",
 *       "edit" = "Drupal\etree\Form\ETreeForm",
 *       "delete" = "Drupal\etree\Form\ETreeDeleteForm",
 *     },
 *     "access" = "Drupal\etree\ETreeAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\etree\Routing\ETreeHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "etree",
 *   data_table = "etree_field_data",
 *   revision_table = "etree_revision",
 *   revision_data_table = "etree_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer etree entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/etree/{etree}",
 *     "add-page" = "/admin/structure/etree/add",
 *     "add-form" = "/admin/structure/etree/add/{etree_type}",
 *     "edit-form" = "/admin/structure/etree/{etree}/edit",
 *     "delete-form" = "/admin/structure/etree/{etree}/delete",
 *     "version-history" = "/admin/structure/etree/{etree}/revisions",
 *     "revision" = "/admin/structure/etree/{etree}/revisions/{etree_revision}/view",
 *     "revision_revert" = "/admin/structure/etree/{etree}/revisions/{etree_revision}/revert",
 *     "revision_delete" = "/admin/structure/etree/{etree}/revisions/{etree_revision}/delete",
 *     "translation_revert" ="/admin/structure/etree/{etree}/revisions/{etree_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/etree/items",
 *   },
 *   bundle_entity_type = "etree_type",
 *   field_ui_base_route = "entity.etree_type.edit_form"
 * )
 */
class ETree extends EntityFull implements ETreeInterface {

  use EntityChangedTrait;
  use ETreeRoutesTrait;

  /**
   * @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $hierarchy
   */
  protected $hierarchy;


  /**
   * @var \Drupal\etree\ETreeHierarchyData $hierarchy_data
   */
  protected $hierarchy_data;

  public function __construct(array $values, $entity_type, $bundle = FALSE, array $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $this->hierarchy_data = new ETreeHierarchyData($bundle);
  }

  public function __clone() {

    $hierarchy_data = $this->hierarchy_data;
    $this->hierarchy_data = &$hierarchy_data;

    return parent::__clone();
  }


  /**
   * @return \Drupal\etree\Entity\ETreeInterface
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getParentId() {
    return $this->getFieldValue('etree_parent');
  }

  /**
   * @return \Drupal\etree\Entity\ETreeInterface
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getParent() {
    return $this->getFieldReferenceEntity('etree_parent');
  }

  public function setHierarchyData($hierarchy_data) {
    $this->hierarchy_data = $hierarchy_data;
  }

  public function getGroupId() {
    return $this->getFieldValue('etree_group');
  }

  /**
   * @return \Drupal\etree_group\Entity\ETreeGroupInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGroup() {
    if ($group_id = $this->getGroupId()) {
      $storage = $this->entityTypeManager()->getStorage('etree_group');
      $group = $storage->load($group_id);
      return $group;
    }
  }

  public function getLevel() {
    return $this->getFieldValue('etree_level');
  }

  public function getWeight() {
    return $this->getFieldValue('etree_weight');
  }

  public function getPath() {
    return $this->getFieldValue('etree_path');
  }


  /**
   * TODO ВОзможно это свойство не нужно, проверить в дальнейшем
   *
   * @return mixed
   */
  public function getHierarchyEntities() {
    return $this->getFieldReferenceEntity('etree_hierarchy');
  }

  protected function getFieldReferenceEntity($name) {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $field */
    $field = $this->get($name);

    if ($field->isEmpty()) {
      return NULL;
    }

    $definition = $this->getFieldDefinition($name)->getFieldStorageDefinition();

    if ($definition->isMultiple()) {
      return $field->referencedEntities();
    }
    else {
      $list = $field->referencedEntities();
      return reset($list);
    }
  }

  protected function getFieldValue($name) {

    $field = $this->get($name);

    if ($field->isEmpty()) {
      return NULL;
    }

    $definition = $this->getFieldDefinition($name)->getFieldStorageDefinition();

    if ($definition->isMultiple()) {
      return array_column($field->getValue(), $definition->getMainPropertyName());
    }
    else {
      $list = $field->getValue();
      return reset($list)[$definition->getMainPropertyName()];
    }
  }

  /**
   * @return \Drupal\etree\ETreeHierarchyData
   */
  public function getHierarchyData() {
    return $this->hierarchy_data;
  }

  public function getParentIds() {
    return $this->hierarchy_data->getParentIds();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    /** @var \Drupal\etree\ETreeStorageInterface $storage */

    $children = [];
    $hstorage = $storage->getHierarchy();
    foreach ($entities as $id => $entity) {
      /** @var \Drupal\etree\Entity\ETreeInterface $entity */
      $level = $entity->getLevel();
      if (!is_null($level)) {
        $ids = $hstorage->getChildrenIdsAll($id, $level);
        $children += $storage->loadMultiple($ids);
      }
      ETreeEventSave::dispatch(ETreeEventSave::POST_DELETE, $entity);

    }

    $storage->delete($children);

  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * @param $storage
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  protected function setHierarchyParent(ETreeHierarchyData $hdata) {
    if ($parent = $this->getParent()) {
      $record = $parent->getHierarchyData()->getChildData($this->bundle(), $this->id());
      $hdata->setData($record);
    }
  }

  /**
   * @param $storage
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  protected function setHierarchyWeight(ETreeHierarchyData $hdata, ETreeStorageHierarchy $hstorage) {
    if ($weight = $this->getWeight() && !empty($weight)) {
      $hdata->setWeight($weight);
      return TRUE;
    }

    $weight = $hstorage->getNextWeight($hdata->level(), $hdata->parentId());
    $hdata->setWeight($weight);
    return TRUE;
  }


  /**
   * @param \Drupal\etree\ETreeStorageInterface $storage
   * @param \Drupal\etree\ETreeHierarchyData $hdata
   * @param \Drupal\etree\ETreeStorageHierarchy $hstorage
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  protected function initNew(ETreeStorageInterface $storage, ETreeHierarchyData $hdata, ETreeStorageHierarchy $hstorage) {

    if ($exists_data = $hstorage->load($this->id())) {
      $hdata->setData($exists_data);
    }
    else {

      $hdata->setData($hdata->dataDefault($this->id()));

      $this->setHierarchyParent($hdata);

      $this->setHierarchyWeight($hdata, $hstorage);
      $hdata->setGroup($this->getGroupId());
    }
  }


  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    /** @var \Drupal\etree\ETreeStorageInterface $storage */
    $hdata = $this->getHierarchyData();
    $hstorage = $storage->getHierarchy();

    $updated = FALSE;

    if ($hdata->isNew()) {
      try {
        $this->initNew($storage, $hdata, $hstorage);
        $updated = TRUE;
      } catch (MissingDataException $e) {
      } catch (ETreeHierarchyDataException $e) {
      }

    }
    if ($this->isParentChanged()) {
      try {
        $this->setHierarchyParent($hdata);
        $updated = TRUE;
      } catch (MissingDataException $e) {
      } catch (ETreeHierarchyDataException $e) {
      }
    }
    if ($this->isWeightChanged()) {
      $this->setHierarchyWeight($hdata, $hstorage);
      $updated = TRUE;
    }

    if ($updated) {
      $storage->updateHierarchy($hdata->getData());
    }
    ETreeEventSave::dispatch(ETreeEventSave::POST_SAVE, $this);
  }

  public function getOriginal() {
    return $this->original;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);


    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the ETree entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the ETree entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'title',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setRequired(TRUE);


    $fields['etree_level'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Level'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setCustomStorage(TRUE);

    $fields['etree_weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setCustomStorage(TRUE);

    $fields['etree_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Code'))
      ->setDescription(t('The code of the ETree entity.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setCustomStorage(TRUE);

    $fields['etree_group'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Group'))
      ->setDescription(t('The group of the ETree entity.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setCustomStorage(TRUE);

    $fields['etree_path'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Path'))
      ->setDescription(t('The path of the ETree entity.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setCustomStorage(TRUE);

    $fields['etree_hierarchy'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Hierarchy'))
      ->setDescription(t('The parent items ids.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'etree')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setCustomStorage(TRUE);


    $fields['etree_parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent'))
      ->setDescription(t('The parents of this item.'))
      ->setCardinality(1)
      ->setSetting('target_type', 'etree')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setCustomStorage(TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Content'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_summary_or_trimmed',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea_with_summary',
        'weight' => 0,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the ETree is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    static::setDisplayConfigurable('form', static::listDisplayConfigurableForm($entity_type), $fields);
    static::setDisplayConfigurable('view', static::listDisplayConfigurableView($entity_type), $fields);

    return $fields;
  }

  /**
   * @param $list
   * @param  BaseFieldDefinition[] $fields
   */
  protected static function setDisplayConfigurable($diaplay, $list, &$fields) {
    foreach ($list as $name) {
      if (!isset($fields[$name])) {
        continue;
      }
      $fields[$name]->setDisplayConfigurable($diaplay, TRUE);
    }
  }

  /**
   * @param $entity_type
   *
   * @return array
   */
  protected static function listDisplayConfigurableForm($entity_type) {
    return [
      'name',
      'user_id',
      'revision_translation_affected',
      'status',
      'body',
      'etree_parent',
      static::getRevisionMetadataKey($entity_type, 'revision_log_message'),
    ];
  }

  /**
   * @param $entity_type
   *
   * @return array
   */
  protected static function listDisplayConfigurableView($entity_type) {
    return [
      'user_id',
      'name',
      'etree_level',
      'etree_weight',
      'etree_code',
      'etree_group',
      'etree_path',
      'body',
      'status',
      static::getRevisionMetadataKey($entity_type, 'revision_log_message'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToSkipFromTranslationChangesCheck() {
    // @todo the current implementation of the parent field makes it impossible
    // for ::hasTranslationChanges() to correctly check the field for changes,
    // so it is currently skipped from the comparision and has to be fixed by
    // https://www.drupal.org/node/2843060.

    $etree = [
      'etree_group',
      'etree_level',
      'etree_weight',
      'etree_path',
      'etree_code',
    ];

    $fields = array_merge(parent::getFieldsToSkipFromTranslationChangesCheck(), $etree);

    return $fields;
  }


  /**
   * @return boolean
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function isWeightChanged() {
    return $this->hierarchy_data->getData('etree_weight') !== $this->getWeight();
  }

  /**
   * @return boolean
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isParentChanged() {
    $parent = $this->getParentId();
    return $this->hierarchy_data->parentId() !== $this->getParentId();
  }

  /**
   * {@inheritdoc}
   */
  public function toLink($text = NULL, $rel = 'canonical', array $options = []) {
    if (!isset($text)) {
      $text = $this->label();
    }

    $url = $this->toUrl($rel);
    $options += $url->getOptions();
    $url->setOptions($options);
    return new Link($text, $url);
  }

  /**
   * Gets an array of placeholders for this entity.
   *
   * Individual entity classes may override this method to add additional
   * placeholders if desired. If so, they should be sure to replicate the
   * property caching logic.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   *
   * @return array
   *   An array of URI placeholders.
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    return $uri_route_parameters;
  }


  /**
   * Build the default links (Read more) for a etree.
   *
   * @param ETreeInterface $entity
   *   The etree object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  public function buildLinks($view_mode) {
    $links = [];
    /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
    $group_name = $this->getGroupId();

    $group = $this->entityTypeManager()->getStorage('etree_group')->load($group_name);
    // Always display a read more link on teasers because we have no way
    // to know when a teaser view is different than a full view.

    $bundle_names = $group->getChildTypes();

    $tree_types = $group->getTreeTypes();

    if (in_array($this->bundle(), $tree_types)) {
      $bundle_names = array_merge($bundle_names, $tree_types);
    }

    $bundles = ETreeType::loadMultiple($bundle_names);
    $etree_title_stripped = strip_tags($this->label());
    foreach ($bundles as $bundle) {
      /** @var ETreeType $bundle */

      //      $link_title = t('Add @bundle', ['@bundle' => $bundle->label()]);
      //      $link_name = "add_{$bundle->id()}";
      //
      //      $options = ['query' => ['parent_id' => $this->id()]];
      //
      //      $route_name = "entity.etree.{$group_name}.add_form";
      //      $router_parameters = ['etree_type' => $bundle->id()];
      //      $url = Url::fromRoute($route_name, $router_parameters, $options);
      //
      //      $links[$link_name] = [
      //        'title' => $link_title,
      //        'url' => $url,
      //        'language' => $this->language(),
      //        'attributes' => [
      //          'rel' => 'tag',
      //          'title' => $etree_title_stripped,
      //          'class' => ['button'],
      //        ],
      //      ];
    }

    return [
      '#theme' => 'links__etree__etree',
      '#links' => $links,
      '#attributes' => ['class' => ['links', 'inline']],
    ];
  }

  static function createRouteNameGroup($group_id, $action) {
    $route_name = "entity.etree_group.{$group_id}.{$action}";
    return str_replace('-', '_', $route_name);
  }

  public static function getStandardPath() {
    return 'admin/structure/etree';
  }

  public static function getGroupPath($group_id) {
    return "admin/structure/etree/group/$group_id";
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    if ($this->id() === NULL) {
      throw new EntityMalformedException(sprintf('The "%s" entity cannot have a URI as it does not have an ID', $this->getEntityTypeId()));
    }

    // The links array might contain URI templates set in annotations.
    $link_templates = $this->linkTemplates();

    // Links pointing to the current revision point to the actual entity. So
    // instead of using the 'revision' link, use the 'canonical' link.
    if ($rel === 'revision' && $this instanceof RevisionableInterface && $this->isDefaultRevision()) {
      $rel = 'canonical';
    }

    $group_id = $this->getGroupId();


    if (isset($link_templates[$rel])) {
      $route_parameters = $this->urlRouteParameters($rel);

      $route_name = $this->getRoutesPluginManager()->getRouteName($group_id, $rel);
      if (!$route_name) {
        $route_name = static::createRouteNameGroup($group_id, $rel);
      }

      $uri = new Url($route_name, $route_parameters);
    }
    else {
      $bundle = $this->bundle();
      // A bundle-specific callback takes precedence over the generic one for
      // the entity type.
      $bundles = $this->entityTypeBundleInfo()->getBundleInfo($this->getEntityTypeId());
      if (isset($bundles[$bundle]['uri_callback'])) {
        $uri_callback = $bundles[$bundle]['uri_callback'];
      }
      elseif ($entity_uri_callback = $this->getEntityType()->getUriCallback()) {
        $uri_callback = $entity_uri_callback;
      }

      // Invoke the callback to get the URI. If there is no callback, use the
      // default URI format.
      if (isset($uri_callback) && is_callable($uri_callback)) {
        $uri = call_user_func($uri_callback, $this);
      }
      else {
        throw new UndefinedLinkTemplateException("No link template '$rel' found for the '{$this->getEntityTypeId()}' entity type");
      }
    }

    // Pass the entity data through as options, so that alter functions do not
    // need to look up this entity again.
    $uri
      ->setOption('entity_type', $this->getEntityTypeId())
      ->setOption('entity', $this);

    // Display links by default based on the current language.
    // Link relations that do not require an existing entity should not be
    // affected by this entity's language, however.
    if (!in_array($rel, ['collection', 'add-page', 'add-form'], TRUE)) {
      $options += ['language' => $this->language()];
    }

    $uri_options = $uri->getOptions();
    $uri_options += $options;

    return $uri->setOptions($uri_options);
  }

  /**
   * Gets an array link templates.
   *
   * @return array
   *   An array of link templates containing paths.
   */
  protected function linkTemplates() {
    $links = $this->getEntityType()->getLinkTemplates();

    return $links;
  }

}
