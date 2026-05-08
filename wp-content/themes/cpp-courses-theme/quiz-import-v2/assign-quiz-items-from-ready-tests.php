<?php
/**
 * Заполнение поля quiz_items по листу «Готовые тесты» (CSV).
 *
 * Сценарий:
 * 1) Уже импортированы записи quiz (заголовок вида "N. Текст вопроса").
 * 2) Есть CSV с колонками:
 *    - Название теста
 *    - № вопросов
 * 3) Скрипт находит страницу по названию и записывает relationship `quiz_items`.
 *
 * Рекомендуемый запуск:
 *
 *   QUIZ_ASSIGN_DRY_RUN=1 wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/assign-quiz-items-from-ready-tests.php
 *
 * Реальный запуск:
 *
 *   wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/assign-quiz-items-from-ready-tests.php
 *
 * Опции через ENV:
 *   QUIZ_ASSIGN_DRY_RUN=1
 *   QUIZ_ASSIGN_FORCE=1                 (перезаписывать даже непустые quiz_items)
 *   QUIZ_ASSIGN_CSV=/abs/path.csv       (по умолчанию ready-tests.csv рядом)
 *   QUIZ_ASSIGN_CREATE_PAGES=1          (создавать страницу, если не найдена)
 *   QUIZ_ASSIGN_STATUS=draft|publish    (статус при создании страницы, default=draft)
 *
 * Опции через токены (если WP-CLI пропускает после --):
 *   dry-run force create-pages status=publish csv=/path.csv
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Запускайте через WP-CLI: wp eval-file .../assign-quiz-items-from-ready-tests.php\n");
    exit(1);
}

if (!function_exists('get_field') || !function_exists('update_field')) {
    fwrite(STDERR, "ACF/SCF не загружены (get_field/update_field отсутствуют).\n");
    exit(1);
}

/**
 * @return array{dry_run:bool,force:bool,csv:string,create_pages:bool,page_status:string}
 */
function cpp_quiz_assign_parse_args() {
    global $argv;
    $args = is_array($argv) ? $argv : array();
    $out = array(
        'dry_run'      => false,
        'force'        => false,
        'csv'          => '',
        'create_pages' => false,
        'page_status'  => 'draft',
    );
    foreach ($args as $i => $a) {
        if ($i === 0 || $a === '--') {
            continue;
        }
        if ($a === '--dry-run' || $a === 'dry-run') {
            $out['dry_run'] = true;
            continue;
        }
        if ($a === '--force' || $a === 'force') {
            $out['force'] = true;
            continue;
        }
        if ($a === '--create-pages' || $a === 'create-pages') {
            $out['create_pages'] = true;
            continue;
        }
        if (strpos($a, '--status=') === 0 || strpos($a, 'status=') === 0) {
            $v = preg_replace('/^--?status=/', '', $a);
            $out['page_status'] = $v === 'publish' ? 'publish' : 'draft';
            continue;
        }
        if (strpos($a, '--csv=') === 0 || strpos($a, 'csv=') === 0) {
            $out['csv'] = preg_replace('/^--?csv=/', '', $a);
            continue;
        }
    }

    $env_dry = getenv('QUIZ_ASSIGN_DRY_RUN');
    $env_force = getenv('QUIZ_ASSIGN_FORCE');
    $env_csv = getenv('QUIZ_ASSIGN_CSV');
    $env_create = getenv('QUIZ_ASSIGN_CREATE_PAGES');
    $env_status = getenv('QUIZ_ASSIGN_STATUS');
    if ($env_dry !== false && $env_dry !== '' && $env_dry !== '0') {
        $out['dry_run'] = true;
    }
    if ($env_force !== false && $env_force !== '' && $env_force !== '0') {
        $out['force'] = true;
    }
    if ($env_create !== false && $env_create !== '' && $env_create !== '0') {
        $out['create_pages'] = true;
    }
    if ($env_csv !== false && $env_csv !== '') {
        $out['csv'] = (string) $env_csv;
    }
    if ($env_status !== false && $env_status !== '') {
        $out['page_status'] = $env_status === 'publish' ? 'publish' : 'draft';
    }
    return $out;
}

