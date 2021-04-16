<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Helmut Tischer <htischer@weihenstephan.org>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Snmportal\Pdfbase\Dompdf;

use Magento\Framework\App\ObjectManager;
use Snmportal\Pdfbase\FontLib\Font;

/**
 * The font metrics class
 *
 * This class provides information about fonts and text.  It can resolve
 * font names into actual installed font files, as well as determine the
 * size of text in a particular font and size.
 *
 * @static
 * @package dompdf
 */
class FontMetrics
{
    /**
     * Name of the font cache file
     *
     * This file must be writable by the webserver process only to update it
     * with save_font_families() after adding the .afm file references of a new font family
     * with FontMetrics::saveFontFamilies().
     * This is typically done only from command line with load_font.php on converting
     * ttf fonts to ufm with php-font-lib.
     */
    const CACHE_FILE = "dompdf_font_family_cache.json";

    /**
     * @var Canvas
     * @deprecated
     */
    protected $pdf;

    /**
     * Underlying {@link Canvas} object to perform text size calculations
     *
     * @var Canvas
     */
    protected $canvas;

    /**
     * Array of font family names to font files
     *
     * Usually cached by the {@link load_font.php} script
     *
     * @var array
     */
    protected static $fontLookup = [];

    /**
     * @var Options
     */
    private $options;

    /**
     * Class initialization
     */
    public function __construct(Canvas $canvas, Options $options)
    {
        $this->setCanvas($canvas);
        $this->setOptions($options);
        $this->loadFontFamilies();
    }

    /**
     * @deprecated
     */
    public function save_font_families()
    {
        $this->saveFontFamilies();
    }

    /**
     * Saves the stored font family cache
     *
     * The name and location of the cache file are determined by {@link
     * FontMetrics::CACHE_FILE}.  This file should be writable by the
     * webserver process.
     *
     * @see Font_Metrics::load_font_families()
     */
    public function saveFontFamilies()
    {
        // replace the path to the DOMPDF font directories with the corresponding constants (allows for more portability)
        /*
        $cacheData = sprintf("<?php return array (%s", PHP_EOL);
        foreach (self::$fontLookup as $family => $variants) {
            $cacheData .= sprintf("  '%s' => array(%s", addslashes($family), PHP_EOL);
            foreach ($variants as $variant => $path) {
                $path = sprintf("'%s'", $path);
                $path = str_replace('\'' . $this->getOptions()->getFontDir() , '$fontDir . \'' , $path);
                $path = str_replace('\'' . $this->getOptions()->getRootDir() , '$rootDir . \'' , $path);
                $cacheData .= sprintf("    '%s' => %s,%s", $variant, $path, PHP_EOL);
            }
            $cacheData .= sprintf("  ),%s", PHP_EOL);
        }
        $cacheData .= "); "; //AUIT
        */
        $cacheData = ObjectManager::getInstance()->get('\Magento\Framework\Serialize\Serializer\Json')->serialize(self::$fontLookup);
        file_put_contents($this->getCacheFile(), $cacheData);
    }

