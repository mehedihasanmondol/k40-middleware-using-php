<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 3:00 PM
 */

class Message
{
    function mail_sender($from_info, $to_info, $subject, $message){
        $result = 0;
        try{
            if (!isset($from_info)){
                throw new Exception("From info not set");
            }
            else{
                if (!isset($from_info['email'])){
                    throw new Exception("From email not set");
                }
                if (!isset($from_info['name'])){
                    throw new Exception("From name not set");
                }
            }
            if (!isset($to_info)){
                throw new Exception("To info not set");
            }
            else{
                if (!isset($to_info['email'])){
                    throw new Exception("To email not set");
                }
            }
            if (!$message){
                throw new Exception("Message not set");
            }
            try{
                $response_code = $this->third_party_mail_sender($from_info,$to_info,$subject,$message);

            }catch (Exception $exception){
                throw new Exception("Mail send failed of third party for ".$exception->getMessage());
            }
            if ($response_code == 200 or $response_code == 202){
                $result = 1;
            }
            else{
                $to = $to_info['email'];
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                // More headers
                $from_email = $from_info['email'];
                $headers .= "From: <$from_email>" . "\r\n";

                $status = mail($to,$subject,$message,$headers);
                if ($status){
                    $result = 1;
                }
            }
        }catch (Exception $exception){
            throw new Exception("Mail not send for ".$exception->getMessage());
        }
        return $result;
    }
    function third_party_mail_sender($from_info, $to_info, $subject, $message){
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from_info['email'], $from_info['name']);
        $email->setSubject($subject);
        $email->addTo($to_info['email'], $to_info['name']);
        $email->addContent(
            "text/html", $message
        );
        $sendgrid = new \SendGrid("///secretkeyfromapi////");
        try {
            $response = $sendgrid->send($email);
            return $response->statusCode();
        } catch (Exception $e) {
            throw new Exception("Send grid mail send fail for ". $e->getMessage());
        }
    }
    function sms_sender($sms,$numbers){
        $result = array(
            "insertedSmsIds" => 0000,
            "isError" => false,
            "message" => "Success",
            "errorCode" => 0,
        );
        $user_id = "";
        $password = "";
        if ((new Server())->is_online_host()){
            $url = "https://powersms.banglaphone.net.bd/httpapi/sendsms?userId=$user_id&password=$password&smsText=$sms&commaSeperatedReceiverNumbers=$numbers";
            $server_response=file_get_contents($url);
            $server_response=json_decode($server_response,true);
            if ($server_response){
                $result = array_merge($result,$server_response);
            }
        }
        return $result;

    }
}