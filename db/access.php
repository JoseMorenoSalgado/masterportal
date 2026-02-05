<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/masterportal:view' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
            'guest' => CAP_PREVENT,
        ],
    ],
    'local/masterportal:manage' => [
        'riskbitmask' => RISK_CONFIG | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ],
    ],
];