    /**
     * @deprecated
     */
    public function load_font_families()
    {
        $this->loadFontFamilies();
    }
    private function getStdFontFamilies($rootDir)
    {

        return [
            'sans-serif' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Helvetica',
                    'bold' => $rootDir . '/lib/fonts/Helvetica-Bold',
                    'italic' => $rootDir . '/lib/fonts/Helvetica-Oblique',
                    'bold_italic' => $rootDir . '/lib/fonts/Helvetica-BoldOblique',
                ],
            'times' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Times-Roman',
                    'bold' => $rootDir . '/lib/fonts/Times-Bold',
                    'italic' => $rootDir . '/lib/fonts/Times-Italic',
                    'bold_italic' => $rootDir . '/lib/fonts/Times-BoldItalic',
                ],
            'times-roman' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Times-Roman',
                    'bold' => $rootDir . '/lib/fonts/Times-Bold',
                    'italic' => $rootDir . '/lib/fonts/Times-Italic',
                    'bold_italic' => $rootDir . '/lib/fonts/Times-BoldItalic',
                ],
            'courier' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Courier',
                    'bold' => $rootDir . '/lib/fonts/Courier-Bold',
                    'italic' => $rootDir . '/lib/fonts/Courier-Oblique',
                    'bold_italic' => $rootDir . '/lib/fonts/Courier-BoldOblique',
                ],
            'helvetica' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Helvetica',
                    'bold' => $rootDir . '/lib/fonts/Helvetica-Bold',
                    'italic' => $rootDir . '/lib/fonts/Helvetica-Oblique',
                    'bold_italic' => $rootDir . '/lib/fonts/Helvetica-BoldOblique',
                ],
            'zapfdingbats' =>
                [
                    'normal' => $rootDir . '/lib/fonts/ZapfDingbats',
                    'bold' => $rootDir . '/lib/fonts/ZapfDingbats',
                    'italic' => $rootDir . '/lib/fonts/ZapfDingbats',
                    'bold_italic' => $rootDir . '/lib/fonts/ZapfDingbats',
                ],
            'symbol' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Symbol',
                    'bold' => $rootDir . '/lib/fonts/Symbol',
                    'italic' => $rootDir . '/lib/fonts/Symbol',
                    'bold_italic' => $rootDir . '/lib/fonts/Symbol',
                ],
            'serif' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Times-Roman',
                    'bold' => $rootDir . '/lib/fonts/Times-Bold',
                    'italic' => $rootDir . '/lib/fonts/Times-Italic',
                    'bold_italic' => $rootDir . '/lib/fonts/Times-BoldItalic',
                ],
            'monospace' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Courier',
                    'bold' => $rootDir . '/lib/fonts/Courier-Bold',
                    'italic' => $rootDir . '/lib/fonts/Courier-Oblique',
                    'bold_italic' => $rootDir . '/lib/fonts/Courier-BoldOblique',
                ],
            'fixed' =>
                [
                    'normal' => $rootDir . '/lib/fonts/Courier',
                    'bold' => $rootDir . '/lib/fonts/Courier-Bold',
                    'italic' => $rootDir . '/lib/fonts/Courier-Oblique',
                    'bold_italic' => $rootDir . '/lib/fonts/Courier-BoldOblique',
                ],
            'dejavu sans' =>
                [
                    'bold' => $rootDir . '/lib/fonts/DejaVuSans-Bold',
                    'bold_italic' => $rootDir . '/lib/fonts/DejaVuSans-BoldOblique',
                    'italic' => $rootDir . '/lib/fonts/DejaVuSans-Oblique',
                    'normal' => $rootDir . '/lib/fonts/DejaVuSans',
                ],
            'dejavu sans mono' =>
                [
                    'bold' => $rootDir . '/lib/fonts/DejaVuSansMono-Bold',
                    'bold_italic' => $rootDir . '/lib/fonts/DejaVuSansMono-BoldOblique',
                    'italic' => $rootDir . '/lib/fonts/DejaVuSansMono-Oblique',
                    'normal' => $rootDir . '/lib/fonts/DejaVuSansMono',
                ],
            'dejavu serif' =>
                [
                    'bold' => $rootDir . '/lib/fonts/DejaVuSerif-Bold',
                    'bold_italic' => $rootDir . '/lib/fonts/DejaVuSerif-BoldItalic',
                    'italic' => $rootDir . '/lib/fonts/DejaVuSerif-Italic',
                    'normal' => $rootDir . '/lib/fonts/DejaVuSerif',
                ]
        ];
    }

    /**
     * Loads the stored font family cache
     *
     * @see save_font_families()
     */
    public function loadFontFamilies()
    {
        if (count(self::$fontLookup)) {
            return;
        }
        $fontDir = $this->getOptions()->getFontDir();
        $rootDir = $this->getOptions()->getRootDir();

        // FIXME: tempoarary define constants for cache files <= v0.6.2
        if (!defined("DOMPDF_DIR")) {
            define("DOMPDF_DIR", $rootDir);
        }
        if (!defined("DOMPDF_FONT_DIR")) {
            define("DOMPDF_FONT_DIR", $fontDir);
        }


        $distFonts = $this->getStdFontFamilies($rootDir);
        /*
        $distFonts = file_get_contents($file);//AUIT7.3

        // FIXME: temporary step for font cache created before the font cache fix
        if (is_readable($fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache")) {
            //$oldFonts = require $fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache";
            $oldFonts =file_get_contents($fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache");//AUIT7.3
            // If the font family cache is still in the old format
            if ($oldFonts === 1) {
                $cacheData = file_get_contents($fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache");
                file_put_contents($fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache", "<" . "?php return $cacheData ?" . ">");
                //$oldFonts = require $fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache";
                $oldFonts =file_get_contents($fontDir . DIRECTORY_SEPARATOR . "dompdf_font_family_cache");//AUIT7.3
            }
            $distFonts += $oldFonts;
        }
        */
        if (!is_readable($this->getCacheFile())) {
            self::$fontLookup = $distFonts;
            return;
        }


        //$cacheData = require $this->getCacheFile();
        $cacheData = file_get_contents($this->getCacheFile());//AUIT7.3
        $cacheData = ObjectManager::getInstance()->get('\Magento\Framework\Serialize\Serializer\Json')->unserialize($cacheData);
        /*
                // If the font family cache is still in the old format
                if ($cacheData === 1) {
                    $cacheData = file_get_contents($this->getCacheFile());
                    file_put_contents($this->getCacheFile(), "<" . "?php return $cacheData ?" . ">");
                    //self::$fontLookup = require $this->getCacheFile();
                    self::$fontLookup = file_get_contents($this->getCacheFile());//AUIT7.3
                }
        */
        self::$fontLookup = [];
        foreach ($cacheData as $key => $value) {
            self::$fontLookup[stripslashes($key)] = $value;
        }

        // Merge provided fonts
        self::$fontLookup += $distFonts;
    }

    /**
     * @param array $files
     * @return array
     * @deprecated
     */
    public function install_fonts($files)
    {
        return $this->installFonts($files);
    }

    /**
     * @param array $files
     * @return array
     */
    public function installFonts(array $files)
    {
        $names = [];

        foreach ($files as $file) {
            $font = Font::load($file);
            $records = $font->getData("name", "records");
            $type = $this->getType($records[2]);
            $names[mb_strtolower($records[1])][$type] = $file;
            $font->close();
        }

        return $names;
    }

    /**
     * @param array $style
     * @param string $remote_file
     * @param resource $context
     * @return bool
     * @deprecated
     */
    public function register_font($style, $remote_file, $context = null)
    {
        return $this->registerFont($style, $remote_file);
    }

    /**
     * @param array $style
     * @param string $remoteFile
     * @param resource $context
     * @return bool
     */
    public function registerFont($style, $remoteFile, $context = null)
    {

        $fontDir = $this->getOptions()->getFontCache();
        $fontname = mb_strtolower($style["family"]);
        $families = $this->getFontFamilies();

        $entry = [];
        if (isset($families[$fontname])) {
            $entry = $families[$fontname];
        }

        $localFile = $fontDir . DIRECTORY_SEPARATOR . sha1($remoteFile);
        $localTempFile = $this->options->get('tempDir') . "/" . sha1($remoteFile);
        $cacheEntry = $localFile;
        $localFile .= ".ttf";

        $styleString = $this->getType("{$style['weight']} {$style['style']}");

        if (!isset($entry[$styleString])) {
            $entry[$styleString] = $cacheEntry;

            // Download the remote file
            $remoteFileContent = file_get_contents($remoteFile, null, $context);
            if (false === $remoteFileContent) {
                return false;
            }
            file_put_contents($localTempFile, $remoteFileContent);

            $font = Font::load($localTempFile);

            if (!$font) {
                unlink($localTempFile);
                return false;
            }

            $font->parse();
            $font->saveAdobeFontMetrics("$cacheEntry.ufm");
            $font->close();

            unlink($localTempFile);

            if (!file_exists("$cacheEntry.ufm")) {
                return false;
            }

            // Save the changes
            file_put_contents($localFile, file_get_contents($remoteFile, null, $context));

            if (!file_exists($localFile)) {
                unlink("$cacheEntry.ufm");
                return false;
            }

            $this->setFontFamily($fontname, $entry);
            $this->saveFontFamilies();
        }

        return true;
    }

    /**
     * @param $text
     * @param $font
     * @param $size
     * @param float $word_spacing
     * @param float $char_spacing
     * @return float
     * @deprecated
     */
    public function get_text_width($text, $font, $size, $word_spacing = 0.0, $char_spacing = 0.0)
    {
        //return self::$_pdf->get_text_width($text, $font, $size, $word_spacing, $char_spacing);
        return $this->getTextWidth($text, $font, $size, $word_spacing, $char_spacing);
    }

    /**
     * Calculates text size, in points
     *
     * @param string $text the text to be sized
     * @param string $font the desired font
     * @param float $size  the desired font size
     * @param float $wordSpacing
     * @param float $charSpacing
     *
     * @internal param float $spacing word spacing, if any
     * @return float
     */
    public function getTextWidth($text, $font, $size, $wordSpacing = 0.0, $charSpacing = 0.0)
    {
        // @todo Make sure this cache is efficient before enabling it
        static $cache = [];

        if ($text === "") {
            return 0;
        }

        // Don't cache long strings
        $useCache = !isset($text[50]); // Faster than strlen

        $key = "$font/$size/$wordSpacing/$charSpacing";

        if ($useCache && isset($cache[$key][$text])) {
            return $cache[$key]["$text"];
        }

        $width = $this->getCanvas()->get_text_width($text, $font, $size, $wordSpacing, $charSpacing);

        if ($useCache) {
            $cache[$key][$text] = $width;
        }

        return $width;
    }

    /**
     * @param $font
     * @param $size
     * @return float
     * @deprecated
     */
    public function get_font_height($font, $size)
    {
        return $this->getFontHeight($font, $size);
    }

    /**
     * Calculates font height
     *
     * @param string $font
     * @param float $size
     *
     * @return float
     */
    public function getFontHeight($font, $size)
    {
        return $this->getCanvas()->get_font_height($font, $size);
    }

    /**
     * @param $family_raw
     * @param string $subtype_raw
     * @return string
     * @deprecated
     */
    public function get_font($family_raw, $subtype_raw = "normal")
    {
        return $this->getFont($family_raw, $subtype_raw);
    }

    /**
     * Resolves a font family & subtype into an actual font file
     * Subtype can be one of 'normal', 'bold', 'italic' or 'bold_italic'.  If
     * the particular font family has no suitable font file, the default font
     * ({@link Options::defaultFont}) is used.  The font file returned
     * is the absolute pathname to the font file on the system.
     *
     * @param string $familyRaw
     * @param string $subtypeRaw
     *
     * @return string
     */
    public function getFont($familyRaw, $subtypeRaw = "normal")
    {
        static $cache = [];

        if (isset($cache[$familyRaw][$subtypeRaw])) {
            return $cache[$familyRaw][$subtypeRaw];
        }

        /* Allow calling for various fonts in search path. Therefore not immediately
         * return replacement on non match.
         * Only when called with NULL try replacement.
         * When this is also missing there is really trouble.
         * If only the subtype fails, nevertheless return failure.
         * Only on checking the fallback font, check various subtypes on same font.
         */

        $subtype = strtolower($subtypeRaw);

        if ($familyRaw) {
            $family = str_replace(["'", '"'], "", strtolower($familyRaw));

            if (isset(self::$fontLookup[$family][$subtype])) {
                return $cache[$familyRaw][$subtypeRaw] = self::$fontLookup[$family][$subtype];
            }

            return null;
        }

        $family = "serif";

        if (isset(self::$fontLookup[$family][$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = self::$fontLookup[$family][$subtype];
        }

        if (!isset(self::$fontLookup[$family])) {
            return null;
        }

        $family = self::$fontLookup[$family];

        foreach ($family as $sub => $font) {
            if (strpos($subtype, $sub) !== false) {
                return $cache[$familyRaw][$subtypeRaw] = $font;
            }
        }

        if ($subtype !== "normal") {
            foreach ($family as $sub => $font) {
                if ($sub !== "normal") {
                    return $cache[$familyRaw][$subtypeRaw] = $font;
                }
            }
        }

        $subtype = "normal";

        if (isset($family[$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = $family[$subtype];
        }

        return null;
    }

    /**
     * @param $family
     * @return null|string
     * @deprecated
     */
    public function get_family($family)
    {
        return $this->getFamily($family);
    }

    /**
     * @param string $family
     * @return null|string
     */
    public function getFamily($family)
    {
        $family = str_replace(["'", '"'], "", mb_strtolower($family));

        if (isset(self::$fontLookup[$family])) {
            return self::$fontLookup[$family];
        }

        return null;
    }

    /**
     * @param $type
     * @return string
     * @deprecated
     */
    public function get_type($type)
    {
        return $this->getType($type);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getType($type)
    {
        if (preg_match("/bold/i", $type)) {
            if (preg_match("/italic|oblique/i", $type)) {
                $type = "bold_italic";
            } else {
                $type = "bold";
            }
        } elseif (preg_match("/italic|oblique/i", $type)) {
            $type = "italic";
        } else {
            $type = "normal";
        }

        return $type;
    }

    /**
     * @return array
     * @deprecated
     */
    public function get_system_fonts()
    {
        return $this->getSystemFonts();
    }

    /**
     * @return array
     */
    public function getSystemFonts()
    {
        $files = glob("/usr/share/fonts/truetype/*.ttf") +
            glob("/usr/share/fonts/truetype/*/*.ttf") +
            glob("/usr/share/fonts/truetype/*/*/*.ttf") +
            glob("C:\\Windows\\fonts\\*.ttf") +
            glob("C:\\WinNT\\fonts\\*.ttf") +
            glob("/mnt/c_drive/WINDOWS/Fonts/");

        return $this->installFonts($files);
    }

    /**
     * @return array
     * @deprecated
     */
    public function get_font_families()
    {
        return $this->getFontFamilies();
    }

    /**
     * Returns the current font lookup table
     *
     * @return array
     */
    public function getFontFamilies()
    {
        return self::$fontLookup;
    }

    /**
     * @param string $fontname
     * @param mixed $entry
     * @deprecated
     */
    public function set_font_family($fontname, $entry)
    {
        $this->setFontFamily($fontname, $entry);
    }

    /**
     * @param string $fontname
     * @param mixed $entry
     */
    public function setFontFamily($fontname, $entry)
    {
        self::$fontLookup[mb_strtolower($fontname)] = $entry;
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return $this->getOptions()->getFontCache() . DIRECTORY_SEPARATOR . self::CACHE_FILE;
    }

    /**
     * @param Options $options
     * @return $this
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Canvas $canvas
     * @return $this
     */
    public function setCanvas(Canvas $canvas)
    {
        $this->pdf = $canvas;
        $this->canvas = $canvas;
        return $this;
    }

    /**
     * @return Canvas
     */
    public function getCanvas()
    {
        return $this->canvas;
    }
}
