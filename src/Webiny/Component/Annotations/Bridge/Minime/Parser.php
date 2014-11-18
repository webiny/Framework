<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations\Bridge\Minime;

/**
 * This class extends the Minime Parser class and replaces the parseValue method which causes exceptions
 * when parsing annotations that are actually a phpDoc block.
 *
 * @package         Webiny\Component\Annotations\Bridge\Minime
 */

class Parser extends \Minime\Annotations\Parser
{
    public function parseValue($value, $key = null)
    {
        return $value;
    }
}