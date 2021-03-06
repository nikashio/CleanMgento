<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien M�nager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Snmportal\Pdfbase\Svg\Tag;

class Circle extends Shape
{
    protected $cx = 0;
    protected $cy = 0;
    protected $r;

    public function start($attributes)
    {
        if (isset($attributes['cx'])) {
            $this->cx = $attributes['cx'];
        }
        if (isset($attributes['cy'])) {
            $this->cy = $attributes['cy'];
        }
        if (isset($attributes['r'])) {
            $this->r = $attributes['r'];
        }

        $this->document->getSurface()->circle($this->cx, $this->cy, $this->r);
    }
}
