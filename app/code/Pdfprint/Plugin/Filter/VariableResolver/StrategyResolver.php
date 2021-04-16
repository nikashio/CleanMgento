<?php

namespace Snmportal\Pdfprint\Plugin\Filter\VariableResolver;

use Snmportal\Pdfprint\Model\Pdf\Filter;

class StrategyResolver
{
    public function beforeResolve($caller, $value, $filter, $templateVariables)
    {
        // set AUITVar
        //
        if ($filter instanceof Filter) {
            $filter->setAuitVariableLine($value);
        }
    }
}
