<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class AbstractMassStatus
 */
class AbstractMassStatus extends Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'template_id';

    /**
     * Redirect url
     */
    const REDIRECT_URL = '*/*/';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection';

    /**
     * Model
     *
     * @var string
     */
    protected $model = 'Magento\Framework\Model\AbstractModel';

    /**
     * Item status
     *
     * @var bool
     */
    protected $status = true;

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $selected = $this->getRequest()
                         ->getParam('selected');
        $excluded = $this->getRequest()
                         ->getParam('excluded');
        try {
            if (isset($excluded)) {
                if (!empty($excluded)) {
                    $this->excludedSetStatus($excluded);
                } else {
                    $this->setStatusAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedSetStatus($selected);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }


        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath(static::REDIRECT_URL);
    }

    /**
     * Set status to all but the not selected
     *
     * @param array $excluded
     *
     * @return void
     * @throws Exception
     */
    protected function excludedSetStatus(array $excluded)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setStatus($collection);
    }

    /**
     * Set status to collection items
     *
     * @param AbstractCollection $collection
     *
     * @return void
     * @throws Exception
     */
    protected function setStatus(AbstractCollection $collection)
    {
        foreach ($collection->getAllIds() as $id) {
            /** @var AbstractModel $model */
            $model = $this->_objectManager->create($this->model);
            $model->load($id);
            $model->setIsActive($this->status);
            $model->save();
        }
    }

    /**
     * Set status to all
     *
     * @return void
     * @throws Exception
     */
    protected function setStatusAll()
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $this->setStatus($collection);
    }

    /**
     * Set status to selected items
     *
     * @param array $selected
     *
     * @return void
     * @throws Exception
     */
    protected function selectedSetStatus(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setStatus($collection);
    }

    /**
     * Execute action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_save');
    }
}
