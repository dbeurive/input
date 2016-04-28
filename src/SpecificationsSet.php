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
     * @var array This array contains the list of errors that result from the validation of each input seen in isolation from the others.
     *      This array associates inputs (array's keys) to errors (array's values).
     */
    private $__errorsOnInputs = [];
    /**
     * @var array This array contains the list of errors that result from the final validation.
     *      This validation checks the global validity of the set of inputs: inputs are checked in relation to the others.
     */
    private $__errorsOnFinalValidation = [];

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
     *        Inputs should be validated in relation to the others.
     *        If all inputs' values are valid individually, then the final validator (if specified) is executed.
     *        The validator signature must be: `array function(array $inputsValues)`.
     *        `$inputsValues`: associative array "input's name" => "input's value".
     *        If the inputs are valid then the function must return an empty array.
     *        Otherwise, the function must return a (non-empty) array of error identifiers.
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
     * @return true If the lists of inputs' values is valid, then the method returns true.
     *         Otherwise, the function returns false.
     */
    public function check(array $inList) {
        $this->__errorsOnFinalValidation = [];
        $this->__errorsOnInputs = [];

        // Validate inputs in isolation to the others.
        /** @var Specification $_specification */
        foreach ($this->__inputsSpecifications as $_name => $_specification) {

            // Make sure that the input is defined, if it is mandatory.
            if ($_specification->getMandatory()) {
                if (! array_key_exists($_name, $inList)) {
                    $this->__errorsOnInputs[$_name] = "The input which name is \"${_name}\" is mandatory!";
                    continue;
                }
            }

            if (! array_key_exists($_name, $inList)) {
                continue;
            }

            $status = $_specification->checkValue($inList[$_name]);
            if (true !== $status) {
                $this->__errorsOnInputs[$_name] = $status;
            }
        }

        // If some inputs are not valid in isolation from the others, then we do not proceed to the final validation.
        if ((count($this->__errorsOnInputs) > 0)) {
            return false;
        }

        // If no final validation is planned, then the validation's status depends on the validation of inputs in isolation from the others
        if (is_null($this->__validator)) {
            return count($this->__errorsOnFinalValidation) == 0;
        }

        // Call the final validator.
        $this->__errorsOnFinalValidation = call_user_func($this->__validator, $inList);
        if (0 == count($this->__errorsOnFinalValidation)) {
            return true;
        }

        return false;
    }

    /**
     * Test if the some inputs are invalid in isolation to the others.
     * @return bool If some inputs are invalid in isolation to the others, then the method returns true.
     *         Otherwise, it returns the value false.
     */
    public function hasErrorsOnInputsInIsolationFromTheOthers() {
        return count($this->__errorsOnInputs) > 0;
    }

    /**
     * Test if the inputs are invalid in relations to the others.
     * @return bool If some inputs are invalid in relation to the others, then the method returns true.
     *         Otherwise, it returns the value false.
     */
    public function hasErrorsOnFinalValidation() {
        return count($this->__errorsOnFinalValidation) > 0;
    }

    /**
     * Return the list of errors detected while validating each input in isolation to the others.
     * @return array The method returns an association between inputs and errors' identifiers.
     *         * Array's keys: inputs' names.
     *         * Array's values: errors' identifiers.
     */
    public function getErrorsOnInputsInIsolationFromTheOthers() {
        return $this->__errorsOnInputs;
    }

    /**
     * Return the list of errors detected while validating inputs in relations to the others.
     * @return array The method returns a list of errors' identifiers.
     */
    public function getErrorsOnFinalValidation() {
        return $this->__errorsOnFinalValidation;
    }

    /**
     * Return a summary of all inputs' specifications.
     * @return array The method returns a summary of all inputs' specifications.
     *         The returned array has the following structure: [<input's name> => <specification>, <input's name> => <specification>...]
     */
    public function inputsSummary() {
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
