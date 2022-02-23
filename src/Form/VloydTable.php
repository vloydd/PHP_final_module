<?php

namespace Drupal\vloyd_final\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Our Table Form Class.
 */
class VloydTable extends FormBase {

  /**
   * Calculates Amount of Rows.
   *
   * @var int
   */
  protected int $row = 1;

  /**
   * Calculates Amount of Rows.
   *
   * @var int
   */
  protected int  $table = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vloydTableForm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id="form-wrapper">';
    $form['#suffix'] = '</div>';
    // Connects Styles.
    $form['#attached']['library'][] = 'vloyd_final/vloyd_final_libraries';
    // Made Just for Fun)).
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<h2 class="form_message_intro">Hello! Now You Can Use this Table to Work with It.</h2>',
    ];
    // Adds a New Row.
    $form['addRow'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#limit_validation_errors' => '',
      '#submit' => ['::addRow'],
      '#ajax' => [
        'wrapper' => 'form-wrapper',
        'callback' => '::setAjax',
      ],
    ];
    // Adds a New Table.
    $form['addTable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#limit_validation_errors' => '',
      '#submit' => ['::addTable'],
      '#ajax' => [
        'wrapper' => 'form-wrapper',
        'callback' => '::setAjax',
      ],
    ];
    // Calls to Function that's Builds Table(-s).
    $this->tablesConfigure($form, $form_state, 0);
    // Submits.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'wrapper' => 'form-wrapper',
      ],
    ];
    return $form;
  }

  /**
   * Ajax Refreshing.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's FormState.
   *
   * @return array
   *   Returns Form.
   */
  public function setAjax(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * Recursive Function Used to Build Rows in Table, and it's Amount.
   *
   * @param array $single_table
   *   Our Table.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's FormState.
   * @param int $rw
   *   It's Row Key(Position).
   * @param int $table_value
   *   Table Position.
   *
   * @return array
   *   Return Rows in Table.
   */
  public function rowsConfigure(array &$single_table, FormStateInterface $form_state, int $rw, int $table_value) {
    // Getting Header of the Table.
    $headerSection = $this->addHeader();
    // Getting Disabled Values (Q-s, Years and YTD).
    $disabled_cells = $this->getDisabledCells();
    // Condition for Exit from Recursion.
    // If there's Any Row.
    if ($rw >= 0) {
      foreach ($headerSection as $key => $value) {
        // Set Params for Every Cell (Step is for Submitting).
        $single_table["row$rw"][$key] = [
          '#type' => 'number',
          '#step' => 0.01,
        ];
        // Setting Year Value Using Default Value.
        $single_table["row$rw"]['year']['#default_value'] = date('Y') - $rw;
        // Checking for Cells That We Don't Need to Be Turned On.
        if (array_key_exists($key, $disabled_cells)) {
          // And We Shut that Cell Down.
          $single_table["row$rw"][$key]['#attributes']['disabled'] = TRUE;
          // Rounding by Precision (Using it in Submit).
          $value = $form_state->getValue(["table$table_value", "row$rw", $key], 0);
          $single_table["row$rw"][$key]['#default_value'] = $value;
        }
      }
      // We Subtract Amount Every Time BC We Going Up->Down.
      $rows_config = $this->rowsConfigure($single_table, $form_state, --$rw, $table_value);
      // Recursion Goes On.
      return $rows_config;
    }
  }

  /**
   * Recursive Function Used to Build Tables, and it's Amount.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   * @param int $tb
   *   It's Table Key(Current Position of Recursive Function).
   *
   * @return array
   *   Return Tables.
   */
  public function tablesConfigure(array &$form, FormStateInterface $form_state, int $tb) {
    // This Time We Move Down->Up.
    // Condition for Exit from Recursion.
    if ($tb < $this->table) {
      // Creating a New Table with Header and Empty Message.
      $form["table$tb"] = [
        '#type' => 'table',
        '#header' => $this->addHeader(),
        '#empty' => $this->t('There is no Data Available(.'),
        '#tree' => TRUE,
      ];
      // Rows Amount to Use it in Recursive Function for Rows.
      $rw = $this->row;
      // Recursive Function for Rows.
      $this->rowsConfigure($form["table$tb"], $form_state, --$rw, $tb);
      // We Add Amount Every Time BC We Going Down->Up.
      $tables_config = $this->tablesConfigure($form, $form_state, ++$tb);
      // Recursion Goes On.
      return $tables_config;
    }
  }

  /**
   * Increases Amount of Tables and Rebuilds Our Form.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   *
   * @return array
   *   Returns Form.
   */
  public function addTable(array &$form, FormStateInterface $form_state): array {
    // Getting Current Amount of Tables and Increase it.
    $this->table++;
    // Sent Out Form to Rebuild.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Increases Amount of Rows and Rebuilds Our Form.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   *
   * @return array
   *   Returns Form.
   */
  public function addRow(array &$form, FormStateInterface $form_state): array {
    // Getting Current Amount of Rows and Increase it.
    $this->row++;
    // Sent Out Form to Rebuild.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Used to Disable Certain Cells in Table.
   *
   * @return array
   *   Returns Array of Disabled Cells.
   */
  public function getDisabledCells(): array {
    // Cells That We Don't Need to be Turned On.
    $disabled_cells = [
      'year' => '',
      'q1' => '',
      'q2' => '',
      'q3' => '',
      'q4' => '',
      'ytd' => '',
    ];
    return $disabled_cells;
  }

  /**
   * Adds Header to Our Table, and Helps Order Cells in Table.
   *
   * @return array
   *   Returns Array of Header Section.
   */
  public function addHeader(): array {
    // It's Our Header.
    $header = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
    return $header;
  }

  /**
   * Checks if the Cell Value is Not Empty.
   *
   * @param string|int|array $value
   *   Cell Value.
   *
   * @return bool
   *   Return true or false.
   */
  public function notEmpty($value): bool {
    // Check if Value isn't Empty.
    // Better than Standard Function empty(), BC '0' here is Not Empty Value.
    return ($value === "0" || $value);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get Everything from All Tables.
    $tables_all = $form_state->getValues();
    // Array that Collects Values Excluding Disabled Cells (Year, Q and YTD),
    // Also Connects Cells from All Rows In One.
    $table_values = [];
    // Array for Checked Values.
    $filled_arr = [];
    // Array for Checked Values (Starting Keys).
    $start_key = [];
    // Array for Checked Values (Ending Keys).
    $end_key = [];
    // Getting No Empty Values.
    for ($tb = 0; $tb < $this->table; $tb++) {
      // Get Values from One Table in One-dimensional Array (All Rows in One).
      $values = $this->getTablesValues($tables_all["table$tb"]);
      // Packing it in Array (Now it's Two-dimensional).
      $table_values[] = $values;
      // Variable for Collecting Amount of No Empty Values.
      $filled_cells = 0;
      // Going Through Array of All Cells in All Rows in One Table.
      for ($rows_values = 0; $rows_values <= count($table_values[$tb]) - 1; $rows_values++) {
        // If There's Different Values in Tables - Knock it Out.
        if ($this->notEmpty($table_values[0][$rows_values]) !== $this->notEmpty($table_values[$tb][$rows_values])) {
          $form_state->setErrorByName($tb, $this->t('Invalid Tables!'));
          break 2;
        }
        // Validation Inside of Table.
        // We're Going Through Two Sides Of Array: Start and End.
        // Start Side: If Cell is Not Empty.
        if ($this->notEmpty($table_values[$tb][$rows_values])) {
          // We Use its Position and Add it in Our New Array of Starting Keys.
          // We Need to Know First Entry in this Array.
          $start_key[$tb][] = $rows_values;
          $filled_cells++;
          // Filling No Empty Values in Array.
          $filled_arr[$tb][$rows_values] = $table_values[$tb][$rows_values];
        }
        // End Side: If Cell is Not Empty.
        if ($this->notEmpty($table_values[$tb][count($table_values[$tb]) - $rows_values])) {
          // We Pack Key in New Array of 'Ending' Keys.
          // We Need to Know First Entry in this Array.
          $end_key[$tb][] = count($table_values[$tb]) - $rows_values;
        }
      }
      // If We Haven't Empty Tables (No Inputs).
      if (count($filled_arr[$tb]) != 0) {
        // We Compare Subtract of Starting and Ending Values
        // with Size of Array of Values.
        // For Example: Start - 6, End - 15, Amount of Values - 10.
        // 15 - 6 == 10 - 1.
        // If Our 'example' has Failed - Knock it Out.
        if ($end_key[$tb][0] - $start_key[$tb][0] != $filled_cells - 1) {
          $form_state->setErrorByName($tb, $this->t('Invalid!'));
          break;
        }
      }
    }
  }

  /**
   * Gets Value from a Single Table.
   *
   * @param array $single_table
   *   A Single Table.
   *
   * @return array
   *   Returns Array - All Rows in One-dimensional Array.
   */
  public function getTablesValues(array $single_table): array {
    // For Storing Values from Cell in Table.
    $table_values = [];
    $disabled_cells = $this->getDisabledCells();
    // Going Through Rows of the Table.
    for ($rw = $this->row - 1; $rw >= 0; $rw--) {
      // Going Through Value of Rows.
      foreach ($single_table["row$rw"] as $key => $value) {
        // If Our Cell is Not Belongs to 'Disabled' section,
        // We Put it in Array of Values.
        if (!array_key_exists($key, $disabled_cells)) {
          $table_values[] = $value;
        }
      }
    }
    return $table_values;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Getting All Values.
    $table_values = $form_state->getValues();
    // Going Through Our Values.
    foreach ($table_values as $table_key => $table) {
      foreach ($table as $row_key => $row) {
        // Setting New Values.
        $q1 = (((int) $row['jan'] + (int) $row['feb'] + (int) $row['mar']) + 1) / 3;
        $q2 = (((int) $row['apr'] + (int) $row['may'] + (int) $row['jun']) + 1) / 3;
        $q3 = (((int) $row['jul'] + (int) $row['aug'] + (int) $row['sep']) + 1) / 3;
        $q4 = (((int) $row['oct'] + (int) $row['nov'] + (int) $row['dec']) + 1) / 3;
        $ytd = (($q1 + $q2 + $q3 + $q4) + 1) / 4;
        // Setting Precision.
        $q1 = round($q1, 2);
        $q2 = round($q2, 2);
        $q3 = round($q3, 2);
        $q4 = round($q4, 2);
        $ytd = round($ytd, 2);
        // Pushing it as Setting Value.
        $form_state->setValue([$table_key, $row_key, 'q1'], $q1);
        $form_state->setValue([$table_key, $row_key, 'q2'], $q2);
        $form_state->setValue([$table_key, $row_key, 'q3'], $q3);
        $form_state->setValue([$table_key, $row_key, 'q4'], $q4);
        $form_state->setValue([$table_key, $row_key, 'ytd'], $ytd);
      }
    }
    $form_state->setRebuild();
    $this->messenger()->addStatus($this->t('Valid'));
  }

}
