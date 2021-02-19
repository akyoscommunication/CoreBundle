<?php

namespace Akyos\CoreBundle\Twig;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FeatureExtension extends AbstractExtension
{
	public function getFilters(): array
	{
		return [
			// If your filter generates SAFE HTML, you should add a third
			// parameter: ['is_safe' => ['html']]
			// Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
//            new TwigFilter('filter_name', [$this, 'doSomething']),
		];
	}
	
	public function getFunctions(): array
	{
		return [
			new TwigFunction('blackOrWhite', [$this, 'blackOrWhite']),
		];
	}
	
	/**
	 * Given a HEX string returns a RGB array equivalent.
	 *
	 * @param string $color
	 *
	 * @return string
	 * @throws Exception
	 */
	public function blackOrWhite($color)
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
	private static function _checkHex($hex)
	{
		// Strip # sign is present
		$color = str_replace("#", "", $hex);
		// Make sure it's 6 digits
		if (strlen($color) == 3) {
			$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		} else if (strlen($color) != 6) {
			throw new Exception("HEX color needs to be 6 or 3 digits long, received: " . $color);
		}
		return $color;
	}
}
