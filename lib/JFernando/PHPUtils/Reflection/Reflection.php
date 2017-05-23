<?php
/**
 * Created by PhpStorm.
 * User: jfernando
 * Date: 21/10/16
 * Time: 08:28
 */

namespace JFernando\PHPUtils\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Inflector\Inflector;

class Reflection extends \ReflectionClass
{
    private $byGet;
    private $reader;

    public function __construct($argument, $byGet = true)
    {
        parent::__construct($argument);
        $this->byGet = $byGet;
        $this->reader = new AnnotationReader();
    }

    public function setPropertyValue($entity, \ReflectionProperty $field, $value)
    {
        if ($this->byGet) {
            $methodName = 'set' . Inflector::classify($field->getName());
            $class = new \ReflectionClass($entity);

            /** @var \ReflectionMethod $method */
            if($class->hasMethod($methodName)){
                $method = $class->getMethod($methodName);
                $method->invoke($entity, $value);
                return;
            }

            if($field->isPublic()){
                $field->setValue($entity, $value);
            }
            return;
        }

        $field->setAccessible(true);
        $field->setValue($entity, $value);
    }

    public function getPropertyValue($entity, \ReflectionProperty $field)
    {
        if ($this->byGet) {
            $methodName = 'get' . Inflector::classify($field->getName());
            $class = new \ReflectionClass($entity);

            if($class->hasMethod($methodName)){
                /** @var \ReflectionMethod $method */
                $method = $class->getMethod($methodName);

                return $method->invoke($entity);
            }

            if($field->isPublic()){
                return $field->getValue($entity);
            }

            return null;
        }

        $field->setAccessible(true);
        return $field->getValue($entity);
    }

    public function getParentClass()
    {
        $class = parent::getParentClass();
        return new Reflection($class->getName());
    }

    public function getAnnotations()
    {
        return $this->reader->getClassAnnotations($this);
    }

    public function getAnnotation($name)
    {
        return $this->reader->getClassAnnotation($this, $name);
    }

    /**
     * @param null $filter
     * @return Property[]
     */
    public function getProperties($filter = null)
    {
        $return = [];
        foreach (parent::getProperties() as $prop) {
            $return[] = new Property($this->getName(), $prop->getName(), $this->byGet);
        }

        return $return;
    }

    public function getProperty($name)
    {
        return new Property($this->getName(), $name, $this->byGet);
    }

    public function getMethod( $name )
    {
        return new Method($this->getName(), $name);
    }

    /**
     * @param null $filter
     * @return Method[]
     */
    public function getMethods( $filter = null )
    {
        $return = [];
        foreach (parent::getMethods() as $method) {
            $return[] = $this->getMethod($method->getName());
        }

        return $return;
    }
}
