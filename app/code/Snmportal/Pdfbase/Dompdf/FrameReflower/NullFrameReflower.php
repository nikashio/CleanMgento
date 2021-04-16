<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Snmportal\Pdfbase\Dompdf\FrameReflower;

use Snmportal\Pdfbase\Dompdf\Frame;
use Snmportal\Pdfbase\Dompdf\FrameDecorator\Block as BlockFrameDecorator;

/**
 * Dummy reflower
 *
 * @package dompdf
 */
class NullFrameReflower extends AbstractFrameReflower
{

    function __construct(Frame $frame)
    {
        parent::__construct($frame);
    }

    function reflow(BlockFrameDecorator $block = null)
    {
        return;
    }
}
