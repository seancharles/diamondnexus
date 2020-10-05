<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ForeverCompanies_Promotions::promotions';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('ForeverCompanies Catalog Price Rules'), __('ForeverCompanies Catalog Price Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ForeverCompanies Catalog Price Rules'));
        $this->_view->renderLayout('root');


        /*

            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('ForeverCompanies_Promotions::ForeverCompanies_promotion');
            $resultPage->addBreadcrumb(__('ForeverCompanies Catalog Price Rules'), __('ForeverCompanies Catalog Price Rules'));
            $resultPage->getConfig()->getTitle()->prepend(__('ForeverCompanies Catalog Price Rules'));
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock('ForeverCompanies\Promotions\Block\Adminhtml\Promo\Rule')
            );
            return $resultPage;
        */

    }
}
