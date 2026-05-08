#!/usr/bin/env python3
"""
Экспорт листа «Банк вопросов» из Excel в UTF-8 CSV для import-quiz-bank.php.

Использование:
  pip install openpyxl
  python3 scripts/export-quiz-bank-sheet-to-csv.py "Группы_тестов_охранники_обычная_нумерация.xlsx"
  (по умолчанию пишет в wp-content/themes/…/quiz-import/quiz-bank.csv)

Строка 0 в Excel — слугое название банка (пропускается).
Строка 1 — заголовки колонок (первая строка CSV).
Данные — со строки 2 Excel (все строки с данными).
"""

from __future__ import annotations

import argparse
import csv
import sys
from pathlib import Path


def main() -> int:
    p = argparse.ArgumentParser(description="Export «Банк вопросов» sheet to CSV")
    p.add_argument("xlsx", type=Path, help="Path to .xlsx file")
    p.add_argument(
        "-o",
        "--output",
        type=Path,
        default=Path("wp-content/themes/cpp-courses-theme/quiz-import/quiz-bank.csv"),
        help="Output CSV path (UTF-8); default: theme quiz-import/quiz-bank.csv",
    )
    p.add_argument(
        "--sheet",
        default="Банк вопросов",
        help="Sheet name (default: Банк вопросов)",
    )
    args = p.parse_args()

    try:
        import openpyxl
    except ImportError:
        print("Install: pip install openpyxl", file=sys.stderr)
        return 1

    if not args.xlsx.is_file():
        print(f"File not found: {args.xlsx}", file=sys.stderr)
        return 1

    wb = openpyxl.load_workbook(args.xlsx, read_only=True, data_only=True)
    if args.sheet not in wb.sheetnames:
        print(f"Sheet not found: {args.sheet!r}. Available: {wb.sheetnames}", file=sys.stderr)
        wb.close()
        return 1

    ws = wb[args.sheet]
    rows_iter = ws.iter_rows(values_only=True)

    # Row 0: titre banque — skip
    first = next(rows_iter, None)
    if first is None:
        print("Empty sheet", file=sys.stderr)
        wb.close()
        return 1

    header = next(rows_iter, None)
    if header is None:
        print("No header row", file=sys.stderr)
        wb.close()
        return 1

    args.output.parent.mkdir(parents=True, exist_ok=True)
    n = 0
    with args.output.open("w", encoding="utf-8-sig", newline="") as fp:
        w = csv.writer(fp, lineterminator="\n")
        w.writerow([("" if c is None else str(c)).strip() for c in header])
        for row in rows_iter:
            if row is None or all(
                c is None or (isinstance(c, str) and not c.strip()) for c in row
            ):
                continue
            w.writerow([("" if c is None else c) for c in row])
            n += 1

    wb.close()
    print(f"Wrote {args.output} ({n} data rows, UTF-8 BOM for Excel).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
