<?php
namespace Fahim\BrandImport\Model\Source;
 
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
 
class Brand implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
 
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }
 
    public function toOptionArray()
    {
        $brandId = 2064;
        $options[] = ['label' => '-- Please Select --', 'value' => ''];
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')->addAttributeToFilter('parent_id',['eq'=>$brandId]);
        
        $options[] = ['label' => 'All Brands', 'value' => '*'];
        foreach ($collection as $category) {
            $brand = trim($category->getName());
            $options[] = [
                'label' => $brand,
                'value' => $brand,
            ];
        }
 
        return $options;
    }
}
