<?php

/**
 * @file
 * Contains \Drupal\site_alert\Form\SiteAlertManageForm.
 */

namespace Drupal\site_alert\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SiteAlertManageForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_alert_manage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $alert = \Drupal::state()->get('site_alert.alert', []);

    $form['site_alert_active'] = [
      '#type' => 'checkbox',
      '#title' => t('If checked, site alert is active.'),
      '#default_value' => $alert['active'] ?: FALSE,
    ];
    $form['site_alert_severity'] = [
      '#type' => 'select',
      '#title' => $this->t('Severity'),
      '#options' => [
        '1' => $this->t('Low'),
        '2' => $this->t('Medium'),
        '3' => $this->t('High'),
      ],
      '#required' => TRUE,
      '#default_value' => $alert['severity'] ?: '',
    ];
    $form['site_alert_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message'),
      '#default_value' => $alert['message']['value'] ?: '',
      '#format' => $alert['message']['format'] ?: NULL,
    ];

    $start_on = $alert['start_on'] ?: '';
    $end_on = $alert['end_on'] ?: '';
    $form['schedule'] = [
      '#type' => 'details',
      '#title' => $this->t('Scheduling'),
      '#open' => !empty($start_on) || !empty($end_on),
    ];
    $form['schedule']['start_on'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Schedule this alert to start'),
      '#default_value' => $alert['start_on'],
    ];
    $form['schedule']['end_on'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Schedule this alert to end'),
      '#default_value' => $alert['end_on'],
    ];

    $form['hide_from_admin'] = [
      '#type' => 'checkbox',
      '#title' => t('If checked, hide this alert on admin screens.'),
      '#default_value' => $alert['hide_from_admin'] ?: FALSE,
    ];

    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $active = $form_state->getValue('site_alert_active') == TRUE;
    $start_on = $form_state->getValue('start_on');

    if ($active && !empty($start_on)) {
      $form_state->setErrorByName('site_alert_active', $this->t('Do not check the box to make this alert active if you have scheduled it to become active in the future.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = \Drupal::state();

    $active = $form_state->getValue('site_alert_active') == TRUE;
    $severity = $form_state->getValue('site_alert_severity');
    $message = $form_state->getValue('site_alert_message');

    $alert = [
      'active' => $active,
      'severity' => $severity,
      'message' => $message,
      'start_on' => $active ? '' : $form_state->getValue('start_on'),
      'end_on' => $form_state->getValue('end_on'),
      'hide_from_admin' => $form_state->getValue('hide_from_admin'),
      // Distinction will only change when the severity or message changes. This
      // can be used to for any action that should only happen for "new" alerts.
      'distinction' => md5($severity . $message['value']),
    ];

    $state->set('site_alert.alert', $alert);
  }

}
