<?php

namespace app\components;

use Yii;

/**
 * Component for notify user
 */
class Notify extends \yii\base\Component
{
    /**
     * @var object
     */
    private $params = null;

    public function init()
    {
        parent::init();

        $this->params = Yii::$app->settings;
    }

    /**
     * Send message
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $params
     * @return bool
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function sendMessage($to, $subject, $view, $params = [])
    {
        $message = \Yii::$app->getMailer()->compose($view, $params);

        if (!strlen($this->params->emailMain) || !strlen($this->params->emailName)) {
            return false;
        }

        $message->setFrom([$this->params->emailMain => $this->params->emailName]);
        $message->setTo($to);

        if (strlen($this->params->emailPrefix)) {
            $message->setSubject($this->params->emailPrefix . ': ' . $subject);
        } else {
            $message->setSubject($subject);
        }

        return $message->send();
    }
}
