<?php

namespace Spinen\MailAssertions\Stubs;

use Spinen\MailAssertions\MailTracking;
use Spinen\MailAssertions\TestCase;

/**
 * Class MailTrackingStub
 */
class MailTrackingStub extends TestCase
{
    use MailTracking;

    /**
     * Stubs Laravel's afterApplicationCreated() method so that tests can run normally
     *
     *
     * @return void
     */
    public function afterApplicationCreated(callable $callback)
    {
        call_user_func($callback);
    }

    /**
     * Public method in the stub to expose the protected email collection
     *
     * This is only needed in the tests to allow access to the raw collection of messages.
     *
     * @return array
     */
    public function exposeMessage()
    {
        return $this->emails;
    }
}
