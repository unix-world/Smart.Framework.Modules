<?php

namespace SVG\Nodes\Structures;

use RuntimeException;

/**
 * Class SVGFont
 * @package SVG\Nodes\Structures
 */
class SVGFont extends SVGStyle
{
    /**
     * Font name
     *
     * @var string
     */
    private $name;

    /**
     * Absolute path to font file
     *
     * @var string
     */
    private $path;

    /**
     * @param string      $name
     * @param string      $path
     * @param bool        $embed     Embed this font file directly in the SVG?
     * @param string|null $mimeType  The MIME-Type of the font file (only needed for embedding a font into the SVG)
     */
    public function __construct($name, $path, $embed = false, $mimeType = null)
    {
        parent::__construct(
            sprintf(
                "@font-face {font-family: %s; src:url('%s');}",
                $name,
                self::resolveFontUrl($path, $embed, $mimeType)
            )
        );

        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Return font absolute path
     *
     * @return mixed
     */
    public function getFontPath()
    {
        return $this->path;
    }

    /**
     * Return font name
     *
     * @return string
     */
    public function getFontName()
    {
        return $this->name;
    }

    private static function resolveFontUrl($path, $embed, $mimeType)
    {
        if (!$embed) {
            return $path;
        }
        //-- security fix by unixman: file_get_contents() is not safe as the SVG MUST NOT be able to access local files but only URLs or Data URLs as in this case !!!
    //  $data = file_get_contents($path);
        //--
        $data = false;
        if((string)trim((string)$path) != '') {
            $arr = (array) \SmartRobot::load_url_content((string)$path); // force load file using HTTP browser and even if this is a file browse it via local URL
            if((int)$arr['result'] == 1) {
                if((int)$arr['code'] == 200) {
                    $data = ($arr['content'] ? (string)$arr['content'] : false); // be consistent with file_get_contents()
                }
            }
        }
        //-- #fix
        if($data === false) {
            throw new RuntimeException('Font file "' . $path . '" could not be read.');
        }

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($data));
    }
}
