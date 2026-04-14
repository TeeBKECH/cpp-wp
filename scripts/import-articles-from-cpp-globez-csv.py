#!/usr/bin/env python3
"""
Import old cpp-globez.ru news pages into WordPress CPT `articles` via WP-CLI.

Input: CSV (UTF-8 or Excel UTF-8 BOM). Required column with page URL (any of:
url, link, ссылка, адрес). Optional: title, yoast_title, yoast_description,
seo_title, seo_description, description (meta description for Yoast).

Content: **only the children** of the first element matching CONTENT_SELECTOR
(default `.uk-margin-medium-top`) — the wrapper itself is **not** included in
`post_content`, so the editor sees normal block HTML. Scripts/styles removed;
inline `style` / `on*` stripped; all `<span>` unwrapped; `<img>` / `<picture>`
removed from body (broken old-site URLs). First image URL is still used for
the featured image unless --no-featured-image.

Output: scripts/import-artifacts/articles-import/run-import.sh and
scripts/import-artifacts/articles-import/posts/<slug>/...

Usage:
  python3 scripts/import-articles-from-cpp-globez-csv.py path/to.csv
  python3 scripts/import-articles-from-cpp-globez-csv.py path/to.csv --dry-run
  python3 scripts/import-articles-from-cpp-globez-csv.py path/to.csv --no-featured-image

Run on server from WordPress root (with wp in PATH):
  bash scripts/import-artifacts/articles-import/run-import.sh
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import re
import sys
import unicodedata
from pathlib import Path
from urllib.parse import unquote, urljoin, urlparse
from urllib.request import Request, urlopen

from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parent.parent
OUT_BASE = ROOT / "scripts" / "import-artifacts" / "articles-import"
POSTS_DIR = OUT_BASE / "posts"

UA = "CppGlobezArticlesImporter/1.2 (+repository import)"
CONTENT_SELECTOR = ".uk-margin-medium-top"


def fetch(url: str) -> str:
    req = Request(url, headers={"User-Agent": UA})
    with urlopen(req, timeout=90) as resp:
        return resp.read().decode("utf-8", errors="replace")


def normalize_header(s: str) -> str:
    s = (s or "").strip().lower()
    if s.startswith("\ufeff"):
        s = s.lstrip("\ufeff").lower()
    return s


def row_url_title_meta(row: dict[str, str]) -> tuple[str, str, str, str]:
    """Return url, title, yoast_title, yoast_desc from a normalized-key row."""
    keymap = {normalize_header(k): v.strip() if isinstance(v, str) else str(v).strip() for k, v in row.items()}

    url = ""
    for k in ("адрес", "url", "link", "ссылка", "page", "страница"):
        if k in keymap and keymap[k]:
            url = keymap[k]
            break
    if not url:
        for _k, v in keymap.items():
            if "http" in v and "cpp-globez" in v:
                url = v
                break

    # Post title: Screaming Frog export uses H1-1; fallback Title 1 / title
    title = ""
    for k in ("h1-1", "h1", "заголовок", "title", "name"):
        if k in keymap and keymap[k]:
            title = keymap[k]
            break

    # Yoast SEO title / description (SF: "Title 1", "Description 1")
    yo_t = ""
    for k in ("title 1", "yoast_title", "seo_title", "meta_title", "title_seo", "seo title"):
        if k in keymap and keymap[k]:
            yo_t = keymap[k]
            break

    yo_d = ""
    for k in (
        "description 1",
        "yoast_description",
        "yoast_desc",
        "seo_description",
        "meta_description",
        "meta description",
        "описание",
    ):
        if k in keymap and keymap[k]:
            yo_d = keymap[k]
            break

    return url, title, yo_t, yo_d


def is_news_url(url: str) -> bool:
    if not url.strip():
        return False
    low = url.strip().lower()
    if "новости" in url:
        return True
    if "novosti" in low:
        return True
    # UTF-8 "новости" percent-encoded (lowercase hex), common on old site
    if "%d0%bd%d0%be%d0%b2%d0%be%d1%81%d1%82%d0%b8" in low:
        return True
    return False


def sanitize_fragment(root) -> None:
    for bad in root.find_all(["script", "style", "iframe", "link", "meta", "noscript"]):
        bad.decompose()

    for tag in root.find_all(True):
        for name in list(tag.attrs):
            ln = name.lower()
            if ln == "style" or ln.startswith("on"):
                del tag.attrs[name]
        if tag.name == "a":
            href = (tag.get("href") or "").strip().lower()
            if href.startswith("javascript:"):
                tag.unwrap()


def unwrap_all_spans(root) -> None:
    """Remove span wrappers (keep children); repeat until none left."""
    while True:
        spans = root.find_all("span")
        if not spans:
            break
        for span in spans:
            span.unwrap()


def remove_images_from_body(root) -> None:
    """Drop img/picture so broken absolute URLs do not appear in post content."""
    for pic in root.find_all("picture"):
        pic.decompose()
    for img in root.find_all("img"):
        img.decompose()


def remove_leading_h1_if_matches(root, title: str) -> None:
    if not title:
        return
    h1 = root.find("h1")
    if not h1:
        return
    if h1.get_text(strip=True).casefold() == title.strip().casefold():
        h1.decompose()


def first_image_url(root, page_url: str) -> str:
    for img in root.find_all("img"):
        src = (img.get("src") or "").strip()
        if not src:
            continue
        if src.startswith("//"):
            src = "https:" + src
        full = urljoin(page_url, src)
        if full.startswith("http"):
            return full
    return ""


def slug_from_url(url: str) -> str:
    path = urlparse(url).path.rstrip("/")
    raw = path.split("/")[-1] if path else "post"
    part = unquote(raw)
    safe = re.sub(r"[^\w\-]+", "-", part, flags=re.UNICODE)
    safe = re.sub(r"-{2,}", "-", safe).strip("-") or "post"
    return safe[:120]


def image_extension(data: bytes, img_url: str) -> str:
    if data[:3] == b"\xff\xd8\xff":
        return ".jpg"
    if data[:8] == b"\x89PNG\r\n\x1a\n":
        return ".png"
    if data[:4] == b"RIFF" and data[8:12] == b"WEBP":
        return ".webp"
    if data[:6] in (b"GIF87a", b"GIF89a"):
        return ".gif"
    low = img_url.lower().split("?", 1)[0]
    for ext in (".jpg", ".jpeg", ".png", ".webp", ".gif"):
        if low.endswith(ext):
            return ".jpg" if ext == ".jpeg" else ext
    return ".img"


def read_csv_rows(path: Path) -> list[dict[str, str]]:
    text = path.read_text(encoding="utf-8-sig")
    reader = csv.DictReader(text.splitlines())
    rows: list[dict[str, str]] = []
    for raw in reader:
        row = {k: (v or "").strip() if isinstance(v, str) else "" for k, v in raw.items() if k}
        if any(v for v in row.values()):
            rows.append(row)  # type: ignore[arg-type]
    return rows


def excerpt_for_database(raw: str, max_len: int = 2000) -> str:
    """
    Safe excerpt for wp_posts.post_excerpt: valid UTF-8, no NUL, length cap.
    Avoids MySQL / WP_DB errors from mojibake or shell-corrupted strings.
    """
    if not raw:
        return ""
    t = unicodedata.normalize("NFKC", raw)
    t = re.sub(r"[\x00-\x08\x0b\x0c\x0e-\x1f]", "", t)
    t = re.sub(r"\s+", " ", t).strip()
    b = t.encode("utf-8", errors="replace")
    t = b.decode("utf-8", errors="replace")
    if len(t) > max_len:
        t = t[: max_len - 1].rstrip() + "…"
    return t


APPLY_EXCERPT_PHP = r"""<?php
/**
 * Set post_excerpt from excerpt.txt next to this file (no shell quoting issues).
 * Run: CPP_IMPORT_POST_ID=123 wp eval-file apply-excerpt.php
 *
 * @package CppGlobezImport
 */
