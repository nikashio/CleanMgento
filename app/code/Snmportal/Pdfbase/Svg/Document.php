<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien M�nager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Snmportal\Pdfbase\Svg;

use Snmportal\Pdfbase\Svg\Surface\SurfaceInterface;
use Snmportal\Pdfbase\Svg\Tag\AbstractTag;
use Snmportal\Pdfbase\Svg\Tag\Anchor;
use Snmportal\Pdfbase\Svg\Tag\Circle;
use Snmportal\Pdfbase\Svg\Tag\Ellipse;
use Snmportal\Pdfbase\Svg\Tag\Group;
use Snmportal\Pdfbase\Svg\Tag\ClipPath;
use Snmportal\Pdfbase\Svg\Tag\Image;
use Snmportal\Pdfbase\Svg\Tag\Line;
use Snmportal\Pdfbase\Svg\Tag\LinearGradient;
use Snmportal\Pdfbase\Svg\Tag\Path;
use Snmportal\Pdfbase\Svg\Tag\Polygon;
use Snmportal\Pdfbase\Svg\Tag\Polyline;
use Snmportal\Pdfbase\Svg\Tag\Rect;
use Snmportal\Pdfbase\Svg\Tag\Stop;
use Snmportal\Pdfbase\Svg\Tag\Text;
use Snmportal\Pdfbase\Svg\Tag\StyleTag;
use Snmportal\Pdfbase\Svg\Tag\UseTag;

class Document extends AbstractTag
{
    protected $filename;
    public $inDefs = false;

    protected $x;
    protected $y;
    protected $width;
    protected $height;

    protected $subPathInit;
    protected $pathBBox;
    protected $viewBox;

    /** @var resource */
    protected $parser;

    /** @var SurfaceInterface */
    protected $surface;

    /** @var AbstractTag[] */
    protected $stack = [];

    /** @var AbstractTag[] */
    protected $defs = [];

    /** @var \Sabberworm\CSS\CSSList\Document[] */
    protected $styleSheets = [];

    public function loadFile($filename)
    {
        $this->filename = $filename;
    }

    protected function initParser()
    {
        $parser = xml_parser_create("utf-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler(
            $parser,
            [$this, "_tagStart"],
            [$this, "_tagEnd"]
        );
        xml_set_character_data_handler(
            $parser,
            [$this, "_charData"]
        );

        return $this->parser = $parser;
    }

    public function __construct()
    {
    }

    /**
     * @return SurfaceInterface
     */
    public function getSurface()
    {
        return $this->surface;
    }

