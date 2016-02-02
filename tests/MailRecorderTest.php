<?php

namespace Spinen\MailAssertions;

use Mockery;
use PHPUnit_Framework_TestCase;
use StdClass;
use Swift_Events_SendEvent;

/**
 * Class MailRecorderTest
 *
 * @package Spinen\MailAssertions
 */
class MailRecorderTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_can_be_constructed()
    {
        $mail_recorder = new MailRecorder($this);

        $this->assertInstanceOf(MailRecorder::class, $mail_recorder);
    }

    /**
     * @test
     * @group unit
     * @expectedException PHPUnit_Framework_Error
     */
    public function it_cannot_be_constructed_without_a_PHPUnit_Framework_TestCase()
    {
        new MailRecorder();
    }

    /**
     * @test
     * @group unit
     * @expectedException PHPUnit_Framework_Error
     */
    public function it_cannot_be_constructed_with_class_other_than_a_PHPUnit_Framework_TestCase()
    {
        new MailRecorder(new StdClass());
    }

    /**
     * @test
     * @group
     */
    public function it_records_the_message_on_the_test_by_calling_recordMail()
    {
        $test_mock = Mockery::mock(PHPUnit_Framework_TestCase::class);

        $test_mock->shouldReceive('recordMail')
                  ->once()
                  ->with('message')
                  ->andReturnNull();

        $swift_message_event_mock = Mockery::mock(Swift_Events_SendEvent::class);

        $swift_message_event_mock->shouldReceive('getMessage')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn('message');

        $mail_recorder = new MailRecorder($test_mock);

        $this->assertNull($mail_recorder->beforeSendPerformed($swift_message_event_mock));
    }
}
