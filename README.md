# Description

This package is an implementation of a simple inputs' manager.

Inputs validation is a common task. This process often involves two steps :

* First, we validate each input in isolation to the others.
* Then, if all inputs are individually valid, we validate the set of inputs in relation to the others.

For example, let’s consider the validation of a register form.

* Some fields are « standalone ». This is the case, for example, for the first name. The validity of the entered value does not depend on another field’s value.
* Other fields, however, cannot be validated in isolation to the others. Typically, this is the case, for the « passwords » fields. Often, a register form asks you to confirm a password. Thus, you have two input fields for the password, and the values of these fields must be identical.

This package implements this simple 2 steps validation process.

* First, you specify each input. These specifications will be used to validate each input in isolation to the others.
  A specification contains the following information : what is the input’s name ? Is the input mandatory ?
  Can the input’s value be null ? Does the input need a special validator ?
* Then, you regroup the previously created specifications into a set of specifications.
  You may assign to the specifications’ set a global validator that will be used to validate all entries in relations to the others.
  Please note that this optional validator is executed if all inputs are valid (according to their respective specifications) in isolation to the others. 

> Inputs' specifications are instances of `dbeurive\Input\Specification`.
> A specifications' set is an instance of `dbeurive\Input\SpecificationsSet`. 

At this point, all inputs’ individual validity rules are defined. And an optional global validator that ensures a coherence between inputs’ values is specified.
You can submit a set of inputs' values to the specifications’ set.

# Installation

From the command line:

    composer require dbeurive/input.

From your `composer.json` file:

    {
        "require": {
            "dbeurive/input": "1.0.*"
        }
    }

# API documentation

The detailed documentation of the API can be extracted from the code by using [PhpDocumentor](https://www.phpdoc.org/).
The file `phpdoc.xml` contains the required configuration for `PhpDocumentor`.
To generate the API documentation, just move into the root directory of this package and run `PhpDocumentor` from this location.

Note:

> Since all the PHP code is documented using PhpDoc annotations, you should be able to exploit the auto completion feature from your favourite IDE.
> If you are using Eclipse, NetBeans or PhPStorm, you probably won’t need to consult the generated API documentation.

## Synopsis

Specify inputs:

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

Create a set of specifications:

    $set = new SpecificationsSet();
    $set->addInputSpecification($pathSpecification)
        ->addInputSpecification($tokenSpecification);
        
    // Print a summary.
                
    foreach ($set->inputsSummary() as $_name => $_summary) {
        echo "$_name => $_summary\n";
    }
    
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

Test a set of inputs' values against the set of specifications:

    $values = ['Path' => '/tmp/my-file.txt', 'Token' => 'Port'];
    
    $status = $set->check($values);

Inspect the status.

    if ($status) {
    
        // Inputs are valid.
        echo "The set of inputs' values is valid\n";
    
    } else {
    
        // Inputs are not valid.
    
        // Check the validity of errors looked in isolation from the others.
        if ($set->hasErrorsOnInputsInIsolationFromTheOthers()) {
    
            echo "Some inputs' values are not valid:\n";
            foreach ($set->getErrorsOnInputsInIsolationFromTheOthers() as $_inputName => $_errorIdentifier) {
                // Here, we returned strings (error messages)... but you can return whatever objects you want...
                echo "  - $_inputName: $_errorIdentifier\n";
            }
    
            exit(0); // The final validator is not executed.
        }
    
        echo "All inputs' values are individually valid.\n";
    
        // This means that the final validation failed !
        echo "But the final validation failed:\n";
        foreach ($set->getErrorsOnFinalValidation() as $_index => $_errorIdentifier) {
            // Here, we returned strings (error messages)... but you can return whatever objects you want...
            echo "  - $_errorIdentifier\n";
        }
    }
    
## Examples

**[example.php](https://github.com/dbeurive/input/blob/master/examples/example.php)**: this example shows how to use the package.



