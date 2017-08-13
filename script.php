<?php
require_once 'app/Mage.php';
umask(0);
Mage::app();

//get all proucts in $all_products
$all_products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('id')
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //visible only catalog & searchable product
            ->addAttributeToFilter('status', 1) // enabled
            ;
if(count($all_products) > 0){
    //loop on products
    foreach($all_products as $all_products){

        $productId = $all_products->getId();

        // load all current product information
        $_product = Mage::getModel('catalog/product')->load($productId);

        // get current product categories
        $product_categories = $_product->getCategoryIds();

        // get the highest category id
        $product_highest_categoryId = max($product_categories);

        //possible related caritra
        $possible_caritras = ['name','description','created_at','updated_at','url_key','sku','price','special_price','special_from_date','special_to_date'];

        //generate random number between 0 and count of possible caritras
        $items_limit_f_t = rand(0, count($possible_caritras));

        $order_by = ['asc', 'desc'];

        //select all items under that category
        $possible_related_products = Mage::getModel('catalog/category')->load($product_highest_categoryId)
                ->getProductCollection()
                ->addAttributeToSelect('id')
                ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //visible only catalog & searchable product
                ->addAttributeToFilter('status', 1) // enabled
                ->setOrder($possible_caritras[$items_limit_f_t], $order_by[rand(0,1)])
                ->setCurPage(1)
                ->setPageSize(10);

        //generate related products parameters
        $param = [];
        $n = 0;
        foreach ($possible_related_products as $value) {
            $n = ++$n;
            $param[$value->getId()] = [
                'position' => $n
            ];
        }
        $link = $_product->getLinkInstance();
        $link->getResource()->saveProductLinks($_product, $param, $link::LINK_TYPE_RELATED);
        $link->getResource()->saveProductLinks($_product, $param, $link::LINK_TYPE_UPSELL);
        $link->getResource()->saveProductLinks($_product, $param, $link::LINK_TYPE_CROSSSELL);
    }
}
