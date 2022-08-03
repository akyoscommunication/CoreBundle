<?php

namespace Akyos\CoreBundle\Twig;

use Exception;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FeatureExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('blackOrWhite', [$this, 'blackOrWhite']),];
    }

    /**
     * Given a HEX string returns a RGB array equivalent.
     * @param string $color
     * @return string
     * @throws Exception
     */
    public function blackOrWhite(string $color): string
    {
        $color = self::_checkHex($color);
        // Convert HEX to DEC
        $R = hexdec($color[0] . $color[1]);
        $G = hexdec($color[2] . $color[3]);
        $B = hexdec($color[4] . $color[5]);
        $RGB['R'] = $R;
        $RGB['G'] = $G;
        $RGB['B'] = $B;
        $Y = 0.2126 * $R + 0.7152 * $G + 0.0722 * $B;

        return $Y < 128 ? '#fff' : '#000';
    }

    /**
     * You need to check if you were given a good hex string
     * @param string $hex
     * @return string Color
     * @throws Exception "Bad color format"
     */
    private static function _checkHex(string $hex): string
    {
        // Strip # sign is present
        $color = str_replace("#", "", $hex);
        // Make sure it's 6 digits
        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        } elseif (strlen($color) !== 6) {
            throw new RuntimeException("HEX color needs to be 6 or 3 digits long, received: " . $color);
        }
        return $color;
    }
}
