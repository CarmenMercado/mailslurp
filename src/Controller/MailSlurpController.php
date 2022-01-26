<?php
namespace App\Controller;

use MailSlurp\ApiException;
use MailSlurp\Apis\InboxControllerApi;
use MailSlurp\Configuration;
use MailSlurp\Models\SendEmailOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MailSlurpController
{
    public $config;

    private function setUp(): Configuration
    {
        if( $this->config == null){
            $this->config = Configuration::getDefaultConfiguration()->setApiKey('x-api-key', $_ENV["API_KEY"]);
        }
        return $this->config;
    }

    public function sendEmail(Request $request):Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if(!$this->validate($request,$response)[0]){
            return $this->validate($request,$response)[1];
        }
        $inboxController = new InboxControllerApi($param1 = null, $config = $this->setUp());
        try {
            $inbox = $inboxController->createInbox();
            $sendOptions = new SendEmailOptions();
            $sendOptions->setFrom($inbox->getEmailAddress());
            $sendOptions->setSubject($request->get('subject'));
            $sendOptions->setBody(
                "Hola! has recibido un correo de: ".$request-> get('email') ." con el asunto:'.".
                $request-> get('subject')."', que dice: '".$request-> get('body')."'"
            );
            $sendOptions->setTo([$inbox->getEmailAddress()]);

            try {
                $result= $inboxController->sendEmailWithHttpInfo($inbox->getId(), $sendOptions);
                $response->setContent(json_encode([
                    'status_code' => $result[1],
                    'Message' => "Send Email"
                ]));
                return $response;
            } catch (ApiException $e) {
                $response->setStatusCode($e->getCode());
                $response->setContent(json_encode([
                    'message' => $e->getMessage()
                ]));
                return $response;
            }
        } catch (ApiException $e) {
            $response->setStatusCode($e->getCode());
            $response->setContent(json_encode([
                'message' => $e->getMessage()
            ]));
            return $response;
        }
    }

    function validate($request, $response): array
    {
        if ($request->get('email')==null ||$request->get('subject') == null || $request-> get('body') == null ){
            $response->setContent(json_encode([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'Message' => "Null or invalid files"
            ]));
            return [false, $response];
        }elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)== false){
            $response->setContent(json_encode([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'Message' => "Invalid email"
            ]));
            return [false, $response];
        }
        return [true, $response];
    }

}
