<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Amazon\Sns;

interface SnsClientInterface
{

    /**
     * Amazon Sns constructor
     *
     * @param $accessKeyId
     * @param $secretAccessKey
     */
    public function  __construct($accessKeyId, $secretAccessKey);

    /**
     * Executes the ConfirmSubscription operation.
     *
     * Verifies an endpoint owner's intent to receive messages by validating the token sent to the endpoint by an earlier Subscribe action.
     * If the token is valid, the action creates a new subscription and returns its Amazon Resource Name (ARN).
     * This call requires an AWS signature only when the $authenticateOnUnsubscribe flag is set to "true".
     *
     * @param string $topicArn
     * @param string $token
     * @param bool   $authenticateOnUnsubscribe
     *
     * @return mixed
     */
    public function confirmSubscription($topicArn, $token, $authenticateOnUnsubscribe = false);

    /**
     * Executes the CreatePlatformApplication operation.<br>
     *
     * Creates a platform application object for one of the supported push notification services, such as APNS and GCM, to which devices and mobile apps may register.<br>
     *
     * Amazon Device Messaging (<b>ADM</b>)<br>
     * Apple Push Notification Service (<b>APNS</b>)<br>
     * Baidu Cloud Push (<b>Baidu</b>)<br>
     * Google Cloud Messaging for Android (<b>GCM</b>)<br>
     * Microsoft Push Notification Service for Windows Phone (<b>MPNS</b>)<br>
     * Windows Push Notification Services (<b>WNS</b>)<br>
     *
     * You must specify PlatformPrincipal and PlatformCredential attributes when using the CreatePlatformApplication action.
     * The PlatformPrincipal is received from the notification service.<br>
     *
     * For <b>APNS/APNS_SANDBOX</b>, PlatformPrincipal is "SSL certificate".<br>
     * For <b>GCM</b>, PlatformPrincipal is not applicable.<br>
     * For <b>ADM</b>, PlatformPrincipal is "client id".<br>
     * The PlatformCredential is also received from the notification service.<br>
     *
     * For <b>APNS/APNS_SANDBOX</b>, PlatformCredential is "private key".<br>
     * For <b>GCM</b>, PlatformCredential is "API key".<br>
     * For <b>ADM</b>, PlatformCredential is "client secret".<br>
     *
     * The PlatformApplicationArn that is returned when using CreatePlatformApplication is then used as an attribute for the CreatePlatformEndpoint action.<br>
     *
     * Possible attributes:<br>
     * - <b>PlatformCredential</b> -- The credential received from the notification service. For APNS/APNS_SANDBOX, PlatformCredential is "private key".
     *   For GCM, PlatformCredential is "API key". For ADM, PlatformCredential is "client secret".<br>
     * - <b>PlatformPrincipal</b> -- The principal received from the notification service. For APNS/APNS_SANDBOX, PlatformPrincipal is "SSL certificate".
     *   For GCM, PlatformPrincipal is not applicable. For ADM, PlatformPrincipal is "client id".<br>
     * - <b>EventEndpointCreated</b> -- Topic ARN to which EndpointCreated event notifications should be sent.<br>
     * - <b>EventEndpointDeleted</b> -- Topic ARN to which EndpointDeleted event notifications should be sent.<br>
     * - <b>EventEndpointUpdated</b> -- Topic ARN to which EndpointUpdate event notifications should be sent.<br>
     * - <b>EventDeliveryFailure</b> -- Topic ARN to which DeliveryFailure event notifications should be sent
     *   upon Direct Publish delivery failure (permanent) to one of the application's endpoints.
     *
     * @param string $name Application name
     * @param string $platform Platform type: APNS|GCM|ADM|Baidu|MPNS|WNS
     * @param array  $attributes
     *
     * @return mixed
     */
    public function createPlatformApplication($name, $platform, array $attributes);

    public function createPlatformEndpoint(array $args = []);

    public function createTopic(array $args = []);

    public function deleteEndpoint(array $args = []);

    public function deletePlatformApplication(array $args = []);

    public function deleteTopic(array $args = []);

    public function getEndpointAttributes(array $args = []);

    public function getPlatformApplicationAttributes(array $args = []);

    public function getSubscriptionAttributes(array $args = []);

    public function getTopicAttributes(array $args = []);

    public function listEndpointsByPlatformApplication(array $args = []);

    public function listPlatformApplications(array $args = []);

    public function listSubscriptions(array $args = []);

    public function listSubscriptionsByTopic(array $args = []);

    public function listTopics(array $args = []);

    public function publish(array $args = []);

    public function removePermission(array $args = []);

    public function setEndpointAttributes(array $args = []);

    public function setPlatformApplicationAttributes(array $args = []);

    public function setSubscriptionAttributes(array $args = []);

    public function setTopicAttributes(array $args = []);

    public function subscribe(array $args = []);

    public function unsubscribe(array $args = []);

    public function getListEndpointsByPlatformApplicationIterator(array $args = []);

    public function getListPlatformApplicationsIterator(array $args = []);

    public function getListSubscriptionsIterator(array $args = []);

    public function getListSubscriptionsByTopicIterator(array $args = []);

    public function getListTopicsIterator(array $args = []);
}