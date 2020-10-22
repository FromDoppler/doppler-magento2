<?php
namespace Combinatoria\Doppler\Model\Source;

class MagentoCustomerField implements \Magento\Framework\Option\ArrayInterface
{
    protected $_attributeFactory;
    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory)
    {
        $this->_attributeFactory = $attributeFactory;
    }
    public function toOptionArray()
    {
        $attributeInfo = $this->_attributeFactory->getCollection()->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 1);
        $options[] = [
            'label' => 'Select a field',
            'value' => '',
        ];
        foreach($attributeInfo as $attributes)
        {
            if($attributes->getAttributeCode() == 'region_id'){
                $label = __("State/Province ID");
            }else{
                $label = $attributes->getFrontendLabel();
            }

            $options[] = [
                'label' => $label,
                'value' => $attributes->getAttributeCode(),
            ];
        }

        usort($options, array('Combinatoria\Doppler\Model\Source\MagentoCustomerField','compareByName'));

        return $options;
    }

    private static function compareByName($a, $b) {
        return strcmp(strtolower($a["label"]), strtolower($b["label"]));
    }
}