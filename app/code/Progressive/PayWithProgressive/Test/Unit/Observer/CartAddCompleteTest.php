<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Test\Unit\Observer;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;

class CartAddCompleteTest extends TestCase
{
    /** @var \Magento\Framework\Event\Observer */
    protected $_object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $_objectManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_cartAddSession;

    protected function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_cartAddSession = $this->getMock( 'Magento\Catalog\Model\Product' [], [], '', false);
        $this->_object = $this->_objectManager->getObject('Magento\Framework\Event\Observer', [
            'cartAddSession' => $this->_cartAddSession,
        ]);
    }

    public function testCardAddComplete()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], false);
        $observer->expects($this->once())->method('getEvent')->will(
            $this->returnValue(new \Magento\Framework\DataObject(
                ['product' => ]
            ))
        )
    }



}
