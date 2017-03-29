<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 02:06
 */

namespace PHPUtils\Test\Converter;


use JFernando\PHPUtils\Converter\Object2Array;
use PHPUnit\Framework\TestCase;

class Object2ArrayTest extends TestCase
{

    public function testeOtoArray(){
        $ob = new Pessoa();

        $objectArr = new Object2Array();
        $array = $objectArr->toArray($ob);
        $newOb = $objectArr->toObject(Pessoa::class, $array);

        $this->assertEquals($ob, $newOb);

    }

}