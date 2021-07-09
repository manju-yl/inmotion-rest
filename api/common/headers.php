<?php
header("X-XSS-Protection: 0");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header("Content-Type: application/json; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Frame-Options: SAMEORIGIN");
