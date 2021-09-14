<?php

namespace ForeverCompanies\StoneEmail\Observer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Sales\Model\OrderFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as AttributeOptionCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;

class PostOrderLogic implements ObserverInterface
{
    protected ProductFactory $productFactory;
    protected StockItemRepository $stockItemModel;
    protected OrderFactory $orderFactory;
    protected StockRegistryInterface $stockRegistry;
    protected DateTime $date;
    protected Attribute $attributeModel;
    protected AttributeOptionCollection $attributeOptionCollectionModel;
    protected ScopeConfigInterface $scopeConfig;
    protected MailHelper $mailHelper;
    protected AdapterInterface $connection;

    public function __construct(
        ProductFactory $prodF,
        ResourceConnection $resource,
        StockItemRepository $stockItemRepo,
        OrderFactory $orderF,
        StockRegistryInterface $stockReg,
        DateTime $dateTime,
        Attribute $attribute,
        AttributeOptionCollection $attributeOptionCollection,
        ScopeConfigInterface $scopeConfig,
        MailHelper $mailH
    ) {
        $this->productFactory = $prodF;
        $this->connection = $resource->getConnection();
        $this->stockItemModel = $stockItemRepo;
        $this->orderFactory = $orderF;
        $this->stockRegistry = $stockReg;
        $this->date = $dateTime;
        $this->attributeModel = $attribute;
        $this->attributeOptionCollectionModel = $attributeOptionCollection;
        $this->scopeConfig = $scopeConfig;
        $this->mailHelper = $mailH;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // the "from" name and email for stones sale email
        // this was hardcoded in M1; copying from M1 implementation
        $sender = [
            'name' => 'Forever Companies',
            'email' => 'fulfillment@forevercompanies.com'
        ];

        $order = $observer->getEvent()->getOrder();

        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getProduct()->getTypeId() == "configurable") {
                continue;
            }
            if ($orderItem->getProduct()->getFcProductType() == "3569") { // if is diamond
                $storeName = $order->getStore()->getGroup()->getName();
                $product = $this->productFactory->create()->load($orderItem->getProduct()->getId());
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $product->save();
                $stockItem = $this->stockRegistry->getStockItemBySku($orderItem->getProduct()->getSku());
                $stockItem->setQty(0);
                $stockItem->setIsInStock(false);
                $this->stockRegistry->updateStockItemBySku($orderItem->getProduct()->getSku(), $stockItem);
                $query = $this->connection->select()->from('stones_supplier')->where('id=?', $product->getSupplier());
                $result = $this->connection->fetchAll($query);
                $supplierEmail = $result[0]['email'];
                $supplier = $result[0]['name'];
                $html = "Hello,<br/><br/>";
                $html .= "This email confirms sale of your diamond, Stock # " . $product->getSku() . ", sold through "
                    . $storeName
                    . ". Please place this diamond on hold. 
                        You will shortly receive a Purchase Order from our fulfillment team at "
                    . $storeName
                    . " with further instructions.<br /><br />";
                $html .= "Order Date: " . $this->date->gmtdate('F j Y h:i:s') . " GMT<br /><br />";
                $html .= "SKU:     " . $product->getSku() . '<br />';
                $html .= "Weight:  " . $product->getCaratWeight() . '<br />';
                $html .= "Shape:   "
                    . $product->getResource()->getAttribute('shape')->getFrontend()->getValue($product) . '<br />';
                $html .= "Clarity: "
                    . $product->getResource()->getAttribute('clarity')->getFrontend()->getValue($product) . '<br />';
                $html .= "Color:   "
                    . $product->getResource()->getAttribute('color')->getFrontend()->getValue($product) . '<br />';
                $html .= "Cut:     "
                    . $product->getResource()->getAttribute('cut_grade')->getFrontend()->getValue(
                        $product
                    ) . '<br />';
                $html .= 'Cert URL: <a href="'
                    . $product->getCertUrlKey() . '">'
                    . $product->getCertUrlKey() . '</a><br /><br />';
                $html .= "Thank You!<br /><br />";
                $html .= $storeName . " Fulfillment Team<br /><br />";
                $html .= '<a href="mailto:loosestones@forevercompanies.com">
                    loosestones@forevercompanies.com</a><br /><br />';
                $html .= $storeName . " is a subsidiary of Lautrec Corporation, doing business as Forever Companies.";
                if (strpos($supplierEmail, ',') !== false) {
                    $supplierEmail = explode(",", $supplierEmail);
                }

                /**
                 * old setFrom method
                 *
                $this->mailHelper->setFrom([
                    'name' => $this->scopeConfig->getValue(
                        'trans_email/ident_support/name',
                        ScopeInterface::SCOPE_STORE
                    ),
                    'email' => $this->scopeConfig->getValue(
                        'trans_email/ident_support/email',
                        ScopeInterface::SCOPE_STORE
                    )
                ]);
                 */

                $this->mailHelper->setFrom($sender);

                if (is_array($supplierEmail)) {
                    foreach ($supplierEmail as $suppEmail) {
                        $this->mailHelper->addTo($suppEmail, $supplier);
                    }
                } else {
                    $this->mailHelper->addTo($supplierEmail, $supplier);
                }
                $this->mailHelper->setSubject("Notice of Diamond Sale. Stock # " . $product->getSku());
                $this->mailHelper->setIsHtml(true);
                $this->mailHelper->setBody($html);
                $this->mailHelper->send();
                return $this;
            }
        }
    }

    protected function _getSupplierName($supplierOptionId)
    {
        $attributeOptionAll = $this->attributeOptionCollectionModel
        ->setPositionOrder('asc')
        ->setAttributeFilter(
            $this->attributeModel->loadByCode('catalog_product', 'supplier')->getAttributeId()
        )
        ->setStoreFilter()
        ->load();
        foreach ($attributeOptionAll->getData() as $attributeOption) {
            if ($attributeOption['option_id'] == $supplierOptionId) {
                return $attributeOption['default_value'];
            }
        }
        return "";
    }
}
