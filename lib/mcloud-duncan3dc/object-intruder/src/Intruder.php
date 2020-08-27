<?php

namespace MediaCloud\Vendor\duncan3dc\ObjectIntruder;

class Intruder
{
    /**
     * @var object $instance The object to intrude.
     */
    private $_intruderInstance;

    /**
     * @var \ReflectionClass $reflection The reflected instance.
     */
    private $_intruderReflection;


    /**
     * Create a new instance.
     *
     * @param object $instance The object to intrude.
     */
    public function __construct($instance)
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException("Only objects can be intruded");
        }

        $this->_intruderInstance = $instance;
    }


    private function getInstance()
    {
        return $this->_intruderInstance;
    }


    private function getReflection()
    {
        if ($this->_intruderReflection === null) {
            $this->_intruderReflection = new \ReflectionClass($this->_intruderInstance);
        }

        return $this->_intruderReflection;
    }


    /**
     * Go hunting for a property up the class hierarchy.
     *
     * @param string $name The name of the property we're looking for
     *
     * @return \ReflectionProperty
     */
    private function getProperty($name)
    {
        $class = $this->getReflection();

        # See if the literal class has this property
        if ($class->hasProperty($name)) {
            return $class->getProperty($name);
        }

        # If not, does it come from a trait?
        foreach ($class->getTraits() as $trait) {
            if ($trait->hasProperty($name)) {
                return $trait->getProperty($name);
            }
        }

        # How about a parent class?
        $parent = $class;
        while (true) {
            $parent = $parent->getParentClass();
            if (!$parent) {
                break;
            }

            if ($parent->hasProperty($name)) {
                return $parent->getProperty($name);
            }
        }

        # We didn't find the property, but use this to give a sensible error
        return $class->getProperty($name);
    }


    public function __get($name)
    {
        $property = $this->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($this->getInstance());
    }


    public function __set($name, $value)
    {
        $property = $this->getProperty($name);
        $property->setAccessible(true);
        return $property->setValue($this->getInstance(), $value);
    }


    public function __call($name, array $arguments)
    {
        return $this->_call($name, ...$arguments);
    }


    /**
     * Allow methods with references to be called.
     *
     * @param string The name of the method to call
     * @param mixed Any parameters the method accepts
     *
     * @return mixed
     */
    public function _call($name, &...$arguments)
    {
        $method = $this->getReflection()->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($this->getInstance(), $arguments);
    }


    public function __toString()
    {
        return (string) $this->getInstance();
    }
}
