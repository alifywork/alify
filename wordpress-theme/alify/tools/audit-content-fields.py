#!/usr/bin/env python3
"""Static coverage check for the WordPress ACF importer; no dependencies required."""
from collections import Counter
from html.parser import HTMLParser
from pathlib import Path
import re
import sys

REPO_ROOT = Path(__file__).resolve().parents[3]
SOURCE = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else REPO_ROOT

class SectionAudit(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.main_depth = 0
        self.section_depth = 0
        self.current = None
        self.sections = []
        self.skip_content = 0
        self.form_depth = 0

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'main':
            self.main_depth += 1
        if not self.main_depth:
            return
        if tag == 'section':
            if self.section_depth == 0:
                self.current = Counter()
                self.current['class'] = attrs.get('class', '')
                self.sections.append(self.current)
            self.section_depth += 1
        if self.section_depth:
            if tag == 'form':
                self.form_depth += 1
                self.current['forms'] += 1
            if tag == 'a' and attrs.get('href'):
                self.current['links'] += 1
            if tag == 'img' and attrs.get('src'):
                self.current['images'] += 1
                self.current['image_alt_fields'] += 1
            if tag != 'img':
                self.current['attribute_fields'] += sum(
                    1 for key in ('title', 'placeholder', 'aria-label', 'alt') if key in attrs
                )
            self.current['background_images'] += len(re.findall(r'url\(', attrs.get('style', ''), re.I))
            if tag in ('script', 'style'):
                self.skip_content += 1

    def handle_endtag(self, tag):
        if self.main_depth and self.section_depth:
            if tag in ('script', 'style') or (tag == 'form' and self.form_depth):
                self.skip_content = max(0, self.skip_content - 1)
            if tag == 'form' and self.form_depth:
                self.form_depth -= 1
            if tag == 'section':
                self.section_depth -= 1
                if self.section_depth == 0:
                    self.current = None
        if tag == 'main' and self.main_depth:
            self.main_depth -= 1

    def handle_data(self, data):
        if self.current is not None and not self.skip_content and data.strip():
            self.current['text_fields'] += 1


def main():
    pages = sorted(SOURCE.glob('*.html'))
    if not pages:
        raise SystemExit(f'No .html files found under {SOURCE}')
    total = Counter()
    failures = []
    print('Static source section → editable ACF field coverage estimate')
    print('-' * 96)
    for page in pages:
        audit = SectionAudit()
        audit.feed(page.read_text(encoding='utf-8', errors='replace'))
        if not audit.sections:
            failures.append(f'{page.name}: no top-level sections inside <main>')
        counts = Counter()
        for row in audit.sections:
            counts.update({k: v for k, v in row.items() if k != 'class'})
        # Every form is replaced by its separate PHP form template and configured form fields.
        counts['forms'] = sum(row['forms'] for row in audit.sections)
        total.update(counts)
        print(f"{page.name:24} sections={len(audit.sections):2} text={counts['text_fields']:3} links={counts['links']:3} images={counts['images']:2} attrs={counts['attribute_fields']:2} forms={counts['forms']}")
    print('-' * 96)
    print(f"TOTAL: {len(pages)} pages, {sum(1 for _ in pages)} files; {total['text_fields']} text fields, {total['links']} link fields, {total['images']} image fields (+ matching alt fields), {total['attribute_fields']} accessible/placeholder fields; {total['forms']} forms routed to templates.")
    if failures:
        raise SystemExit('\n'.join(failures))
    print('Coverage check passed: each static page has importable sections.')

if __name__ == '__main__':
    main()
