<?php
// Resources hub page: catalog style with left menu only (no right column).
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/masterportal/resources.php'));
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('resources', 'local_masterportal'));
$PAGE->set_heading(get_string('resources', 'local_masterportal'));

$courseid = (int)get_config('local_masterportal', 'courseid');
if (!$courseid) {
    $courseid = SITEID;
}
$tab = optional_param('tab', 'all', PARAM_ALPHANUMEXT);

$course = get_course($courseid);
$modinfo = get_fast_modinfo($course);
$sections = $modinfo->get_section_info_all();

// Quick items config (sections), excluding section 0.
$quickjson = get_config('local_masterportal', 'quickitems');
$quickitems = [];
if (!empty($quickjson)) {
    $decoded = json_decode($quickjson, true);
    if (is_array($decoded)) {
        foreach ($decoded as $it) {
            if (!empty($it['section']) && (int)$it['section'] > 0) {
                $quickitems[] = $it;
            }
        }
    }
}

function mp_icon_for_mod(string $modname): string {
    $map = [
        'resource' => 'fa-file-lines',
        'url'      => 'fa-link',
        'page'     => 'fa-file',
        'book'     => 'fa-book',
        'folder'   => 'fa-folder',
        'assign'   => 'fa-file-arrow-up',
        'quiz'     => 'fa-circle-question',
        'forum'    => 'fa-comments',
        'lesson'   => 'fa-chalkboard',
        'bigbluebuttonbn' => 'fa-video',
        'h5pactivity' => 'fa-puzzle-piece',
    ];
    return $map[$modname] ?? 'fa-circle';
}

function mp_first_img(string $html): string {
    if (preg_match('~<img[^>]+src=["\']([^"\']+)["\']~i', $html, $m)) {
        return $m[1];
    }
    return '';
}

$cards = [];
foreach ($sections as $snum => $sinfo) {
    if ((int)$snum === 0) { // omit general
        continue;
    }
    if (!$sinfo || empty($modinfo->sections[$snum])) {
        continue;
    }
    if ($tab !== 'all' && $tab !== ('s' . (int)$snum)) {
        continue;
    }

    foreach ($modinfo->sections[$snum] as $cmid) {
        $cm = $modinfo->get_cm($cmid);
        if (!$cm || !$cm->uservisible) {
            continue;
        }

        $name = format_string($cm->name);
        $url  = $cm->url ? $cm->url->out(false) : '#';
        $modname = $cm->modname;

        // Fetch intro to try extract first image.
        $introhtml = '';
        try {
            global $DB;
            $table = $modname;
            if ($DB->get_manager()->table_exists($table)) {
                $rec = $DB->get_record($table, ['id' => $cm->instance], 'intro, introformat', IGNORE_MISSING);
                if ($rec && isset($rec->intro)) {
                    $introhtml = format_text($rec->intro, $rec->introformat);
                }
            }
        } catch (\Throwable $e) {
            $introhtml = '';
        }

        $img = '';
        if (!empty($introhtml)) {
            $img = mp_first_img($introhtml);
        }

        $cards[] = [
            'section' => (int)$snum,
            'sectionname' => format_string(get_section_name($course, $sinfo)),
            'title' => $name,
            'url' => $url,
            'img' => $img,
            'hasimg' => !empty($img),
            'icon' => mp_icon_for_mod($modname),
        ];
    }
}

// Build left menu links (match plugin routes).
$www = $CFG->wwwroot;
$menu = [
    ['key'=>'dashboard','label'=>get_string('dashboard','local_masterportal'),'url'=>"$www/local/masterportal/dashboard.php",'icon'=>'fa-gauge'],
    ['key'=>'resources','label'=>get_string('resources','local_masterportal'),'url'=>"$www/local/masterportal/resources.php",'icon'=>'fa-layer-group','active'=>true],
    ['key'=>'live','label'=>get_string('live','local_masterportal'),'url'=>"$www/local/masterportal/live.php",'icon'=>'fa-video'],
    ['key'=>'community','label'=>get_string('community','local_masterportal'),'url'=>"$www/local/masterportal/community.php",'icon'=>'fa-users'],
];

$template = [
    'tabs' => array_merge(
        [['key'=>'all','label'=>get_string('all','local_masterportal'),'active'=>($tab==='all')]],
        array_map(function($it) use ($tab) {
            $s = (int)$it['section'];
            $key = 's'.$s;
            $label = !empty($it['title']) ? $it['title'] : ('Sección '.$s);
            return ['key'=>$key,'label'=>$label,'active'=>($tab===$key)];
        }, $quickitems)
    ),
    'cards' => $cards,
    'menu' => $menu,
    'brand' => get_config('local_masterportal', 'brandname') ?: format_string($SITE->shortname),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_masterportal/resources_page', $template);
echo $OUTPUT->footer();
