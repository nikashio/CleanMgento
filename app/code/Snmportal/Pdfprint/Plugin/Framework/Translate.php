<?php

namespace Snmportal\Pdfprint\Plugin\Framework;

class Translate
{
    /**
     * @param \Magento\Framework\Translate $subject
     * @param                              $proceed
     * @param                              $area
     * @param                              $forceReload
     *
     * @return mixed
     */
    public function aroundLoadData(
        \Magento\Framework\Translate $subject,
        $proceed,
        $area,
        $forceReload
    ) {
        return $proceed();
    }

    /**
     * @param \Magento\Framework\Translate $subject
     * @param                              $result
     *
     * @return mixed
     */
    public function afterLoadData(
        \Magento\Framework\Translate $subject,
        $result
    ) {

        return $result;
    }
}
