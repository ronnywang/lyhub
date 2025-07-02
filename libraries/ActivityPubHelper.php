<?php

class ActivityPubHelper
{
    public static function verify_http_signature($server, $body) {
		if (!isset($server['HTTP_SIGNATURE'])) {
			return false;
		}

		$signature_parts = [];
		foreach (explode(',', $server['HTTP_SIGNATURE']) as $part) {
			if (preg_match('/(.+)="([^"]+)"/', $part, $matches)) {
				$signature_parts[$matches[1]] = $matches[2];
			}
		}

		$keyId = $signature_parts['keyId'];
		$headers_to_sign = $signature_parts['headers'];
		$signature = base64_decode($signature_parts['signature']);

		// Fetch the public key from the remote actor
		$remote_actor_data = json_decode(file_get_contents($keyId, false, stream_context_create(['http' => ['header' => 'Accept: application/activity+json']])), true);
		if (!isset($remote_actor_data['publicKey']['publicKeyPem'])) {
			return false;
		}
		$remote_public_key = $remote_actor_data['publicKey']['publicKeyPem'];

		// Build the string to verify
        $string_to_sign = '';
		foreach (explode(' ', $headers_to_sign) as $header) {
			if ($header === '(request-target)') {
				$string_to_sign .= "(request-target): " . strtolower($server['REQUEST_METHOD']) . " " . $server['REQUEST_URI'] . "\n";
			} elseif ($header === 'digest') {
				$digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
                $string_to_sign .= "digest: " . $digest . "\n";
            } elseif ($header == 'content-type') {
                $content_type = isset($server['CONTENT_TYPE']) ? $server['CONTENT_TYPE'] : 'application/ld+json';
                $string_to_sign .= "content-type: " . $content_type . "\n";
			} else {
				$string_to_sign .= $header . ": " . $server['HTTP_' . strtoupper(str_replace('-', '_', $header))] . "\n";
			}
		}
		$string_to_sign = rtrim($string_to_sign, "\n");

		// Verify
		return openssl_verify($string_to_sign, $signature, $remote_public_key, OPENSSL_ALGO_SHA256) === 1;
	}

	public static function send_accept_activity($follow_activity, $our_account, $follower_inbox_url, $domain)
	{
		$activity_id = "https://{$domain}/activities/" . uniqid();
		$accept_activity = [
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $activity_id,
			'type' => 'Accept',
			'actor' => "https://{$domain}/users/{$our_account}",
			'object' => $follow_activity,
		];

        self::send_signed_request($follower_inbox_url, json_encode($accept_activity), $our_account, $domain);
	}

    public static function send_signed_request($inbox_url, $body, $our_account, $domain)
	{
        $actor_id = "https://{$domain}/users/{$our_account}";
		$key_id = "{$actor_id}#main-key";
		$private_key = file_get_contents(__DIR__ . "/../config/private");

		$url_parts = parse_url($inbox_url);
		$host = $url_parts['host'];
		$path = $url_parts['path'];

		$date = gmdate('D, d M Y H:i:s T');
		$digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));

		$string_to_sign = "(request-target): post {$path}\nhost: {$host}\ndate: {$date}\ndigest: {$digest}";

		openssl_sign($string_to_sign, $signature, $private_key, OPENSSL_ALGO_SHA256);
		$encoded_signature = base64_encode($signature);

		$header = [
			'Host: ' . $host,
			'Date: ' . $date,
			'Digest: ' . $digest,
			'Signature: keyId="' . $key_id . '",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="' . $encoded_signature . '"',
			'Content-Type: application/ld+json',
			'Accept: application/activity+json'
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $inbox_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_exec($ch);
		curl_close($ch);
	}


}
