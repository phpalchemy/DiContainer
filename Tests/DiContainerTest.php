<?php
/**
 * DiContainer Test
 */
include __DIR__ . '/../DiContainer.php';

class Service{}

class DiContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testWithString()
    {
        $pimple = new DiContainer();
        $pimple['param'] = 'value';

        $this->assertEquals('value', $pimple['param']);
    }

    public function testWithClosure()
    {
        $pimple = new DiContainer();
        $pimple['service'] = function () {
            return new Service();
        };

        $this->assertInstanceOf('Service', $pimple['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $pimple = new DiContainer();
        $pimple['service'] = function () {
            return new Service();
        };

        $serviceOne = $pimple['service'];
        $this->assertInstanceOf('Service', $serviceOne);

        $serviceTwo = $pimple['service'];
        $this->assertInstanceOf('Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $pimple = new DiContainer();
        $pimple['service'] = function () {
            return new Service();
        };
        $pimple['container'] = function ($container) {
            return $container;
        };

        $this->assertNotSame($pimple, $pimple['service']);
        $this->assertSame($pimple, $pimple['container']);
    }

    public function testIsset()
    {
        $pimple = new DiContainer();
        $pimple['param'] = 'value';
        $pimple['service'] = function () {
            return new Service();
        };

        $pimple['null'] = null;

        $this->assertTrue(isset($pimple['param']));
        $this->assertTrue(isset($pimple['service']));
        $this->assertTrue(isset($pimple['null']));
        $this->assertFalse(isset($pimple['non_existent']));
    }

    public function testConstructorInjection ()
    {
        $params = array("param" => "value");
        $pimple = new DiContainer($params);

        $this->assertSame($params['param'], $pimple['param']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $pimple = new DiContainer();
        echo $pimple['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $pimple = new DiContainer();
        $pimple['foo'] = null;
        $this->assertNull($pimple['foo']);
    }

    public function testUnset()
    {
        $pimple = new DiContainer();
        $pimple['param'] = 'value';
        $pimple['service'] = function () {
            return new Service();
        };

        unset($pimple['param'], $pimple['service']);
        $this->assertFalse(isset($pimple['param']));
        $this->assertFalse(isset($pimple['service']));
    }

    public function testShare()
    {
        $pimple = new DiContainer();
        $pimple['shared_service'] = $pimple->share(function () {
            return new Service();
        });

        $serviceOne = $pimple['shared_service'];
        $this->assertInstanceOf('Service', $serviceOne);

        $serviceTwo = $pimple['shared_service'];
        $this->assertInstanceOf('Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    public function testProtect()
    {
        $pimple = new DiContainer();
        $callback = function () { return 'foo'; };
        $pimple['protected'] = $pimple->protect($callback);

        $this->assertSame($callback, $pimple['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $pimple = new DiContainer();
        $pimple['global_function'] = 'strlen';
        $this->assertSame('strlen', $pimple['global_function']);
    }

    public function testRaw()
    {
        $pimple = new DiContainer();
        $pimple['service'] = $definition = function () { return 'foo'; };
        $this->assertSame($definition, $pimple->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $pimple = new DiContainer();
        $pimple['foo'] = null;
        $this->assertNull($pimple->raw('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testRawValidatesKeyIsPresent()
    {
        $pimple = new DiContainer();
        $pimple->raw('foo');
    }

    public function testExtend()
    {
        $pimple = new DiContainer();
        $pimple['shared_service'] = $pimple->share(function () {
            return new Service();
        });

        $value = 12345;

        $pimple->extend('shared_service', function($sharedService) use ($value) {
            $sharedService->value = $value;
            return $sharedService;
        });

        $serviceOne = $pimple['shared_service'];
        $this->assertInstanceOf('Service', $serviceOne);
        $this->assertEquals($value, $serviceOne->value);

        $serviceTwo = $pimple['shared_service'];
        $this->assertInstanceOf('Service', $serviceTwo);
        $this->assertEquals($value, $serviceTwo->value);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testExtendValidatesKeyIsPresent()
    {
        $pimple = new DiContainer();
        $pimple->extend('foo', function () {});
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" does not contain an object definition.
     */
    public function testExtendValidatesKeyYieldsObjectDefinition()
    {
        $pimple = new DiContainer();
        $pimple['foo'] = 123;
        $pimple->extend('foo', function () {});
    }

    public function testKeys()
    {
        $pimple = new DiContainer();
        $pimple['foo'] = 123;
        $pimple['bar'] = 123;

        $this->assertEquals(array('foo', 'bar'), $pimple->keys());
    }
}