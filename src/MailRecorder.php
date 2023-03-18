<?php

namespace Spinen\MailAssertions;

use Swift_Events_EventListener;
use Swift_Events_SendEvent;

/**
 * Class MailRecorder
 */
class MailRecorder implements Swift_Events_EventListener
{
    protected $test;

    /**
     * MailRecorder constructor.
     *
     * @param  mixed  $test The PhpUnit TestCase class to use
     */
    public function __construct($test)
    {
        $this->test = $test;
    }

    /**
     * Called by Laravel before email is given to the transporter.
     *
     * Passes the email to the test, so that assertions can be ran against the messages.
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $event)
    {
        $this->test->recordMail($event->getMessage());
    }
}
