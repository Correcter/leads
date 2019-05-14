<?php

namespace SocialBundle\Traits;

/**
 * @author Vitaly Dergunov
 */
trait FacebookTrait
{
    /**
     * @param $string
     *
     * @return mixed
     */
    public static function removeEmoji($string)
    {
        $regex =
            '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
            '|[\x00-\x7F][\x80-\xBF]+'.
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S';

        return preg_replace($regex, '', $string);
    }
}
