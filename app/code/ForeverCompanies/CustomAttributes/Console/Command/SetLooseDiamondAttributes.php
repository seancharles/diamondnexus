<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetLooseDiamondAttributes extends Command
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:set-loose-diamond-attributes';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductInterface
     */
    protected $productRepository;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Action
     */
    protected $productActionObject;

    /**
     * @var
     */
    protected $helper;

    /**
     * ID for loose diamond clearance category
     * @var int
     */
    private $looseDiamondClearanceCategoryId = 906;

    /**
     * Sort values for color attribute values
     * @var int[]
     */
    private $colorSortValues  = [
        '2871' => 100,
        '2870' => 200,
        '2869' => 300,
        '2868' => 400,
        '2867' => 500,
        '2866' => 600,
        '2865' => 700,
    ];

    /**
     * Sort values for cut_grade attribute values
     * @var int[]
     */
    private $cutGradeSortValues = [
        '2879' => 100,
        '2878' => 200,
        '2876' => 300,
        '2877' => 400,
        '3076' => 500,
    ];

    /**
     * Sort values for clarity attribute values
     * @var int[]
     */
    private $claritySortValues = [
        '2858' => 100,
        '2857' => 200,
        '2861' => 300,
        '2859' => 400,
        '2863' => 500,
        '2862' => 600,
        '2854' => 700,
        '3564' => 800,
    ];

    /**
     * Sort values for shape attribute values, alphabetically
     * @var int[]
     */
    private $shapeAlphaSortValues = [
        '2844' => 100,
        '2845' => 200,
        '2848' => 300,
        '2846' => 400,
        '2851' => 500,
        '2847' => 600,
        '2850' => 700,
        '2843' => 800,
        '2849' => 900,
        '2842' => 1000,
    ];

    /**
     * Sort values for shape attribute values, popularity
     * @var int[]
     */
    private $shapePopSortValues = [
        '2842' => 100,
        '2843' => 200,
        '2845' => 300,
        '2847' => 400,
        '2848' => 500,
        '2850' => 600,
        '2844' => 700,
        '2849' => 800,
        '2851' => 900,
        '2846' => 1000,
    ];

    /**
     * TransformMultiselect constructor.
     * @param CollectionFactory $collectionFactory
     * @param ProductInterface $productRepository
     * @param Config $eavConfig
     * @param Action $action
     * @param TransformData $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductInterface $productRepository,
        Config $eavConfig,
        Action $action,
        TransformData $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->productActionObject = $action;
        $this->helper = $helper;
        parent::__construct($this->name);
    }


    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln("Set all loose diamond attributes...");

        // create arrays to hold our data for each attribute
        $colorSortData = [];
        $cutGradeData = [];
        $clarityData = [];
        $shapeAlphaData = [];
        $shapePopData = [];

        // get all loose diamonds
        $productCollection = $this->helper->getProductsLooseDiamonds();
        $output->writeln('Number of diamonds found: ' . $productCollection->count());

        // loop through each product and set all attributes
        $i = 1;
        foreach ($productCollection->getItems() as $item) {

            // get categories the product is assigned to
            $categoryIds = $item->getCategoryIds();

            // if the product is in the clearance category, continue on and do nothing with this product
            if (is_array($categoryIds) && !empty($categoryIds) && in_array($this->looseDiamondClearanceCategoryId, $categoryIds)) {
                $output->writeln("#" . $i . " - " . $item->getData('entity_id') . " - clearance item, skipped");
                continue;
            }

            // get color
            $color = $item->getData('color');
            if (array_key_exists($color, $this->colorSortValues)) {
                $colorSortData[$color][] = (int) $item->getData('entity_id');
            }

            // get cut grade
            $cutGrade = $item->getData('cut_grade');
            if (array_key_exists($cutGrade, $this->cutGradeSortValues)) {
                $cutGradeData[$cutGrade][] = (int) $item->getData('entity_id');
            }

            // get clarity
            $clarity = $item->getData('clarity');
            if (array_key_exists($clarity, $this->claritySortValues)) {
                $clarityData[$clarity][] = (int) $item->getData('entity_id');
            }

            // get shape for both alpha and pop sort
            $shape = $item->getData('shape');
            if (array_key_exists($shape, $this->shapeAlphaSortValues)) {
                $shapeAlphaData[$shape][] = (int) $item->getData('entity_id');
            }
            if (array_key_exists($shape, $this->shapePopSortValues)) {
                $shapePopData[$shape][] = (int) $item->getData('entity_id');
            }

            $output->writeln("#" . $i . " - " . $item->getData('entity_id') . " - data logged");
            $i++;
        }

        //
        // now we have our arrays of data that need to get updated, let's do the updates for each
        //

        $output->writeln('Starting update of attributes...');

        try {
            // update colors
            $output->writeln('Starting color_sort update...');
            if (!empty($colorSortData)) {
                foreach ($colorSortData as $color => $entityIds) {
                    if (array_key_exists($color, $this->colorSortValues)) {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['color_sort' => $this->colorSortValues[$color]],
                            0
                        );
                    }
                }
            }
            $output->writeln('color_sort updated!');

            // update cut grade
            $output->writeln('Starting cut_grade_sort update...');
            if (!empty($cutGradeData)) {
                foreach ($cutGradeData as $cutGrade => $entityIds) {
                    if (array_key_exists($cutGrade, $this->cutGradeSortValues)) {

                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['cut_grade_sort' => $this->cutGradeSortValues[$cutGrade]],
                            0
                        );
                    }
                }
            }
            $output->writeln('cut_grade_sort updated!');

            // update clarity
            $output->writeln('Starting clarity_sort update...');
            if (!empty($clarityData)) {
                foreach ($clarityData as $clarity => $entityIds) {
                    if (array_key_exists($clarity, $this->claritySortValues)) {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['clarity_sort' => $this->cutGradeSortValues[$clarity]],
                            0
                        );
                    }
                }
            }
            $output->writeln('cut_grade_sort updated!');

            // update shape alpha
            $output->writeln('Starting shape_alpha_sort update...');
            if (!empty($shapeAlphaData)) {
                foreach ($shapeAlphaData as $shapeAlpha => $entityIds) {
                    if (array_key_exists($shapeAlpha, $this->shapeAlphaSortValues)) {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['shape_alpha_sort' => $this->shapeAlphaSortValues[$shapeAlpha]],
                            0
                        );
                    }
                }
            }
            $output->writeln('shape_alpha_sort updated!');

            // update shape pop
            $output->writeln('Starting shape_pop_sort update...');
            if (!empty($shapePopData)) {
                foreach ($shapePopData as $shapePop => $entityIds) {
                    if (array_key_exists($shapePop, $this->shapePopSortValues)) {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['shape_pop_sort' => $this->shapePopSortValues[$shapePop]],
                            0
                        );
                    }
                }
            }
            $output->writeln('shape_pop_sort updated!');
        } catch (\Exception $e) {
            $output->writeln("Error: " . $e->getMessage());
        }

        $output->writeln('Loose diamonds attributes updated! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Set Product Types - update the product_type attribute");
        parent::configure();
    }
}
