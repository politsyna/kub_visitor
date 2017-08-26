<?php

namespace Drupal\node_visitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\node\Entity\Node;

/**
 * SimpleForm.
 */
class CalcCost extends FormBase {

  /**
   * F ajaxModeDev.
   */
  public function ajaxCost(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $oplacheno = $form_state->getValue('money');
    if (!is_numeric($oplacheno) || $oplacheno <= 0) {
      $response->addCommand(new HtmlCommand('#button-cost-form', '<br>Введена некорректная сумма'));
      // Выйти из функции, если ошибка (введена некорректная сумма).
      return $response;
    }
    $node = $form_state->node_visitor;
    $config = \Drupal::config('node_kassa.settings');
    $config2 = \Drupal::service('config.factory')->getEditable('node_kassa.settings');
    $number = $form_state->getValue('number');
    $forma_oplati = $form_state->getValue('select');
    $nid = $node->id();
    if (strlen($number) < 3) {
      $number = $config->get('number');
    }
    $seria = $config->get('seria');
    $node_kassa = Node::create([
      'type' => 'kassa',
      'title' => 'Title',
      'field_kassa_seria' => $seria,
      'field_kassa_number' => $number,
      'field_kassa_summa' => $oplacheno,
      'field_kassa_ref_visitor' => $nid,
      'field_kassa_forma' => $forma_oplati,
    ]);
    $node_kassa->save();
    $node_kassa_id = $node_kassa->id();
    if ($node_kassa_id) {
      $number++;
      $number = str_pad($number, 5, "0", STR_PAD_LEFT);
      $config2->set('number', $number)->save();
      $oplacheno = $node->field_visitor_cost->value;
      $rasch_cost = $node->field_visitor_cost_raschet->value;
      $node->field_visitor_cost->setValue($oplacheno);
      $node->field_visitor_cost_k_oplate->setValue($k_oplate);
      $node->save();
      $response->addCommand(new RedirectCommand('/node/' . $node->id()));
    }
    else {
      $response->addCommand(new HtmlCommand('#button-cost-form', '<br>dbxbcbма'));
    }

    return $response;
  }

  /**
   * Page Callback.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $node = $extra;
    $oplacheno = $node->field_visitor_cost->value;
    $ostatok_k_oplate = $node->field_visitor_cost_k_oplate->value;
    $form_state->node_visitor = $node;
    $form_state->setCached(FALSE);
    $config = \Drupal::config('node_kassa.settings');
    if ($ostatok_k_oplate > 0) {
      $form['money'] = [
        '#type' => 'textfield',
        '#title' => '<span class="cost">Внесено в кассу: </span>',
        "#default_value" => number_format($ostatok_k_oplate, 1, ".", ""),
      ];
      $form['number'] = [
        '#type' => 'textfield',
        '#title' => '<span class="cost">Номер билета: </span>',
        "#default_value" => $config->get('number'),
        "#placeholder" => $config->get('number'),
      ];
      $form['select'] = [
        '#type' => 'select',
        '#title' => 'Форма оплаты',
        '#options' => [
          'cash' => 'наличные',
          'card' => 'банковская карта',
          'beznal' => 'безналичный расчет',
        ],
        '#default_value' => 1,
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
      $form['#prefix'] = '<br><span class="cost">Все оплачено! </span>';
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
