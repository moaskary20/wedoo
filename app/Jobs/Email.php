<?php

namespace App\Jobs;

use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use Exception;

class Email extends BaseJob implements JobInterface
{
    public function process()
    {
        log_message('info', 'Email job started.');

        try {
            $db = db_connect();
            $db->transStrict(false);
            $db->transBegin();

            $email = service('email', null, false);
            $result = $email
                ->setTo('recipient@example.com')
                ->setSubject('My test email')
                ->setMessage($this->data['message'])
                ->send(false);

            if (! $result) {
                throw new Exception($email->printDebugger('headers'));
            }

            log_message('info', 'Email sent successfully.');

            if ($db->transStatus() === false) {
                $db->transRollback();
            } else {
                $db->transCommit();
            }

            return $result;
        } catch (\Throwable $th) {
            log_message('error', 'Error processing email job: ' . $th->getMessage());
            $db->transRollback();
            throw $th;
        }
    }
}
