<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Logging\Controller\Adminhtml\Logging;

/**
 * Test logging changes
 *
 * @magentoAppArea adminhtml
 */
class EventLoggingChangesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test that configured admin actions are properly logged with changes.
     *
     * @magentoDataFixture Magento/Logging/_files/user_and_role.php
     * @magentoDbIsolation enabled
     */
    public function testLogChangesOnDeleteAction()
    {
        $user = $this->_objectManager->get(\Magento\User\Model\User::class)->loadByUsername('newuser');

        $action = 'delete';
        $url = 'backend/admin/user/' . $action;
        $post = ['user_id' => $user->getId(), 'current_password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD];

        $this->getRequest()->setMethod('POST')->setPostValue($post);
        $this->dispatch($url);

        /* load last event */
        $lastEvent = $this->getLastEventByAction('adminhtml_user_' . $action);
        $info = json_decode($lastEvent->getInfo());
        /* asserts */
        $this->assertEquals($action, $lastEvent['action'], $action . ' event wasn\'t logged');
        $this->assertEquals('1, ' . $user->getId(), $info->general, $action . ' event didn\'t save deleted user id');
        $this->assertTrue($lastEvent->hasChanges(), 'Event didn\'t save changes');

        /* load event changes */
        $change = $this->getChangesByEventId($lastEvent->getLogId());
        /* check changes */
        $originalData = json_decode($change->getOriginalData());
        $this->assertEquals($user->getUserId(), $originalData->user_id, 'user_id differs in changes');
        $this->assertEquals($user->getUserName(), $originalData->username, 'username differs in changes');
        $this->assertEquals($user->getFirstName(), $originalData->firstname, 'first_name differs in changes');
        $this->assertEquals($user->getLastName(), $originalData->lastname, 'last_name differs in changes');
        $this->assertEquals($user->getEmail(), $originalData->email, 'email differs in changes');
    }

    /**
     * Find last event by action.
     *
     * @param string $action
     * @return \Magento\Logging\Model\Event
     */
    private function getLastEventByAction(string $action): \Magento\Logging\Model\Event
    {
        $collection = $this->_objectManager->create(\Magento\Logging\Model\Event::class)->getCollection();
        $lastEvent = $collection->addFieldToFilter('fullaction', $action)
            ->setOrder('time', 'DESC')
            ->getLastItem();

        return $lastEvent;
    }

    /**
     * Find changes by event.
     *
     * @param int $eventId
     * @return \Magento\Logging\Model\Event\Changes
     */
    private function getChangesByEventId(int $eventId): \Magento\Logging\Model\Event\Changes
    {
        $changesCollection = $this->_objectManager->create(\Magento\Logging\Model\Event\Changes::class)
            ->getCollection();
        $change = $changesCollection->addFieldToFilter('event_id', $eventId)
            ->setPageSize(1)
            ->getFirstItem();

        return $change;
    }
}
