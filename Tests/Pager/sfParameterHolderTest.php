<?php

namespace Ideato\SimplePagerBundle\Tests\Pager;

use Ideato\SimplePagerBundle\Pager\sfParameterHolder;

class sfParameterHolderTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->parameter_holder = new sfParameterHolder();
    }

    public function testGetParameter()
    {
        $this->parameter_holder->set('name', 'test');
        $this->assertEquals('test', $this->parameter_holder->get('name'));

        $this->assertEquals(null, $this->parameter_holder->get('no_name', null));

        $this->assertEquals(null, $this->parameter_holder->get('no_name'));
    }

    public function testHasParameter()
    {
        $this->parameter_holder->set('name', 'test');

        $this->assertTrue($this->parameter_holder->has('name'));
        $this->assertFalse($this->parameter_holder->has('no_name'));
    }
}
