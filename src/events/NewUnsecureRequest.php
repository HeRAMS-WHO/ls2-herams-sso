<?php


namespace WHO\LimeSurvey\FederatedLogin\events;


use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use ls\pluginmanager\AuthPluginBase;
use ls\pluginmanager\PluginEvent;

class NewUnsecureRequest extends EventHandler
{
    public function run(PluginEvent $event)
    {
        if (!$event->get('plugin') === 'FederatedLogin'
            || !$event->get('function') === 'SSO') {
            return;
        }

        $request = $this->api->getRequest();

        if (!$request->getIsPostRequest()) {
            throw new \CHttpException(405, 'Request must be POST');
        }

        $jwt = $this->api->getRequest()->getPost('jwt');

        // Parse and verify the JWT
        $parser = new Parser();
        $token = $parser->parse($jwt);

        $signer = new Sha256();
        if (!$token->verify($signer, $this->get('publicKey'))) {
            throw new \CHttpException(400, 'Signature verification failed');
        }

        // From here we can trust that data in the token is secure (even though it might no longer be valid)
        try {
            $validationData = new ValidationData();
            $validationData->setAudience($this->plugin->getLoginUrl());
            if (!$token->validate($validationData)) {
                throw new \CHttpException(400, 'Token validation failed');
            }


            if (!$token->hasClaim('username')) {
                throw new \CHttpException(400, 'Token is valid but does not contain a username claim');
            }


            $user = $this->api->getUserByName($token->getClaim('username'));
            if (!isset($user)) {
                throw new \CHttpException(400, 'Unknown user');
            }

            $currentUser = $this->api->getCurrentUser();
            if (!empty($currentUser) && $currentUser->uid != $user->uid) {
                throw new \CHttpException(412, 'Another user is already logged in');
            } elseif (!empty($currentUser)) {
                // Already logged in as the same user from the JWT.
                $this->api->getRequest()->redirect($this->api->createUrl('admin/', []));
            }

        } catch (\CHttpException $e) {
            if ($token->hasClaim('errorUrl')) {
                $base = $token->getClaim('errorUrl');
                $connector = empty(parse_url($base, PHP_URL_QUERY)) ? '?' : '&';
                $url = $base . $connector . http_build_query(['error' => $e->getMessage()]);
                $this->api->getRequest()->redirect($url);
            } else {
                throw $e;
            }
        }
        $this->api->getRequest()->redirect($this->api->createUrl('admin/authentication', ['jwt' => $jwt]));
    }
}