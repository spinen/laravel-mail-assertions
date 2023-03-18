<?php

namespace Spinen\MailAssertions;

use Mockery;
use StdClass;
use Swift_Events_SendEvent;
use TypeError;

/**
 * Class MailRecorderTest
 */
class MailRecorderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $mail_recorder = new MailRecorder($this);

        $this->assertInstanceOf(MailRecorder::class, $mail_recorder);
    }

    /**
     * @test
     */
    public function it_cannot_be_constructed_without_a_PHPUnit_Framework_TestCase()
    {
        $this->markTestSkipped('Figure out what the correct exception should be');
        //$this->expectException(PHPUnit_Framework_Error::class);

        if (class_exists(TypeError::class)) {
            try {
                new MailRecorder();
            } catch (TypeError $e) {
                throw new PHPUnit_Framework_Error('Argument 1 passed to method must be an array, but not', 0,
                    $e->getFile(), $e->getLine());
            }
        } else {
            new MailRecorder();
        }
    }

    /**
     * @test
     */
    public function it_cannot_be_constructed_with_class_other_than_a_PHPUnit_Framework_TestCase()
    {
        if (class_exists(TypeError::class)) {
            try {
                new MailRecorder(new StdClass());
            } catch (TypeError $e) {
                throw new PHPUnit_Framework_Error('Argument 1 passed to method must be an array, but not', 0,
                    $e->getFile(), $e->getLine());
            }
        } else {
            new MailRecorder(new StdClass());
        }
    }

    /**
     * @test
     *
     * @group
     */
    public function it_records_the_message_on_the_test_by_calling_recordMail()
    {
        $test_mock = Mockery::mock(TestCase::class);

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
