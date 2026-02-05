<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

$systemcontext = context_system::instance();
require_login();
require_capability('local/masterportal:manage', $systemcontext);

// Importante: para evitar que el theme RemUI cargue el árbol de administración (admin_get_root)
// al construir el settingsnav, seteamos el contexto de página al usuario.
$pagecontext = context_user::instance($USER->id);
$PAGE->set_context($pagecontext);
$PAGE->set_url(new moodle_url('/local/masterportal/manage_quick.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('managequick','local_masterportal'));
$PAGE->set_heading(get_string('managequick','local_masterportal'));

$courseid = (int)(get_config('local_masterportal','mastercourseid') ?: 2);

class local_masterportal_quick_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        $custom = $this->_customdata;
        $items = $custom['items'];
        $courseid = $custom['courseid'];

        $mform->addElement('static', 'help', '', get_string('managequick_desc','local_masterportal'));

        $mform->addElement('text', 'courseid', get_string('mastercourseid','local_masterportal'));
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $courseid);

        $mform->addElement('header', 'h1', get_string('quickaccess','local_masterportal'));

        for ($i=0; $i<6; $i++) {
            $mform->addElement('text', "mpmplabel{$i}", get_string('quick_item_label','local_masterportal') . " #" . ($i+1));
            $mform->setType("mpmplabel{$i}", PARAM_TEXT);
            $mform->setDefault("mpmplabel{$i}", $items[$i]['label'] ?? '');

            $mform->addElement('text', "mpmpsection{$i}", get_string('quick_item_section','local_masterportal') . " #" . ($i+1));
            $mform->setType("mpmpsection{$i}", PARAM_INT);
            $mform->setDefault("mpmpsection{$i}", $items[$i]['section'] ?? ($i+1));


$mform->addElement('text', "mpmpicon{$i}", get_string('quick_item_icon','local_masterportal') . " #" . ($i+1));
$mform->setType("mpmpicon{$i}", PARAM_TEXT);
$mform->setDefault("mpmpicon{$i}", $items[$i]['icon'] ?? '');
$mform->addHelpButton("mpmpicon{$i}", 'quick_item_icon', 'local_masterportal');
            $mform->addElement('static', "mpmpsep{$i}", '', '<hr>');
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}

$raw = get_config('local_masterportal','quickjson') ?: '';
$items = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $items = $decoded;
}
if (!$items) {
    $items = \local_masterportal\local\quick_repository::defaults_from_sections($courseid);
}

$form = new local_masterportal_quick_form(null, [
    'items' => $items,
    'courseid' => $courseid,
    'contextid' => $systemcontext->id,
]);

if ($data = $form->get_data()) {
    $courseid = (int)$data->courseid;
    set_config('mastercourseid', $courseid, 'local_masterportal');

    // v0.3.7 flat fields

    

    $newitems = [];
    for ($i=0; $i<6; $i++) {
        $label = trim($data->{'mplabel' . $i} ?? '');
        $section = (int)($data->{'mpsection' . $i} ?? 0);
        if ($label === '' || $section <= 0) continue; // section 0 is not allowed here.

        $newitems[] = [
            'label' => $label,
            'section' => $section,
            'meta' => '',
                    ];
    }

    \local_masterportal\local\quick_repository::save_quick_items($newitems);

    redirect(new moodle_url('/local/masterportal/manage_quick.php'), get_string('quick_saved','local_masterportal'), 2);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managequick','local_masterportal'));
$form->display();
echo $OUTPUT->footer();