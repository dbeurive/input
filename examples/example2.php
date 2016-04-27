<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use dbeurive\Input\Specification;
use dbeurive\Input\SpecificationsSet;

// ---------------------------------------------------------------------------------------------------------------------
// Define some validators.
// ---------------------------------------------------------------------------------------------------------------------

$pathValidator = function($inPath) {
    if (file_exists($inPath)) {
        return true;
    }
    return "The file which path is \"$inPath\" does not exist.";
};

$protocolValidator = function($inProtocol) {
    if (1 === preg_match('/^simple|secure|paranoid/i', $inProtocol)) {
        return true;
    }
    return "Invalid protocol name \"$inProtocol\"\n";
};

// ---------------------------------------------------------------------------------------------------------------------
// Describe the inputs.
// ---------------------------------------------------------------------------------------------------------------------

// Input "path" is mandatory, its value can not be null, and it has a special validator.
$optPath = new Specification("path", true, false, $pathValidator);

// Input "force" is not mandatory and its value can be null.
$optForce = new Specification("force", false, true);

// Input "protocol" is mandatory and its value can not be null, and it has a special validator.
$optProtocol = new Specification("protocol");
$optProtocol->setMandatory() 
    ->setCanNotBeNull()
    ->setValidator($protocolValidator);

// ---------------------------------------------------------------------------------------------------------------------
// Create the set of options.
// ---------------------------------------------------------------------------------------------------------------------

$specificationsSet = new SpecificationsSet();
$specificationsSet->addInputSpecification($optPath)
       ->addInputSpecification($optForce)
       ->addInputSpecification($optProtocol);

// ---------------------------------------------------------------------------------------------------------------------
// Test a good configuration.
// ---------------------------------------------------------------------------------------------------------------------

$goodInputsSet = [
    'path'     => '/bin/ls',
    'force'    => 1,
    'protocol' => 'simple'
];

if (true === $specificationsSet->check($goodInputsSet)) {
    print "TEST 1: The configuration is OK\n\n";
}

// ---------------------------------------------------------------------------------------------------------------------
// Test a bad configuration.
// ---------------------------------------------------------------------------------------------------------------------

$badInputsSet = [
    'path'     => '/bin/ls',
    'protocol' => 'very strange'
];

$errors = $specificationsSet->check($badInputsSet);
if (true !== $errors) {
    print "TEST 2: The configuration is not OK\n\n";
    print_r($errors);
}

// ---------------------------------------------------------------------------------------------------------------------
// Use a final validator.
// ---------------------------------------------------------------------------------------------------------------------

$validator = function($inInputs) {
    if (($inInputs['path'] == "/tmp/secret") && ($inInputs['protocol'] == "simple")) {
        return [ "The protocol <simple> is not safe for this kind of data!" ];
    }
    return true;
};

$specificationsSet->setValidator($validator);

$goodInputsSet = [
    'path'     => '/bin/ls',
    'force'    => 1,
    'protocol' => 'simple'
];

if (true === $specificationsSet->check($goodInputsSet)) {
    print "TEST 3: The configuration is OK\n\n";
}

touch("/tmp/secret");
$badInputsSet = [
    'path'     => '/tmp/secret',
    'protocol' => 'simple'
];

$errors = $specificationsSet->check($badInputsSet);
if (true !== $errors) {
    print "TEST 4: The configuration is not OK\n\n";
    print_r($errors);
}

