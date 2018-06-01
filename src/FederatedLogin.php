<?php

namespace WHO\LimeSurvey\FederatedLogin;

use ls\pluginmanager\DbStorage;
use WHO\LimeSurvey\FederatedLogin\events\EventHandler;

class FederatedLogin extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Federated login via public key crypto';
    static protected $name = 'FederatedLogin';

    protected $storage = DbStorage::class;

    protected $settings = [
        'publicKey' => [
            'type' => 'text',
            'label' => 'Public key',
            'help' => 'Public key used for signing JWT tokens'
        ],
        'ssoButtonText' => [
            'type' => 'string',
            'label' => 'Text for SSO button',
            'help' => 'Used for button on login page',
        ],
        'ssoUrl' => [
            'type' => 'string',
            'label' => 'Url for SSO',
            'help' => 'Used for button on login page',
        ],
        

    ];



    protected function subscribe($event, $function = null)
    {
        if (isset($function)) {
            throw new \Exception("Use event classes instead");
        }
        parent::subscribe($event, 'router');
    }


    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
//        $this->subscribe('createNewUser');
        $this->subscribe('beforeLogin');
        $this->subscribe('newLoginForm');
//        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('newUserSession');
//        $this->subscribe('beforeDeactivate');
        // Now register for the core exports
//        $this->subscribe('listExportPlugins');
//        $this->subscribe('listExportOptions');
//        $this->subscribe('newExport');
    }

    public function router()
    {
        $event = $this->event;
        $class = __NAMESPACE__ . '\\events\\' . ucfirst($event->getEventName());
        if (!class_exists($class) || !is_subclass_of($class, EventHandler::class)) {
            throw new \Exception("Event class $class not found");
        }

        /** @var EventHandler $handler */
        $handler = new $class($this->api, $this);

        $handler->execute($event);
    }
}