<?php


namespace Drupal\etree_links;


use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\etree\ETreeCommon;
use Drupal\zurb_library\Ajax\OffCanvas;

class ETreeAjaxOverview {

  const CONTENT_SELECTOR = 'etree-collection-overview-content';

  const TITLE_SELECTOR = 'etree-collection-overview-title';

  /**
   * @param $etree
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function Response($etree) {
    /** @var ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()->getStorage('etree')->load($etree);

    $output = [];
    $output[] = \Drupal::entityTypeManager()->getViewBuilder('etree')->view($entity, 'teaser');
    $output[] = ETreeCommon::entityModalLink($entity, t('Open'));

    $content = render($output);

    # New response
    $response = new AjaxResponse();

    $container = self::createContainer((string) $content);

    $container = render($container);

    # Commands Ajax
    $response->addCommand(new HtmlCommand('#' . self::CONTENT_SELECTOR, $container, $settings = NULL));

    $response->addCommand(new OffCanvas('open', '#offCanvasRight'));
    # Return response
    return $response;
  }


  public static function createContainer($content) {
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $content,
      '#attributes' => ['id' => self::CONTENT_SELECTOR],
      '#attached' => ['library' => ['zurb_library/ajax_command']],
    ];
  }

  public static function link($title, $id) {
    $url = Url::fromRoute('entity.etree.collection.overview', ['etree' => $id]);

    $link = [
      '#type' => 'link',
      '#url' => $url,
      '#title' => $title,
      '#attributes' => [
        'class' => ['use-ajax', 'load-content'],
      ],
    ];
    return render($link);
  }
}