    public function getStack()
    {
        return $this->stack;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getDimensions()
    {
        $rootAttributes = null;

        $parser = xml_parser_create("utf-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler(
            $parser,
            function ($parser, $name, $attributes) use (&$rootAttributes) {
                if ($name === "svg" && $rootAttributes === null) {
                    $attributes = array_change_key_case($attributes, CASE_LOWER);

                    $rootAttributes = $attributes;
                }
            },
            function ($parser, $name) {
            }
        );

        $fp = fopen($this->filename, "r");
        while ($line = fread($fp, 8192)) {
            xml_parse($parser, $line, false);

            if ($rootAttributes !== null) {
                break;
            }
        }

        xml_parser_free($parser);

        return $this->handleSizeAttributes($rootAttributes);
    }

    public function handleSizeAttributes($attributes)
    {
        if ($this->width === null) {
            if (isset($attributes["width"])) {
                $width = Style::convertSize($attributes["width"], 400);
                $this->width  = $width;
            }

            if (isset($attributes["height"])) {
                $height = Style::convertSize($attributes["height"], 300);
                $this->height = $height;
            }

            if (isset($attributes['viewbox'])) {
                $viewBox = preg_split('/[\s,]+/is', trim($attributes['viewbox']));
                if (count($viewBox) == 4) {
                    $this->x = $viewBox[0];
                    $this->y = $viewBox[1];

                    if (!$this->width) {
                        $this->width = $viewBox[2];
                    }
                    if (!$this->height) {
                        $this->height = $viewBox[3];
                    }
                }
            }
        }

        return [
            0        => $this->width,
            1        => $this->height,

            "width"  => $this->width,
            "height" => $this->height,
        ];
    }

    public function getDocument()
    {
        return $this;
    }

    /**
     * Append a style sheet
     *
     * @param \Sabberworm\CSS\CSSList\Document $stylesheet
     */
    public function appendStyleSheet($stylesheet)
    {
        $this->styleSheets[] = $stylesheet;
    }

    /**
     * Get the document style sheets
     *
     * @return \Sabberworm\CSS\CSSList\Document[]
     */
    public function getStyleSheets()
    {
        return $this->styleSheets;
    }

    protected function before($attributes)
    {
        $surface = $this->getSurface();

        $style = new DefaultStyle();
        $style->inherit($this);
        $style->fromAttributes($attributes);

        $this->setStyle($style);

        $surface->setStyle($style);
    }

    public function render(SurfaceInterface $surface)
    {
        $this->inDefs = false;
        $this->surface = $surface;

        $parser = $this->initParser();

        if ($this->x || $this->y) {
            $surface->translate(-$this->x, -$this->y);
        }

        $fp = fopen($this->filename, "r");
        while ($line = fread($fp, 8192)) {
            xml_parse($parser, $line, false);
        }

        xml_parse($parser, "", true);

        xml_parser_free($parser);
    }

    protected function svgOffset($attributes)
    {
        $this->attributes = $attributes;

        $this->handleSizeAttributes($attributes);
    }

    public function getDef($id)
    {
        $id = ltrim($id, "#");

        return isset($this->defs[$id]) ? $this->defs[$id] : null;
    }

    private function _tagStart($parser, $name, $attributes)
    {
        $this->x = 0;
        $this->y = 0;

        $tag = null;

        $attributes = array_change_key_case($attributes, CASE_LOWER);

        switch (strtolower($name)) {
            case 'defs':
                $this->inDefs = true;
                return;

            case 'svg':
                if (count($this->attributes)) {
                    $tag = new Group($this, $name);
                } else {
                    $tag = $this;
                    $this->svgOffset($attributes);
                }
                break;

            case 'path':
                $tag = new Path($this, $name);
                break;

            case 'rect':
                $tag = new Rect($this, $name);
                break;

            case 'circle':
                $tag = new Circle($this, $name);
                break;

            case 'ellipse':
                $tag = new Ellipse($this, $name);
                break;

            case 'image':
                $tag = new Image($this, $name);
                break;

            case 'line':
                $tag = new Line($this, $name);
                break;

            case 'polyline':
                $tag = new Polyline($this, $name);
                break;

            case 'polygon':
                $tag = new Polygon($this, $name);
                break;

            case 'lineargradient':
                $tag = new LinearGradient($this, $name);
                break;

            case 'radialgradient':
                $tag = new LinearGradient($this, $name);
                break;

            case 'stop':
                $tag = new Stop($this, $name);
                break;

            case 'style':
                $tag = new StyleTag($this, $name);
                break;

            case 'a':
                $tag = new Anchor($this, $name);
                break;

            case 'g':
            case 'symbol':
                $tag = new Group($this, $name);
                break;

            case 'clippath':
                $tag = new ClipPath($this, $name);
                break;

            case 'use':
                $tag = new UseTag($this, $name);
                break;

            case 'text':
                $tag = new Text($this, $name);
                break;
        }

        if ($tag) {
            if (isset($attributes["id"])) {
                $this->defs[$attributes["id"]] = $tag;
            } else {
                /** @var AbstractTag $top */
                $top = end($this->stack);
                if ($top && $top != $tag) {
                    $top->children[] = $tag;
                }
            }

            $this->stack[] = $tag;

            $tag->handle($attributes);
        } else {
            //echo "Unknown: '$name'\n";
        }
    }

    function _charData($parser, $data)
    {
        $stack_top = end($this->stack);

        if ($stack_top instanceof Text || $stack_top instanceof StyleTag) {
            $stack_top->appendText($data);
        }
    }

    function _tagEnd($parser, $name)
    {
        /** @var AbstractTag $tag */
        $tag = null;
        switch (strtolower($name)) {
            case 'defs':
                $this->inDefs = false;
                return;

            case 'svg':
            case 'path':
            case 'rect':
            case 'circle':
            case 'ellipse':
            case 'image':
            case 'line':
            case 'polyline':
            case 'polygon':
            case 'radialgradient':
            case 'lineargradient':
            case 'stop':
            case 'style':
            case 'text':
            case 'g':
            case 'symbol':
            case 'clippath':
            case 'use':
            case 'a':
                $tag = array_pop($this->stack);
                break;
        }

        if (!$this->inDefs && $tag) {
            $tag->handleEnd();
        }
    }
}
