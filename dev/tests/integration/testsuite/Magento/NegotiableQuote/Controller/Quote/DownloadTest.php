<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\NegotiableQuote\Model\CommentManagement;
use Magento\NegotiableQuote\Model\CommentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class DownloadTest extends AbstractController
{
    /**
     * @magentoDataFixture Magento/NegotiableQuote/_files/quote_with_comment_attachment.php
     */
    public function testNormalDownload()
    {
        $this->login(567);
        $attachmentId = $this->getAttachmentIdWithCommentText('A file is attached');

        ob_start();
        $this->dispatch('negotiable_quote/quote/download/attachmentId/' . $attachmentId);
        $response = ob_get_clean();

        $this->assertSame('hello world', $response);
    }

    public function testInvalidAttachmentIsNotFound()
    {
        $this->dispatch('negotiable_quote/quote/download/attachmentId/123');

        $this->assertEquals('noroute', $this->getRequest()->getControllerName());
        $this->assertContains('404 Not Found', $this->getResponse()->getBody());
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    private function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\Session::class);
        $session->loginById($customerId);
    }

    private function getAttachmentIdWithCommentText(string $commentText): string
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField(CommentInterface::COMMENT)
            ->setValue($commentText)
            ->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilters([$filter1]);
        $searchCriteria = $searchCriteriaBuilder->create();

        /** @var CommentRepositoryInterface $commentRepository */
        $commentRepository = Bootstrap::getObjectManager()->create(CommentRepositoryInterface::class);

        $searchResult = $commentRepository->getList($searchCriteria);
        /** @var CommentInterface $comment */
        $comment = current($searchResult->getItems());

        /** @var CommentManagement $commentManagment */
        $commentManagment = Bootstrap::getObjectManager()->create(CommentManagement::class);
        $attachments = $commentManagment->getCommentAttachments($comment->getEntityId())
            ->getItems();

        return current($attachments)
            ->getData('attachment_id');
    }
}
