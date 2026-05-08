<?php
/**
 * Импорт банка вопросов квиза из CSV в тип записи quiz (SCF).
 *
 * Рядом с этим файлом лежит готовый quiz-bank.csv (репозиторий). На сервере
 * импорт не требует Python: достаточно WP-CLI и PHP.
 *
 * Надёжный запуск для WP-CLI, где флаги после eval-file не пробрасываются:
 *
 *   QUIZ_IMPORT_DRY_RUN=1 wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php
 *
 * Также поддерживаются «безфлаговые» токены:
 *
 *   wp eval-file .../import-quiz-bank.php -- dry-run status=publish page-id=123
 *
 * Путь к CSV по умолчанию — quiz-bank.csv в этой же папке (можно передать другой).
 *
 * Опции:
 * 1) ENV:
 *   QUIZ_IMPORT_DRY_RUN=1
 *   QUIZ_IMPORT_FORCE=1
 *   QUIZ_IMPORT_STATUS=draft|publish
 *   QUIZ_IMPORT_PAGE_ID=123
 *   QUIZ_IMPORT_CSV=/abs/path.csv
 *
 * 2) CLI токены:
 *   dry-run | force | status=publish | page-id=123 | csv=/path.csv
 *   также поддерживаются варианты с префиксом --: --dry-run, --status=...
 *
 * Формат CSV: первая строка — заголовки (№ вопроса, Вопрос, Ответ 1–3, Правильный вариант).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Запускайте через WP-CLI: wp eval-file …/quiz-import-v2/import-quiz-bank.php\n");
    exit(1);
}

if (!function_exists('get_field') || !function_exists('update_field')) {
    fwrite(STDERR, "ACF/SCF не загружены (get_field/update_field отсутствуют).\n");
    exit(1);
}

/**
 * @return array{dry_run:bool,status:string,page_id:int,force:bool,csv:string}
 */
