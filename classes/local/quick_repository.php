<?php
namespace local_masterportal\local;

defined('MOODLE_INTERNAL') || die();

class quick_repository {


private static function guess_icon(string $label): string {
    $l = mb_strtolower($label, 'UTF-8');
    if (strpos($l, 'caso') !== false) return '🦷';
    if (strpos($l, 'video') !== false) return '🎬';
    if (strpos($l, 'clase') !== false || strpos($l, 'live') !== false) return '🎓';
    if (strpos($l, 'art') !== false || strpos($l, 'artículo') !== false) return '📄';
    if (strpos($l, 'recurso') !== false) return '📚';
    if (strpos($l, 'comun') !== false) return '💬';
    if (strpos($l, 'archivo') !== false) return '📁';
    if (strpos($l, 'cuestion') !== false || strpos($l, 'quiz') !== false) return '✅';
    return '📌';
}

    public static function get_quick_items(int $courseid, \moodle_page $page): array {
        $raw = get_config('local_masterportal', 'quickjson') ?: '';
        $items = [];
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }
        if (!$items) {
            $items = self::defaults_from_sections($courseid);
        }

        $sysctx = \context_system::instance();
        $out = [];
        $i = 0;
        foreach ($items as $it) {
            $label = trim($it['label'] ?? '');
            $section = (int)($it['section'] ?? 0);
            if ($label === '' || $section <= 0) {
                continue; // omit general section 0 always.
            }

            $url = (new \moodle_url('/course/view.php', ['id' => $courseid, 'section' => $section]))->out(false);

            $out[] = [
                'label' => $label,
                'section' => $section,
                'url' => $url,
                'icon' => $it['icon'] ?? self::guess_icon($label),
                                                'meta' => $it['meta'] ?? '',
            ];
            $i++;
            if ($i >= 12) break;
        }
        return $out;
    }

    public static function defaults_from_sections(int $courseid): array {
        $modinfo = get_fast_modinfo($courseid);
        $sections = $modinfo->get_section_info_all();
        $out = [];
        $i = 0;
        foreach ($sections as $s) {
            if ($s->section == 0) continue;
            if (!$s->visible) continue;
            $name = trim(get_section_name($courseid, $s));
            if ($name === '') continue;
            $out[] = [
                'label' => $name,
                'section' => (int)$s->section,
                'meta' => '',
                'icon' => self::guess_icon($name),
            ];
            $i++;
            if ($i >= 4) break;
        }
        return $out;
    }

    public static function save_quick_items(array $items): void {
        set_config('quickjson', json_encode($items, JSON_UNESCAPED_UNICODE), 'local_masterportal');
    }
}
