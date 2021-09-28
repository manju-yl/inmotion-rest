<?php
header("Strict-Transport-Security: max-age=15768000");
header('Access-Control-Allow-Origin', "lookuptools-dev.informamarkets.com, lookuptools.informamarkets.com");
header("X-XSS-Protection: 0");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Referrer-Policy: same-origin");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header("Expires: 0"); 
header("Content-Type: application/json; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Frame-Options: SAMEORIGIN");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Request-Headers: X-Custom-Header');
header('Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization, accept, client-security-token');
