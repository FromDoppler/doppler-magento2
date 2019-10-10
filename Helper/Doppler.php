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
namespace Combinatoria\Doppler\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\ObjectManagerInterface;
use Combinatoria\Doppler\Model\ResourceModel\Map\Collection as Map;
use Combinatoria\Doppler\Model\ResourceModel\Map\CollectionFactory as MapFactory;
use Magento\Integration\Model\IntegrationFactory;

/**
 * Class Doppler
 * @package Combinatoria\Doppler\Helper
 */
class Doppler extends AbstractHelper{
    /**
     * @var ObjectManagerInterface $objectManager
     */
    protected $objectManager;

    protected $_scopeConfig;
    private $_configInterface;

    protected $_map;
    protected $_mapFactory;

    protected $_leadMapping = array();

    private $integration;
    private $storeManager;

    const CONFIG_DOPPLER_SYNC_CRON_FREQUENCY_PATH = 'doppler_config/synch/frequency';
    const CONFIG_DOPPLER_SYNC_CRON_EXPR_PATH               = 'crontab/default/jobs/doppler_synch/schedule/cron_expr';
    const CONFIG_DOPPLER_SYNC_CRON_MODEL_PATH              = 'crontab/default/jobs/doppler_synch/run/model';
    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $configInterface,
        ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Map $map,
        MapFactory $mapFactory,
        IntegrationFactory $integrationFactory
    ) {
        parent::__construct($context);

        $this->_scopeConfig = $scopeConfig;
        $this->_configInterface = $configInterface;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->connection = $resource->getConnection();
        $this->_map = $map;
        $this->_mapFactory = $mapFactory;
        $this->integration = $integrationFactory;
    }

    /**
     * Returns config value
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, Store::DEFAULT_STORE_ID);
    }

    /**
     * Saves config value
     *
     * @param string $path
     * @param mixed  $value
     * @return void
     */
    public function setConfigValue($path, $value)
    {
        $this->_configInterface->saveConfig($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, Store::DEFAULT_STORE_ID);
        return;
    }
    /**
     * API call to test if Doppler API is active
     */
    public function testAPIConnection()
    {
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        // API not available error code
        $statusCode = '4040';

        if($usernameValue != '' && $apiKeyValue != '')
        {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }

            // Close request to clear up some resources
            curl_close($ch);
        } else {
            throw new \Exception(
                __('Error: Fill username and key.')
            );
        }

        return $statusCode;
    }

    /**
     * Get all fields from Doppler
     *
     * @return array
     */
    public function getDopplerFields()
    {
        $fieldsArray = array();

        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/fields');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);

                if(isset($responseContent['items'])){
                    $fieldsResponseArray = $responseContent['items'];

                    foreach ($fieldsResponseArray as $field)
                    {
                        $fieldName = $field['name'];

                        // The 'EMAIL' field shouldn't be available since it's read-only in Doppler
                        if ($fieldName != 'EMAIL')
                        {
                            $fieldsArray[$fieldName] = $fieldName;
                        }
                    }
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $fieldsArray;
    }

    /**
     * Get Doppler lists from API
     *
     * @return array
     */
    public function getDopplerLists()
    {
        $listsArray = array();

        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/lists?page=1&per_page=200');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);

                if(isset($responseContent['items'])){
                    $listsResponseArray = $responseContent['items'];

                    if(is_array($listsResponseArray)){
                        foreach ($listsResponseArray as $list)
                        {
                            $list['id_field_name'] = 'listId';
                            $listsArray[] = $list;
                        }
                    }
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $listsArray;
    }

    /**
     * Create Doppler list from API
     *
     * @param string $name
     * @return array
     */
    public function createDopplerLists($name)
    {
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '')
        {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                    "Content-Type: application/json",
                ]
            );

            // Create body
            $body = '{ name: "' . $name . '" }';

            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode == '201')
                {
                    $responseContent = json_decode($resp, true);
                    return $responseContent['createdResourceId'];
                } else {
                    $responseContent = json_decode($resp, true);
                    throw new \Exception(
                        __('The following errors occurred creating your list: ' . $responseContent['title'])
                    );
                }
            }
            // Close request to clear up some resources
            curl_close($ch);
        }
    }

    /**
     * Export multiple Magento customers to Doppler
     *
     * @param $customers
     * @param $dopplerListId
     *
     * @return bool $errorOnExport
     */
    public function exportMultipleCustomersToDoppler($customers, $dopplerListId)
    {
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '')
        {
            $dopplerMappedFields = $this->getDopplerMappedFields();

            // Create body
            $body = '{ "fields": [ ';

            $mappedFieldsCount = count($dopplerMappedFields);
            $leadMappingArrayKeys = array_keys($dopplerMappedFields);

            $dopplerFieldsDataType = array();
            for ($i = 0; $i < $mappedFieldsCount; $i++) {
                $fieldName = $leadMappingArrayKeys[$i];
                $dopplerFieldsDataType[$fieldName] = $this->getDopplerFieldDataType($fieldName);
            }

            // Get list of mapped fields
            for ($i = 0; $i < $mappedFieldsCount; $i++)
            {
                $fieldName = $leadMappingArrayKeys[$i];
                $body .= '"' . $fieldName . '"';

                if (($i + 1) < $mappedFieldsCount)
                {
                    $body .= ',';
                }
            }

            $body .= '],';

            $body .= '"items": [ ';

            $customerCount = $customers->getSize();

            $customerCounter = 1;

            $customersArray = $customers->getData();

            $list = $this->getDopplerSubscribers($dopplerListId);

            $items = 0;
            foreach ($customersArray as $customer)
            {
                if(isset($customer['email'])){
                    if($this->isSynchronized($customer['email'],$list)){
                        continue;
                    }
                }

                if(isset($customer['subscriber_email'])){
                    if($this->isSynchronized($customer['subscriber_email'],$list)){
                        continue;
                    }
                }

                $items = 1;
                // Load Magento customer attributes from mapped fields
                foreach ($dopplerMappedFields as $field)
                {
                    // Cleanup $field value
                    $trimmedFieldValue = trim($field);

                    // Get data from customer attribute
                    if(!isset($customer[$trimmedFieldValue])){
                        $customer[$trimmedFieldValue] = '';
                    }
                    $customerData =  $customer[$trimmedFieldValue];

                    $this->_customerAttributes[$trimmedFieldValue] = $customerData;

                }

                if(!isset($customer['email']) && isset($customer['subscriber_email'])){
                    $customer['email'] = $customer['subscriber_email'];
                }
                //$this->log($this->_customerAttributes,'customer-attributes.log');

                $body .= '{ "email": "' . $customer['email'] . '", ';

                
                $body .= ' "fields": [ ';
                
                if($mappedFieldsCount > 0){
                    $customerAttributesArrayKeys = array_keys($this->_customerAttributes);

                    for ($i = 0; $i < $mappedFieldsCount; $i++)
                    {
                        $fieldName = $leadMappingArrayKeys[$i];
                        $customerAttributeValue = $this->_customerAttributes[$customerAttributesArrayKeys[$i]];

                        // Validate each mapped field before exporting
                        $dopplerFieldDataType = $dopplerFieldsDataType[$fieldName];

                        switch ($dopplerFieldDataType) {
                            case 'date':
                                // Format: yyyy-MM-dd
                                if ($dopplerFieldDataType == 'date' ||
                                    $dopplerFieldDataType == 'datetime'
                                ) {
                                    $customerAttributeValue = $this->getFormattedDate($customerAttributeValue);
                                }
                                break;
                            case 'gender':
                                // M or F
                                // Magento saves 1 for Male and 2 for Female
                                // Conver that to M for Male and F for Female
                                if ($customerAttributesArrayKeys[$i] == 'gender')
                                {
                                    if ($customerAttributeValue == 1)
                                    {
                                        $customerAttributeValue = 'M';
                                    } else if ($customerAttributeValue == 2)
                                    {
                                        $customerAttributeValue = 'F';
                                    }
                                }

                                break;
                            case 'country':
                                // Country: ISO 3166-1 alpha 2
                                // Check if attribute is 'country', if not return false
                                // Magento already stores the country in ISO 3166-1 alpha 2
                                // No conversion is necessary
                                break;
                            default:
                        }

                        $body .= '{ "name": "' . $fieldName . '", "value": "' . $customerAttributeValue . '"';

                        if (($i + 1) < $mappedFieldsCount)
                        {
                            $body .= '},';
                        } else {
                            $body .= '}';
                        }
                    }
                }

                if ($customerCounter == $customerCount)
                {
                    $body .= ']}';
                } else {
                    $body .= ']},';
                }

                $customerCounter++;

            }

            if(!$items){
                return true;
            }
            $body .= '],}}';

            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists/' . $dopplerListId . '/subscribers/import');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token " . $apiKeyValue,
                "Content-Type: application/json",
                "X-Doppler-Subscriber-Origin: Magento"
            ]);

            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                // There has been an error when trying to export the customer to Doppler
                // Then process the error message
                $responseContent = json_decode($resp, true);

                // If the response contains the 'error' item, then it's a validation error
                if (isset($responseContent['status']) && $responseContent['status'] != 200)
                {
                    $errorResponseArray = $responseContent['errors'];
                    foreach ($errorResponseArray as $error) {
                        $errors[] = $error['detail'];
                    }
                    throw new \Exception(implode("\n",$errors));
                }else{
                    return true;
                }
            }
            // Close request to clear up some resources
            curl_close($ch);
        }else{
            throw new \Exception(
                __('Error: Fill username and key.')
            );
        }
    }

    public function getDopplerMappedFields() {

        // Get Doppler mapped fields from Magento
        $leadmapCollection = $this->_mapFactory->create();

        foreach ($leadmapCollection->getData() as $leadmap)
        {
            $this->_leadMapping[$leadmap['doppler_field_name']] = $leadmap['magento_field_name'];
        }

        return $this->_leadMapping;
    }

    /**
     * Get data type from Doppler field
     *
     * Possible results:
     * boolean
     * number
     * string (400 character max)
     * date (yyyy-MM-dd)
     * gender (M or F)
     * country (ISO 3166-1 alpha-2)
     *
     * @param string $dopplerFieldName
     * @return string
     */
    public function getDopplerFieldDataType($dopplerFieldName)
    {
        $this->_fieldsArray = array();

        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/fields');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);

                if(isset($responseContent['items'])){
                    $fieldsResponseArray = $responseContent['items'];

                    foreach ($fieldsResponseArray as $field)
                    {
                        $fieldName = $field['name'];

                        if ($fieldName == $dopplerFieldName)
                        {
                            return $field['type'];
                        }
                    }
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return '';
    }

    /**
     * Get all subscribers from Doppler
     *
     * @return array
     */
    public function getDopplerSubscribers($listId)
    {
        $fieldsResponseArray = array();
        $this->_fieldsArray = array();

        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/lists/' . $listId . '/subscribers');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);
                if(isset($responseContent['items'])){
                    $fieldsResponseArray = $responseContent['items'];
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $fieldsResponseArray;
    }

    /**
     * @param $mensaje String
     * @param $archivo String
     */
    public static function log($mensaje,$archivo)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$archivo);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($mensaje);
    }

    /**
     * Get date in format yyyy-MM-dd
     *
     * Native Magento format: yyyy-mm-dd hh:mm:ss
     *
     * @return string
     */
    public function getFormattedDate($date)
    {
        $formattedDate = '';

        if ($date)
        {
            $dateTime = strtotime($date);
            $formattedDate = date('Y-m-d', $dateTime);
        }

        return $formattedDate;
    }

    public function isSynchronized($email,$list){
        foreach ($list as $item){
            if($email == $item['email']){
                return true;
            }
        }
        return false;
    }

    public function putIntegration(){
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        $accessToken = $this->getConfigValue('doppler_config/integration/token');
        $accountName = $this->storeManager->getStore()->getBaseUrl();

        if($usernameValue != '' && $apiKeyValue != '')
        {
            $body = '{ "accessToken":"' . $accessToken . '", "accountName":"' . $accountName . '" }';
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/integrations/magento');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token " . $apiKeyValue,
                "Content-Type: application/json",
            ]);

            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                // There has been an error when trying to export the customer to Doppler
                // Then process the error message
                $responseContent = json_decode($resp, true);

                // If the response contains the 'error' item, then it's a validation error
                if (isset($responseContent['status']) && $responseContent['status'] != 200)
                {
                    if($responseContent['errorCode'] == 42){
                        $errorMsg = __("Ouch! Your Magento store is already connected through the Control Panel of your Doppler Account.");
                    }else{
                        $errorMsg = __($responseContent['detail']);
                    }

                    throw new \Exception($errorMsg);
                }else{
                    return true;
                }
            }
            // Close request to clear up some resources
            curl_close($ch);
        }
    }

    public function deleteIntegration(){
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if($usernameValue != '' && $apiKeyValue != '')
        {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/integrations/magento');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token " . $apiKeyValue,
                "Content-Type: application/json",
            ]);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                // There has been an error when trying to export the customer to Doppler
                // Then process the error message
                $responseContent = json_decode($resp, true);

                // If the response contains the 'error' item, then it's a validation error
                if (isset($responseContent['status']) && $responseContent['status'] != 200)
                {
                    if($responseContent['errorCode'] == 40){
                        $errorMsg = __("Ouch! You can't disconnect the integration 'cause you've Campaigns associated to it.");
                    }else{
                        $errorMsg = __($responseContent['detail']);
                    }

                    throw new \Exception($errorMsg);
                }else{
                    return true;
                }
            }
            // Close request to clear up some resources
            curl_close($ch);
        }
    }

    public function deleteList($listId)
    {
        $usernameValue = $this->getConfigValue('doppler_config/config/username');
        $apiKeyValue = $this->getConfigValue('doppler_config/config/key');

        if ($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists/' . $listId);

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                    "Content-Type: application/json",
                ]
            );


            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode == '200') {
                    return true;
                } else {
                    $responseContent = json_decode($resp, true);
                    $errorMsg = '';
                    if($responseContent['errorCode'] == 8){
                        switch ($responseContent['blockingReasonCode']){
                            case "CannotDeleteSubscribersListWithAnScheduledCampaign":
                                $errorMsg = __("Ouch! The List is associated to a Campaign in sending process.");
                                break;
                            case "CannotDeleteSubscribersListWithAnAssociatedSegment":
                                $errorMsg = __("Ouch! The List has associated Segments. To delete it, go to Doppler and disassociate them.");
                                break;
                            case "CannotDeleteSubscribersListWithAnAssociatedEvent":
                                $errorMsg = __("Ouch! The List is associated with an active Automation. To delete it, go to Doppler and disassociate them.");
                                break;
                            case "CannotDeleteSubscribersListWithAnAssociatedForm":
                                $errorMsg = __("Ouch! The List is associated with a Form. To delete it, go to Doppler and disassociate them.");
                                break;
                            case "CannotDeleteSubscribersListWithAnAssociatedIntegration":
                                $errorMsg = __("Ouch! The List is associated with an active integration. To delete it, go to Doppler and disconnect the integration.");
                                break;
                            case "CannotDeleteSubscribersListInMergingProcess":
                                $errorMsg = __("Ouch! The List is in the process of union with another one.");
                                break;
                            case "CannotDeleteSubscribersListInSegmentGenerationProcess":
                                $errorMsg = __("Ouch! The List is still in the process of being created.");
                                break;
                            case "CannotDeleteSubscribersListInImportSubscribersProcess":
                                $errorMsg = __("Ouch! The List is in the process of loading.");
                                break;
                            case "CannotDeleteSubscribersListInExportSubscribersProcess":
                                $errorMsg = __("Ouch! the list is in process of being exported.");
                                break;
                            default:
                                $errorMsg = __($responseContent['detail']);
                        }
                    }else if($responseContent['errorCode'] == 1){
                        $errorMsg = __("Ouch! The List is in the process of being deleted.");
                    }else{
                        $errorMsg = __($responseContent['detail']);
                    }

                    throw new \Exception($errorMsg);
                }
            }
        }
    }
}
