# Импорт банка вопросов (quiz)

## Что лежит здесь

| Файл | Назначение |
|------|------------|
| `quiz-bank.csv` | Готовый выгруз из Excel (лист «Банк вопросов»), UTF-8. Уезжает на сервер вместе с темой (rsync/deploy). |
| `import-quiz-bank.php` | Скрипт импорта через WP-CLI. На хостинге не нужны Python и openpyxl. |

## Зачем CSV уже в репозитории

Чтобы не ставить Python на сервер только ради конвертации xlsx в csv. На проде импорт — это `wp eval-file` и PHP, которые уже нужны WordPress.

## Запуск на сервере

Из корня WordPress (где `wp-config.php`):

```bash
QUIZ_IMPORT_DRY_RUN=1 wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php
```

Реальный импорт черновиками:

```bash
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php
```

С публикацией и привязкой к странице теста (подставьте ID страницы шаблона QUIZ):

```bash
QUIZ_IMPORT_STATUS=publish QUIZ_IMPORT_PAGE_ID=123 wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php
```

## Если ваш WP-CLI режет `--dry-run`

Некоторые сборки WP-CLI не пропускают `--dry-run` после `eval-file` и падают с `unknown parameter`.
Используйте env-переменные (рекомендуется) или токены без префикса:

```bash
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php -- dry-run
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import-v2/import-quiz-bank.php -- status=publish page-id=123
```

## Переменные окружения

- `QUIZ_IMPORT_DRY_RUN=1`
- `QUIZ_IMPORT_FORCE=1`
- `QUIZ_IMPORT_STATUS=draft|publish`
- `QUIZ_IMPORT_PAGE_ID=123`
- `QUIZ_IMPORT_CSV=/abs/path.csv` (если CSV не рядом со скриптом)

## Пересборка CSV после правок Excel

В корне репозитория (не на сервере):

```bash
pip install openpyxl
python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx"
```

По умолчанию скрипт пишет прямо сюда: `quiz-import-v2/quiz-bank.csv`.
