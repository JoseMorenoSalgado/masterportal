<?php
define('NO_MOODLE_COOKIES', true);
require_once(__DIR__ . '/../../config.php');

header('Content-Type: text/css; charset=utf-8');

$color = required_param('c', PARAM_RAW_TRIMMED);
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
    $color = '#0b2a3a';
}
echo ":root{--mp-navy:{$color};--mp-navy-2:{$color};}\n";
