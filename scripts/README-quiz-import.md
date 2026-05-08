# Импорт банка вопросов (Excel → записи `quiz`)

## Что нужно

- На сервере установлены **WordPress**, **WP-CLI**, расширение PHP **mbstring**.
- В админке работает **SCF/ACF** (поля `quiz_answers`, `cpp_quiz_multiple` на типе записи `quiz`).
- Файл импорта: [`import-quiz-bank.php`](import-quiz-bank.php) лежит в каталоге `scripts/` **рядом с корнем WordPress** или укажите полный путь в команде `wp eval-file`.

## Шаг 1: получить CSV из вашей книги Excel

Из корня репозитория (где лежит `Группы_тестов_…xlsx`):

```bash
pip install openpyxl
python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx" -o scripts/quiz-bank.csv
```

Скрипт пропускает первую строку листа «Банк вопросов» (заголовок банка), вторую строку пишет как **заголовки CSV**, дальше — все строки с данными.

Альтернатива: вручную в Excel **Сохранить как** → CSV UTF-8, убедившись, что **первая строка файла — имена колонок** (без лишней строки сверху), иначе поправьте файл или используйте Python-скрипт выше.

## Шаг 2: загрузить CSV на сервер

Скопируйте `quiz-bank.csv` туда, откуда WP-CLI сможет его прочитать (например `~/public_html/scripts/quiz-bank.csv`).

## Шаг 3: запуск импорта (из корня WordPress)

Черновики (рекомендуется первый раз):

```bash
cd /path/to/wordpress
wp eval-file scripts/import-quiz-bank.php -- scripts/quiz-bank.csv --dry-run
```

Без `--dry-run` — реальная запись:

```bash
wp eval-file scripts/import-quiz-bank.php -- scripts/quiz-bank.csv --status=draft
```

Опубликовать сразу:

```bash
wp eval-file scripts/import-quiz-bank.php -- scripts/quiz-bank.csv --status=publish
```

После импорта **сразу записать очередь** на страницу теста (шаблон QUIZ), подставьте ID страницы:

```bash
wp eval-file scripts/import-quiz-bank.php -- scripts/quiz-bank.csv --status=publish --page-id=123
```

Повторный запуск на том же CSV **пропустит** строки с тем же содержимым (по полю мета `_quiz_import_hash`), если не указать `--force`.

## Ожидаемые колонки в CSV

Обязательны по смыслу (имена могут немного отличаться по регистру и пробелам):

| Колонка | Назначение |
|---------|------------|
| № вопроса | Число для заголовка записи (необязательно: тогда номер = порядок строки в CSV) |
| Вопрос | Текст вопроса (`post_content`) |
| Ответ 1, Ответ 2, Ответ 3 | Варианты |
| Правильный вариант | **1**, **2** или **3** |

Заголовок записи: `«Номер». «Вопрос»` (длинный заголовок обрезается, полный текст остаётся в содержимом).

## Если `wp eval-file` не находит файл

Укажите **абсолютный путь** к `import-quiz-bank.php` и к CSV:

```bash
wp eval-file /home/user/public_html/scripts/import-quiz-bank.php -- /home/user/public_html/scripts/quiz-bank.csv
```

## Где лежит логика в теме

- Поля вопроса: [`acf-json/group_cpp_quiz_question.json`](../wp-content/themes/cpp-courses-theme/acf-json/group_cpp_quiz_question.json)
- Выдача на фронте: [`inc/quiz-ajax.php`](../wp-content/themes/cpp-courses-theme/inc/quiz-ajax.php)

Черновик плана (спецификация): [`plan-quiz-bank-import.md`](plan-quiz-bank-import.md)