/**
 * @param string $h
 * @return string
 */
function cpp_quiz_assign_norm_header($h) {
    $h = trim((string) $h);
    $h = mb_strtolower($h, 'UTF-8');
    $h = preg_replace('/\x{00a0}/u', ' ', $h);
    $h = preg_replace('/\s+/u', ' ', $h);
    return $h;
}

/**
 * @param array<int,string> $headers
 * @return array<string,int>
 */
function cpp_quiz_assign_map_columns($headers) {
    $map = array();
    foreach ($headers as $i => $h) {
        $n = cpp_quiz_assign_norm_header($h);
        if ($n === 'название теста') {
            $map['test_title'] = $i;
            continue;
        }
        if ($n === '№ вопросов' || $n === 'номер вопросов' || strpos($n, 'вопросов') !== false) {
            $map['question_numbers'] = $i;
            continue;
        }
    }
    return $map;
}

/**
 * Разбор списка номеров: "1,2,3" и диапазоны "10-20" / "10–20".
 *
 * @param string $raw
 * @return array<int,int>
 */
function cpp_quiz_assign_parse_numbers($raw) {
    $raw = trim((string) $raw);
    if ($raw === '') {
        return array();
    }
    $raw = str_replace(array('—', '–'), '-', $raw);
    $parts = preg_split('/[;,]+/', $raw);
    $out = array();
    foreach ($parts as $p) {
        $p = trim((string) $p);
        if ($p === '') {
            continue;
        }
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $p, $m)) {
            $a = (int) $m[1];
            $b = (int) $m[2];
            if ($a > 0 && $b > 0) {
                if ($a <= $b) {
                    for ($x = $a; $x <= $b; $x++) {
                        $out[] = $x;
                    }
                } else {
                    for ($x = $a; $x >= $b; $x--) {
                        $out[] = $x;
                    }
                }
            }
            continue;
        }
        if (preg_match('/^\d+$/', $p)) {
            $out[] = (int) $p;
            continue;
        }
        // вытащим все числа, если там мусор между ними
        if (preg_match_all('/\d+/', $p, $ms)) {
            foreach ($ms[0] as $n) {
                $out[] = (int) $n;
            }
        }
    }
    $out = array_values(array_unique(array_filter($out, static function ($n) {
        return (int) $n > 0;
    })));
    return $out;
}

/**
 * @return array<int,int> map question_num => quiz_post_id
 */
function cpp_quiz_assign_build_quiz_num_map() {
    $ids = get_posts(
        array(
            'post_type'      => 'quiz',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        )
    );
    $map = array();
    foreach ($ids as $id) {
        $title = get_the_title((int) $id);
        if (preg_match('/^\s*(\d+)\./u', (string) $title, $m)) {
            $num = (int) $m[1];
            if ($num > 0 && !isset($map[$num])) {
                $map[$num] = (int) $id;
            }
        }
    }
    return $map;
}

/**
 * @param string $title
 * @param bool $create
 * @param string $status
 * @return int
 */
function cpp_quiz_assign_find_or_create_page($title, $create, $status) {
    $title = trim((string) $title);
    if ($title === '') {
        return 0;
    }
    $page = get_page_by_title($title, OBJECT, 'page');
    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }
    $slug = sanitize_title($title);
    if ($slug !== '') {
        $p = get_page_by_path($slug, OBJECT, 'page');
        if ($p instanceof WP_Post) {
            return (int) $p->ID;
        }
    }
    if (!$create) {
        return 0;
    }
    $pid = wp_insert_post(
        array(
            'post_type'   => 'page',
            'post_title'  => $title,
            'post_status' => $status === 'publish' ? 'publish' : 'draft',
            'post_name'   => $slug !== '' ? $slug : '',
        ),
        true
    );
    if (is_wp_error($pid)) {
        return 0;
    }
    return (int) $pid;
}