$post_id = absint( getenv( 'CPP_IMPORT_POST_ID' ) );
if ( ! $post_id ) {
	fwrite( STDERR, "apply-excerpt.php: set CPP_IMPORT_POST_ID to post ID\n" );
	exit( 1 );
}
$file = __DIR__ . '/excerpt.txt';
if ( ! is_readable( $file ) ) {
	exit( 0 );
}
$excerpt = (string) file_get_contents( $file );
wp_update_post(
	array(
		'ID'           => $post_id,
		'post_excerpt' => $excerpt,
	)
);
"""


def unique_post_dir(base_slug: str) -> Path:
    d = POSTS_DIR / base_slug
    if not d.is_dir():
        return d
    h = hashlib.sha256(base_slug.encode()).hexdigest()[:8]
    return POSTS_DIR / f"{base_slug}-{h}"


def main() -> int:
    ap = argparse.ArgumentParser(description="Build WP-CLI import for articles from CSV + cpp-globez.")
    ap.add_argument("csv_path", type=Path, nargs="?", default=None, help="Path to CSV (Google export)")
    ap.add_argument("--dry-run", action="store_true", help="Only print planned rows, no fetch, no files")
    ap.add_argument("--no-featured-image", action="store_true", help="Do not download / attach first image")
    ap.add_argument("--limit", type=int, default=0, help="Process at most N URLs (0 = all)")
    args = ap.parse_args()

    csv_path = args.csv_path
    if csv_path is None:
        candidates = sorted(ROOT.glob("scripts/*.csv")) + sorted(ROOT.glob("scripts/**/*.csv"))
        candidates = [p for p in candidates if "import-artifacts" not in str(p)]
        if not candidates:
            print("Pass CSV path or place a .csv file under scripts/", file=sys.stderr)
            return 1
        csv_path = candidates[0]
        print(f"Using CSV: {csv_path}", file=sys.stderr)

    if not csv_path.is_file():
        print(f"File not found: {csv_path}", file=sys.stderr)
        return 1

    rows = read_csv_rows(csv_path)
    planned: list[tuple[str, str, str, str, str]] = []

    for row in rows:
        url, title_csv, yo_t, yo_d = row_url_title_meta(row)
        if not url or not is_news_url(url):
            continue
        planned.append((url, title_csv, yo_t, yo_d, slug_from_url(url)))

    if args.limit and args.limit > 0:
        planned = planned[: args.limit]

    if not planned:
        print("No rows with news URLs found. Check CSV columns (url / link / ссылка).", file=sys.stderr)
        return 1

    if args.dry_run:
        for url, title_csv, yo_t, yo_d, slug in planned:
            print(f"{slug}\t{url}\t{title_csv!r}\t{yo_t!r}\t{yo_d!r}")
        return 0

    POSTS_DIR.mkdir(parents=True, exist_ok=True)
    sh_lines: list[str] = [
        "#!/usr/bin/env bash",
        "# Generated by import-articles-from-cpp-globez-csv.py",
        "set -euo pipefail",
        'SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"',
        "",
    ]

    created = 0
    for url, title_csv, yo_t, yo_d, slug in planned:
        post_dir = unique_post_dir(slug)
        post_dir.mkdir(parents=True, exist_ok=True)

        print(f"Fetch {url} …", file=sys.stderr)
        try:
            html = fetch(url)
        except Exception as exc:
            print(f"  SKIP fetch error: {exc}", file=sys.stderr)
            continue

        soup = BeautifulSoup(html, "html.parser")
        container = soup.select_one(CONTENT_SELECTOR)
        if not container:
            print(f"  SKIP no {CONTENT_SELECTOR!r}", file=sys.stderr)
            continue

        work = BeautifulSoup(str(container), "html.parser").select_one(CONTENT_SELECTOR)
        if not work:
            work = container

        h1 = soup.find("h1")
        title_page = h1.get_text(strip=True) if h1 else ""
        title = (title_csv or title_page or slug).strip()
        if not title:
            title = slug

        remove_leading_h1_if_matches(work, title)

        # Featured image from first <img> before we strip images from body.
        img_url = first_image_url(work, url)

        sanitize_fragment(work)
        unwrap_all_spans(work)
        remove_images_from_body(work)

        # Inner HTML only: no .uk-margin-medium-top wrapper in post_content.
        body_html = work.decode_contents()

        (post_dir / "title.txt").write_text(title, encoding="utf-8")
        (post_dir / "body.html").write_text(body_html, encoding="utf-8")
        (post_dir / "yoast_title.txt").write_text(yo_t or "", encoding="utf-8")
        (post_dir / "yoast_desc.txt").write_text(yo_d or "", encoding="utf-8")

        excerpt = ""
        for p in work.find_all("p"):
            t = p.get_text(strip=True)
            if len(t) > 40:
                excerpt = excerpt_for_database(re.sub(r"\s+", " ", t)[:800])
                break
        (post_dir / "excerpt.txt").write_text(excerpt, encoding="utf-8")
        (post_dir / "apply-excerpt.php").write_text(APPLY_EXCERPT_PHP, encoding="utf-8")

        featured_name = ""
        if not args.no_featured_image and img_url:
            try:
                req = Request(img_url, headers={"User-Agent": UA})
                with urlopen(req, timeout=60) as resp:
                    data = resp.read()
                if len(data) > 200:
                    ext = image_extension(data, img_url)
                    featured_name = f"featured{ext}"
                    (post_dir / featured_name).write_bytes(data)
            except Exception as exc:
                print(f"  (no featured image: {exc})", file=sys.stderr)

        rel = post_dir.relative_to(OUT_BASE)
        sh_lines.append(f'echo "---- {slug}"')
        sh_lines.append(f'POST_ID=$(wp post create "$SCRIPT_DIR/{rel}/body.html" \\')
        sh_lines.append('  --post_type=articles --post_status=publish \\')
        sh_lines.append(f'  --post_title="$(cat "$SCRIPT_DIR/{rel}/title.txt")" \\')
        sh_lines.append("  --porcelain)")
        sh_lines.append('if [[ -z "${POST_ID:-}" ]]; then echo "wp post create failed"; exit 1; fi')
        sh_lines.append(f'CPP_IMPORT_POST_ID="$POST_ID" wp eval-file "$SCRIPT_DIR/{rel}/apply-excerpt.php"')

        if yo_t:
            sh_lines.append(
                f'wp post meta update "$POST_ID" _yoast_wpseo_title "$(cat "$SCRIPT_DIR/{rel}/yoast_title.txt")"'
            )
        if yo_d:
            sh_lines.append(
                f'wp post meta update "$POST_ID" _yoast_wpseo_metadesc "$(cat "$SCRIPT_DIR/{rel}/yoast_desc.txt")"'
            )

        if featured_name and (post_dir / featured_name).is_file():
            sh_lines.append(
                f'wp media import "$SCRIPT_DIR/{rel}/{featured_name}" '
                f'--post_id="$POST_ID" --title="Featured" --featured_image'
            )

        sh_lines.append("")
        created += 1

    sh_path = OUT_BASE / "run-import.sh"
    sh_path.write_text("\n".join(sh_lines) + "\n", encoding="utf-8")
    sh_path.chmod(0o755)
    print(f"Wrote {sh_path} ({created} posts in script).", file=sys.stderr)
    print("Upload scripts/import-artifacts/articles-import/ to the server next to WP, then:", file=sys.stderr)
    print("  cd ~/cpp-wp/public_html && bash scripts/import-artifacts/articles-import/run-import.sh", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
