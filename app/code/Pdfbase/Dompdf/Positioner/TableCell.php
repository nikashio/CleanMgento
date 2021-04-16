<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Snmportal\Pdfbase\Dompdf\Positioner;

use Snmportal\Pdfbase\Dompdf\FrameDecorator\AbstractFrameDecorator;
use Snmportal\Pdfbase\Dompdf\FrameDecorator\Table;

/**
 * Positions table cells
 *
 * @package dompdf
 */
class TableCell extends AbstractPositioner
{

    function __construct(AbstractFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

    //........................................................................

    function position()
    {

        $table = Table::find_parent_table($this->_frame);
        $cellmap = $table->get_cellmap();
        $this->_frame->set_position($cellmap->get_frame_position($this->_frame));
    }
}
