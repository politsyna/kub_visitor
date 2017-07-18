<?php

namespace Drupal\node_visitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * SimpleForm.
 */
class CalcCost extends FormBase {

  /**
   * F ajaxModeDev.
   */
  public function ajaxCost(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $node = $form_state->node_visitor;
    $fakt_cost = $node->field_visitor_cost->value;
    $rasch_cost = $node->field_visitor_cost_raschet->value;
    $fakt = $form_state->getValue('fakt');
    $node->field_visitor_cost->setValue($fakt);
    $status = $node->field_visitor_status->value;
    $status_done = "done";
    if ($status == "zayvka") {
      $node->field_visitor_status->setValue($status_done);
      $node->save();
      $response->addCommand(new HtmlCommand("#status-change", "Статус заявки успешно изменен"));
    }

    $response->addCommand(new RedirectCommand('/node/' . $node->id()));
    $node->save();
    return $response;
  }

  /**
   * Page Callback.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $node = $extra;
    $fakt_cost = $node->field_visitor_cost->value;
    $k_oplate = $node->field_visitor_cost_k_oplate->value;
    $form_state->node_visitor = $node;
    $form_state->setCached(FALSE);

    if ($fakt_cost == 0) {
      $form['fakt'] = [
        '#type' => 'textfield',
        '#title' => '<span class="cost">Внесено: </span>',
        "#default_value" => number_format($k_oplate, 0, ",", " "),
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => 'OK',
        '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-success']],
        '#suffix' => '<div class="otvet"></div>',
        '#ajax' => [
          'callback' => '::ajaxCost',
          'effect' => 'fade',
          'progress' => ['type' => 'throbber', 'message' => ""],
        ],
      ];
    }
    else {
      $form['#prefix'] = '<br><span class="cost">Внесено: </span>'
      . number_format($k_oplate, 0, ",", " ") . " руб.";
    }
    return $form;
  }

  /**
   * Getter method for Form ID.
   */
  public function getFormId() {
    return 'button_cost_form';
  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

}
