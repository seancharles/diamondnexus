<?php
namespace ForeverCompanies\ReturnAndWarrantyWarnings\Model\Order\Pdf\Items\Invoice;

use Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice as OrigDefaultInvoice;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Tax\Helper\Data;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\RtlTextHandler;

class DefaultInvoice extends OrigDefaultInvoice
{
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param RtlTextHandler|null $rtlTextHandler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        StringUtils $string,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ?RtlTextHandler $rtlTextHandler = null
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $string,
            $resource,
            $resourceCollection,
            $data
        );
        $this->rtlTextHandler = $rtlTextHandler  ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
    }
    
    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        
        // draw Product name
        $lines[0][] = [
            'text' => $this->string->split($this->prepareText((string)$item->getName()), 35, true, true),
            'feed' => 35
        ];
        
        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($this->prepareText((string)$this->getSku($item)), 17),
            'feed' => 290,
            'align' => 'right',
        ];
        
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 435, 'align' => 'right'];
        
        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 395;
        $feedSubtotal = $feedPrice + 170;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            $i++;
        }
        
        // draw Tax
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];
        
        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];
                
                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }
        
        $product = $item->getOrderItem()->getProduct();
        if (!$product->getData('returnable')) {
            $lines[][] = ['text' => 'This item is not returnable', 'font' => 'bold', 'feed' => 35];
            
        }
        if ($product->getAttributeText('limited_warranty') == 'Yes') {
            $lines[][] = ['text' => 'This item has a limited warranty', 'font' => 'bold', 'feed' => 35];
        }
        
        $lineBlock = ['lines' => $lines, 'height' => 20];
        
        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
    
    /**
     * Returns prepared for PDF text, reversed in case of RTL text
     *
     * @param string $string
     * @return string
     */
    private function prepareText(string $string): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return $this->rtlTextHandler->reverseRtlText(html_entity_decode($string));
    }
}
