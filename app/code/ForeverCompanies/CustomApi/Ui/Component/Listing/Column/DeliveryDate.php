<?php

namespace ForeverCompanies\CustomApi\Ui\Component\Listing\Column;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use ShipperHQ\Shipper\Helper\CarrierGroup;

/**
 * Class Address
 */
class DeliveryDate extends Column
{
    /**
     * @var CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataBundle
     */
    private $dataBundle;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param CarrierGroup      $carrierGroupHelper
     * @param DateTime $date
     * @param ContextInterface                            $context
     * @param UiComponentFactory                          $uiComponentFactory
     * @param TimezoneInterface                           $timezone
     * @param array                                       $components
     * @param array                                       $data
     * @param ResolverInterface|null                      $localeResolver
     * @param DataBundle|null                             $dataBundle
     */
    public function __construct(
        CarrierGroup $carrierGroupHelper,
        DateTime $date,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        DataBundle $dataBundle,
        array $components = [],
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
        $this->dataBundle = $dataBundle;
        $this->locale = $this->localeResolver->getLocale();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['filter'])) {
            $config['filter'] = [
                'filterType' => 'dateRange',
                'templates' => [
                    'date' => [
                        'options' => [
                            // MNB-764 Always use the store date format. M2 won't filter reliably using MMM dd, YYYY
                            'dateFormat' => $this->timezone->getDateFormatWithLongYear()
                        ]
                    ]
                ]
            ];
        }

        $localeData = $this->dataBundle->get($this->locale);
        /** @var \ResourceBundle $monthsData */
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $months = array_values(iterator_to_array($monthsData['format']['wide']));
        $monthsShort = array_values(
            iterator_to_array(
                null !== $monthsData->get('format')->get('abbreviated')
                    ? $monthsData['format']['abbreviated']
                    : $monthsData['format']['wide']
            )
        );

        $config['storeLocale'] = $this->locale;
        $config['calendarConfig'] = [
            'months' => $months,
            'monthsShort' => $monthsShort,
        ];
        if (!isset($config['dateFormat'])) {
            $config['dateFormat'] = $this->timezone->getDateTimeFormat(\IntlDateFormatter::MEDIUM);
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = null;
                $orderGridDetails = $this->carrierGroupHelper->loadOrderGridDetailByOrderId($item["entity_id"]);
                foreach ($orderGridDetails as $orderDetail) {
                    if ($orderDetail->getDeliveryDate() != '') {
                        $deliveryDate = $orderDetail->getDeliveryDate();

                        $date = $this->timezone->date(new \DateTime($deliveryDate), null, false, false);

                        $item[$this->getData('name')] = $date->format('Y-m-d');
                    }
                }
            }
        }

        return $dataSource;
    }
}
