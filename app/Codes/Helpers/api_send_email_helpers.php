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
    function api_send_email_send_grid($email_from = 'test@example.com', $email_to = 'test@example.com', $email_subject = 'SendGrid PHP Library', $email_content = '<html<p>some text here</p></html>')
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
    function api_send_email_mail_gun($email_from = 'test@example.com', $email_to = 'test@example.com', $email_subject = 'SendGrid PHP Library', $email_content = '<html<p>some text here</p></html>')
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
    }
}

if (! function_exists('api_send_email')) {
    /**
     * @param string $email_from
     * @param string $email_to
     * @param string $email_subject
     * @param string $email_content
     */
    function api_send_email($email_from = 'test@example.com', $email_to = 'test@example.com', $email_subject = 'SendGrid PHP Library', $email_content = '<html<p>some text here</p></html>')
    {
        api_send_email_mail_gun($email_from, $email_to, $email_subject, $email_content);
//        api_send_email_send_grid($email_from, $email_to, $email_subject, $email_content);
    }
}
