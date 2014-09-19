<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests\Mocks;

/**
 * This class mocks \TwitterOAuth class.
 */
class TwitterOAuthMock extends \Abraham\TwitterOAuth\TwitterOAuth
{
    public $http_code = 200;

    public function getRequestToken($oauth_callback)
    {
        return 'rToken';
    }


    public function getAuthorizeURL($requestToken)
    {
        return 'http://www.twitter.com/authMe';
    }

    public function getAccessToken($verifier)
    {
        return [
            "oauth_token"        => "the-access-token",
            "oauth_token_secret" => "the-access-secret",
            "user_id"            => "5555",
            "screen_name"        => "WebinyPlatform"
        ];
    }

}