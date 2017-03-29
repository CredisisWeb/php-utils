<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 01:52
 */

namespace JFernando\PHPUtils\Converter;


interface Converter
{
    public function toArray($content, $params = []);
    public function toObject($content, $params = []);
}