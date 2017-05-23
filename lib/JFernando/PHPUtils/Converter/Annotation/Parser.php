<?php
/**
 * Created by PhpStorm.
 * User: lunify
 * Date: 29/03/17
 * Time: 01:49
 */

namespace JFernando\PHPUtils\Converter\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class Parser
 *
 * @package JFernando\PHPUtils\Converter\Annotation
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class Parser extends Annotation
{

    public $name      = false;
    public $converter = false;
    public $class     = false;


    public $arrayWhenEmpty = false;

}