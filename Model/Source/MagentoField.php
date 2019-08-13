<?php
namespace Combinatoria\Doppler\Model\Source;
class MagentoField implements \Magento\Framework\Option\ArrayInterface
{
    protected $_attributeFactory;
    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory)
    {
        $this->_attributeFactory = $attributeFactory;
    }
    public function toOptionArray()
    {
        $attributeInfo = $this->_attributeFactory->getCollection()->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 2);
        $options[] = [
            'label' => 'Select a field',
            'value' => '',
        ];
        foreach($attributeInfo as $attributes)
        {
            $options[] = [
                'label' => $attributes->getFrontendLabel(),
                'value' => $attributes->getAttributeCode(),
            ];
        }
        return $options;
    }
}