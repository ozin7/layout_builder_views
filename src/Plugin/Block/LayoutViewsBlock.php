<?php

namespace Drupal\layout_builder_views\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Element\View as ViewElement;

/**
 * Provides a 'LayoutViewsBlock' block.
 *
 * @Block(
 *  id = "layout_views_block",
 *  admin_label = @Translation("Default listing"),
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
      'views' => '',
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
      '#default_value' => $this->configuration['text']['value'] ?? '',
      '#format' => 'full_html',
    ];
    $views_options = $this->getViewsOptions();

    $form['views'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('List'),
      '#options' => $views_options,
      '#default_value' => $this->configuration['views'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (isset($values['views'])) {
      $this->configuration['views'] = $values['views'];
    }
    if (isset($values['text'])) {
      $this->configuration['text'] = $values['text'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    [$view_id, $display_id] = explode(':', $this->configuration['views']);

    if ($view = Views::getView($view_id)) {
      $build['content'] = [
        '#theme' => 'layout_builder_views',
        '#views' => $this->buildViewsOutput($view, $display_id),
        '#text' => [
          '#type' => 'processed_text',
          '#text' => $this->configuration['text']['value'],
          '#format' => $this->configuration['text']['format'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Build views options.
   */
  public function getViewsOptions() {
    $views = Views::getEnabledViews();
    $options = [];
    /** @var \Drupal\views\Entity\View $view */
    foreach ($views as $view) {
      foreach ($view->get('display') as $display) {
        if ($display['display_plugin'] === 'block') {
          if (isset($display['display_options']['block_category'])) {
            if ($display['display_options']['block_category'] === 'Layout blocks') {
              $options[$view->id() . ':' . $display['id']] = $display['display_title'];
            }
          }
        }
      }
    }

    return $options;
  }

  /**
   * Build views output.
   */
  public function buildViewsOutput(ViewExecutable $view, string $display_id, array $args = []) {
    $output = $view->buildRenderable($display_id, $args);
    $output = ViewElement::preRenderViewElement($output);

    return $output;
  }

}
