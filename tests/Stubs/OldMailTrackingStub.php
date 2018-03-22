<?php

namespace Spinen\MailAssertions\Stubs;

use Spinen\MailAssertions\MailTracking;
use Spinen\MailAssertions\TestCase;

/**
 * Class MailTrackingStub
 *
 * @package Spinen\MailAssertions\Stubs
 */
class OldMailTrackingStub extends TestCase
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
