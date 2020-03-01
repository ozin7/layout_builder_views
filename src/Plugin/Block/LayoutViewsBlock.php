<?php

namespace Drupal\layout_builder_views\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LayoutViewsBlock' block.
 *
 * @Block(
 *  id = "layout_views_block",
 *  admin_label = @Translation("Layout views block"),
 *  category = @Translation("Layout blocks"),
 * )
 */
class LayoutViewsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Views storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewsStorage;

  /**
   * LayoutViewsBlock constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manger;
    $this->viewsStorage = $this->entityTypeManager->getStorage('view');
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
  public function defaultConfiguration() {
    return [
      'title' => '',
      'text' => [],
      'views_id' => '',
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Text'),
      '#default_value' => !empty($this->configuration['text']['value']) ?? '',
      '#format' => 'full_html',
    ];

    $this->getViews();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['content']['#markup'] = 'Implement LayoutViewsBlock.';

    return $build;
  }

  public function getViews() {
    $views = $this->viewsStorage->loadByProperties(['status' => TRUE]);
    $g =6;
  }

}