$opts = cpp_quiz_assign_parse_args();
$csv_path = $opts['csv'];
if ($csv_path === '') {
    $default_csv = __DIR__ . DIRECTORY_SEPARATOR . 'ready-tests.csv';
    if (is_readable($default_csv)) {
        $csv_path = $default_csv;
    }
}
if ($csv_path === '' || !is_readable($csv_path)) {
    fwrite(STDERR, "Нет CSV ready-tests.csv рядом со скриптом. Задайте QUIZ_ASSIGN_CSV=/path.csv\n");
    exit(1);
}

$quiz_map = cpp_quiz_assign_build_quiz_num_map();
if (empty($quiz_map)) {
    fwrite(STDERR, "Не найдено записей quiz с заголовком вида 'N. ...'. Сначала импортируйте банк вопросов.\n");
    exit(1);
}

$fh = fopen($csv_path, 'rb');
if (!$fh) {
    fwrite(STDERR, "Не удалось открыть CSV: {$csv_path}\n");
    exit(1);
}
$bom = fread($fh, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($fh);
}
$headers = fgetcsv($fh);
if (!$headers || count($headers) < 2) {
    fclose($fh);
    fwrite(STDERR, "CSV пустой или без шапки.\n");
    exit(1);
}
$cols = cpp_quiz_assign_map_columns($headers);
if (!isset($cols['test_title']) || !isset($cols['question_numbers'])) {
    fclose($fh);
    fwrite(STDERR, "Нужны колонки: 'Название теста' и '№ вопросов'. Найдено: " . implode(' | ', $headers) . "\n");
    exit(1);
}

$row_num = 1;
$updated = 0;
$created_pages = 0;
$skipped = 0;
$errors = 0;

while (($row = fgetcsv($fh)) !== false) {
    $row_num++;
    if (count(array_filter($row, static function ($c) {
        return $c !== null && trim((string) $c) !== '';
    })) === 0) {
        continue;
    }

    $title = trim((string) ($row[ $cols['test_title'] ] ?? ''));
    $num_list = trim((string) ($row[ $cols['question_numbers'] ] ?? ''));

    if ($title === '' || $num_list === '') {
        fwrite(STDERR, "Строка {$row_num}: пустое название теста или № вопросов, пропуск.\n");
        $errors++;
        continue;
    }

    $page_id = cpp_quiz_assign_find_or_create_page($title, $opts['create_pages'], $opts['page_status']);
    if ($page_id < 1) {
        fwrite(STDERR, "Строка {$row_num}: страница '{$title}' не найдена.\n");
        $errors++;
        continue;
    }

    $numbers = cpp_quiz_assign_parse_numbers($num_list);
    if (empty($numbers)) {
        fwrite(STDERR, "Строка {$row_num}: не удалось распарсить № вопросов.\n");
        $errors++;
        continue;
    }

    $ids = array();
    $missing = array();
    foreach ($numbers as $n) {
        if (isset($quiz_map[$n])) {
            $ids[] = (int) $quiz_map[$n];
        } else {
            $missing[] = $n;
        }
    }
    if (empty($ids)) {
        fwrite(STDERR, "Строка {$row_num}: ни один № вопроса не найден в quiz.\n");
        $errors++;
        continue;
    }
    if (!empty($missing)) {
        fwrite(STDOUT, "Строка {$row_num}: предупреждение — отсутствуют вопросы: " . implode(', ', $missing) . "\n");
    }

    if (!$opts['force']) {
        $existing = get_field('quiz_items', $page_id);
        if (is_array($existing) && !empty($existing)) {
            fwrite(STDOUT, "Строка {$row_num}: page {$page_id} уже имеет quiz_items, пропуск (use force).\n");
            $skipped++;
            continue;
        }
    }

    if ($opts['dry_run']) {
        fwrite(STDOUT, "[dry-run] строка {$row_num}: page {$page_id}, quiz_items=" . count($ids) . "\n");
        $updated++;
        continue;
    }

    update_field('quiz_items', array_values(array_unique(array_map('intval', $ids))), $page_id);
    fwrite(STDOUT, "Строка {$row_num}: обновлена page {$page_id}, quiz_items=" . count($ids) . "\n");
    $updated++;
}

fclose($fh);

fwrite(STDOUT, "Готово. Обновлено/проверено: {$updated}, пропущено: {$skipped}, ошибок: {$errors}.\n");
