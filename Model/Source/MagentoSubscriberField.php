<?php
namespace Combinatoria\Doppler\Model\Source;

class MagentoSubscriberField implements \Magento\Framework\Option\ArrayInterface
{
    protected $resourceConnection;

    /**
     * MagentoSubscriberField constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }


    public function toOptionArray()
    {
        $sql = "SHOW FULL COLUMNS FROM newsletter_subscriber";
        $connection = $this->resourceConnection->getConnection();
        $attributeInfo = $connection->fetchAll($sql);

        $options[] = [
            'label' => 'Select a field',
            'value' => '',
        ];
        foreach($attributeInfo as $attributes)
        {
            if($attributes['Field'] == 'subscriber_email'){
                continue;
            }

            $options[] = [
                'label' => $attributes['Comment'],
                'value' => $attributes['Field'],
            ];
        }

        usort($options, array('Combinatoria\Doppler\Model\Source\MagentoSubscriberField','compareByName'));

        return $options;
    }

    private static function compareByName($a, $b) {
        return strcmp(strtolower($a["label"]), strtolower($b["label"]));
    }
}