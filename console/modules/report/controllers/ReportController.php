<?php


namespace console\modules\report\controllers;

use App;
use yii2rails\extension\console\base\Controller;

class ReportController extends Controller
{

    public function behaviors()
    {
        return [
//            'authenticator' => Behavior::auth(),
        ];
    }

    public function actionIndex()
    {
        $filename = './error_mail_log';
        $emails =  App::$domain->mail->report->getEmailForReport($filename);
        $reposts = [];
//        $user = $users[24];
        foreach ($emails as $email) {
            App::$domain->mail->report->generateEmlFile($email['message_id'], $email['sender'], $email['to'], $email['subject'], $email['body']);
            $reposts = App::$domain->mail->report->sendReport($email);
//            $reposts[] = \App::$domain->mail->report->sendLetter();
        }
        var_dump($emails);
        var_dump($reposts);
//        var_dump($filename2);
    }

    public function actionIndex2()
    {
        echo shell_exec('pwd');
    }

}