<?php


namespace domain\mail\v1\services;


use App;
use CURLFile;
use domain\mail\v1\entities\MailEntity;
use yii\httpclient\Client;
use yii2lab\rest\domain\helpers\RestHelper;
use yii2rails\app\domain\helpers\EnvService;
use yubundle\account\domain\v2\forms\LoginForm;

class ReportService
{

    public function generateEmlFile ($message_id = "some",
                              $fromEmail="from@mail.ru",
                              $toEmail="to@mail.ru",
                              $subject="Тема по умолчанию",
                              $body="Тело письма" )
    {
//        echo shell_exec('pwd');

        $content = file_get_contents("./mails/example/example.eml");
        $content = str_replace("TEMPLATE_FROM_ADDRESS", $fromEmail, $content);
        $content = str_replace("TEMPLATE_TO_ADDRESS", $toEmail, $content);
        $content = str_replace("TEMPLATE_SUBJECT", $subject, $content);
        $content = str_replace("TEMPLATE_BODY", $body, $content);

        $handle = fopen("./mails/attachments/".$message_id.'mail.eml','w+');

        fwrite($handle, $content);

        fclose($handle);
    }

    public function sendReport($email) {

        $filename = "./mails/attachments/". $email['message_id']."mail.eml";
        $result = $this->sendLetter($email, $filename);
        return $result;

    }

    public function sendLetter($email, $filePath)
    {
        $url = EnvService::getUrl(API, 'v1/mail-receiver-form');
//        $url = 'https://api.t-cloud.kz/v1/mail-receiver-form';
        $token = $this->getToken();
//        $token = "jwt eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6Ijk3NmNiNzU2LWRmYzEtNGRjNS1kYjMzLTQ2MTIzYjQ0NDk0YSJ9.eyJzdWJqZWN0Ijp7ImlkIjoxfSwiYXVkIjpbXSwiZXhwIjoxODc2NTM2MTkwfQ.9y8uKoy079KttDdJv1lu20IcjR7VHykyfcyBRN1wJXo";

        $post = [
            'from' => 'admin@yumail.kz',
            'to' => $email['sender'],
            'subject' => 'Отчет#'.rand(1,1000),
            'content' => 'Ваше письмо не было доставлено <br>'. $email['all_content'],
            'files[]'=> new CURLFile ($filePath)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: $token",
            'Content-Type: multipart/form-data'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result=curl_exec ($ch);
        curl_close ($ch);

        return ($result);
    }

    private function getToken() {
        $loginForm = new LoginForm;
        $loginForm->login = "admin";
        $loginForm->password = "Wwwqqq111";
        $loginEntity = App::$domain->account->auth->authenticationFromApi($loginForm);
        return $loginEntity->security->token;
    }


    public function getEmailForReport ($filename) {
        $file = file_get_contents($filename, true);
        $mails = explode("From MAILER-DAEMON", $file);

        $perorts = [];
        $i = 0;

        foreach($mails as $mail) {
            $undeliveredMark = strripos($mail, "Content-Description: Undelivered Message");
//    echo $undeliveredMark ."<br>";
            if ($undeliveredMark > 0) {
                $i++;
                $pattern = "/[-a-z0-9!#$%&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i";
                preg_match_all($pattern, $mail, $foundEmails);
                $foundEmails = array_unique($foundEmails[0]);

                $date = $this->getSting($mail, "Date:", "Subject:");
                $date = str_replace("Date: ","",$date);
                $timestamp = strtotime($date);

                $subject = $this->getSting($mail, "Subject:", "From:");
                $subject = str_replace("Subject: ","",$subject);

                $boundary = $this->getSting($mail, "boundary=\"", "\"");
                $boundaryNew = str_replace("boundary=\"","",$boundary);

                $body = $this->getSting($mail, "Content-Transfer-Encoding:", "--$boundaryNew--")."<br>";
                $body = str_replace("Content-Transfer-Encoding: quoted-printable","",$body);

                $sender = $this->getSting($mail, "From:", "To:");
                $messageId = $this->getSting($mail, "X-Postfix-Queue-ID:", "X-Postfix-Sender:");

                $to = $this->getSting($mail, "To:", "MIME-Version: 1.0");
                $to = str_replace("To: ","",$to);

                $isNotZero = strlen($sender) != 0;
                $isLessThan100 = strlen($sender) < 100;

                $sender = str_replace("From: ","",$sender);
                $messageId = str_replace("X-Postfix-Queue-ID:","",$messageId);

                if ($isNotZero && $isLessThan100) {
//            echo "<br><br>".$sender."<br>";
//            echo $messageId;
                    $perorts[$i]['sender'] = trim($sender," \t\n\r" );
                    $perorts[$i]['to'] = trim($to," \t\n\r" );
                    $perorts[$i]['date'] = trim($timestamp," \t\n\r" );
                    $perorts[$i]['subject'] = trim($subject," \t\n\r" );
                    $perorts[$i]['body'] = trim($body," \t\n\r" );

                    $perorts[$i]['message_id'] = trim($messageId," \t\n\r" );

                    $mail = str_replace(PHP_EOL, "<br>", $mail);
                    $perorts[$i]['all_content'] = $mail;
                }

            }
        }

        return $perorts;
    }

    private function getSting($string, $from, $to) {

        $positionStart = strripos($string, $from);
        $positionEnd = strrpos($string, $to);
        $result = substr($string, $positionStart, $positionEnd - $positionStart);
        return $result;

        //$positionStart = strripos($file, "From MAILER-DAEMON");
        //$positionEnd = strripos($file, "Undelivered Message");
        //echo "<br>".$positionStart." ".$positionEnd;
        //$result = substr($file, $positionStart, $positionEnd - $positionStart);
        //echo "<br>".$result;
    }

    public function sendServerReport(MailEntity $mailEntity) {
        $wrongEmailsTo = App::$domain->mail->address->isExternalList($mailEntity->to);
        $wrongEmailsCopyTo = App::$domain->mail->address->isExternalList($mailEntity->copy_to);
        if (!empty($mailEntity->blind_copy)) {
            $wrongEmailsBlindCopy = App::$domain->mail->address->isExternalList($mailEntity->blind_copy);
        } else {
            $mailEntity->blind_copy = [];
            $wrongEmailsBlindCopy = [];
        }
        $wrongEmails = array_merge($wrongEmailsTo, $wrongEmailsCopyTo, $wrongEmailsBlindCopy);
        if (count($wrongEmails) != 0) {
            $addressEntity = \App::$domain->mail->address->myAddress();
            $dataError = [
                'from' => 'mailsender@' . $addressEntity->domain,
                'to' => $mailEntity->from,
                'subject' => '550 Server Error',
                'content' => 'The requested actions were not executed because mailboxes <b>' .
                    implode('</b>, <b>', $wrongEmails) .
                    '</b> are not available. The team may be rejected from the policy.'
            ];
            \App::$domain->mail->mail->create($dataError);
        }
        $mailEntity->to = array_diff($mailEntity->to, $wrongEmailsTo);
        $mailEntity->copy_to = array_diff($mailEntity->copy_to, $wrongEmailsCopyTo);
        $mailEntity->blind_copy = array_diff($mailEntity->blind_copy, $wrongEmailsCopyTo);
        return $mailEntity;
    }

}