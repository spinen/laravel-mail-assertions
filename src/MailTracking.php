<?php

namespace Spinen\MailAssertions;

use Illuminate\Support\Facades\Mail;
use PHPUnit_Framework_TestCase;
use Swift_Message;

/**
 * Class MailTracking
 *
 * Trait to mixin to your test to allow for custom assertions when using PHPUnit with Laravel.
 *
 * This originally started out as a copy & paste from a video series that Jeffrey Way did on laracasts.com. If you do
 * not have an account on Laracasts, you should get one. It is an amazing resource to learn from. We used that
 * example & converted it to a package so that it would be easy to install. We have also expanded on the initial
 * assertions.
 *
 * I WANT IT CLEAR THAT THIS WOULD NOT HAVE HAPPENED WITHOUT THE INITIAL WORK OF JEFFREY WAY. WE ARE NOT CLAIMING TO
 * BE THE CREATORS OF THE CONCEPT.
 *
 * @package Spinen\MailAssertions
 * @see     https://gist.github.com/JeffreyWay/b501c53d958b07b8a332
 * @tutorial https://laracasts.com/series/phpunit-testing-in-laravel/episodes/12
 */
trait MailTracking
{
    // TODO: Add check for attachments (number of & name)
    // TODO: Add check for header
    // TODO: Add check for message type
    // TODO: Add check for Priority
    // TODO: Allow checking specific message not just most recent one

    /**
     * Delivered emails.
     *
     * @var array
     */
    protected $emails = [];

    /**
     * Register a listener for new emails.
     *
     * This calls my PHPUnit before each test it runs. It registers the MailRecorder "plugin" with Swift, so that we
     * can get a copy of each email that is sent during that test.
     *
     * @before
     */
    public function setUpMailTracking()
    {
        $this->afterApplicationCreated(function() {
            Mail::getSwiftMailer()
                ->registerPlugin(new MailRecorder($this));
        });
    }

    /**
     * Retrieve the appropriate Swift message.
     *
     * @param Swift_Message|null $message
     *
     * @return Swift_Message
     */
    protected function getEmail(Swift_Message $message = null)
    {
        $this->seeEmailWasSent();

        return $message ?: $this->lastEmail();
    }

    /**
     * Retrieve the mostly recently sent Swift message.
     */
    protected function lastEmail()
    {
        return end($this->emails);
    }

    /**
     * Store a new Swift message.
     *
     * Collection of emails that were received by the MailRecorder plugin during a test.
     *
     * @param Swift_Message $email
     */
    public function recordMail(Swift_Message $email)
    {
        $this->emails[] = $email;
    }

