<?php


namespace WHO\LimeSurvey\FederatedLogin\events;


use ls\pluginmanager\PluginEvent;

class BeforeLogin extends EventHandler
{


    public function run(
        PluginEvent $event
    ) {
        $jwt = $this->api->getRequest()->getQuery('jwt');

        if (isset($jwt)) {
            $event->get('identity')->plugin = \FederatedLogin::class;
            $event->stop();
        }
    }
}