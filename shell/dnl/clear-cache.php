<?
system("find ~/magento//var/full_page_cache/ -type f -exec rm -f {} \;");
system("find ~/magento//var/cache/mage--*/ -type f -exec rm -f {} \;");
?>
