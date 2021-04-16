<?php

namespace Snmportal\Pdfprint\Model\Pdf;

use Exception;

class Filter extends \Magento\Email\Model\Template\Filter
{
    const CONSTRUCTION_WHEN_PATTERN = '/{{snm_when\s*(.*?)}}(.*?)({{snm_otherwise}}(.*?))?{{\\/snm_when\s*}}/si';

    const CONSTRUCTION_SET_PATTERN = '/{{snm_set\s*(.*?)}}(.*?){{\\/snm_set\s*}}/si';

    protected $_auitvariableline = '';

    /**
     * @return string
     */
    public function getAuitVariableLine()
    {
        return $this->_auitvariableline;
    }

    public function setAuitVariableLine($value)
    {
        if (strpos($value, 'helper.') !== false) {
            $this->_auitvariableline = $value;
        }
    }

    /**
     * @param        $name
     * @param string $default
     *
     * @return string
     */
    public function auitVariable($name, $default = '')
    {
        return $this->_getVariable($name, $default);
    }

    /**
     * @param        $value
     * @param string $default
     *
     * @return string
     */
    protected function _getVariable($value, $default = '{no_value_defined}')
    {
        if (strpos($value, 'helper.') !== false) {
            $this->_auitvariableline = $value;
        }

        return parent::getVariable($value, $default);
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->templateVars;
    }

    /**
     * @param $construction
     *
     * @return string
     * @throws Exception
     */
    public function setDirective($construction)
    {
        if (isset($construction[1]) && trim($construction[1])) {
            $v = [];
            $v[$construction[1]] = '';
            if (isset($construction[2]) && trim($construction[2])) {
                $v[$construction[1]] = $this->filter($construction[2]);
            }
            $this->setVariables($v);
        }

        return '';
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws Exception
     */
    public function filter($value)
    {
        foreach ([
                     self::CONSTRUCTION_WHEN_PATTERN => 'whenDirective',
                     self::CONSTRUCTION_SET_PATTERN  => 'setDirective'
                 ] as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        return parent::filter($value);
        /*
                try {
                    $value = \Magento\Framework\Filter\Template::filter($value);
                } catch (\Exception $e) {
                    // Since a single instance of this class can be used to filter content multiple times, reset callbacks to
                    // prevent callbacks running for unrelated content (e.g., email subject and email body)
                    $this->resetAfterFilterCallbacks();

                   $value = sprintf(__('Error filtering template: %s'), $e->getMessage());
                }
                return $value;
        */
    }

    /**
     * @param $construction
     *
     * @return string
     */
    public function whenDirective($construction)
    {
        if (count($this->templateVars) == 0) {
            return $construction[0];
        }

        if ($this->getVariable($construction[1], '') == '') {
            if (isset($construction[3]) && isset($construction[4])) {
                return $construction[4];
            }

            return '';
        } else {
            return $construction[2];
        }
    }

    /**
     * @param string $value
     * @param string $default
     *
     * @return string
     */
    protected function getVariable($value, $default = '{no_value_defined}')
    {
        return $this->_getVariable($value, $default);
    }

    /**
     * @param array $construction
     *
     * @return string
     */
    public function blockDirective($construction)
    {
        $skipParams = ['class', 'id', 'output'];
        $blockParameters = $this->getParameters($construction[2]);
        $block = null;
        if (!isset($blockParameters['snm'])) {
            return parent::blockDirective($construction);
        }
        $block = $this->_layout->createBlock('Snmportal\Pdfprint\Block\Helper');
        if (!$block) {
            return '';
        }
        $block->setBlockId($blockParameters['snm']);
        $block->setTemplateFilter($this);
        $block->setBlockParams($blockParameters);
        foreach ($blockParameters as $k => $v) {
            if (in_array($k, $skipParams)) {
                continue;
            }
            $block->setDataUsingMethod($k, $v);
        }

        if (isset($blockParameters['output'])) {
            $method = $blockParameters['output'];
        }
        if (!isset($method) || !is_string($method) || !method_exists($block, $method) || !is_callable(
            [$block, $method]
        )) {
            $method = 'toHtml';
        }

        return $block->{$method}();
    }
}
