#!/usr/bin/env python3
"""Validate the bundled source catalog data used by the ACF single-content importer."""
import json
from html.parser import HTMLParser
from pathlib import Path

THEME = Path(__file__).resolve().parents[1]
CATALOG = THEME / "import-source" / "catalog-content.json"
EXPECTED = {"services": 8, "industries": 9, "projects": 6, "case_studies": 6, "posts": 6}
REQUIRED = {
    "services": {"code", "title", "desc", "bg", "tags", "stats", "deliverables", "stack", "related"},
    "industries": {"code", "title", "desc", "bg", "tags", "stats", "challenges", "solutions", "related"},
    "projects": {"title", "desc", "bg", "gallery", "chips", "outcomes", "caseId"},
    "case_studies": {"client", "title", "summary", "kpis", "benchmarks", "ch1", "ch2", "ch3", "ch4", "quote", "author", "related"},
    "posts": {"title", "category", "date", "readTime", "image", "lead", "sections", "tags"},
}


class SourceEntryCounter(HTMLParser):
    def __init__(self):
        super().__init__()
        self.counts = {"testimonial-grid-col": 0, "faq-item": 0, "service-grid-col": 0}
        self.service_categories = set()

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        classes = (attrs.get("class") or "").split()
        for name in self.counts:
            if name in classes:
                self.counts[name] += 1
        if "service-grid-col" in classes and attrs.get("data-category"):
            self.service_categories.add(attrs["data-category"])


def main():
    data = json.loads(CATALOG.read_text(encoding="utf-8"))
    errors = []
    for family, count in EXPECTED.items():
        records = data.get(family, {})
        if len(records) != count:
            errors.append(f"{family}: expected {count} records, found {len(records)}")
        for slug, record in records.items():
            missing = REQUIRED[family] - record.keys()
            if missing:
                errors.append(f"{family}/{slug}: missing fields {', '.join(sorted(missing))}")
            if not record.get("title"):
                errors.append(f"{family}/{slug}: empty title")
            if family == "posts" and not record.get("sections"):
                errors.append(f"posts/{slug}: no article sections")
    if errors:
        print("Catalog validation failed:")
        print("\n".join(f"- {error}" for error in errors))
        raise SystemExit(1)
    source = SourceEntryCounter()
    source.feed((THEME / "import-source" / "testimonials.html").read_text(encoding="utf-8"))
    source.feed((THEME / "import-source" / "faq.html").read_text(encoding="utf-8"))
    source.feed((THEME / "import-source" / "services.html").read_text(encoding="utf-8"))
    if source.counts["testimonial-grid-col"] != 12:
        errors.append(f"testimonials: expected 12 source cards, found {source.counts['testimonial-grid-col']}")
    if source.counts["faq-item"] != 20:
        errors.append(f"FAQs: expected 20 source questions, found {source.counts['faq-item']}")
    if source.counts["service-grid-col"] != 8 or source.service_categories != {"engineering", "design", "growth"}:
        errors.append(f"service categories: expected 8 cards across engineering/design/growth; found {source.counts['service-grid-col']} cards and {sorted(source.service_categories)}")
    if errors:
        print("Catalog validation failed:")
        print("\n".join(f"- {error}" for error in errors))
        raise SystemExit(1)
    total = sum(len(data[family]) for family in EXPECTED) + 12 + 20
    print(f"Catalog data valid: {total} source-backed records (35 catalog entries, 12 testimonials, 20 FAQs); service categories: {', '.join(sorted(source.service_categories))}.")


if __name__ == "__main__":
    main()
