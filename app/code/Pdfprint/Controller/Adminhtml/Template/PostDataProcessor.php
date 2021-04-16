<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

class PostDataProcessor
{
    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Date             $dateFilter
     * @param ManagerInterface $messageManager
     * @param ValidatorFactory $validatorFactory
     */
    public function __construct(
        Date $dateFilter,
        ManagerInterface $messageManager,
        ValidatorFactory $validatorFactory
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     *
     * @return array
     */
    public function filter($data)
    {
        return $data;
    }

    /**
     * Validate post data
     *
     * @param array $data
     *
     * @return bool     Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        /*
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            ** @var $validatorCustomLayout \Magento\Framework\View\Model\Layout\Update\Validator *
            $validatorCustomLayout = $this->validatorFactory->create();
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml'])
                && !$validatorCustomLayout->isValid($data['custom_layout_update_xml'])
            ) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }
    */
        return true;
    }
}
