<?php
declare(strict_types=1);

namespace BeyondCode\Mailbox\Tests\Controllers;

use BeyondCode\Mailbox\Facades\Mailbox;
use BeyondCode\Mailbox\InboundEmail;
use BeyondCode\Mailbox\Tests\TestCase;
use Symfony\Component\Mime\Email;

class MailCareTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']['mailbox.driver'] = 'mailcare';
        $app['config']['mailbox.basic_auth.username'] = null;
    }

    /**
     * @test
     */
    public function it_accepts_raw_email_requests(): void
    {

        $message = new Email();
        $message->subject("subject");
        $message->from("from@example.com");
        $message->to("to@example.com");
        $message->text("this is body text");

        Mailbox::shouldReceive("callMailboxes", function(InboundEmail $email){
            return $email->subject() === 'this is body text'
                && $email->from() === 'from@example.com'
                && $email->to()[0]->getEmail() === 'to@example.com'
                && $email->body() === 'this is body text';
        });
        $this->callWithEmail('POST','/laravel-mailbox/mailcare',$message)
            ->assertStatus(200);
    }

    private function callWithEmail(string $method, string $url, Email $message): \Illuminate\Testing\TestResponse
    {
        $raw = $message->toString();
        $parts = explode("\r\n\r\n", $raw);
        $headerRaw = array_shift($parts);
        $bodyRaw = implode("\r\n\r\n", $parts);
        $headers = [];
        foreach(explode("\r\n", $headerRaw) as $headerRawLine){
            list($name, $value) = explode(': ',$headerRawLine);
            $headers[$name] = $value;
        }
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call($method, $url,[],[],[], $server, $bodyRaw);
    }
}
