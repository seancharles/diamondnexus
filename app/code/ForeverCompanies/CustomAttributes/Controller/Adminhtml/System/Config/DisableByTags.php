<?php
namespace ForeverCompanies\CustomAttributes\Controller\Adminhtml\System\Config;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class DisableByTags extends Action
{

    protected $resultJsonFactory;

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param TransformData $helper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TransformData $helper,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return void
     */
    public function execute()
    {
        $productCollection = $this->helper->getProductsForDisableCollection();
        foreach ($productCollection->getItems() as $item) {
            try {
                $this->helper->disableProduct((int)$item->getData('entity_id'));
            } catch (InputException $e) {
                $this->logger->error('Can\'t delete product ID = ' . $item->getData('entity_id'));
            } catch (NoSuchEntityException $e) {
                $this->logger->error('Can\'t find product ID = ' . $item->getData('entity_id'));
            } catch (StateException $e) {
                $this->logger->error('Can\'t delete product ID = ' . $item->getData('entity_id'));
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_CustomAttributes::config');
    }
}
