<?php

namespace Drupal\nfa_landing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure NFA Landing settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nfa_landing_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nfa_landing.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    foreach ($this->config('nfa_landing.settings')->get('sites') as $site => $data) {
      $options[$site] = $data['name'];
    }
    $form['site'] = [
      '#type' => 'select',
      '#title' => $this->t('NFA site'),
      '#description' => $this->t('Select the current NFA site name.'),
      '#options' => $options,
      '#default_value' => $this->config('nfa_landing.settings')->get('site'),
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#default_value' => $this->config('nfa_landing.settings')->get('message'),
      '#description' => $this->t('The welcome message that will be shown on the home page.'),
      '#required' => TRUE,
      '#rows' => '8',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('nfa_landing.settings')
      ->set('site', $form_state->getValue('site'))
      ->set('message', $form_state->getValue('message'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
