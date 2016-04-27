<?php

use dbeurive\Input\Specification;
use dbeurive\Input\SpecificationsSet;


class ConfigTest extends PHPUnit_Framework_TestCase
{
    /** @var Specification */
    private $__inputA;
    /** @var Specification */
    private $__inputB;
    /** @var Specification */
    private $__inputC;
    /** @var Specification */
    private $__inputD;
    /** @var Specification */
    private $__inputV1;
    /** @var Specification */
    private $__inputV2;
    /** @var SpecificationsSet */
    private $__set;
    /** @var callable */
    private $__validator1;
    /** @var callable */
    private $__validator2;



    protected function setUp() {

        $validatorV1 = function ($inValue) {
            if (1 === preg_match('/^A|B|C$/', $inValue)) {
                return true;
            }
            return "The given value <$inValue> is not valid.";
        };

        $validatorV2 = function($inValue) {
            if (1 === preg_match('/^A|B|C$/', $inValue)) {
                return true;
            }
            return "The given value <$inValue> is not valid.";
        };

        $this->__validator1 = $validatorV1;
        $this->__validator2 = $validatorV2;

        $this->__inputA  = new Specification("A");  // Is not mandatory, may be null, no validator.
        $this->__inputB  = new Specification("B");  // Is not mandatory, must not be null, no validator.
        $this->__inputV1 = new Specification("V1"); // Is not mandatory, must not be null, validator.

        $this->__inputC  = new Specification("C");  // Is mandatory, may be null, no validator.
        $this->__inputD  = new Specification("D");  // Is mandatory, must not be null, no validator.
        $this->__inputV2 = new Specification("V2"); // Is mandatory, must not be null, validator.

        $this->__inputA->setMandatory(false)
            ->setCanBeNull();

        $this->__inputB->setMandatory(false)
            ->setCanNotBeNull();

        $this->__inputV1->setMandatory(false)
            ->setCanNotBeNull()
            ->setValidator($validatorV1);



        $this->__inputC->setMandatory(true)
            ->setCanBeNull();

        $this->__inputD->setMandatory(true)
            ->setCanNotBeNull();

        $this->__inputV2->setMandatory(true)
            ->setCanNotBeNull()
            ->setValidator($validatorV2);

        $this->__set = new SpecificationsSet();
        $this->__set->addInputSpecification($this->__inputA)
            ->addInputSpecification($this->__inputB)
            ->addInputSpecification($this->__inputC)
            ->addInputSpecification($this->__inputD)
            ->addInputSpecification($this->__inputV1)
            ->addInputSpecification($this->__inputV2);
    }

    function testMandatory() {
        $this->assertTrue($this->__inputC->getMandatory());
        $this->assertTrue($this->__inputD->getMandatory());
        $this->assertTrue($this->__inputV2->getMandatory());
        $this->assertFalse($this->__inputA->getMandatory());
        $this->assertFalse($this->__inputB->getMandatory());
        $this->assertFalse($this->__inputV1->getMandatory());
    }

    function testNull() {
        $this->assertTrue($this->__inputA->getCanBeNull());
        $this->assertTrue($this->__inputC->getCanBeNull());
        $this->assertFalse($this->__inputB->getCanBeNull());
        $this->assertFalse($this->__inputD->getCanBeNull());
        $this->assertFalse($this->__inputV1->getCanBeNull());
        $this->assertFalse($this->__inputV2->getCanBeNull());
    }

    function testHastValidator() {
        $this->assertFalse($this->__inputA->hasValidator());
        $this->assertFalse($this->__inputB->hasValidator());
        $this->assertFalse($this->__inputC->hasValidator());
        $this->assertFalse($this->__inputD->hasValidator());
        $this->assertTrue($this->__inputV1->hasValidator());
        $this->assertTrue($this->__inputV2->hasValidator());
    }

    function testGetValidator() {
        $this->assertSame($this->__inputV1->getValidator(), $this->__validator1);
        $this->assertSame($this->__inputV2->getValidator(), $this->__validator2);
    }

