<?php

namespace {
	/**
	 * Fetch args from CLI and return fetched args array
	 * @return array Sample:
	 * <pre>Array
	(
	[request] => smtpd_access_policy
	[protocol_state] => MAIL
	[protocol_name] => ESMTP
	[client_address] => 91.124.206.200
	[client_name] => 200-206-124-91.pool.ukrtel.net
	[reverse_client_name] => 200-206-124-91.pool.ukrtel.net
	[helo_name] => [192.168.1.3]
	[sender] => test@redmine.org
	[recipient] =>
	[recipient_count] => 0
	[queue_id] =>
	[instance] =>
	[size] => 1732
	[etrn_domain] =>
	[stress] =>
	[sasl_method] => LOGIN
	[sasl_username] => test.redmine
	[sasl_sender] =>
	[ccert_subject] =>
	[ccert_issuer] =>
	[ccert_fingerprint] =>
	[ccert_pubkey_fingerprint] =>
	[encryption_protocol] =>
	[encryption_cipher] =>
	[encryption_keysize] => 0
	)
	 * </pre>
	 */
	function fetchArgs()
	{
		$args = [];
		while ($line = trim(fgets(STDIN)))
		{
			list($key, $value) = explode('=', $line);
			$args[$key] = $value;
		}

		return $args;
	}

	define('ACTION_ALLOW', 'dunno');
	define('ACTION_REJECT', 'reject');

	/**
	 * Send the result and stop script work
	 *
	 * @param $action
	 * @param string $message
	 */
	function sendResult($action, $message = '')
	{
		echo "action=$action" . (trim($message) ? (' ' . trim($message)) : '') . "\n\n";
		exit(0);
	}

	/**
	 * Parse hash and return result
	 * @param $path
	 * @param array $bindings
	 * @param bool $hashKey
	 * @return array Example:
	 * <pre> Array
	(
	[test@redmine.org] => Array
	(
	[email] => test@redmine.org
	[account] => test.redmine
	)
	)</pre><br/>
	 *      OR <br/>
	 * <pre> Array
	(
	[0] => Array
	(
	[email] => test@redmine.org
	[account] => test.redmine
	)
	)</pre><br/>
	 */
	function parseHash($path, array $bindings, $hashKey = FALSE)
	{
		if ($hashKey AND !in_array($hashKey, $bindings)) throw new \InvalidArgumentException('Bindings must contain a key!');
		if (!file_exists($path)) throw new \RuntimeException("File \"$path\"not exists!");

		$parsed = [];
		$contents = file_get_contents($path);
		foreach (preg_split('#[\n\r]+#', $contents) as $line)
		{
			$parsedLine = preg_split('#[\s]{1,}#', $line);
			if (empty($parsedLine[0])) continue; // empty string

			$parsedRow = [];
			foreach ($bindings as $index => $key)
			{
				if (!empty($parsedLine[$index])) $parsedRow[$key] = trim($parsedLine[$index]);
			}

			if (!$hashKey) $parsed[] = $parsedRow;
			else $parsed[$parsedRow[$hashKey]] = $parsedRow;
		}

		return $parsed;
	}
}