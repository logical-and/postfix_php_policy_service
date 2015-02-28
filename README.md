# Postfix check_policy_service handler

To customize filtering by sender (live example):

1. Add
```smtpd_sender_restrictions = check_policy_service unix:private/smtp-check-sender```
to /etc/postfix/mail.cf

2. Add
<pre>smtp-check-sender unix   -       n       n       -       0       spawn
         user=postfix-script argv=/etc/postfix/scripts/smtp-check-sender.php</pre>
into /etc/poftfix/master.cf

3. Create /etc/postfix/scripts/smtp-check-sender.php with content:

```php
#!/usr/bin/php
<?php

require __DIR__ . '/postfix-util.php';

$args = fetchArgs();

// Skip not authenticated
if (!empty($args['sasl_username']))
{
        // Check username in virtual table
        $virtualPath = '/etc/postfix/virtual';
        try {
                $table = parseHash($virtualPath, ['email', 'account'], 'account');
        }
        catch (\Exception $e)
        {
                sendResult(ACTION_REJECT, 'Error occured: ' . $e->getMessage());
        }

        if (empty($table[$args['sasl_username']])) sendResult(ACTION_REJECT, "Username \"{$args['sasl_username']}\" is unknown!");
        elseif ($args['sender'] != $table[$args['sasl_username']]['email'])
        {
                sendResult(ACTION_REJECT,
                        'Your account can only send email from "' . $table[$args['sasl_username']]['email'] . '" email! ' .
                        "(tried from \"{$args['sender']}\")");
        }
}

sendResult(ACTION_ALLOW);
```

