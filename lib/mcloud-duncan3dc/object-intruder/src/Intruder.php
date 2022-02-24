<?php

namespace MediaCloud\Vendor\duncan3dc\ObjectIntruder;

class Intruder
{
    private object $_intruderInstance;

    private ?\ReflectionClass $_intruderReflection = null;


    /**
     * @param class-string $className The fully qualified class to instantiate
     * @param mixed ...$args Any arguments to pass to the constructor
     */
    public static function construct(string $className, ...$args): self
    {
        $reflection = new \ReflectionClass($className);
        $instance = $reflection->newInstanceWithoutConstructor();

        $constructor = $reflection->getMethod('__construct');
        $constructor->setAccessible(true);
        $constructor->invoke($instance, ...$args);

        $intruder = new self($instance);
        $intruder->_intruderReflection = $reflection;
        return $intruder;
    }


    /**
     * Create a new instance.
     *
     * @param object $instance The object to intrude.
     */
    public function __construct(object $instance)
    {
        $this->_intruderInstance = $instance;
    }


    /**
     * Get the object we are wrapping.
     *
     * @return object
     */
    private function getInstance(): object
    {
        return $this->_intruderInstance;
    }


    /**
     * Get a reflection class of the object we are wrapping.
     *
     * @return \ReflectionClass
     */
    private function getReflection(): \ReflectionClass
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
    private function getProperty(string $name): \ReflectionProperty
    {
        $class = $this->getReflection();

        # See if the literal class has this property
        if ($class->hasProperty($name)) {
            return $class->getProperty($name);
        }

        # If not this class then check its parents
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


    /**
     * Get the value of a private/protected property.
     *
     * @param string $name The name of the property to get
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $property = $this->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($this->getInstance());
    }


    /**
     * Update the value of private/protected property.
     *
     * @param string $name The name of the property to update
     * @param mixed $value The value to set the property to
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $property = $this->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->getInstance(), $value);
    }


    /**
     * Call a private/protected method.
     *
     * @param string $name The name of the method to call
     * @param array $arguments Any arguments to pass to the method
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->_call($name, ...$arguments);
    }


    /**
     * Allow methods with references to be called.
     *
     * @param string $name The name of the method to call
     * @param array<int, mixed> ...$arguments Any parameters the method accepts
     *
     * @return mixed
     */
    public function _call(string $name, &...$arguments)
    {
        $method = $this->getReflection()->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($this->getInstance(), $arguments);
    }


    /**
     * Get a string representation of the object we're wrapping.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getInstance();
    }
}
