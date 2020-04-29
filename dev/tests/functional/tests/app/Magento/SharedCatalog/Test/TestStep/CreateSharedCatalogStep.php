<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Create shared catalog.
 */
class CreateSharedCatalogStep implements TestStepInterface
{
    /**
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var string
     */
    private $sharedCatalog;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param string $sharedCatalog
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        string $sharedCatalog
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalog = $sharedCatalog;
    }

    /**
     * Create shared catalog.
     *
     * @return array
     */
    public function run()
    {
        $sharedCatalog = $this->fixtureFactory->createByCode(
            'shared_catalog',
            ['dataset' => $this->sharedCatalog]
        );
        $sharedCatalog->persist();

        return ['sharedCatalog' => $sharedCatalog];
    }
}
