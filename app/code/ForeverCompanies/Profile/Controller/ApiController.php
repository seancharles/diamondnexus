<?php
namespace ForeverCompanies\Profile\Controller;
     
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
     
abstract class ApiController extends Action implements CsrfAwareActionInterface
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }
        
    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
             return null;
    }
        
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
