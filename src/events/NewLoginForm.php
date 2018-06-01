<?php


namespace WHO\LimeSurvey\FederatedLogin\events;


use Authdb;
use ls\pluginmanager\PluginEvent;

class NewLoginForm extends EventHandler
{

    protected function run(PluginEvent $event)
    {
        $text = $this->get('ssoButtonText');
        $url = $this->get('ssoUrl');
        if (empty($text) || empty($url)) {
            return;
        }
        $button = json_encode(\CHtml::link($this->get('ssoButtonText'), $this->get('ssoUrl'), [
            'class' => 'action ui-button ui-widget ui-state-default ui-corner-all  limebutton',
            'style' => 'padding: 0.4em 1em'
        ]));
        $js = <<<JS
$(document).ready(function() {
    $($button).appendTo('.messagebox');
});

JS;

        $event->getContent(Authdb::class)->addContent("<script>$js</script>");
    }
}