<?php
header('Content-Type: text/plain');

$domain = "apitest.dtes.mh.gob.sv";

// 1. Resolver DNS
$ip = gethostbyname($domain);
echo "IP resuelta para $domain: $ip\n";

// 2. Probar conexión socket a puerto 443
$fp = @fsockopen($domain, 443, $errno, $errstr, 5);

if ($fp) {
    echo "Conexión exitosa al puerto 443 de $domain\n";
    fclose($fp);
} else {
    echo "Fallo la conexión a $domain puerto 443: $errno - $errstr\n";
}