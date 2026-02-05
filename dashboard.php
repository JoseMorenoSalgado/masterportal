<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/calendar/lib.php');

$systemcontext = context_system::instance();
require_login();
require_capability('local/masterportal:view', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('embedded');
$PAGE->set_heading('');
$PAGE->navbar->ignore_active();

$primary = get_config('local_masterportal', 'primarycolor') ?: '#0b2a3a';
$brandname = get_config('local_masterportal', 'brandname') ?: 'Portal Máster';

$PAGE->requires->css(new moodle_url('/local/masterportal/styles.css'));
$PAGE->requires->js_init_code(<<<'JS'
(function(){
  var app = document.querySelector('.mp-app');
  if(!app) return;
  var btn = document.querySelector('.mp-burger');
  var overlay = document.querySelector('[data-mp-overlay="1"]');
  if(!btn) return;

  function closeMenu(){
    app.classList.remove('is-menu-open');
    btn.setAttribute('aria-expanded','false');
  }
  function toggleMenu(){
    var open = app.classList.toggle('is-menu-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  btn.addEventListener('click', function(e){ e.preventDefault(); toggleMenu(); });
  if(overlay){ overlay.addEventListener('click', function(){ closeMenu(); }); }
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeMenu(); } });
})();
JS
);
$PAGE->requires->css(new moodle_url('/local/masterportal/theme.php', ['v' => time(), 'c' => rawurlencode($primary)]));

$output = $PAGE->get_renderer('core');

$template = [];
$template['brandname'] = $brandname;
$template['username'] = fullname($USER);

$userpic = new user_picture($USER);
$userpic->size = 100;
$template['avatarurl'] = $userpic->get_url($PAGE)->out(false);
$template['profileurl'] = (new moodle_url('/user/profile.php', ['id' => $USER->id]))->out(false);

$template['messageurl'] = (new moodle_url('/message/index.php'))->out(false);
$template['notificationurl'] = (new moodle_url('/message/output/popup/notifications.php'))->out(false);

$template['q'] = optional_param('q', '', PARAM_TEXT);
$template['searchaction'] = (new moodle_url('/local/masterportal/dashboard.php'))->out(false);
$template['searchplaceholder'] = get_string('searchall', 'local_masterportal');

$template['suggestions'] = \local_masterportal\local\activity_repository::suggestions($template['q'], 8);

$template['menu'] = [
  ['label'=>get_string('dashboard','local_masterportal'), 'url'=>(new moodle_url('/local/masterportal/dashboard.php'))->out(false), 'icon'=>'D', 'active'=>true],
  ['label'=>get_string('resources','local_masterportal'), 'url'=>(new moodle_url('/local/masterportal/resources.php'))->out(false), 'icon'=>'+', 'active'=>false],
  ['label'=>get_string('live','local_masterportal'), 'url'=>(new moodle_url('/local/masterportal/live.php'))->out(false), 'icon'=>'▣', 'active'=>false],
  ['label'=>get_string('community','local_masterportal'), 'url'=>(new moodle_url('/local/masterportal/community.php'))->out(false), 'icon'=>'👤', 'active'=>false],
];

$courseid = (int)(get_config('local_masterportal', 'mastercourseid') ?: 2);
$template['resourcesurl'] = (new moodle_url('/local/masterportal/resources.php'))->out(false);

// Quick access from course sections (configurable) and omit section 0 always.
$template['quick'] = \local_masterportal\local\quick_repository::get_quick_items($courseid, $PAGE);

// News from config.
$newsraw = get_config('local_masterportal', 'newsitems') ?: "";
$news = [];
$lines = preg_split("/\r\n|\r|\n/", trim($newsraw));
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = array_map('trim', explode('|', $line));
    $news[] = ['type'=>$parts[0] ?? '', 'title'=>$parts[1] ?? '', 'date'=>$parts[2] ?? ''];
}
$template['news'] = $news;
$template['newsbar'] = $news[0]['title'] ?? 'Últimas actualizaciones';

$template['showcourses'] = (bool)(get_config('local_masterportal','showcourses') ?? 1);
$limit = (int)(get_config('local_masterportal','courseslimit') ?? 6);
if ($limit < 1) $limit = 6;

$courses = enrol_get_my_courses(['id','fullname','shortname'], 'sortorder ASC');
$coursecards = [];
$thumbs = ['t-course-1','t-course-2','t-course-3','t-course-4','t-course-5','t-course-6'];
$i = 0;
foreach ($courses as $c) {
    if ($c->id == SITEID) continue;
    if (count($coursecards) >= $limit) break;
    $coursecards[] = [
        'name' => format_string($c->fullname),
        'shortname' => format_string($c->shortname),
        'url' => (new moodle_url('/course/view.php', ['id'=>$c->id]))->out(false),
        'thumbclass' => $thumbs[$i % count($thumbs)],
    ];
    $i++;
}
$template['courses'] = $coursecards;


$content = $output->render_from_template('local_masterportal/dashboard', $template);

echo $output->header();
echo $output->render_from_template('local_masterportal/layout', array_merge($template, ['content'=>$content]));
echo $output->footer();
// Mini calendario nativo de Moodle (marca eventos y actividades).
// Moodle cambia algunas funciones auxiliares entre versiones/temas: hacemos fallback seguro.
$cal_m = (int)date('n');
$cal_y = (int)date('Y');

$courses = [];
$groups = [];
$users = [];

if (function_exists('calendar_get_default_courses')) {
    $courses = calendar_get_default_courses();
}
if (function_exists('calendar_get_default_groups')) {
    $groups = calendar_get_default_groups();
}
if (function_exists('calendar_get_default_users')) {
    $users = calendar_get_default_users();
}

$template['minicalendarhtml'] = '';
if (function_exists('calendar_get_mini')) {
    try {
        // Moodle 4.x/5.x típicamente.
        $template['minicalendarhtml'] = calendar_get_mini($courses, $groups, $users, $cal_m, $cal_y);
    } catch (\Throwable $e) {
        try {
            // Fallback a firmas antiguas (si aplica).
            $template['minicalendarhtml'] = calendar_get_mini($courses, $cal_m, $cal_y);
        } catch (\Throwable $e2) {
            try {
                $template['minicalendarhtml'] = calendar_get_mini($cal_m, $cal_y);
            } catch (\Throwable $e3) {
                $template['minicalendarhtml'] = '';
            }
        }
    }
}


