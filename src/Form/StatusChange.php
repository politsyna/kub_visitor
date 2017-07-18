<?php

namespace Drupal\node_visitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\user\Entity\User;

/**
 * SimpleForm.
 */
class StatusChange extends FormBase {

  /**
   * F ajaxModeDev.
   */
  public function ajaxStatusChange(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    if ($user->hasPermission('visitor-form')) {
      $node = $form_state->node_visitor;
      $status = $form_state->getValue('select');
      $node->field_visitor_status->setValue($status);
      $node->save();
      $response->addCommand(new HtmlCommand("#status-change", "Статус заявки успешно изменен"));
      $response->addCommand(new RedirectCommand('/node/' . $node->id()));
    }
    else {
      $response->addCommand(new HtmlCommand("#status-change", "Доступ запрещен"));
    }
    return $response;
  }

  /**
   * Build the simple form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $node = $extra;
    $form_state->node_visitor = $node;
    $form_state->setCached(FALSE);
    $status = $node->field_visitor_status->value;
    $form["#suffix"] = '<div id="status-change"></div>';
    $form['select'] = [
      '#type' => 'select',
      '#default_value' => $status,
      '#title' => 'Статус заявки',
      '#options' => [
        'zayvka' => 'Заявка',
        'done' => 'Исполнено',
        'cancel' => 'Отказ',
      ],
      '#ajax' => [
        'callback' => '::ajaxStatusChange',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   */
  public function getFormId() {
    return 'button_status_change_form';
  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

}
