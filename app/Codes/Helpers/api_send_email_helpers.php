<?php

if (! function_exists('api_send_email_send_grid')) {
    /**
     * @param string $email_from
     * @param string $email_to
     * @param string $email_subject
     * @param string $email_content
     *
     * @return array
     */
    function api_send_email_send_grid(string $email_from = 'test@example.com', string $email_to = 'test@example.com',
                                      string $email_subject = 'SendGrid PHP Library',
                                      string $email_content = '<html<p>some text here</p></html>'): array
    {
        $from    = new SendGrid\Email(null, $email_from);
        $subject = $email_subject;
        $to      = new SendGrid\Email(null, $email_to);
        $content = new SendGrid\Content('text/html', $email_content);
        $mail    = new SendGrid\Mail($from, $subject, $to, $content);

        $apiKey = env('SENDGRID_API');
        $sg     = new \SendGrid($apiKey);

        $response   = $sg->client->mail()->send()->post($mail);
        $get_code   = $response->statusCode();
        $get_header = $response->headers();
        $get_body   = $response->body();

        if ((int) $get_code >= 400) {
            \App\Codes\Models\EmailLog::fail([
                'email'         => $email_to,
                'subject'       => $email_subject,
                'content'       => $email_content,
                'error_message' => 'SendGrid :' . $get_code,
            ]);
        }

        return [
            'code'   => $get_code,
            'header' => $get_header,
            'body'   => $get_body,
        ];
    }
}

if (! function_exists('api_send_email_mail_gun')) {
    /**
     * @param string $email_from
     * @param string $email_to
     * @param string $email_subject
     * @param string $email_content
     *
     * @return mixed
     */
    function api_send_email_mail_gun(string $email_from = 'test@example.com', string $email_to = 'test@example.com',
                                     string $email_subject = 'SendGrid PHP Library',
                                     string $email_content = '<html<p>some text here</p></html>')
    {
        try {
            $mg = \Mailgun\Mailgun::create(env('MAILGUN_API_KEY'));

            return $mg->messages()->send(env('MAILGUN_DOMAIN'), [
                'from'    => 'Info VisitKorea<'.$email_from.'>',
                'to'      => $email_to,
                'subject' => $email_subject,
                'html'    => $email_content,
            ]);
        } catch (\Exception $e) {
            \App\Codes\Models\EmailLog::fail([
                'email'         => $email_to,
                'subject'       => $email_subject,
                'content'       => $email_content,
                'error_message' => $e->getMessage(),
            ]);
        }
        return false;
    }
}

if (! function_exists('api_send_email_mailjet')) {
    /**
     * @param string $email_from
     * @param string $email_to
     * @param string $email_subject
     * @param string $email_content
     *
     * @return bool
     */
    function api_send_email_mailjet(string $email_from = 'test@example.com', string $email_to = 'test@example.com',
                                    string $email_subject = 'SendGrid PHP Library',
                                    string $email_content = '<html<p>some text here</p></html>'): bool
    {
        try {
            $mj = new \Mailjet\Client(env('MJ_APIKEY_PUBLIC'), env('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);

            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $email_from,
//                            'Name' => "Me"
                        ],
                        'To' => [
                            [
                                'Email' => $email_to,
//                                'Name' => "You"
                            ]
                        ],
                        'Subject' => $email_subject,
//                        'TextPart' => "Greetings from Mailjet!",
                        'HTMLPart' => $email_content
                    ]
                ]
            ];

            $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                $response->getData();
                return true;
            }

        } catch (\Exception $e) {
            \App\Codes\Models\V1\LogEmail::create([
                'email'         => $email_to,
                'subject'       => $email_subject,
                'content'       => $email_content,
                'error_message' => $e->getMessage(),
            ]);
        }
        return false;
    }
}

if (! function_exists('api_send_email')) {
    /**
     * @param string $email_from
     * @param string $email_to
     * @param string $email_subject
     * @param string $email_content
     */
    function api_send_email(string $email_from = 'test@example.com', string $email_to = 'test@example.com',
                            string $email_subject = 'SendGrid PHP Library',
                            string $email_content = '<html<p>some text here</p></html>')
    {
        api_send_email_mail_gun($email_from, $email_to, $email_subject, $email_content);
//        api_send_email_send_grid($email_from, $email_to, $email_subject, $email_content);
    }
}
