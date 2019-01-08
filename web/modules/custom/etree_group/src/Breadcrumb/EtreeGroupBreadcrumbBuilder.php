<?php


namespace Drupal\etree_group\Breadcrumb;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree\ETreeCommon;
use Symfony\Component\HttpFoundation\RequestStack;
use function strpos;

class EtreeGroupBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /** @var \Drupal\etree_group\Entity\ETreeGroupInterface group */
  protected $group;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
  }

  protected function groupStorage() {
    try {
      return $this->entityTypeManager->getStorage('etree_group');
    } catch (InvalidPluginDefinitionException $e) {
      $this->messenger()->addError($e);
    } catch (PluginNotFoundException $e) {
      $this->messenger()->addError($e);
    }
  }

  protected function etreeStorage() {
    try {
      return $this->entityTypeManager->getStorage('etree');
    } catch (InvalidPluginDefinitionException $e) {
      $this->messenger()->addError($e);
    } catch (PluginNotFoundException $e) {
      $this->messenger()->addError($e);
    }
  }


  /**
   * Whether this breadcrumb builder should be used to build the breadcrumb.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return bool
   *   TRUE if this builder should be used or FALSE to let other builders
   *   decide.
   */
  public function applies(RouteMatchInterface $route_match) {

    $this->group = $this->getsGroupFromPath($this->getRequest()->getPathInfo());

    return (bool) $this->group;

  }

  protected function getsGroupFromPath($path) {
    try {
      $groups = $this->entityTypeManager->getStorage('etree_group')->loadMultiple();
      foreach ($groups as $group) {
        /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
        if ($group->isCollectionPath($path)) {
          return $group;
        }
      }
    } catch (InvalidPluginDefinitionException $e) {
    } catch (PluginNotFoundException $e) {
    }
    return NULL;
  }


  protected function isRootPath($parameters) {
    return isset($parameters['view_id']);
  }

  /**
   * Gets the request object.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The request object.
   */
  protected function getRequest() {
    if (!$this->requestStack) {
      $this->requestStack = \Drupal::service('request_stack');
    }
    return $this->requestStack->getCurrentRequest();
  }


  /**
   * Builds the breadcrumb.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   A breadcrumb.
   */
  public function build(RouteMatchInterface $route_match) {
    $links = [];
    $links[] = Link::createFromRoute('Home', '<front>');
    $links[] = $this->LinkGroupCollection();

    $parameters = $route_match->getParameters()->all();
    if (isset($parameters['etree'])) {
      /** @var \Drupal\etree\Entity\ETreeInterface $entity */
      $links = array_merge($links, $this->LinkETreeHierarchy($parameters['etree']));
    }

    $breadcrumb = new Breadcrumb();
    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

  /**
   * @return \Drupal\Core\Link
   */
  protected function LinkGroupCollection() {
    /** @var \Drupal\views\Entity\View $view */
    $view_id = ETreeCommon::getGroupViewsName($this->group->id());
    try {
      $view = $this->entityTypeManager->getStorage('view')->load($view_id);
    } catch (InvalidPluginDefinitionException $e) {
      $this->messenger()->addError($e->getMessage());
    } catch (PluginNotFoundException $e) {
      $this->messenger()->addError($e->getMessage());
    }


    /** @var \Drupal\views\Plugin\views\display\Page $display */
    $display = $view->getExecutable()->getDisplay();
    $display = $display->getRoutedDisplay();

    $route_name = $display->getRouteName();
    $title = $this->t('Project "@project"', ['@project' => $display->getOption('menu')['title']]);

    return Link::createFromRoute($title, $route_name);

  }

  /**
   *
   * @param \Drupal\etree\Entity\ETreeInterface $entity
   *
   * @return array
   */
  protected function LinkETreeHierarchy(ETreeInterface $entity) {
    $links = [];


    $parent_ids = $entity->getParentIds();

    if (!$parent_ids) {
      return $links;
    }

    $parents = $this->etreeStorage()->loadMultiple($parent_ids);
    $parents[] = $entity;

    foreach ($parents as $parent) {
      try {
        $links[] = Link::fromTextAndUrl($parent->label(), $parent->toUrl('canonical'));
      } catch (EntityMalformedException $e) {
        $this->messenger()->addError($e->getMessage());
      }
    }

    return $links;
  }


}