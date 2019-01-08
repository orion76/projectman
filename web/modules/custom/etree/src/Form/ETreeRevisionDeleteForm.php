<?php

namespace Drupal\etree\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a ETree revision.
 *
 * @ingroup etree
 */
class ETreeRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The ETree revision.
   *
   * @var \Drupal\etree\Entity\ETreeInterface
   */
  protected $revision;

  /**
   * The ETree storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ETreeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new ETreeRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->ETreeStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('etree'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'etree_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => format_date($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.etree.version_history', ['etree' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $etree_revision = NULL) {
    $this->revision = $this->ETreeStorage->loadRevision($etree_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->ETreeStorage->deleteRevision($this->revision->getRevisionId());

    $entity = $this->entity;

    $this->logger('content')->notice('ETree: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    drupal_set_message(t('Revision from %revision-date of ETree %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    //TODO Необходимо добавить к роуту ID группы, данный роут не правильный
//    $form_state->setRedirect(
//      'entity.etree.canonical',
//       ['etree' => $this->revision->id()]
//    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {etree_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
//      $form_state->setRedirect(
      //        'entity.etree.version_history',
      //         ['etree' => $this->revision->id()]
      //      );
    }
  }

}
