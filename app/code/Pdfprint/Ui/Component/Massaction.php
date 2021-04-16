<?php

namespace Snmportal\Pdfprint\Ui\Component;

use Magento\Framework\UrlInterface;
use Snmportal\Pdfprint\Helper\Template as TemplateHelper;
use Zend\Stdlib\JsonSerializable;

//use Vendor\Extension\Model\ResourceModel\Extension\CollectionFactory;
class Massaction implements JsonSerializable
{
    /**
     * @var TemplateHelper
     */
    protected $templateHelper;

    protected $options;

    protected $collectionFactory;

    protected $data;

    protected $urlBuilder;

    protected $urlPath;

    protected $paramName;

    protected $additionalData = [];

    public function __construct(
        //       CollectionFactory $collectionFactory,
        TemplateHelper $templateHelper,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        //   $this->collectionFactory = $collectionFactory;
        $this->templateHelper = $templateHelper;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $i = 0;
        if ($this->options === null) {

            $type = $this->data['type'];
            $templates = $this->templateHelper->getMassTemplates($type);

//            if(!count($templates)){
            //              return $this->options;
            //        }
            //make a array of massaction
            $options = [];
            if ($type == 'order') {
                $options[$i]['value'] = 0;
                $options[$i]['label'] = __('Default');
                $i++;
            }
            foreach ($templates as $item) {
                $options[$i]['value'] = $item['value'];
                $options[$i]['label'] = $item['label'];
                $i++;
            }
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type'  => 'extension_' . $optionCode['value'],
                    'label' => ' • ' . $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }

                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }

            // return the massaction data
            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {

        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
