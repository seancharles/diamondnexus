<?php

	namespace ForeverCompanies\Forms\Controller;
	 
	use Magento\Framework\Controller\ResultFactory;
	use Magento\Framework\App\RequestInterface;
	use Magento\Framework\App\Request\InvalidRequestException;
	 
	abstract class ApiController extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
	{
		public function __construct(\Magento\Framework\App\Action\Context $context ) {
			parent::__construct($context); 
		} 
		
		public function createCsrfValidationException( RequestInterface $request ): ?       InvalidRequestException {
				 return null; 
		} 
		
		public function validateForCsrf(RequestInterface $request): ?bool {
			return true; 
		}
	}
