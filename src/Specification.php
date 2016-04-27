<?php

/**
 * This file implements an input's specification.
 */

namespace dbeurive\Input;

/**
 * Class Specification
 *
 * This class represents an input's specification.
 *
 * @package dbeurive\Input
 */

class Specification {

    /**
     * @var string Input's name.
     */
    private $__name;
    /**
     * @var bool Specify whether the input is mandatory or not.
     */
    private $__isMandatory;
    /**
     * @var bool Specify whether the input's value can be NULL or not.
     */
    private $__isNullAllowed;
    /**
     * @var callable|null Function used to validate the input's value.
     */
    private $__validator = null;

    /**
     * Create a new specification.
     * @param string $inName The input's name.
     * @param bool $inIsMandatory Specify whether the input is mandatory or not.
     * @param bool $inCanBeNull Specify whether the input's value can be null or not.
     * @param callable|null $inValidator Function used to validate the input's value.
     */
    public function __construct($inName, $inIsMandatory=true, $inCanBeNull=true, callable $inValidator=null) {
        $this->__name          = $inName;
        $this->__isMandatory   = $inIsMandatory;
        $this->__isNullAllowed = $inCanBeNull;
        $this->__validator     = $inValidator;
    }

    /**
     * Set the input's name.
     * @param string $inName This parameter represents the input's name.
     * @return $this
     */
    public function setName($inName) {
        $this->__name = $inName;
        return $this;
    }

    /**
     * Return the input's name.
     * @return string The method returns the input's name.
     */
    public function getName() {
        return $this->__name;
    }

    /**
     * Specify whether the input is mandatory or not.
     * @param bool $inIsMandatory
     *        * The value true means that the input is mandatory.
     *        * The value false means that the input is not mandatory.
     * @return $this
     */
    public function setMandatory($inIsMandatory=true) {
        $this->__isMandatory = $inIsMandatory;
        return $this;
    }

    /**
     * Check whether the input is mandatory or not.
     * @return bool If the input is mandatory, then the method returns true. Otherwise, the method returns false.
     */
    public function getMandatory() {
        return $this->__isMandatory;
    }

    /**
     * Specify that the input's value can be null.
     * @return $this
     */
    public function setCanBeNull() {
        $this->__isNullAllowed = true;
        return $this;
    }

    /**
     * Specify that the input's value can not be null.
     * @return $this
     */
    public function setCanNotBeNull() {
        $this->__isNullAllowed = false;
        return $this;
    }

    /**
     * Test if the input's value can be null.
     * @return bool If the input can be null, then the method returns true. Otherwise, the method returns false.
     */
    public function getCanBeNull() {
        return $this->__isNullAllowed;
    }

    /**
     * Set input's validator.
     * @param callable $inValidator This input represents the input's validator.
     *        The function's signature must be: `mixed|true function($inValue)`.
     *        * If the input's value is valid, then the function must return true.
     *        * Otherwise, the function must return an error identifier.
     *          Error identifiers may be anything you want (string, integer, objects...).
     * @return $this
     */
    public function setValidator(callable $inValidator) {
        $this->__validator = $inValidator;
        return $this;
    }

    /**
     * Get the input's validator.
     * @return callable The method returns the input's validator.
     */
    public function getValidator() {
        return $this->__validator;
    }

    /**
     * Test if the input has a validator.
     * @return bool If the input has a validator, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function hasValidator() {
        return ! is_null($this->__validator);
    }

    /**
     * Return a text that sums up the input's specification.
     * @return string The method returns a text that sums up all the input's specifications.
     */
    public function toString() {
        $constraints = [];

        $constraints[] = $this->getCanBeNull() ? "Can be null." : "Can not be null.";
        $constraints[] = $this->getMandatory() ? "Is mandatory." : "Is not mandatory.";
        $constraints[] = $this->getValidator() ? "Has a validator." : "Does not have a validator.";
        return join(" ", $constraints);
    }

    /**
     * Check a given value.
     * @param mixed $inValue Value to check.
     * @return bool|string If the value is valid relatively to the input's specification, then the method returns the value true.
     *         Otherwise, the method returns a string that represents an error message.
     */
    public function checkValue($inValue) {
        if (is_null($inValue)) {
            if (! $this->__isNullAllowed) {
                return "Option \"{$this->__name}\": The options's value cannot be null.";
            }
        }
        if (! is_null($this->__validator)) {
            $res = call_user_func($this->__validator, $inValue);
            if (true !== $res) {
                return $res;
            }
        }
        return true;
    }
}
