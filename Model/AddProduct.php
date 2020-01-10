<?php
namespace GetCP\Rest\Model;
use GetCP\Rest\Api\AddProductInterface;

class AddProduct implements AddProductInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function name($name) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $product = $objectManager->create('\Magento\Catalog\Model\Product');
        $product->setSku($name);
        /*$sku = $_GET['sku']; */
        $product->setName($name); 
        $product->setAttributeSetId(4); 
        $product->setStatus(1);
        $weight = $_GET['weight'];  
        $product->setWeight($weight); 
        $product->setVisibility(4);
        $product->setTaxClassId(0);
        $product->setTypeId('simple');
        $price = $_GET['price']; 
        $product->setPrice($price); 
        $product->setWebsiteIds(array(1));
        $product->setStockData(
                                array(
                                    'use_config_manage_stock' => 0,
                                    'manage_stock' => 1,
                                    'is_in_stock' => 1,
                                    'qty' => 5555
                                )
                            );
        $product->save();
         
         //print_r($product);
         //die('jj');
        $categoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
        $categoryIds = array('4','5');
        $categoryLinkRepository->assignProductToCategories($name, $categoryIds);
        return $name ." Product added Successfully.";
        echo json_encode($product);
    }
}
?>