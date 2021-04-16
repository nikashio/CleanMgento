<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class AbstractMassDelete
 */
class AbstractMassDelete extends Action
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
     * @return Redirect
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
                    $this->excludedDelete($excluded);
                } else {
                    $this->deleteAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedDelete($selected);
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
     * Delete all but the not selected
     *
     * @param array $excluded
     *
     * @return void
     * @throws Exception
     */
    protected function excludedDelete(array $excluded)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * Set error messages
     *
     * @param int $count
     *
     * @return void
     */
    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
    }

    /**
     * Delete collection items
     *
     * @param AbstractCollection $collection
     *
     * @return int
     * @throws Exception
     */
    protected function delete(AbstractCollection $collection)
    {
        $count = 0;
        foreach ($collection->getAllIds() as $id) {
            /** @var AbstractModel $model */
            $model = $this->_objectManager->create($this->model);
            $model->load($id);
            $model->delete();
            ++$count;
        }

        return $count;
    }

    /**
     * Delete all
     *
     * @return void
     * @throws Exception
     */
    protected function deleteAll()
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * Delete selected items
     *
     * @param array $selected
     *
     * @return void
     * @throws Exception
     */
    protected function selectedDelete(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);


        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_delete');
    }
}
