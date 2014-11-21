<?php

use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

/**
 * A workaround for fetching the users email address if the user does not have a
 * public email address.
 */
class GithubProvider extends Github
{
    public function userDetails($response, AccessToken $token)
    {
        $user = parent::userDetails($response, $token);
        
        // Fetch the primary email address
        if (!$user->email) {
            $emails = $this->fetchUserEmails($token);
            $emails = json_decode($emails);
            $email  = null;
            
            foreach ($emails as $email) {
                if ($email->primary) {
                    $email = $email->email;
                    break;
                }
            }
            
            $user->email = $email;
        }
        
        return $user;
    }
    
    protected function fetchUserEmails(AccessToken $token)
    {
        $url = "https://api.github.com/user/emails?access_token={$token}";
        
        try {

            $client = $this->getHttpClient();
            $client->setBaseUrl($url);

            if ($this->headers) {
                $client->setDefaultOption('headers', $this->headers);
            }

            $request = $client->get()->send();
            $response = $request->getBody();

        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }
}