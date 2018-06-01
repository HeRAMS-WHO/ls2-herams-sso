<?php

namespace WHO\LimeSurvey\FederatedLogin\events;

use ls\pluginmanager\iPlugin;
use ls\pluginmanager\LimesurveyApi;
use ls\pluginmanager\PluginBase;
use ls\pluginmanager\PluginEvent;
use ls\pluginmanager\PluginEventContent;
use WHO\LimeSurvey\FederatedLogin\FederatedLogin;

abstract class EventHandler
{
    /**
     * @var LimesurveyApi
     */
    protected $api;

    /**
     * @var FederatedLogin
     */
    private $plugin;

    /** @var PluginEventContent */
    private $content;

    public function __construct(
        LimesurveyApi $api,
        iPlugin $plugin
    ) {
        $this->api = $api;
        $this->plugin = $plugin;
    }

    final public function execute(PluginEvent $event)
    {
        $this->content = $event->getContent($this->plugin);
        $this->run($event);
    }

    /**
     * Gets a plugin setting from storage
     * @param string $name
     * @param null $default
     * @return mixed
     */
    protected function get(string $name, $default = null)
    {
        return $this->plugin->getStore()->get($this->plugin, $name, null, null, $default);
    }
    protected function appendContent(string $content)
    {
        $this->content->addContent($content);
    }

    protected function prependContent(string $content)
    {
        $this->content->addContent($content, PluginEventContent::PREPEND);
    }


    abstract protected function run(PluginEvent $event);
}