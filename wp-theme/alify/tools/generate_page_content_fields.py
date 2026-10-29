#!/usr/bin/env python3
"""Regenerate fully prefilled dashboard copy fields and stable markup markers.

Run from the repository root:
    python3 wp-theme/alify/tools/generate_page_content_fields.py

Includes headings, body copy, quotes, labels, card text, links/buttons, list items,
form labels/errors, accessible labels and textual placeholders/alt text. The generated
editor uses paging, so the PHP max_input_vars setting does not limit page content.
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
TEXT_TAGS = {
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote',
    'span', 'a', 'button', 'li', 'label', 'strong', 'em', 'small',
    'summary', 'figcaption', 'th', 'td', 'dt', 'dd', 'time', 'option', 'legend',
}
TEXT_ATTRIBUTES = ('placeholder', 'aria-label', 'alt', 'title')
BLOCK_COPY_TAGS = {'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote'}


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
        if self.stack:
            self.stack[-1]['has_child'] = True
            if tag in TEXT_TAGS:
                for ancestor in self.stack:
                    if ancestor['target']:
                        ancestor['has_target_child'] = True
        attr_map = dict(attrs)
        self.stack.append({
            'tag': tag,
            'start': start,
            'open_end': start + len(raw),
            'target': tag in TEXT_TAGS,
            'root': not any(node['target'] for node in self.stack),
            'has_child': False,
            'has_target_child': False,
            'inside_block_target': any(parent['tag'] in BLOCK_COPY_TAGS for parent in self.stack),
            'text': [],
            'attrs': attr_map,
        })
        for attr in TEXT_ATTRIBUTES:
            value = attr_map.get(attr, '')
            if isinstance(value, str):
                value = re.sub(r'\s+', ' ', html.unescape(value)).strip()
                if value and len(value) > 1:
                    self.nodes.append({
                        'tag': tag,
                        'start': start,
                        'open_end': start + len(raw),
                        'default': value,
                        'attribute': attr,
                    })

    def handle_startendtag(self, tag, attrs):
        return

    def handle_data(self, data):
        for node in self.stack:
            if node['target']:
                node['text'].append(data)
            node.setdefault('all_text', []).append(data)

    def handle_endtag(self, tag):
        index = next((i for i in range(len(self.stack) - 1, -1, -1)
                      if self.stack[i]['tag'] == tag), None)
        if index is None:
            return
        node = self.stack[index]
        self.stack = self.stack[:index]
        is_leaf_text = node['target'] and not node['inside_block_target'] and (not node['has_target_child'] or tag in BLOCK_COPY_TAGS)
        is_leaf_div = tag == 'div' and not node['has_child'] and not any(parent['target'] for parent in self.stack)
        if not (is_leaf_text or is_leaf_div):
            return
        text_parts = node.get('all_text', []) if tag in BLOCK_COPY_TAGS else node.get('text', [])
        text = re.sub(r'\s+', ' ', html.unescape(' '.join(text_parts))).strip()
        # Leaf divs are not target nodes, so use their directly collected text.
        if is_leaf_div:
            text = re.sub(r'\s+', ' ', html.unescape(' '.join(node.get('all_text', [])))).strip()
        if not text or (len(text) < 2 and not text.isdigit()):
            return
        self.nodes.append({
            'tag': tag,
            'start': node['start'],
            'open_end': node['open_end'],
            'default': text,
            'attribute': '',
        })


def main():
    catalog = {}
    legacy_map = json.loads((THEME / 'tools' / 'page-copy-legacy-map.json').read_text(encoding='utf-8'))
    for slug, (title, template) in PAGES.items():
        partial = THEME / 'template-parts' / 'pages' / (slug + '.php')
        if not partial.is_file():
            continue
        source = partial.read_text(encoding='utf-8')
        source = re.sub(r'\sdata-alify-copy(?:-[a-z-]+)?="[^"]*"', '', source)
        parser = CopyParser(source)
        parser.feed(source)
        nodes = sorted(parser.nodes, key=lambda node: (node['start'], node.get('attribute', '')))
        legacy_queues = {}
        for legacy_field in legacy_map.get(slug, []):
            key = (legacy_field['tag'], legacy_field['default'])
            legacy_queues.setdefault(key, []).append(legacy_field['name'])
        fields, insertions = [], []
        for index, node in enumerate(nodes, 1):
            name = 'copy_' + slug.replace('-', '_') + '_%04d' % index
            label = '%s %04d — %s' % (
                (node.get('attribute') or node['tag']).upper(), index, node['default'][:72]
            )
            if node.get('attribute'):
                marker = ' data-alify-copy-%s="%s"' % (node['attribute'], name)
                kind = 'attribute'
            else:
                marker = ' data-alify-copy="%s"' % name
                kind = 'text'
            insertions.append((node['open_end'] - 1, marker))
            legacy_name = ''
            legacy_key = (node['tag'], node['default'])
            if not node.get('attribute') and legacy_queues.get(legacy_key):
                legacy_name = legacy_queues[legacy_key].pop(0)
            field_data = {
                'name': name,
                'label': label,
                'default': node['default'],
                'tag': node['tag'],
                'attribute': node.get('attribute', ''),
                'kind': kind,
            }
            if legacy_name:
                field_data['legacy_name'] = legacy_name
            fields.append(field_data)
        for offset, marker in reversed(insertions):
            source = source[:offset] + marker + source[offset:]
        partial.write_text(source, encoding='utf-8')
        catalog[slug] = {'title': title, 'template': template, 'fields': fields}
        print('%-20s %4d fields' % (slug, len(fields)))

    # The live Posts Index is rendered by home.php rather than template-parts/pages/blog.php.
    blog_fields = catalog.get('blog', {}).get('fields', [])
    blog_h1 = next((f['name'] for f in blog_fields if f['tag'] == 'h1' and f['kind'] == 'text'), '')
    blog_desc = next((f['name'] for f in blog_fields if f['tag'] == 'p' and f['default'].startswith('Actionable guides,')), '')
    dynamic_blog = THEME / 'home.php'
    if dynamic_blog.is_file() and blog_h1 and blog_desc:
        text = dynamic_blog.read_text(encoding='utf-8')
        text = re.sub(r'(<h1\s+class="inner-hero-title")[^>]*(>)', r'\1 data-alify-copy="' + blog_h1 + r'"\2', text, count=1)
        text = re.sub(r'(<p\s+class="inner-hero-desc")[^>]*(>)', r'\1 data-alify-copy="' + blog_desc + r'"\2', text, count=1)
        dynamic_blog.write_text(text, encoding='utf-8')

    json_text = json.dumps(catalog, ensure_ascii=False, separators=(',', ':'))
    php = (
        "<?php\n"
        "/** Generated per-template, pre-filled copy fields. Rebuild with tools/generate_page_content_fields.py. */\n"
        "return json_decode( <<<'ALIFY_PAGE_COPY_JSON'\n"
        + json_text + "\nALIFY_PAGE_COPY_JSON\n, true );\n"
    )
    (THEME / 'inc' / 'page-content-catalog.php').write_text(php, encoding='utf-8')
    print('Total: %d pre-filled text/attribute fields across %d templates.' % (
        sum(len(page['fields']) for page in catalog.values()), len(catalog)))


if __name__ == '__main__':
    main()
