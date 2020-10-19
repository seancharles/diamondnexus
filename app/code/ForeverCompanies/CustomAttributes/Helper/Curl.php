<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class Curl extends AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $curl;

    /**
     * Curl constructor.
     * @param Context $context
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Adapter\Curl $curl
    )
    {
        parent::__construct($context);
        $this->curl = $curl;
    }

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $url)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $url);
        $image = $this->curl->read();
        if (empty($image)) {
            throw new LocalizedException(
                __('The preview image information is unavailable. Check your connection and try again.')
            );
        }
        return $image;
    }
}
