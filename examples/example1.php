<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use dbeurive\Input\Specification;
use dbeurive\Input\SpecificationsSet;

// Define a validator for the option.
// Please note that defining a validator for an input is optional.
$pathValidator = function($inPath) {
    if (file_exists($inPath)) {
        return true;
    }
    return "The file which path is \"$inPath\" does not exist.";
};

// We say that:
//   - The name of the input is "Path".
//   - The input is mandatory.
//   - The input can not be null.
//   - The input has a validator.
$pathSpecification = new Specification("Path", true, false, $pathValidator);

// You can also use mutators to specify an input.
// The input named "token" is not mandatory and its value can be null.
// It does not have any specific validator.
$tokenSpecification = new Specification("Token");
$tokenSpecification->setMandatory(false)
    ->setCanBeNull();

// Create a set of specifications:

$set = new SpecificationsSet();
$set->addInputSpecification($pathSpecification)
    ->addInputSpecification($tokenSpecification);

// Note: you may specify a final validator.
// If the file exists, and if a token is specified, then make sure that the token is found in the file.
// If everything is OK, the validator must return true.
// Otherwise, it must return a list of errors' identifiers (you are free to return any kind of values...).
// Note: here we return a list of error messages
$finalValidator = function($inInputs) {
    $data = file_get_contents($inInputs['Path']);
    if (array_key_exists('Token', $inInputs) && (! is_null($inInputs['Token']))) {
        if (false === strstr($data, $inInputs['Token'])) {
            return ["The file " . $inInputs['Path'] . " exists, but it does not contain the token <" . $inInputs['Token'] . "> !"];
        } else {
            return true;
        }
    }
    return true;
};

$set->setValidator($finalValidator);

// Test a set of inputs' values against the set of specifications:

$values = ['Path' => '/tmp/my-file.txt', 'Token' => 'Port'];

$status = $set->check($values);

// Inspect the status.

if (true === $status) {
    echo "The set of inputs' values is valid\n";
} else {
    /** @var array $status */

    // Check for errors in inputs, taken individually.
    if (count($status['inputs']) > 0) {
        echo "Some inputs' values are not valid:\n";
        foreach ($status['inputs'] as $_inputName => $_errorIdentifier) {
            echo "  - $_inputName: $_errorIdentifier\n";
        }

        // We do not check for the status of the final validation since this validation was not performed.
    } else {
        echo "All inputs' values are individually valid.\n";

        // This means that the final validation failed !
        echo "But the final validation failed:\n";
        foreach ($status['errors'] as $_index => $_errorIdentifier) {
            // Here, we returned strings (error messages)... but you can return whatever objects you want...
            echo "  - $_errorIdentifier\n";
        }
    }
}
