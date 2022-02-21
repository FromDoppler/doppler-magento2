<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Combinatoria\Doppler\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config as resourceConfig;
use Magento\Eav\Model\Config;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\OauthService;

use Magento\Integration\Model\ResourceModel\Integration;
use Magento\Integration\Model\ResourceModel\Oauth\Token as resourceModelToken;
use Psr\Log\LoggerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class dopplerIntegration implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $eavConfig;
    private $integration;
    private $oauthService;
    private $authorizationService;
    private $token;
    private $logger;
    protected $resourceConfig;
    protected $resourceModelIntegration;
    protected $resourceModelToken;
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        IntegrationFactory $integrationFactory,
        OauthService $oauthService,
        AuthorizationService $authorizationService,
        Token $token,
        LoggerInterface $logger,
        resourceConfig $resourceConfig,
        Integration $resourceModelIntegration,
        resourceModelToken $resourceModelToken
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->integration          = $integrationFactory;
        $this->oauthService         = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token                = $token;
        $this->logger               = $logger;
        $this->resourceConfig       = $resourceConfig;
        $this->resourceModelIntegration       = $resourceModelIntegration;
        $this->resourceModelToken       = $resourceModelToken;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws \Exception
     */
    public function apply()
    {
        $this->resourceConfig->saveConfig(
            'doppler_config/integration/enabled',
            0,
            'default',
            0
        );
        $integrationExists = $this->resourceModelIntegration->load($this->integration->create(), "doppler", 'name');
        if (empty($integrationExists)) {
            try {
                $integrationData = [
                    'name'       => 'doppler',
                    'email'      => 'integrations@doppler.com',
                    'status'     => '1',
                    'endpoint'   => '',
                    'setup_type' => '0'
                ];

                // Code to create Integration
                $integrationFactory = $this->integration->create();
                $integration = $integrationFactory->setData($integrationData);

                $this->resourceModelIntegration->save($integration);
                $integrationId = $integration->getId();
                $consumerName = 'Integration' . $integrationId;

                // Code to create consumer
                $oauthService = $this->oauthService;
                $consumer = $oauthService->createConsumer(['name' => $consumerName]);
                $consumerId = $consumer->getId();
                $integration->setConsumerId($consumer->getId());
                $this->resourceModelIntegration->save($integration);

                // Code to grant permission
                $authrizeService = $this->authorizationService;
                $authrizeService->grantAllPermissions($integrationId);

                // Code to Activate and Authorize
                $token = $this->token;
                $uri = $token->createVerifierToken($consumerId);
                $token->setType('access');
                $this->resourceModelToken->save($token);

                $this->resourceConfig->saveConfig(
                    'doppler_config/integration/token',
                    $token->getToken(),
                    'default',
                    0
                );
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
