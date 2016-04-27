<?php

/**
 * This file implements a set of inputs' specifications.
 */

namespace dbeurive\Input;

/**
 * Class SpecificationsSet
 *
 * This class represents a set of inputs' specifications.
 *
 * @package dbeurive\Input
 */

class SpecificationsSet implements \Iterator {

    /**
     * @var array Set of inputs' specifications.
     */
    private $__inputsSpecifications = [];  // name => specification
    /**
     * @var null|callable Final validator.
     *      This function is executed if all inputs' values are valid, relatively to their associated validators.
     */
    private $__validator = null;

    /**
     * Add an input' specification to the set of specifications.
     * @param Specification $inSpecification Input's specification to add.
     * @return $this
     */
    public function addInputSpecification(Specification $inSpecification) {
        $this->__inputsSpecifications[$inSpecification->getName()] = $inSpecification;
        return $this;
    }

    /**
     * Get the specification for a given input identified by its name.
     * @param string $inInputName Input's name.
     * @return Specification The method returns the input's specification.
     * @throws \Exception
     */
    public function getInputSpecificationFor($inInputName) {
        if (! array_key_exists($inInputName, $this->__inputsSpecifications)) {
            throw new \Exception("The input which name is \"${inInputName}\" is not specified!");
        }
        return $this->__inputsSpecifications[$inInputName];
    }

    /**
     * Set the final validator.
     * @param callable $inValidator Function used to perform a final validation.
     *        If all inputs' values are valid individually, then a final validator is executed.
     *        The validator signature must be: `array|true function(array $inputsValues)`.
     *        `$inputsValues`: associative array "input's name" => "input's value".
     *        If the inputs are valid then the function must return true.
     *        Otherwise, the function must return an array of error identifiers.
     *        Error identifiers may be anything you want (string, integer, objects...).
     * @return $this
     */
    public function setValidator(callable $inValidator) {
        $this->__validator = $inValidator;
        return $this;
    }

    /**
     * Return the final validator.
     * @return callable|null The method returns the final validator.
     */
    public function getValidator() {
        return $this->__validator;
    }

    /**
     * This method checks a given set of inputs' values against the inputs' specifications.
     * @param array $inList The set of inputs' values to check.
     * @return array|true If the lists of inputs' values is valid, then the method returns true.
     *         Otherwise, the function returns an associative array.
     *         The returned associative array has the following structure (please, see the provided examples):
     *         [
     *             'inputs' => [ <input's name> => <error message>,
     *                           <input's name> => <error message>,
     *                               ... ],
     *             'errors' => [ <error identifier>, <error identifier> ]
     *         ]
     */
    public function check(array $inList) {
        $inputsErrors = [];

        /** @var Specification $_specification */
        foreach ($this->__inputsSpecifications as $_name => $_specification) {

            // Make sure that the input is defined, if it is mandatory.
            if ($_specification->getMandatory()) {
                if (! array_key_exists($_name, $inList)) {
                    $inputsErrors[$_name] = "The input which name is \"${_name}\" is mandatory!";
                    continue;
                }
            }

            if (! array_key_exists($_name, $inList)) {
                continue;
            }

            $status = $_specification->checkValue($inList[$_name]);
            if (true !== $status) {
                $inputsErrors[$_name] = $status;
            }
        }

        if (is_null($this->__validator)) {
            if (count($inputsErrors) == 0) {
                return true;
            }
            return [ 'inputs' => $inputsErrors, 'errors' => [] ];
        }

        if ((count($inputsErrors) > 0)) {
            return [ 'inputs' => $inputsErrors, 'errors' => [] ];
        }

        // Call the final validator.
        $errors = call_user_func($this->__validator, $inList);
        if (true === $errors) {
            return true;
        }

        return [ 'inputs' => $inputsErrors, 'errors' => $errors ];
    }

    /**
     * Return a summary of all inputs' specifications.
     * @return array The method returns a summary of all inputs' specifications.
     *         The returned array has the following structure: [<input's name> => <specification>, <input's name> => <specification>...]
     */
    public function toString() {
        $res = [];
        /** @var Specification $_s */
        foreach ($this->__inputsSpecifications as $_s) {
            $res[$_s->getName()] = $_s->toString();
        }
        return $res;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Implement the iterator interface.
    // -----------------------------------------------------------------------------------------------------------------

    function rewind() {
        return reset($this->__inputsSpecifications);
    }
    function current() {
        return current($this->__inputsSpecifications);
    }
    function key() {
        return key($this->__inputsSpecifications);
    }
    function next() {
        return next($this->__inputsSpecifications);
    }
    function valid() {
        return key($this->__inputsSpecifications) !== null;
    }
}
