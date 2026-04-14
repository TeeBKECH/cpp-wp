# Импорт статей с cpp-globez.ru (`articles`)

## 1. CSV в репозитории

Положите экспорт из Google Таблиц в каталог `scripts/`, например:

`scripts/cpp - Лист1.csv`

Колонки (имена можно на латинице или русском, регистр не важен):

| Назначение | Подходящие имена колонок |
|------------|---------------------------|
| URL страницы новости | `url`, `link`, `ссылка`, `адрес` … или любая ячейка с полным URL на `cpp-globez.ru` и путём `/новости/...` или `/novosti/...` |
| Заголовок записи | `title`, `заголовок`, `h1` |
| Yoast SEO title | `yoast_title`, `seo_title`, `meta_title` |
| Yoast meta description | `yoast_description`, `seo_description`, `meta_description`, `описание` |

Строки без URL новости пропускаются.

## 2. Сборка артефактов (локально или в CI)

Из корня репозитория:

```bash
python3 scripts/import-articles-from-cpp-globez-csv.py "scripts/cpp - Лист1.csv"
```

Проверка без сети (только список строк из CSV):

```bash
python3 scripts/import-articles-from-cpp-globez-csv.py "scripts/cpp - Лист1.csv" --dry-run
```

Без миниатюры:

```bash
python3 scripts/import-articles-from-cpp-globez-csv.py "scripts/cpp - Лист1.csv" --no-featured-image
```

Появится каталог `scripts/import-artifacts/articles-import/` (в `.gitignore`, в git не коммитится) и скрипт `run-import.sh`.

## 3. Запуск на Beget

1. Загрузите на сервер **всю папку** `scripts/import-artifacts/articles-import/` в то же относительное место от корня сайта (рядом с `wp-content`), либо скопируйте её в `public_html/scripts/import-artifacts/articles-import/`.
2. Из корня WordPress:

```bash
cd ~/cpp-wp/public_html
bash scripts/import-artifacts/articles-import/run-import.sh
```

Требуется **WP-CLI** и тип записи **`articles`**. Слаг записи задаёт WordPress (Cyr-To-Lat и т.д.) — старый slug из URL не копируется.

## Контент

Берётся блок **`.uk-margin-medium-top`**: удаляются `script`/`style`/iframe, снимаются инлайн-стили и обработчики `on*`. Если заголовок из CSV совпадает с первым `h1` в блоке, дублирующий `h1` из контента убирается.

Первое изображение в блоке скачивается при генерации и импортируется как **миниатюра** (если не указан `--no-featured-image`).
