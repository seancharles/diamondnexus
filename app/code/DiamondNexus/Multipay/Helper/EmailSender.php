<?php

declare(strict_types=1);

namespace DiamondNexus\Multipay\Helper;

use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class EmailSender extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var CollectionFactory
     */
    protected $templateFactory;

    /**
     * EmailSender constructor.
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param CollectionFactory $templateFactory
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        CollectionFactory $templateFactory
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->templateFactory = $templateFactory;
        parent::__construct($context);
    }

    /**
     * @param string $template
     * @param string $toEmail
     * @param array $templateVars
     */
    public function sendEmail(string $template, string $toEmail, array $templateVars)
    {
        $templateId = $this->findTemplate($template);
        if ($templateId == false) {
            return;
        }
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $this->inlineTranslation->suspend();
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * @param $template
     * @return false|string
     */
    public function mappingTemplate($template)
    {
        if ($template == '- new order') {
            try {
                $website = $this->storeManager->getWebsite()->getName();
                switch (trim($website)) {
                    case 'Diamond Nexus':
                        return 'New Order Email (March 2020)';
                    case 'Forever Artisans':
                        return 'Order Update (responsive)';
                    case '1215 Diamonds':
                        return 'New Order (March 2020)';
                    default:
                }
            } catch (LocalizedException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param string $template
     * @return string|false
     */
    protected function findTemplate(string $template)
    {
        try {
            $website = $this->storeManager->getWebsite()->getName();
            switch (trim($website)) {
                case 'Diamond Nexus':
                    $storeSymbols = 'DN';
                    break;
                case 'Forever Artisans':
                    $storeSymbols = 'FA';
                    break;
                case '1215 Diamonds':
                    $storeSymbols = '1215';
                    break;
                default:
                    $storeSymbols = false;
            }
            if ($storeSymbols !== false) {
                $templates = $this->templateFactory->create()
                    ->addFieldToFilter('template_code', ['like' => '%' . $template . '%'])
                    ->load();
                foreach ($templates as $template) {
                    if (strpos($template['template_code'], $storeSymbols) !== false) {
                        return $template['template_id'];
                    }
                }
            }
        } catch (LocalizedException $e) {
            return false;
        }
        return false;
    }
}
