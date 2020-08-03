# Mage2 Module ForeverCompanies CustomAttributes

    ``forevercompanies/module-customattributes``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities


## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/ForeverCompanies`
 - Enable the module by running `php bin/magento module:enable ForeverCompanies_CustomAttributes`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require forevercompanies/module-customattributes`
 - enable the module by running `php bin/magento module:enable ForeverCompanies_CustomAttributes`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications




## Attributes

 - Product - Product Type (product_type)

 - Product - Allow in bundles (allow_in_bundles)

 - Product - Bundle tags (bundle_tags)

 - Product - Bundle price use (bundle_price_use)

 - Product - is transformed (is_transformed)

 - Product - Dev tag (dev_tag)

