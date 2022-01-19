<?php
namespace App\Controller;

use MailSlurp\Apis\InboxControllerApi;
use MailSlurp\Apis\WaitForControllerApi;
use MailSlurp\Configuration;
use MailSlurp\Models\SendEmailOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MailslurpController
{
    public $config;

    public function setUp()
    {
        if( $this->config == null){
            $this->config = Configuration::getDefaultConfiguration()->setApiKey('x-api-key','d7eec2cf7d3241701019794d2666599601602b4f590e79358714e6038fb22707');
        }
        return $this->config;
    }

    public function newEmail(Request $request):Response
    {
        // create inbox and waitFor controllers
        $inbox_controller = new InboxControllerApi(null, $this->setUp());
        $wait_for_controller = new WaitForControllerApi(null, $this->setUp());

        // create two inboxes
        $inbox_1 = $inbox_controller->createInbox();

        // send a confirmation code from inbox1 to inbox2 (sends an actual email)
        $send_options = new SendEmailOptions();
        $send_options->setTo([$inbox_1->getEmailAddress(), "test@test.com"]);
        $send_options->setSubject("Test 18". "carmen@semantic.com");
        $send_options->setBody("Confirmation code = abc123");
        $inbox_controller->sendEmail($inbox_1->getId(), $send_options);
        return new Response(
            '<html><body>Token'.$inbox_controller->getConfig()->getAccessToken().'</body></html>'
        );
    }
}