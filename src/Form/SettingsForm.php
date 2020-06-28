<?php

namespace Drupal\managed_file_purge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines form class for configuring managed file purge settings.
 */
class SettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->setStringTranslation($container->get('string_translation'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'managed_file_purge_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['managed_file_purge.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['cdn_base_urls'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('CDN Base URls'),
      '#description' => $this->t('Provide a list of base URLs used by the CDN(s) to invalidate file URLs.'),
      '#tree' => TRUE,
      '#prefix' => '<div id="cdn-base-urls-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    // Keep count of how many URL fields to display. Initial form build will be
    // based on existing config values.
    $num_urls = $form_state->get('num_urls');
    if (is_null($num_urls)) {
      $config = $this->config('managed_file_purge.settings');
      $cdn_base_urls = $config->get('cdn_base_urls') ?? [];
      $num_urls = count($cdn_base_urls);
      $form_state->set('num_urls', $num_urls);
    }

    // Add a URL field for each entered URL.
    foreach (range(0, $num_urls - 1) as $delta) {
      $form['cdn_base_urls']['urls'][$delta] = [
        '#type' => 'url',
        '#default_value' => $cdn_base_urls[$delta] ? rtrim($cdn_base_urls[$delta], '/') : '',
      ];
    }
    // And an extra blank one to enter an additional value.
    $form['cdn_base_urls']['urls'][] = [
      '#type' => 'url',
    ];

    // Add AJAX-enabled button to allow more URL fields to be added.
    $form['cdn_base_urls']['actions'] = [
      '#type' => 'actions',
    ];
    $form['cdn_base_urls']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another URL'),
      '#limit_validation_errors' => [],
      '#submit' => ['::addUrl'],
      '#ajax' => [
        'callback' => '::addUrlCallback',
        'wrapper' => 'cdn-base-urls-fieldset-wrapper',
      ],
      // Disable this button if the last URL field is empty.
      '#states' => [
        'disabled' => [
          ':input[name="cdn_base_urls[urls][' . ($delta + 1) . ']"]' => [
            'empty' => TRUE,
          ],
        ],
      ],
    ];

    $form_state->setCached(FALSE);
    return parent::buildForm($form, $form_state);
  }

  /**
   * AJAX callback for add another URL button.
   *
   * Selects and returns the fieldset with the URLs in it.
   */
  public function addUrlCallback(array &$form, FormStateInterface $form_state) {
    return $form['cdn_base_urls'];
  }

  /**
   * Submit handler for the add another URL button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addUrl(array &$form, FormStateInterface $form_state) {
    $form_state->set('num_urls', $form_state->get('num_urls') + 1);

    // Since our buildForm() method relies on the value of 'num_urls' to
    // generate URL form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('managed_file_purge_cloudflare.info.yml.settings');
    // Remove any empty URL field values.
    $cdn_base_urls = array_values(array_filter($form_state->getValue(['cdn_base_urls', 'urls'], [])));
    foreach ($cdn_base_urls as &$cdn_base_url) {
      // Remove trailing slash from each URL.
      $cdn_base_url = rtrim($cdn_base_url, '/');
    }
    $config->set('cdn_base_urls', $cdn_base_urls)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
