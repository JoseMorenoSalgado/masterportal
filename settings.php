<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_masterportal', get_string('settings', 'local_masterportal'));

    $settings->add(new admin_setting_configtext(
        'local_masterportal/brandname',
        get_string('brandname', 'local_masterportal'),
        get_string('brandname_desc', 'local_masterportal'),
        'CirugíaOral',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_masterportal/primarycolor',
        get_string('primarycolor', 'local_masterportal'),
        get_string('primarycolor_desc', 'local_masterportal'),
        '#0b2a3a',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_masterportal/mastercourseid',
        get_string('mastercourseid', 'local_masterportal'),
        get_string('mastercourseid_desc', 'local_masterportal'),
        '2',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_masterportal/newsitems',
        get_string('newsitems', 'local_masterportal'),
        get_string('newsitems_desc', 'local_masterportal'),
        "Seminario|Avances en cirugía periodontal|12 abr 2024
Actualización|Cambio en el horario de clase|8 abr 2024
Evento|Simposio de implantología|2 abr 2024",
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_masterportal/showcourses',
        get_string('showcourses', 'local_masterportal'),
        get_string('showcourses_desc', 'local_masterportal'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_masterportal/courseslimit',
        get_string('courseslimit', 'local_masterportal'),
        get_string('courseslimit_desc', 'local_masterportal'),
        6,
        PARAM_INT
    ));

    $ADMIN->add('localplugins', $settings);

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_masterportal_managequick',
        get_string('managequick','local_masterportal'),
        new moodle_url('/local/masterportal/manage_quick.php')
    ));
}
