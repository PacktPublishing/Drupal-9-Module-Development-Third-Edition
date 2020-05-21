<?php

namespace Drupal\hello_world;

/**
 * Class used to demonstrate a simple Unit test.
 */
class Calculator {

  /**
   * The first value.
   *
   * @var int
   */
  protected $a;

  /**
   * The second value.
   *
   * @var int
   */
  protected $b;

  /**
   * Constructs a Calculator.
   *
   * @param int $a
   *   The first value.
   * @param int $b
   *   The second value.
   */
  public function __construct($a, $b) {
    $this->a = $a;
    $this->b = $b;
  }

  /**
   * Adds the values.
   */
  public function add() {
    return $this->a + $this->b;
  }

  /**
   * Subtracts a from b.
   */
  public function subtract() {
    return $this->a - $this->b;
  }

  /**
   * Multiplies a by b.
   */
  public function multiply() {
    return $this->a * $this->b;
  }

  /**
   * Divides a by b.
   */
  public function divide() {
    return $this->a / $this->b;
  }

}