    function testGetSpecificationFor() {
        $this->assertSame($this->__inputA, $this->__set->getInputSpecificationFor('A'));
        $this->assertSame($this->__inputB, $this->__set->getInputSpecificationFor('B'));
        $this->assertSame($this->__inputC, $this->__set->getInputSpecificationFor('C'));
        $this->assertSame($this->__inputD, $this->__set->getInputSpecificationFor('D'));
        $this->assertSame($this->__inputV1, $this->__set->getInputSpecificationFor('V1'));
        $this->assertSame($this->__inputV2, $this->__set->getInputSpecificationFor('V2'));
    }

    function testCheckOptionsList() {

        // A:  Is not mandatory, may be null, no validator.
        // B:  Is not mandatory, must not be null, no validator.
        // V1: Is not mandatory, must not be null, validator.

        // C:  Is mandatory, may be null, no validator.
        // D:  Is mandatory, must not be null, no validator.
        // V2: Is mandatory, must not be null, validator.

        // -------------------------------------------------------------------------------------------------------------
        // Test without validator.
        // -------------------------------------------------------------------------------------------------------------

        // A, B and V1 are not mandatory.
        $list = ['C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list));

        // A may be null.
        // B and V1 are not mandatory.
        $list = ['A' => null, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list));
        $list = ['A' => 1, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list));

        // A may be null.
        // B must not be null.
        $list = ['A' => null, 'B' => 1, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list)); // V1 is not mandatory.
        $list = ['A' => null, 'B' => null, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertCount(1, $this->__set->check($list)['inputs']); // B is not valid.

        // A may be null.
        // B must not be null.
        // V1 must be "A|B|C".
        $list = ['A' => null, 'B' => 1, 'V1' => 'B', 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list));
        $list = ['A' => null, 'B' => 1, 'V1' => 'D', 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertCount(1, $this->__set->check($list)['inputs']); // V1 is not valid
        $list = ['A' => null, 'B' => null, 'V1' => 'D', 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertCount(2, $this->__set->check($list)['inputs']); // V1 and B are not valid

        // C is mandatory.
        // D is mandatory.
        // V2 is mandatory.
        $list = ['A' => null, 'B' => 1, 'V1' => 'B'];
        $this->assertCount(3, $this->__set->check($list)['inputs']); // C, D, V2 are not valid.

        // C is mandatory.
        // D must not be null.
        // V2 is mandatory.
        $list = ['A' => null, 'B' => 1, 'V1' => 'B', 'D' => null];
        $this->assertCount(3, $this->__set->check($list)['inputs']); // C, D, V2 are not valid.

        // -------------------------------------------------------------------------------------------------------------
        // Test with validator.
        // -------------------------------------------------------------------------------------------------------------

        $validator = function($inInputs) {
            if (array_key_exists('A', $inInputs) && array_key_exists('B', $inInputs)) {
                if (is_int($inInputs['A']) && is_int($inInputs['B'])) {
                    return true;
                }
                return [ "A and B must be integers!" ];
            }
            return true;
        };

        $this->__set->setValidator($validator);

        // A may be null.
        // B must not be null.
        // But, if A and B are simultaneously defined, then they must be integers => check will fail.
        $list = ['A' => null, 'B' => 1, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertCount(2, $this->__set->check($list));
        $this->assertArrayHasKey('inputs', $this->__set->check($list));
        $this->assertArrayHasKey('global', $this->__set->check($list));
        $this->assertCount(0, $this->__set->check($list)['inputs']); // No error on parameters.
        $this->assertCount(1, $this->__set->check($list)['global']); // But the final test fails.

        // A may be null.
        // B must not be null.
        // A and B are integers => the check will succeed.
        $list = ['A' => 10, 'B' => 1, 'C' => null, 'D' => 10, 'V2' => 'A'];
        $this->assertTrue($this->__set->check($list));
    }
}