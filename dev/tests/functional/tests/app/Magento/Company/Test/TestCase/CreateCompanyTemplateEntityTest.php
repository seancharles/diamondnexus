<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Company\Test\Fixture\EmailTemplate;
use Magento\Email\Test\Page\Adminhtml\EmailTemplateIndex;
use Magento\Company\Test\Page\Adminhtml\EmailTemplateNewIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login to the admin panel.
 * 2. Go to Marketing -> Email templates and add new template.
 *
 * @group Company
 * @ZephyrId MAGETWO-68309
 */
class CreateCompanyTemplateEntityTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Page for create newsletter template.
     *
     * @var EmailTemplateNewIndex
     */
    protected $templateNewIndex;

    /**
     * Page with newsletter template grid.
     *
     * @var EmailTemplateIndex
     */
    protected $templateIndex;

    /**
     * Inject email page.
     *
     * @param EmailTemplateIndex $templateIndex
     * @param EmailTemplateNewIndex $templateNewIndex
     */
    public function __inject(
        EmailTemplateIndex $templateIndex,
        EmailTemplateNewIndex $templateNewIndex
    ) {
        $this->templateIndex = $templateIndex;
        $this->templateNewIndex = $templateNewIndex;
    }

    /**
     * Create email template.
     *
     * @param EmailTemplate $template
     * @return array
     */
    public function testCreateNewsletterTemplate(EmailTemplate $template)
    {
        // Steps:
        $this->templateIndex->open();
        $this->templateIndex->getGridPageActions()->addNew();
        $this->templateNewIndex->getTemplateBlock()->loadTemplate();
        $this->templateNewIndex->getEditForm()->fill($template);
        $this->templateNewIndex->getFormPageActions()->save();

        return ['templateCode' => $template->getTemplateCode()];
    }
}
