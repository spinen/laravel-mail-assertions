<?php

namespace Spinen\MailAssertions;

use PHPUnit_Framework_TestCase;
use Swift_Events_EventListener;
use Swift_Events_SendEvent;

/**
 * Class MailRecorder
 *
 * @package Spinen\MailAssertions
 */
class MailRecorder implements Swift_Events_EventListener
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    protected $test;

    /**
     * MailRecorder constructor.
     *
     * @param $test
     */
    public function __construct($test)
    {
        $this->test = $test;
    }

    /**
     * Called by Laravel before email is given to the transporter.
     *
     * Passes the email to the test, so that assertions can be ran against the messages.
     *
     * @param Swift_Events_SendEvent $event
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $event)
    {
        $this->test->recordMail($event->getMessage());
    }
}
