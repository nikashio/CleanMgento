<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Snmportal\Pdfprint\Controller\Adminhtml\AbstractMassDelete;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassDelete
{
    /**
     * Field id
     */
    const ID_FIELD = 'template_id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Snmportal\Pdfprint\Model\ResourceModel\Template\Collection';

    /**
     * Page model
     *
     * @var string
     */
    protected $model = 'Snmportal\Pdfprint\Model\Template';
}
