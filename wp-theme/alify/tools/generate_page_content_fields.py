#!/usr/bin/env python3
"""Regenerate page-specific, pre-filled ALIFY content fields from the HTML templates.

Run from the repository root:
    python3 wp-theme/alify/tools/generate_page_content_fields.py

The generated data-alify-copy markers are intentionally attached only to the top-level
H1–H6, paragraph and blockquote copy blocks. Dynamic CPT cards remain field-driven by
 their own schemas.
"""
from html.parser import HTMLParser
from pathlib import Path
import html
import json
import re

THEME = Path(__file__).resolve().parent.parent
PAGES = {
    'home': ('Home', 'page-templates/template-home.php'),
    'about': ('About Us', 'page-templates/template-about.php'),
    'services': ('Services', 'page-templates/template-services.php'),
    'service-single': ('Service Detail', 'page-templates/template-service-single.php'),
    'industries': ('Industries', 'page-templates/template-industries.php'),
    'industry-single': ('Industry Detail', 'page-templates/template-industry-single.php'),
    'work': ('Work', 'page-templates/template-work.php'),
    'work-single': ('Project Detail', 'page-templates/template-work-single.php'),
    'case-studies': ('Case Studies', 'page-templates/template-case-studies.php'),
    'case-study-single': ('Case Study Detail', 'page-templates/template-case-study-single.php'),
    'pricing': ('Pricing', 'page-templates/template-pricing.php'),
    'testimonials': ('Testimonials', 'page-templates/template-testimonials.php'),
    'faq': ('FAQs', 'page-templates/template-faq.php'),
    'blog': ('Blog', 'page-templates/template-blog.php'),
    'blog-single': ('Article Detail', 'page-templates/template-blog-single.php'),
    'contact': ('Contact Us', 'page-templates/template-contact.php'),
    'start-project': ('Start a Project', 'page-templates/template-start-project.php'),
    'book-call': ('Book a Strategy Call', 'page-templates/template-book-call.php'),
    'thank-you': ('Thank You', 'page-templates/template-thank-you.php'),
    'privacy-policy': ('Privacy Policy', 'page-templates/template-privacy-policy.php'),
    'terms-of-service': ('Terms of Service', 'page-templates/template-terms-of-service.php'),
    '404': ('404 Error', ''),
}
TARGETS = {'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote'}


class CopyParser(HTMLParser):
    def __init__(self, source):
        super().__init__(convert_charrefs=True)
        self.source = source
        self.line_offsets = [0]
        self.line_offsets.extend(match.end() for match in re.finditer('\n', source))
        self.stack = []
        self.nodes = []

    def handle_starttag(self, tag, attrs):
        line, column = self.getpos()
        start = self.line_offsets[line - 1] + column
        raw = self.get_starttag_text()
        self.stack.append({
            'tag': tag,
            'start': start,
            'inner': start + len(raw),
            'target': tag in TARGETS,
            'root': not any(node['target'] for node in self.stack),
            'text': [],
        })

    def handle_startendtag(self, tag, attrs):
        return

    def handle_data(self, data):
        for node in self.stack:
            if node['target'] and node['root']:
                node['text'].append(data)

    def handle_endtag(self, tag):
        index = next((i for i in range(len(self.stack) - 1, -1, -1)
                      if self.stack[i]['tag'] == tag), None)
        if index is None:
            return
        closed = self.stack[index:]
        self.stack = self.stack[:index]
        node = closed[0]
        if not node['target'] or not node['root']:
            return
        text = re.sub(r'\s+', ' ', html.unescape(' '.join(node['text']))).strip()
        if not text or len(text) < 2:
            return
        close = re.search(r'</' + re.escape(tag) + r'\s*>', self.source[node['inner']:], re.I)
        if close:
            self.nodes.append({
                'tag': tag,
                'start': node['start'],
                'default': text,
            })


def main():
    catalog = {}
    for slug, (title, template) in PAGES.items():
        partial = THEME / 'template-parts' / 'pages' / (slug + '.php')
        if not partial.is_file():
            continue
        source = partial.read_text(encoding='utf-8')
        source = re.sub(r'\sdata-alify-copy="[^"]*"', '', source)
        parser = CopyParser(source)
        parser.feed(source)
        nodes = sorted(parser.nodes, key=lambda node: node['start'])
        fields, insertions = [], []
        for index, node in enumerate(nodes, 1):
            name = 'copy_' + slug.replace('-', '_') + '_%03d' % index
            label = '%s %03d — %s' % (node['tag'].upper(), index, node['default'][:76])
            opening_end = source.find('>', node['start'])
            if opening_end < 0:
                raise ValueError('Malformed opening tag in %s at %d' % (partial, node['start']))
            insertions.append((opening_end, ' data-alify-copy="%s"' % name))
            fields.append({
                'name': name,
                'label': label,
                'default': node['default'],
                'tag': node['tag'],
            })
        for offset, marker in reversed(insertions):
            source = source[:offset] + marker + source[offset:]
        partial.write_text(source, encoding='utf-8')
        catalog[slug] = {'title': title, 'template': template, 'fields': fields}
        print('%-20s %3d fields' % (slug, len(fields)))

    json_text = json.dumps(catalog, ensure_ascii=False, separators=(',', ':'))
    php = (
        "<?php\n"
        "/** Generated per-template page copy field catalog; defaults mirror marked template text. */\n"
        "return json_decode( <<<'ALIFY_PAGE_COPY_JSON'\n"
        + json_text + "\nALIFY_PAGE_COPY_JSON\n, true );\n"
    )
    (THEME / 'inc' / 'page-content-catalog.php').write_text(php, encoding='utf-8')
    print('Total: %d pre-filled copy fields across %d pages.' % (
        sum(len(page['fields']) for page in catalog.values()), len(catalog)))


if __name__ == '__main__':
    main()
