<?php
namespace GetCP\Rest\Api;
 
/**
 * @api
 */
interface CategoryLinkInterface
{
    /**
     * Get products assigned to a category
     *
     * @param int $categoryId
     * @return \GetCP\Rest\Api\Data\CategoryProductLinkInterface[]
     */
    public function getAssignedProducts($categoryId);
}
?>