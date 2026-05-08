# Импорт банка вопросов (quiz)

## Что лежит здесь

| Файл | Назначение |
|------|------------|
| `quiz-bank.csv` | Готовый выгруз из Excel (лист «Банк вопросов»), UTF-8. Уезжает на сервер вместе с темой (rsync/deploy). |
| `import-quiz-bank.php` | Скрипт импорта через **WP-CLI** — на хостинге **не нужны** Python и openpyxl. |

## Зачем CSV уже в репозитории

Чтобы **вам и на сервере** не ставить Python и библиотеки только ради одной конвертации xlsx → csv. Импорт на проде — это `wp eval-file` и PHP (они уже есть для WordPress).

Excel в корне репозитория меняется редко: при обновлении банка **один раз** можно перегенерировать CSV у себя или в CI (см. `scripts/export-quiz-bank-sheet-to-csv.py` в корне репо) и закоммитить новый `quiz-bank.csv` сюда.

## Запуск на сервере

Из **корня WordPress** (где `wp-config.php`):

```bash
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import/import-quiz-bank.php -- --dry-run
```

CSV подставится автоматически из этой папки. Черновики:

```bash
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import/import-quiz-bank.php --
```

С публикацией и привязкой к странице теста (подставьте ID страницы с шаблоном QUIZ):

```bash
wp eval-file wp-content/themes/cpp-courses-theme/quiz-import/import-quiz-bank.php -- --status=publish --page-id=123
```

Опции: `--force`, `--csv=/другой/путь.csv` — см. шапку `import-quiz-bank.php`.

Требования: WP-CLI, PHP **mbstring**, активная тема и плагин SCF/ACF.

## Пересборка CSV после правок Excel

В корне репозитория (не на сервере):

```bash
pip install openpyxl
python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx" -o wp-content/themes/cpp-courses-theme/quiz-import/quiz-bank.csv
```

Затем коммит обновлённого `quiz-bank.csv`.