    /**
     * Assert that the last email was bcc'ed to the given address.
     *
     * @param string             $bcc
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailBcc($bcc, Swift_Message $message = null)
    {
        $this->assertArrayHasKey($bcc, (array)$this->getEmail($message)
                                                   ->getBcc(), "The last email sent was not bcc'ed to $bcc.");

        return $this;
    }

    /**
     * Assert that the last email was cc'ed to the given address.
     *
     * @param string             $cc
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailCc($cc, Swift_Message $message = null)
    {
        $this->assertArrayHasKey($cc, (array)$this->getEmail($message)
                                                  ->getCc(), "The last email sent was not cc'ed to $cc.");

        return $this;
    }

    /**
     * Assert that the last email's body contains the given text.
     *
     * @param string             $excerpt
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailContains($excerpt, Swift_Message $message = null)
    {
        $this->assertContains($excerpt, $this->getEmail($message)
                                             ->getBody(), "The last email sent did not contain the provided body.");

        return $this;
    }

    /**
     * Assert that the last email's content type equals the given text.
     * For example, "text/plain" and "text/html" are valid content types for an email.
     *
     * @param string             $content_type
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailContentTypeEquals($content_type, Swift_Message $message = null)
    {
        $this->assertEquals($content_type, $this->getEmail($message)
                                                ->getContentType(),
            "The last email sent did not contain the provided body.");

        return $this;
    }

    /**
     * Assert that the last email's body does not contain the given text.
     *
     * @param string             $excerpt
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailDoesNotContain($excerpt, Swift_Message $message = null)
    {
        $this->assertNotContains($excerpt, $this->getEmail($message)
                                                ->getBody(),
                                 "The last email sent contained the provided text in its body.");

        return $this;
    }

    /**
     * Assert that the last email's body equals the given text.
     *
     * @param string             $body
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailEquals($body, Swift_Message $message = null)
    {
        $this->assertEquals($body, $this->getEmail($message)
                                        ->getBody(), "The last email sent did not match the given email.");

        return $this;
    }

    /**
     * Assert that the last email was delivered by the given address.
     *
     * @param string             $sender
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailFrom($sender, Swift_Message $message = null)
    {
        // TODO: Allow from to be an array to check email & name
        $this->assertArrayHasKey($sender, (array)$this->getEmail($message)
                                                      ->getFrom(), "The last email sent was not sent from $sender.");

        return $this;
    }

    /**
     * Assert that the last email had the given priority level.
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     *
     * @param integer            $priority
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailPriorityEquals($priority, Swift_Message $message = null)
    {
        $actual_priority = $this->getEmail($message)
                                ->getPriority();

        $this->assertEquals($priority, $actual_priority,
            "The last email sent had a priority of $actual_priority but expected $priority.");

        return $this;
    }

    /**
     * Assert that the last email was set to reply to the given address.
     *
     * @param string             $reply_to
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailReplyTo($reply_to, Swift_Message $message = null)
    {
        $this->assertArrayHasKey($reply_to, (array)$this->getEmail($message)
                                                        ->getReplyTo(),
                                 "The last email sent was not set to reply to $reply_to.");

        return $this;
    }

    /**
     * Assert that the given number of emails were sent.
     *
     * @param integer $count
     *
     * @return PHPUnit_Framework_TestCase $this
     * @deprecated in favor of seeEmailCountEquals
     */
    protected function seeEmailsSent($count)
    {
        return $this->seeEmailCountEquals($count);
    }

    /**
     * Assert that the given number of emails were sent.
     *
     * @param integer $count
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailCountEquals($count)
    {
        $emailsSent = count($this->emails);

        $this->assertCount($count, $this->emails, "Expected $count emails to have been sent, but $emailsSent were.");

        return $this;
    }

    /**
     * Assert that the last email's subject matches the given string.
     *
     * @param string             $subject
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     * @deprecated in favor of seeEmailSubjectEquals
     */
    protected function seeEmailSubject($subject, Swift_Message $message = null)
    {
        return $this->seeEmailSubjectEquals($subject, $message);
    }

    /**
     * Assert that the last email's subject contains the given string.
     *
     * @param string             $excerpt
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailSubjectContains($excerpt, Swift_Message $message = null)
    {
        $this->assertContains($excerpt, $this->getEmail($message)
                                             ->getSubject(),
                              "The last email sent did not contain the provided subject.");

        return $this;
    }

    /**
     * Assert that the last email's subject does not contain the given string.
     *
     * @param string             $excerpt
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailSubjectDoesNotContain($excerpt, Swift_Message $message = null)
    {
        $this->assertNotContains($excerpt, $this->getEmail($message)
                                                ->getSubject(),
                                 "The last email sent contained the provided text in its subject.");

        return $this;
    }

    /**
     * Assert that the last email's subject matches the given string.
     *
     * @param string             $subject
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailSubjectEquals($subject, Swift_Message $message = null)
    {
        $this->assertEquals($subject, $this->getEmail($message)
                                           ->getSubject(),
                            "The last email sent did not contain a subject of $subject.");

        return $this;
    }

    /**
     * Assert that the last email was sent to the given recipient.
     *
     * @param string             $recipient
     * @param Swift_Message|null $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailTo($recipient, Swift_Message $message = null)
    {
        $this->assertArrayHasKey($recipient, (array)$this->getEmail($message)
                                                         ->getTo(), "The last email sent was not sent to $recipient.");

        return $this;
    }

    /**
     * Assert that no emails were sent.
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailWasNotSent()
    {
        $emailsSent = count($this->emails);

        $this->assertEmpty($this->emails, "Did not expect any emails to have been sent, but found $emailsSent");

        return $this;
    }

    /**
     * Assert that at least one email was sent.
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailWasSent()
    {
        $this->assertNotEmpty($this->emails, 'Expected at least one email to be sent, but found none.');

        return $this;
    }
}
