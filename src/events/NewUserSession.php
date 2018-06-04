<?php


namespace WHO\LimeSurvey\FederatedLogin\events;


use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use ls\pluginmanager\AuthPluginBase;
use ls\pluginmanager\PluginEvent;

class NewUserSession extends EventHandler
{

    public function run(PluginEvent $event)
    {
        // Do nothing if this user is not Authdb type
        /** @var \LSUserIdentity $identity */
        $identity = $event->get('identity');
        if ($identity->plugin != \FederatedLogin::class)
        {
            return;
        }

        $jwt = $this->api->getRequest()->getQuery('jwt');
        // Attempt login with JWT.
        $parser = new Parser();
        $token = $parser->parse($jwt);

        $signer = new Sha256();
        if (!$token->verify($signer, $this->get('publicKey'))) {
            throw new \Exception('Signature verification failed');
        }


        $validationData = new ValidationData();
        $validationData->setAudience($this->plugin->getLoginUrl());

        if (!$token->validate($validationData)) {
            throw new \Exception('Token is invalid, possibly expired');
        }

        if (!$token->hasClaim('username')) {
            throw new \Exception('Token is valid but does not contain a username claim');
        }

        $user = $this->api->getUserByName($token->getClaim('username'));
        if (!isset($user->lang)) {
            $user->lang = 'en';
        }

        if (!isset($user)) {
            $event->set('result', new \LSAuthResult(AuthPluginBase::ERROR_USERNAME_INVALID));
            return;
        }

        $identity->id = $user->uid;
        $identity->user = $user;
        $identity->username = $user->users_name;
        
        $event->set('identity', $identity);
        $event->set('result', new \LSAuthResult());
    }
}