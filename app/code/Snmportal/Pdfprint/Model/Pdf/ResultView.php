<?php
/**
 * Created by PhpStorm.
 * User: mau
 * Date: 14.04.2016
 * Time: 09:10
 */

namespace Snmportal\Pdfprint\Model\Pdf;

use Magento\Framework;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View;
use Magento\Framework\View\Design\Theme\ResolverInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\Page;

//use Magento\Framework\App\ResponseInterface;
class ResultView extends Page
{
    /**
     * @var ResolverInterface
     */
    protected $themeResolver;

    /**
     * @var ResolverInterface
     */
    protected $generatorPool;

    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        //  \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        $template,
        $isIsolated = false
    ) {

        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $pageConfigRendererFactory,
            $pageLayoutReader,
            $template,
            false
        );

        $this->generatorPool = $generatorPool;
        $this->layout = $layoutFactory->create(['reader' => $layoutReaderPool, 'generatorPool' => $generatorPool]);
        $this->layout->setGeneratorPool($generatorPool);
        $this->initLayoutBuilder();

//        $this->layout = $this->layoutFactory->create(['reader' => $this->layoutReaderPool, 'generatorPool' => $generatorPool]);
        //      $this->layout->setGeneratorPool($generatorPool);
        //   $themeResolver = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\View\Design\Theme\ResolverInterface');
        //  error_log("\n" . print_r(get_class($themeResolver), true), 3, 'auit.log');
        // error_log("\n" . print_r($themeResolver->get()->getData(), true), 3, 'auit.log');
    }

    public function setBlankTheme()
    {
        /**
         * @var DesignInterface $design
         */


        $design = ObjectManager::getInstance()
                               ->get('\Magento\Framework\View\DesignInterface');
        //       $design->setDefaultDesignTheme();
        $design->setDesignTheme(1);
        $this->layout = $this->layoutFactory->create(
            ['reader' => $this->layoutReaderPool, 'generatorPool' => $this->generatorPool]
        );
        $this->layout->setGeneratorPool($this->generatorPool);
        $this->initLayoutBuilder();
        $this->addDefaultHandle();
        $themeResolver = ObjectManager::getInstance()
                                      ->get('\Magento\Framework\View\Design\Theme\ResolverInterface');
        $theme = $themeResolver->get();
        /*
        \Magento\Framework\View\DesignInterface $viewDesign,

        \Magento\Framework\View\DesignInterface $viewDesign,
        $storeTheme = $this->_viewDesign->getConfigurationDesignTheme($area, ['store' => $storeId]);
        $this->_viewDesign->setDesignTheme($storeTheme, $area);

        if ($area == \Magento\Framework\App\Area::AREA_FRONTEND) {
            $designChange = $this->_design->loadChange($storeId);
            if ($designChange->getData()) {
                $this->_viewDesign->setDesignTheme($designChange->getDesign(), $area);
            }
        }
        */
    }
}
