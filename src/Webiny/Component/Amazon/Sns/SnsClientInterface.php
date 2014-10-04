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
     * Confirm subscription<br>
     *
     * Verifies an endpoint owner's intent to receive messages by validating the token sent to the endpoint by an earlier Subscribe action.
     * If the token is valid, the action creates a new subscription and returns its Amazon Resource Name (ARN).
     * This call requires an AWS signature only when the $authenticateOnUnsubscribe flag is set to "true".
     *
     * @param string $topicArn
     * @param string $token
     * @param bool   $authenticateOnUnsubscribe
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_confirmSubscription
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object
     */
    public function confirmSubscription($topicArn, $token, $authenticateOnUnsubscribe = false);

    /**
     * Create platform application<br>
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
     * You must specify <b>PlatformPrincipal</b> and <b>PlatformCredential</b> attributes when using the <b>CreatePlatformApplication</b> action.
     * The <b>PlatformPrincipal</b> is received from the notification service.<br>
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
     * The <b>PlatformApplicationArn</b> that is returned when using <b>CreatePlatformApplication</b> is then used as an attribute for the <b>CreatePlatformEndpoint</b> action.<br>
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
     * @param string $name     Application name
     * @param string $platform Platform type: APNS|GCM|ADM|Baidu|MPNS|WNS
     * @param array  $attributes
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_createPlatformApplication
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>SubscriptionArn</b> key)
     */
    public function createPlatformApplication($name, $platform, array $attributes);

    /**
     * Create platform endpoint<br>
     *
     * Creates an endpoint for a device and mobile app on one of the supported push notification services, such as GCM and APNS.<br>
     *
     * <b>CreatePlatformEndpoint</b> requires the <b>PlatformApplicationArn</b> that is returned from <b>CreatePlatformApplication</b>.
     * The <b>EndpointArn</b> that is returned when using <b>CreatePlatformEndpoint</b> can then be used by the <b>Publish</b> action to send a message
     * to a mobile app or by the <b>Subscribe</b> action for subscription to a topic.<br>
     *
     * The <b>CreatePlatformEndpoint</b> action is idempotent, so if the requester already owns an endpoint with the same device token and attributes,
     * that endpoint's ARN is returned without creating a new endpoint.<br>
     *
     * When using <b>CreatePlatformEndpoint</b> with Baidu, two attributes must be provided: <b>ChannelId</b> and <b>UserId</b>.
     * The token field must also contain the <b>ChannelId</b>.<br>
     *
     * Possible attributes:<br>
     * - <b>CustomUserData</b> -- arbitrary user data to associate with the endpoint. Amazon SNS does not use this data. The data must be in UTF-8 format and less than 2KB.<br>
     * - <b>Enabled</b> -- flag that enables/disables delivery to the endpoint. Amazon SNS will set this to false when a notification service indicates to Amazon SNS that
     *   the endpoint is invalid. Users can set it back to true, typically after updating Token.<br>
     * - <b>Token</b> -- device token, also referred to as a registration id, for an app and mobile device.<br>
     *   This is returned from the notification service when an app and mobile device are registered with the notification service.<br>
     *
     * @param string $platformApplicationArn PlatformApplicationArn returned from CreatePlatformApplication is used to create an endpoint.
     * @param string $token                  Unique identifier created by the notification service for an app on a device.
     * @param string $customUserData         Arbitrary user data to associate with the endpoint. Amazon SNS does not use this data. The data must be in UTF-8 format and less than 2KB.
     * @param array  $attributes             Associative array of attributes (see "Possible attributes")
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_createPlatformEndpoint
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object
     */
    public function createPlatformEndpoint($platformApplicationArn, $token, $customUserData, array $attributes = []);

    /**
     * Create topic<br>
     *
     * Constraints: Topic names must be made up of only uppercase and lowercase ASCII letters, numbers, underscores, and hyphens, and must be between 1 and 256 characters long.
     *
     * @param string $name Topic name
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_createTopic
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>TopicArn</b> key)
     */
    public function createTopic($name);

    /**
     * Delete endpoint
     *
     * @param string $endpointArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_deleteEndpoint
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object
     */
    public function deleteEndpoint($endpointArn);

    /**
     * Delete platform application
     *
     * @param string $platformApplicationArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_deletePlatformApplication
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object
     */
    public function deletePlatformApplication($platformApplicationArn);

    /**
     * Delete topic
     *
     * @param string $topicArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_deleteTopic
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object
     */
    public function deleteTopic($topicArn);

    /**
     * Get endpoint attributes<br>
     *
     * Retrieves the endpoint attributes for a device on one of the supported push notification services, such as GCM and APNS.
     *
     * @param string $endpointArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_getEndpointAttributes
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>Attributes</b> key)
     */
    public function getEndpointAttributes($endpointArn);

    /**
     * Get platform application attributes<br>
     *
     * Retrieves the attributes of the platform application object for the supported push notification services, such as APNS and GCM.
     *
     * @param string $platformApplicationArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_getPlatformApplicationAttributes
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>Attributes</b> key)
     */
    public function getPlatformApplicationAttributes($platformApplicationArn);

    /**
     * Get subscription attributes<br>
     *
     * Returns all of the properties of a subscription.
     *
     * @param string $subscriptionArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_getSubscriptionAttributes
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>Attributes</b> key)
     */
    public function getSubscriptionAttributes($subscriptionArn);

    /**
     * Get topic attributes<br>
     *
     * Returns all of the properties of a topic. Topic properties returned might differ based on the authorization of the user.
     *
     * @param string $topicArn
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_getTopicAttributes
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>Attributes</b> key)
     */
    public function getTopicAttributes($topicArn);

    /**
     * List endpoints by platform application<br>
     *
     * Lists the endpoints and endpoint attributes for devices in a supported push notification service, such as GCM and APNS.
     * The results for <b>ListEndpointsByPlatformApplication</b> are paginated and return a limited list of endpoints, up to 100.
     * If additional records are available after the first page results, then a NextToken string will be returned.
     * To receive the next page, you call <b>ListEndpointsByPlatformApplication</b> again using the <b>NextToken</b> string received from the previous call.
     * When there are no more records to return, <b>NextToken</b> will be null.
     *
     * @param string $platformApplicationArn
     * @param string $nextToken
     *
     * @see      http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_listEndpointsByPlatformApplication
     *
     * @return \Guzzle\Service\Resource\Model Returns a response Model object (contains <b>Endpoints</b> and <b>NextToken</b> key)
     */
    public function listEndpointsByPlatformApplication($platformApplicationArn, $nextToken);

    /**
     * List platform applications
     *
     * @param string $nextToken
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sns.SnsClient.html#_listPlatformApplications
     *
     * @return mixed
     */
    public function listPlatformApplications($nextToken);

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