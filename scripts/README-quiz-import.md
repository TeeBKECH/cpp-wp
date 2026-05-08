# Импорт банка вопросов (quiz)

Актуальные файлы и инструкция теперь в теме:

- `wp-content/themes/cpp-courses-theme/quiz-import/README.md`
- `wp-content/themes/cpp-courses-theme/quiz-import/import-quiz-bank.php`
- `wp-content/themes/cpp-courses-theme/quiz-import/quiz-bank.csv`

## Только пересборка CSV из Excel

После изменения файла xlsx в корне репозитория:

```bash
pip install openpyxl
python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx"
```

`-o` не обязателен: по умолчанию скрипт пишет в `quiz-import/quiz-bank.csv` внутри темы.