function cpp_quiz_import_parse_args() {
    global $argv;
    $args = is_array($argv) ? $argv : array();
    $out = array(
        'dry_run' => false,
        'status'  => 'draft',
        'page_id' => 0,
        'force'   => false,
        'csv'     => '',
    );
    $positional = array();
    foreach ($args as $i => $a) {
        if ($i === 0) {
            continue;
        }
        if ($a === '--') {
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
        if (strpos($a, '--status=') === 0 || strpos($a, 'status=') === 0) {
            $v = preg_replace('/^--?status=/', '', $a);
            $out['status'] = $v === 'publish' ? 'publish' : 'draft';
            continue;
        }
        if (preg_match('/^--?page-id=(\d+)$/', $a, $m)) {
            $out['page_id'] = (int) $m[1];
            continue;
        }
        if (strpos($a, '--csv=') === 0 || strpos($a, 'csv=') === 0) {
            $out['csv'] = preg_replace('/^--?csv=/', '', $a);
            continue;
        }
        if ($a !== '' && $a[0] !== '-') {
            $positional[] = $a;
        }
    }
    if ($out['csv'] === '' && isset($positional[0]) && strpos($positional[0], '=') === false && $positional[0] !== 'dry-run' && $positional[0] !== 'force') {
        $out['csv'] = $positional[0];
    }

    // ENV fallback (приоритетнее CLI, чтобы работало даже когда wp съедает токены)
    $env_dry = getenv('QUIZ_IMPORT_DRY_RUN');
    $env_force = getenv('QUIZ_IMPORT_FORCE');
    $env_status = getenv('QUIZ_IMPORT_STATUS');
    $env_page_id = getenv('QUIZ_IMPORT_PAGE_ID');
    $env_csv = getenv('QUIZ_IMPORT_CSV');

    if ($env_dry !== false && $env_dry !== '' && $env_dry !== '0') {
        $out['dry_run'] = true;
    }
    if ($env_force !== false && $env_force !== '' && $env_force !== '0') {
        $out['force'] = true;
    }
    if ($env_status !== false && $env_status !== '') {
        $out['status'] = $env_status === 'publish' ? 'publish' : 'draft';
    }
    if ($env_page_id !== false && $env_page_id !== '' && is_numeric($env_page_id)) {
        $out['page_id'] = (int) $env_page_id;
    }
    if ($env_csv !== false && $env_csv !== '') {
        $out['csv'] = (string) $env_csv;
    }
    return $out;
}

/**
 * @param string $h
 */
function cpp_quiz_import_norm_header($h) {
    $h = trim((string) $h);
    $h = mb_strtolower($h, 'UTF-8');
    $h = preg_replace('/\x{00a0}/u', ' ', $h);
    $h = preg_replace('/\s+/u', ' ', $h);
    return $h;
}

/**
 * Map normalized header -> key
 *
 * @param array<int,string> $headers
 * @return array<string,int>
 */
function cpp_quiz_import_map_columns($headers) {
    $map = array();
    foreach ($headers as $i => $raw) {
        $n = cpp_quiz_import_norm_header($raw);
        if ($n === '№ вопроса' || $n === 'номер вопроса' || $n === 'n' || $n === 'no' || preg_match('/^№\s*вопроса$/u', $raw)) {
            $map['num'] = $i;
            continue;
        }
        if ($n === 'вопрос' || $n === 'question') {
            $map['question'] = $i;
            continue;
        }
        if ($n === 'ответ 1' || $n === 'answer 1' || $n === 'вариант 1') {
            $map['a1'] = $i;
            continue;
        }
        if ($n === 'ответ 2' || $n === 'answer 2' || $n === 'вариант 2') {
            $map['a2'] = $i;
            continue;
        }
        if ($n === 'ответ 3' || $n === 'answer 3' || $n === 'вариант 3') {
            $map['a3'] = $i;
            continue;
        }
        if (strpos($n, 'правильный вариант') !== false || $n === 'правильный ответ (номер)' || $n === 'correct') {
            $map['correct_idx'] = $i;
            continue;
        }
    }
    return $map;
}

/**
 * @param mixed $v
 */
function cpp_quiz_import_cell_str($v) {
    if ($v === null) {
        return '';
    }
    if (is_float($v) || is_int($v)) {
        return (string) $v;
    }
    return trim((string) $v);
}

/**
 * @param mixed $v
 * @return int
 */
function cpp_quiz_import_question_num($v) {
    if ($v === null || $v === '') {
        return 0;
    }
    if (is_numeric($v)) {
        return (int) round((float) $v);
    }
    return (int) preg_replace('/\D/', '', (string) $v);
}

/**
 * @param string $title
 * @return string
 */
function cpp_quiz_import_truncate_title($title) {
    $max = 200;
    if (function_exists('mb_strlen') && mb_strlen($title, 'UTF-8') <= $max) {
        return $title;
    }
    if (!function_exists('mb_strlen')) {
        return strlen($title) <= $max ? $title : (substr($title, 0, $max - 1) . '…');
    }
    return mb_substr($title, 0, $max - 1, 'UTF-8') . '…';
}

$opts = cpp_quiz_import_parse_args();
$csv_path = $opts['csv'];
if ($csv_path === '') {
    $default_csv = __DIR__ . DIRECTORY_SEPARATOR . 'quiz-bank.csv';
    if (is_readable($default_csv)) {
        $csv_path = $default_csv;
    }
}
if ($csv_path === '' || !is_readable($csv_path)) {
    fwrite(STDERR, "Нет CSV: положите quiz-bank.csv рядом со скриптом или задайте QUIZ_IMPORT_CSV=/path.csv\n");
    exit(1);
}

$fh = fopen($csv_path, 'rb');
if (!$fh) {
    fwrite(STDERR, "Не удалось открыть файл: {$csv_path}\n");
    exit(1);
}

$bom = fread($fh, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($fh);
}

$headers = fgetcsv($fh);
if (!$headers || count($headers) < 3) {
    fclose($fh);
    fwrite(STDERR, "CSV: пустой файл или нет строки заголовков.\n");
    exit(1);
}

$col = cpp_quiz_import_map_columns($headers);
$need = array('question', 'a1', 'a2', 'a3', 'correct_idx');
foreach ($need as $k) {
    if (!isset($col[$k])) {
        fclose($fh);
        fwrite(STDERR, "В заголовках CSV не найдена колонка для ключа: {$k}\n");
        fwrite(STDERR, 'Найдены колонки: ' . implode(' | ', $headers) . "\n");
        exit(1);
    }
}

$row_num = 1;
$seq_fallback = 0;
$created = 0;
$skipped = 0;
$errors = 0;
$ids_ordered = array();

while (($row = fgetcsv($fh)) !== false) {
    $row_num++;
    if (count(array_filter($row, static function ($c) {
        return $c !== null && trim((string) $c) !== '';
    })) === 0) {
        continue;
    }
    $seq_fallback++;

    $qtext = cpp_quiz_import_cell_str($row[ $col['question'] ] ?? '');
    $a1 = cpp_quiz_import_cell_str($row[ $col['a1'] ] ?? '');
    $a2 = cpp_quiz_import_cell_str($row[ $col['a2'] ] ?? '');
    $a3 = cpp_quiz_import_cell_str($row[ $col['a3'] ] ?? '');
    $correct_raw = cpp_quiz_import_cell_str($row[ $col['correct_idx'] ] ?? '');
    $num_display = isset($col['num'])
        ? cpp_quiz_import_question_num($row[ $col['num'] ] ?? '')
        : $seq_fallback;

    if ($qtext === '') {
        fwrite(STDERR, "Строка {$row_num}: пустой «Вопрос», пропуск.\n");
        $errors++;
        continue;
    }

    $correct_num = (int) preg_replace('/\D/', '', $correct_raw);
    if ($correct_num < 1 || $correct_num > 3) {
        fwrite(STDERR, "Строка {$row_num}: «Правильный вариант» должно быть 1–3, получено: {$correct_raw}\n");
        $errors++;
        continue;
    }

    $answers = array($a1, $a2, $a3);
    foreach ($answers as $ai => $at) {
        if ($at === '') {
            fwrite(STDERR, "Строка {$row_num}: пустой ответ " . ($ai + 1) . ".\n");
            $errors++;
            continue 2;
        }
    }

    $row_hash = hash('sha256', $num_display . '|' . $qtext . '|' . $a1 . '|' . $a2 . '|' . $a3 . '|' . $correct_num);

    if (!$opts['force']) {
        $existing = get_posts(
            array(
                'post_type'      => 'quiz',
                'post_status'    => 'any',
                'meta_key'       => '_quiz_import_hash',
                'meta_value'     => $row_hash,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            )
        );
        if (!empty($existing)) {
            fwrite(STDOUT, "Строка {$row_num}: дубликат (hash), ID {$existing[0]} — пропуск. force чтобы создать снова.\n");
            $ids_ordered[] = (int) $existing[0];
            $skipped++;
            continue;
        }
    }

    $title = $num_display . '. ' . $qtext;
    $title = cpp_quiz_import_truncate_title($title);

    $content = '<p>' . str_replace(array("\r\n", "\r", "\n"), '</p><p>', esc_html($qtext)) . '</p>';
    if (strpos($qtext, '<') !== false && $qtext !== wp_strip_all_tags($qtext)) {
        $content = wp_kses_post($qtext);
    }

    if ($opts['dry_run']) {
        fwrite(STDOUT, "[dry-run] строка {$row_num}: {$title}\n");
        $created++;
        continue;
    }

    $post_id = wp_insert_post(
        array(
            'post_type'    => 'quiz',
            'post_status'  => $opts['status'],
            'post_title'   => $title,
            'post_content' => $content,
        ),
        true
    );

    if (is_wp_error($post_id)) {
        fwrite(STDERR, "Строка {$row_num}: ошибка wp_insert_post: " . $post_id->get_error_message() . "\n");
        $errors++;
        continue;
    }

    update_post_meta((int) $post_id, '_quiz_import_hash', $row_hash);
    update_post_meta((int) $post_id, '_quiz_source_csv_line', $row_num);

    update_field('cpp_quiz_multiple', false, (int) $post_id);

    $repeater = array(
        array(
            'answer_text' => $a1,
            'is_correct'  => $correct_num === 1,
        ),
        array(
            'answer_text' => $a2,
            'is_correct'  => $correct_num === 2,
        ),
        array(
            'answer_text' => $a3,
            'is_correct'  => $correct_num === 3,
        ),
    );
    update_field('quiz_answers', $repeater, (int) $post_id);

    fwrite(STDOUT, "Строка {$row_num}: создан пост quiz ID {$post_id}\n");
    $ids_ordered[] = (int) $post_id;
    $created++;
}

fclose($fh);

if ($opts['page_id'] > 0 && !empty($ids_ordered) && !$opts['dry_run']) {
    $page = get_post((int) $opts['page_id']);
    if (!$page || $page->post_type !== 'page') {
        fwrite(STDERR, "Страница page-id={$opts['page_id']} не найдена.\n");
    } else {
        update_field('quiz_items', array_map('intval', $ids_ordered), (int) $opts['page_id']);
        fwrite(STDOUT, "Поле quiz_items обновлено на странице {$opts['page_id']}, записей: " . count($ids_ordered) . "\n");
    }
}

fwrite(
    STDOUT,
    "Готово. Создано/учтено: {$created}, пропущено дубликатов: {$skipped}, ошибок: {$errors}.\n"
);
