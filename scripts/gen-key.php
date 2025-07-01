<?php

$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$res = openssl_pkey_new($config);
openssl_pkey_export($res, $private_key); // 存到 DB 的 private_key 欄位
$public_key_details = openssl_pkey_get_details($res);
$public_key = $public_key_details["key"]; // 存到 DB 的 public_key 欄位

file_put_contents(__DIR__ . "/../config/private", $private_key);
file_put_contents(__DIR__ . "/../config/public", $public_key);

