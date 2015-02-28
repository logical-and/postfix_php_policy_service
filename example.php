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