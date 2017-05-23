<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 02:05
 */

namespace PHPUtils\Test\Converter;


use JFernando\PHPUtils\Converter\Annotation\Parser;

class Pessoa
{

    public $nome = 'teste';
    public $idade = 12;

    /**
     * @Parser(name="nome2")
     */
    public function getSome(){
        return "Ok";
    }
    //TODO Fazer os testes unitários
}