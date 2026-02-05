<?php
defined('MOODLE_INTERNAL') || die();

function local_masterportal_extend_navigation(global_navigation $navigation): void {
    if (!isloggedin() || isguestuser()) return;

    $systemcontext = context_system::instance();
    if (!has_capability('local/masterportal:view', $systemcontext)) return;

    $node = $navigation->add(
        get_string('pluginname', 'local_masterportal'),
        new moodle_url('/local/masterportal/dashboard.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_masterportal_root',
        new pix_icon('i/navigationitem', '')
    );
    $node->showinflatnavigation = true;
}

function local_masterportal_before_http_headers(): void {
    if (!isloggedin() || isguestuser()) return;
    if (is_siteadmin()) return;

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($script === '/my/index.php' || $script === '/my/') {
        redirect(new moodle_url('/local/masterportal/dashboard.php'));
    }
}

function local_masterportal_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    require_login();
    if (isguestuser()) {
        return false;
    }

    if ($filearea !== 'quickthumb') {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args) . '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_masterportal', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, false, $options);
}
