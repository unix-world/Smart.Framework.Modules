<?php

namespace SVG\Rasterization\Renderers;

use SVG\SVG;
use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw referenced images (from <image> tags).
 *
 * Options:
 * - string href: the image URI
 * - float x: the x coordinate of the upper left corner
 * - float y: the y coordinate of the upper left corner
 * - float width: the width
 * - float height: the height
 */
class ImageRenderer extends Renderer
{
    /**
     * @inheritdoc
     */
    public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context)
    {
        $href   = $options['href'];
        $x      = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $y      = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $width  = self::prepareLengthX($options['width'], $rasterizer);
        $height = self::prepareLengthY($options['height'], $rasterizer);

        $image = $rasterizer->getImage();

        $img = $this->loadImage($href, $width, $height);
    //  if (!empty($img) && is_resource($img)) {
        if (!empty($img) && (is_resource($img) OR (is_a($img, '\\GdImage')))) { // fix by unixman for PHP8
            imagecopyresampled(
                $image,         // dst
                $img,           // src
                $x,             // dst_x
                $y,             // dst_y
                0,              // src_x
                0,              // src_y
                $width,         // dst_w
                $height,        // dst_h
                imagesx($img),  // src_w
                imagesy($img)   // src_h
            );
        }
    }

    /**
     * Loads the image locatable via the given HREF and creates a GD resource
     * for it.
     *
     * This method supports data URIs, as well as SVG files (they are rasterized
     * through this very library). As such, the dimensions given are the
     * dimensions the rasterized SVG would have.
     *
     * @param string $href The image URI.
     * @param int    $w    The width that the rasterized image should have.
     * @param int    $h    The height that the rasterized image should have.
     *
     * @return resource The loaded image.
     */
    private function loadImage($href, $w, $h)
    {
        //-- fix by unixman
        $content = $this->loadImageContent($href);
        if(!$content) {
            return null;
        }
        //-- #fix

        if (strpos($content, '<svg') !== false && strrpos($content, '</svg>') !== false) {
            $svg = SVG::fromString($content);
            return $svg->toRasterImage($w, $h);
        }

        return imagecreatefromstring($content);
    }

    /**
     * Loads the data of an image locatable via the given HREF into a string.
     *
     * @param string $href The image URI.
     *
     * @return string The image content.
     */
    private function loadImageContent($href)
    {

        //-- security fix by unixman: file_get_contents() is not safe as the SVG MUST NOT be able to access local files but only URLs or Data URLs as in this case !!!
        /*
        $dataPrefix = 'data:';

        // check if $href is data URI
        if (substr($href, 0, strlen($dataPrefix)) === $dataPrefix) {
            $commaPos = strpos($href, ',');
            $metadata = substr($href, 0, $commaPos);
            $content  = substr($href, $commaPos + 1);

            if (strpos($metadata, ';base64') !== false) {
                $content = base64_decode($content);
            }

            return $content;
        }

        return file_get_contents($href);
        */

        if((string)trim((string)$href) == '') {
            return false; // be consistent with file_get_contents()
        }
        $arr = (array) \SmartRobot::load_url_img_content((string)$href); // works with dataURL and also URL or local files via URL ; force load image using HTTP browser and even if this is a file browse it via local URL
        if((int)$arr['result'] != 1) {
            return false; // be consistent with file_get_contents()
        }
        if((int)$arr['code'] != 200) {
            return false; // be consistent with file_get_contents()
        }
        return ($arr['content'] ? (string)$arr['content'] : false); // be consistent with file_get_contents()
        //-- #fix
    }
}
