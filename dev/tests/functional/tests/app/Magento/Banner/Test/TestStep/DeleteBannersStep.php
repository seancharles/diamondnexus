<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Banner\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Banner\Test\Page\Adminhtml\BannerIndex;
use Magento\Banner\Test\Page\Adminhtml\BannerNew;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Delete provided banners.
 */
class DeleteBannersStep implements TestStepInterface
{
    /**
     * @var string[]|null
     */
    private $banners;

    /**
     * BannerIndex page
     *
     * @var BannerIndex
     */
    private $bannerIndex;

    /**
     * BannerNew page
     *
     * @var BannerNew
     */
    private $bannerNew;

    /**
     * @var Cache
     */
    private $cacheCli;

    /**
     * @param BannerIndex $bannerIndex
     * @param BannerNew $bannerNew
     * @param Cache $cache
     * @param string[]|null $banners
     */
    public function __construct(BannerIndex $bannerIndex, BannerNew $bannerNew, Cache $cache, ?array $banners = null)
    {
        $this->banners = $banners;
        $this->bannerIndex = $bannerIndex;
        $this->bannerNew = $bannerNew;
        $this->cacheCli = $cache;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        if ($this->banners) {
            foreach ($this->banners as $banner) {
                $this->bannerIndex->open();
                $this->bannerIndex->getGrid()->searchAndOpen(['banner' => $banner]);
                $this->bannerNew->getPageMainActions()->delete();
                $this->bannerNew->getModalBlock()->acceptAlert();
            }
            $this->cacheCli->flush();
        }
    }
}
