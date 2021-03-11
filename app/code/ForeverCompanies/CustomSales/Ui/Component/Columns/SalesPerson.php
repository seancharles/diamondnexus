<?php

namespace ForeverCompanies\CustomSales\Ui\Component\Columns;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\User\Model\UserFactory;

class SalesPerson extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param UserFactory $userFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->userFactory = $userFactory;

        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                if ($item["sales_person_id"] > 0) {
                    // it's method derpecated. Todo: load by resource model or repository
                    $user = $this->userFactory->create()->load($item["sales_person_id"]);

                    $item[$this->getData('name')] = $user->getUsername();
                } else {
                    $item[$this->getData('name')] = "Web";
                }
            }
        }

        return $dataSource;
    }
}
