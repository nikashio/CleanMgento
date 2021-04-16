<?php

namespace Snmportal\Pdfprint\Model\Pdf;

//Autoloader::register();
use Snmportal\Pdfbase\Dompdf\Canvas;
use Snmportal\Pdfbase\Dompdf\Dompdf;
use Snmportal\Pdfbase\I18N\Arabic\Glyphs;

class Renderer extends Dompdf
{
    public $_extcanvas;

    public $_extoption;

    public $stringSubsetsText;

    protected $_pdfTemplate;

    protected $_tplIdx;

    protected $_globalCSS = '';

    protected $_styleInfos = [];

    protected $_caller;

    protected $_globalcss;

    /**
     * Renderer constructor.
     *
     * @param null $options
     * @param null $extcanavas
     */
    public function __construct($options = null, $extcanavas = null)
    {
        $this->_extcanvas = $extcanavas;
        $this->_extoption = $options;
        $this->_globalcss = '';
        parent::__construct($options);
    }

    /**
     * @param $v
     *
     * @return mixed
     */
    public static function pt2mm($v)
    {
        return $v * 0.3528;
    }

    /**
     * @param $v
     *
     * @return float
     */
    public static function mm2pt($v)
    {
        $v = (double)$v;

        return $v / 0.3528;
    }

    /**
     * @return string
     */
    public function getStream()
    {
        return $this->output();
    }

    /**
     * @param $ohtml
     * @param $css
     */
    public function writeHTML($ohtml, $css)
    {
        //$canvas = $this->getCanvas();
        //$renderer = new Renderer($this->_extoption, $canvas);
        $canvas = $this->getCanvas();
        $renderer = new Renderer($this->_extoption, $canvas);
        $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $html .= '<html><head>';
        $html .= '<style type="text/css">@page { margin:0mm;  }@page :first { margin:0mm;  }@page :left { margin:0mm;  }@page :right { margin:0mm;  }@page :odd { margin:0mm;  }@page :even { margin:0mm;  }' . $css . '</style>';
        $html .= '</head><body>' . $ohtml . '</body></html>';
        $renderer->loadHtml($html);
        $renderer->render();
    }

    /**
     * @param string $str
     * @param null   $encoding
     */
    public function loadHtml($str, $encoding = null)
    {

        parent::loadHtml($str, $encoding);
    }

    /**
     * @param Canvas $canvas
     *
     * @return $this
     */
    public function setCanvas(Canvas $canvas)
    {
        if ($this->_extcanvas) {
            parent::setCanvas($this->_extcanvas);
        } else {
            parent::setCanvas($canvas);
        }

        return $this;
    }

    public function handleRTLSupport()
    {
        foreach ($this->getTree()
                      ->get_frames() as $frame) {
            $style = $frame->get_style();
            $node = $frame->get_node();

            // Handle text nodes
            if ($node->nodeName === "#text") {
                if ($style->direction == 'rtl') {
                    $node->nodeValue = $this->checkRTL($node->nodeValue);
                }
                continue;
            }
        }
        if ($this->stringSubsetsText) {
            foreach ($this->stringSubsetsText as $font => $texts) {
                foreach ($texts as $text) {
                    $this->getCanvas()
                         ->register_string_subset($font, $text);
                }
            }
        }
    }

    /**
     * @param        $string
     * @param bool   $revInt
     * @param string $encoding
     *
     * @return string
     */
    private function hebstrrev($string, $revInt = false, $encoding = 'UTF-8')
    {
        $mb_strrev = function ($str) use ($encoding) {
            return mb_convert_encoding(strrev(mb_convert_encoding($str, 'UTF-16BE', $encoding)), $encoding, 'UTF-16LE');
        };

        if (!$revInt) {
            $s = '';
            foreach (array_reverse(preg_split('/(?<=\D)(?=\d)|\d+\K/', $string)) as $val) {
                $s .= ctype_digit($val) ? $val : $mb_strrev($val);
            }

            return $s;
        } else {
            return $mb_strrev($string);
        }
    }

    public function checkRTL($str)
    {
        if ($str && preg_match('/\p{Arabic}/u', $str)) {
            $Arabic = new Glyphs();//'Glyphs');
            $str = $Arabic->utf8Glyphs($str);
        } else {
            if (preg_match('/[\p{Hebrew}]/u', $str)) {
                $str = iconv("ISO-8859-8", "UTF-8", hebrev(iconv("UTF-8", "ISO-8859-8", $str)));
            }
        }

        return $str;
    }
}
