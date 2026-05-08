# Импорт банка вопросов (quiz)

**Актуальные файлы и инструкция:**  
**[wp-content/themes/cpp-courses-theme/quiz-import/README.md](../wp-content/themes/cpp-courses-theme/quiz-import/README.md)**

Там лежат готовый `quiz-bank.csv` и `import-quiz-bank.php`.

## Только пересборка CSV из Excel

После изменения файла xlsx в корне репозитория:

```bash
pip install openpyxl
python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx" -o wp-content/themes/cpp-courses-theme/quiz-import/quiz-bank.csv
```

Параметр `-o` по умолчанию в скрипте указывает на папку темы — см. `export-quiz-bank-sheet-to-csv.py`.
