<?php
namespace local_masterportal\local;

defined('MOODLE_INTERNAL') || die();

class activity_repository {

    public static function suggestions(string $q, int $limit = 8): array {
        global $USER;

        $courses = enrol_get_my_courses(['id', 'fullname', 'shortname'], 'sortorder ASC');
        if (!$courses) return [];

        $q = trim($q);
        if ($q === '') return [];

        $items = [];
        foreach ($courses as $course) {
            if (count($items) >= $limit) break;
            $modinfo = get_fast_modinfo($course, $USER->id);
            foreach ($modinfo->get_cms() as $cm) {
                if (count($items) >= $limit) break;
                if (!$cm->uservisible) continue;
                if ($cm->modname === 'label') continue;
                $name = trim($cm->name ?? '');
                if ($name === '') continue;
                if (stripos($name, $q) === false) continue;

                $items[] = [
                    'name' => $name,
                    'url' => $cm->url ? $cm->url->out(false) : (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
                    'modname' => $cm->modname,
                    'coursename' => $course->fullname,
                ];
            }
        }
        return $items;
    }
}
