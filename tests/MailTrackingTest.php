<?php

namespace Spinen\MailAssertions;

use Illuminate\Support\Facades\Mail;
use Mockery;
use ReflectionClass;
use Spinen\MailAssertions\Stubs\MailTrackingStub as MailTracking;
use Swift_Mailer;
use Swift_Message;

/**
 * Class MailTrackingTest
 *
 * @package Spinen\MailAssertions
 */
class MailTrackingTest extends TestCase
{
    /**
     * @var MailTracking
     */
    protected $mail_tracking;

    /**
     * Make a new MailTracking (Stub) instance for each test
     */
    protected function setUp()
    {
        $this->mail_tracking = new MailTracking();

        parent::setUp();
    }

    /**
     * Since all of the assertions are protected methods, this allows access to them.
     *
     * @param string $method_name
     * @param array  $args
     *
     * @return mixed
     */
    protected function callProtectedMethod($method_name, array $args = [])
    {
        $reflection = new ReflectionClass($this->mail_tracking);

        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs($this->mail_tracking, $args);
    }

    /**
     * Make a swift message.
     *
     * @param null $subject
     * @param null $body
     * @param null $to
     * @param null $from
     * @param null $contentType
     * @param null $charset
     *
     * @return Swift_Message
     */
    protected function makeMessage(
        $subject = null,
        $body = null,
        $to = null,
        $from = null,
        $contentType = null,
        $charset = null
    ) {
        $message = new Swift_Message($subject, $body, $contentType, $charset);

        if (!is_null($to)) {
            $message->setTo($to);
        }

        if (!is_null($from)) {
            $message->setFrom($from);
        }

        return $message;
    }

    /**
     * @test
     * @group unit
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(MailTracking::class, $this->mail_tracking);
    }

    /**
     * @test
     * @group unit
     */
    public function it_registers_MailRecorder_withMail_in_the_setup_with_before_annotated_method()
    {
        $swift_mock = Mockery::mock(Swift_Mailer::class);

        $swift_mock->shouldReceive('registerPlugin')
                   ->once()
                   ->with(Mockery::on(function($closure) {
                       return is_a($closure, MailRecorder::class);
                   }))
                   ->andReturnNull();

        Mail::shouldReceive('getSwiftMailer')
            ->once()
            ->withNoArgs()
            ->andReturn($swift_mock);

        // TODO: Get this method name by parsing annotations
        $this->mail_tracking->setUpMailTracking();
    }

    /**
     * @test
     * @group unit
     */
    public function it_records_emails_in_collection()
    {
        $this->assertCount(0, $this->mail_tracking->exposeMessage());

        $message = $this->makeMessage();

        $this->mail_tracking->recordMail($message);

        $this->assertCount(1, $this->mail_tracking->exposeMessage());
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_bcc_address()
    {
        $message = $this->makeMessage();
        $message->setBcc('bcc@domain.tld');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailBcc', ['bcc@domain.tld']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_cc_address()
    {
        $message = $this->makeMessage();
        $message->setCc('cc@domain.tld');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailCc', ['cc@domain.tld']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_body_for_content()
    {
        $message = $this->makeMessage('subject', 'body');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailContains', ['body']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_body_does_not_have_content()
    {
        $message = $this->makeMessage('subject', '');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailDoesNotContain', ['body']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_makes_sure_email_body_is_what_is_expected()
    {
        $message = $this->makeMessage('subject', 'full message body');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailEquals', ['full message body']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_from_address()
    {
        $message = $this->makeMessage('subject', 'body', 'to@domain.tld', 'from@domain.tld');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailFrom', ['from@domain.tld']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_reply_to_address()
    {
        $message = $this->makeMessage();
        $message->setReplyTo('replyto@domain.tld');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailReplyTo', ['replyto@domain.tld']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_knows_how_many_emails_have_been_sent()
    {
        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailsSent', [0]));

        $message = $this->makeMessage();

        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailsSent', [1]));

        $message = $this->makeMessage();

        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailsSent', [2]));
    }

    /**
     * @test
     * @group unit
     */
    public function it_makes_sure_email_subject_is_what_is_expected()
    {
        $message = $this->makeMessage('full message subject');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking,
                            $this->callProtectedMethod('seeEmailSubject', ['full message subject']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_makes_sure_email_subject_contains_expected_string()
    {
        $message = $this->makeMessage('full message subject');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking,
                            $this->callProtectedMethod('seeEmailSubjectContains', ['subject']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_makes_sure_email_subject_does_not_contain_string()
    {
        $message = $this->makeMessage('');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailSubjectDoesNotContain', ['subject']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_email_to_address()
    {
        $message = $this->makeMessage('subject', 'body', 'to@domain.tld', 'from@domain.tld');
        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailTo', ['to@domain.tld']));
    }

    /**
     * @test
     * @group unit
     */
    public function it_knows_if_email_has_not_been_sent_or_not()
    {
        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailWasNotSent'));

        $message = $this->makeMessage();

        $this->mail_tracking->recordMail($message);

        $this->assertEquals($this->mail_tracking, $this->callProtectedMethod('seeEmailWasSent'));
    }
}
