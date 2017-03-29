<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 01:55
 */

namespace JFernando\PHPUtils\Converter;


use Doctrine\Common\Proxy\Proxy;
use JFernando\PHPUtils\Converter\Annotation\Avoid;
use JFernando\PHPUtils\Converter\Annotation\Parser;
use JFernando\PHPUtils\Converter\Exception\InvalidArrayMapException;
use JFernando\PHPUtils\Converter\Exception\InvalidClassException;
use JFernando\PHPUtils\Converter\Exception\InvalidObjectException;
use JFernando\PHPUtils\Reflection\Property;
use JFernando\PHPUtils\Reflection\Reflection;

class Object2Array
{

    /** @var bool */
    protected $byGet;

    /** @var bool */
    protected $byConstructor;

    public function __construct($byGet = true, $byConstructor = true) {
        $this->byGet = $byGet;
        $this->byConstructor = $byConstructor;
    }

    public function toArray($entity, $params = []){
        if(!is_object($entity)){
            throw new InvalidObjectException();
        }

        return $this->objToArray($entity, [], [], $params);
    }

    private function objToArray( $entity, array $array, array $stack, array $params  = []) {
        $class = new Reflection(get_class($entity), $this->byGet);

        if($entity instanceof \stdClass) {
            return $this->evaluate((array) $entity, [], []);
        }

        if ($entity instanceof Proxy) {
            $entity->__load();
            $class = $class->getParentClass();
        }

        $stack[] = $entity;

        foreach ( $class->getProperties() as $property) {
            if($property->getAnnotation(Avoid::class)) continue;

            $parserAnnotation = $property->getAnnotation(Parser::class);
            if($parserAnnotation) {
                $array = $this->parse( $entity, $property, $parserAnnotation, $array, $stack, $params );
                continue;
            }

            $value = $this->evaluate($property->getValue($entity), $stack, $params);
            if($value !== null){
                $array[$property->getName()] = $value;
            }
        }

        return $array;
    }

    private function evaluate($value, array $stack, array $params = []){

        if(is_object($value)){
            if(method_exists($value, 'toArray')){
                $value = $value->toArray();
            }

            if($value instanceof \DateTime){
                $value = $value->format('Y-m-d');
            }
        }

        if(is_object($value)) {
            if(in_array($value, $stack)){
                return null;
            }
            return $this->objToArray($value, [], $stack, $params);
        }

        if(is_array($value)){
            $list = [];
            foreach ( $value as $key => $item ) {
                $list[$key] = $this->evaluate($item, $stack, $params);
            }

            return $list;
        }

        return $value;
    }

    private function parse($entity, Property $property, Parser $parserAnnotation, array $array, array $stack, array $params = []) {
        $name = $property->getName();
        $value = $property->getValue($entity);

        if($value === null) return $array;

        if($parserAnnotation->name){
            $name = $parserAnnotation->name;
        }

        if($parserAnnotation->converter){
            $converterClass = new Reflection($parserAnnotation->converter);
            /** @var Converter $converter */
            $converter = $converterClass->newInstanceWithoutConstructor();
            $array[$name] = $converter->toArray($value, $params);
            return $array;
        }

        $array[$name] = $this->evaluate($value, $stack, $params);

        return $array;
    }




    public function toObject($class, array $array, array $params = []){
        if(!$this->isAssocArray($array)){
            throw new InvalidArrayMapException();
        }

        try{
            $class = new Reflection($class);
        }catch(\Exception $ex){
            throw new InvalidClassException('Invalid class', 0, $ex);
        }

        $instance = $this->newInstance($class);

        return $this->arrToObj($instance, $array, $class, $params);
    }

    private function arrToObj($entity, array $value, Reflection $class, $params = []){
        foreach ( $class->getProperties() as $property ) {
            /** @var Parser $parserAnnot */
            $name = $property->getName();
            $parserAnnot = $property->getAnnotation(Parser::class);

            if($parserAnnot){
                if($parserAnnot->name){
                    $name = $parserAnnot->name;
                }

                if(array_key_exists($name, $value)){
                    if(is_array($value)){
                        $parsed = $this->parserArray($value[$name], $parserAnnot, $params);
                        $property->setValue($entity, $parsed);
                        continue;
                    }
                    $property->setValue($entity, $value);

                }
                continue;
            }

            if(array_key_exists($name, $value)){
                $property->setValue($entity, $value[$name]);
            }
        }

        return $entity;
    }

    private function parserArray($value, Parser $parserAnnot, array $params = []){

        if($parserAnnot->converter){
            $converterClass = new Reflection($parserAnnot->converter);
            /** @var Converter $converter */
            $converter = $converterClass->newInstanceWithoutConstructor();
            return $converter->toObject($value, $params);
        }

        if($parserAnnot->class){
            $parserClass = new Reflection($parserAnnot->class);

            if(count($value) === 0){
                return $this->newInstance($parserClass);
            }

            if(!$this->isAssocArray($value)){
                $return = [];
                foreach ( $value as $item ) {
                    $instance = $this->newInstance($parserClass);
                    $return[] = $this->arrToObj($instance, $item, $parserClass, $params);
                }

                return $return;
            }

            $instance = $this->newInstance($parserClass);
            return $this->arrToObj($instance, $value, $parserClass, $params);
        }

        return $value;
    }

    private function newInstance(Reflection $class){
        if($this->byConstructor){
            return $class->newInstance();
        }

        return $class->newInstanceWithoutConstructor();
    }



    private function isAssocArray($array)
    {
        if(!is_array($array)) return false;

        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

}
