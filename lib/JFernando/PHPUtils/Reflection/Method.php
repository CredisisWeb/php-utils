<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 01:44
 */

namespace JFernando\PHPUtils\Reflection;


use Doctrine\Common\Annotations\AnnotationReader;

class Method extends \ReflectionMethod
{

    /**
     * @var AnnotationReader
     */
    protected $reader;

    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->reader = new AnnotationReader();
    }

    public function getAnnotation($nome)
    {
        return $this->reader->getMethodAnnotation($this, $nome);
    }

    public function getAnnotations()
    {
        return $this->reader->getMethodAnnotations($this);
    }

}