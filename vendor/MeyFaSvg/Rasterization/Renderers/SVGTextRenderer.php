<?php
namespace SVG\Rasterization\Renderers;

// includes fixes by unixman

use SVG\Rasterization\SVGRasterizer;

class SVGTextRenderer extends SVGRenderer
{
	protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
	{
		return array(
			'x'         => self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX(),
			'y'         => self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY(),
			'size'      => self::prepareLengthY($options['size'], $rasterizer),
			'font_path' => $options['font_path'],
			'text'      => $options['text'],
		);
	}

	protected function renderFill($image, array $params, $color)
	{
		imagettftext(
			$image,
			$params['size'],
			0,
			$params['x'],
			$params['y'],
			$color,
			realpath($params['font_path']), // unixman fix: on windows, PHP 7+ GD needs real path for TTF Fonts
			$params['text']
		);
	}

	protected function renderStroke($image, array $params, $color, $strokeWidth)
	{
		$x = $params['x'];
		$y = $params['y'];
		$px = $strokeWidth;

		for ($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++) {
			for ($c2 = ($y - abs($px)); $c2 <= ($y + abs($px)); $c2++) {
				imagettftext($image, $params['size'], 0, $c1, $c2, $color, realpath($params['font_path']), $params['text']); // unixman fix: on windows, PHP 7+ GD needs real path for TTF Fonts
			}
		}
	}
}
