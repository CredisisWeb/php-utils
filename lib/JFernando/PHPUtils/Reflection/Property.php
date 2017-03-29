<?php
/**
 * Created by PhpStorm.
 * User: jfernando
 * Date: 21/10/16
 * Time: 09:51
 */

namespace Credisis\PhpCnab\Utils;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Inflector\Inflector;

class Property extends \ReflectionProperty
{

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var bool
     */
    private $byGet;

    public function __construct($class, $name, $byGet = true)
    {
        parent::__construct($class, $name);
        $this->reader = new AnnotationReader();
        $this->byGet = $byGet;
    }

    public function getAnnotation($nome)
    {
        return $this->reader->getPropertyAnnotation($this, $nome);
    }

    public function getAnnotations()
    {
        return $this->reader->getPropertyAnnotations($this);
    }

    public function getValue($object = null)
    {
        if ($this->byGet) {
            $methodName = 'get' . Inflector::classify($this->getName());
            $class = new \ReflectionClass($object);

            if($class->hasMethod($methodName)){
                /** @var \ReflectionMethod $method */
                $method = $class->getMethod($methodName);
                $method->setAccessible(true);

                return $method->invoke($object);
            }

            if($this->isPublic()){
                return parent::getValue($object);
            }

            return null;
        }

        $this->setAccessible(true);
        return parent::getValue($object);
    }

    public function setValue($object, $value = null)
    {
        if ($this->byGet) {
            $methodName = 'set' . Inflector::classify($this->getName());
            $class = new \ReflectionClass($object);

            /** @var \ReflectionMethod $method */
            if($class->hasMethod($methodName)){
                $method = $class->getMethod($methodName);
                $method->invoke($object, $value);
                return;
            }

            if($this->isPublic()){
                parent::setValue($object, $value);
            }
            return;
        }

        $this->setAccessible(true);
        parent::setValue($object, $value);
    }
}
