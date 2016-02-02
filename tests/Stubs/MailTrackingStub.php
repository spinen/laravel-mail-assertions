<?php

namespace Spinen\MailAssertions\Stubs;

use PHPUnit_Framework_TestCase;
use Spinen\MailAssertions\MailTracking;

/**
 * Class MailTrackingStub
 *
 * @package Spinen\MailAssertions\Stubs
 */
class MailTrackingStub extends PHPUnit_Framework_TestCase
{
    use MailTracking;

    /**
     * Public method in the stub to expose the protected email collection
     *
     * This is only needed in the tests to allow access to the raw collection of messages.
     *
     * @return array
     */
    public function exposeMessage() {
        return $this->emails;
    }
}
