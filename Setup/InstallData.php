<?php
/**
 * Doppler Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Combinatoria
 * @package     Combinatoria_Doppler
 */
namespace Combinatoria\Doppler\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\Oauth\Token;
use Magento\Config\Model\ResourceModel\Config as resourceConfig;

/**
 * Class InstallData
 * @package Combinatoria\Doppler\Setup
 */
class InstallData implements InstallDataInterface{

    private $eavSetupFactory;
    private $eavConfig;
    private $integration;
    private $oauthService;
    private $authorizationService;
    private $token;
    protected $resourceConfig;

    public function __construct(EavSetupFactory $eavSetupFactory,
                                Config $eavConfig,
                                IntegrationFactory $integrationFactory,
                                OauthService $oauthService,
                                AuthorizationService $authorizationService,
                                Token $token,
                                resourceConfig $resourceConfig)
    {
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->integration          = $integrationFactory;
        $this->oauthService         = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token                = $token;
        $this->resourceConfig       = $resourceConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /* Add DopplerSynced Attribute */
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'doppler_synced',
            [
                'type'         => 'int',
                'label'        => 'Exported to Doppler',
                'input'        => 'boolean',
                'required'     => false,
                'visible'      => true,
                'user_defined' => false,
                'position'     => 998,
                'system'       => 0,
            ]
        );
        $syncedAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'doppler_synced');

        // more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
        $syncedAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );

        $syncedAttribute->addData([
            'attribute_set_id' => 1,
            'attribute_group_id' => 1
        ]);

        $syncedAttribute->save();

        /* Add DopplerExportStatus Attribute */
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'status_doppler_sync',
            [
                'type'         => 'varchar',
                'label'        => 'Doppler Export Status',
                'default'      => 'Pending',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
            ]
        );
        $statusAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'status_doppler_sync');

        // more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
        $statusAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );

        $statusAttribute->addData([
            'attribute_set_id' => 1,
            'attribute_group_id' => 1
        ]);

        $statusAttribute->save();

        $this->resourceConfig->saveConfig(
            'doppler_config/integration/enabled',
            0,
            'default',
            0
        );

        $integrationExists = $this->integration->create()->load("doppler",'name')->getData();
        if(empty($integrationExists)){
            $integrationData = array(
                'name' => 'doppler',
                'email' => 'integrations@doppler.com',
                'status' => '1',
                'endpoint' => '',
                'setup_type' => '0'
            );
            try{
                // Code to create Integration
                $integrationFactory = $this->integration->create();
                $integration = $integrationFactory->setData($integrationData);
                $integration->save();
                $integrationId = $integration->getId();$consumerName = 'Integration' . $integrationId;


                // Code to create consumer
                $oauthService = $this->oauthService;
                $consumer = $oauthService->createConsumer(['name' => $consumerName]);
                $consumerId = $consumer->getId();
                $integration->setConsumerId($consumer->getId());
                $integration->save();


                // Code to grant permission
                $authrizeService = $this->authorizationService;
                $authrizeService->grantAllPermissions($integrationId);


                // Code to Activate and Authorize
                $token = $this->token;
                $uri = $token->createVerifierToken($consumerId);
                $token->setType('access');
                $token->save();

                $this->resourceConfig->saveConfig(
                    'doppler_config/integration/token',
                    $token->getToken(),
                    'default',
                    0
                );

            }catch(Exception $e){
                echo 'Error : '.$e->getMessage();
            }
        }
    }
}