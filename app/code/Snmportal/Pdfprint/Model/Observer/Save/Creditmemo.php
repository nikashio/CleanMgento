<?php

namespace Snmportal\Pdfprint\Model\Observer\Save;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;

class Creditmemo extends Base implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(Observer $observer)
    {
        $doc = $observer->getEvent()
                        ->getDataObject();
        if ($doc instanceof AbstractModel) {
            $this->createAndSaveDocument($doc);
        }

        return $this;
    }
}
