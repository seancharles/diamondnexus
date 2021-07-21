<?php
namespace ForeverCompanies\ProductUrlPrefix\Model;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Category;

class CategoryUrlPathGen extends CategoryUrlPathGenerator
{
    protected $storeManager;
    protected $scopeConfig;
    protected $categoryRepository;
    
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        
        parent::__construct($storeManager, $scopeConfig, $categoryRepository);
    }
    
    public function getUrlKey($category)
    {
        $urlKey = $category->getUrlKey();
        
     //   echo 'the url key is ' . $urlKey;die;
        $category->setUrlKey('collections/' . $urlKey);
        return 'collections/' . $urlKey;
        
        return $category->formatUrlKey($urlKey === '' || $urlKey === null ? $category->getName() : $urlKey);
    }
    
    public function getUrlPath($category, $parentCategory = null)
    {
        if (in_array($category->getParentId(), [Category::ROOT_CATEGORY_ID, Category::TREE_ROOT_ID])) {
            echo 'aaa';
            die;
            return '';
        }
        
        $path = $category->getUrlPath();
        if ($path !== null && !$category->dataHasChangedFor('url_key') && !$category->dataHasChangedFor('parent_id')) {
            return $this->getUrlKey($category);
            return 'collections/' . $path;
        }
        $path = $category->getUrlKey();
        if ($path === false) {
            echo 'ccc';
            die;
            return $category->getUrlPath();
        }
        if ($this->isNeedToGenerateUrlPathForParent($category)) {
            echo 'ddd';
            die;
            $parentCategory = $parentCategory === null ?
            $this->categoryRepository->get($category->getParentId(), $category->getStoreId()) : $parentCategory;
            $parentPath = $this->getUrlPath($parentCategory);
            $path = $parentPath === '' ? $path : $parentPath . '/' . $path;
        }
        return $path;
    }
}
