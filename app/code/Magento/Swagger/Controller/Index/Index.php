<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Controller\Index;

/**
 * Class Index
 * @package Magento\Swagger\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Page\Config */
    private $pageConfig;

    /** @var \Magento\Framework\View\Result\PageFactory */
    private $pageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageConfig = $pageConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->pageConfig->addBodyClass('swagger-section');
        $resultPage = $this->pageFactory->create();

        $storeCodeName = 'store_code';
        $storeCode = $this->getRequest()->getParam($storeCodeName);
        $this->_view->getLayout()->getBlock('swaggerUiContent')->assign($storeCodeName, $storeCode);
        return $resultPage;
    }
}
