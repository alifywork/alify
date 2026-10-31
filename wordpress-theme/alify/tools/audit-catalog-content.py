#!/usr/bin/env python3
"""Validate the bundled source catalog data used by the ACF single-content importer."""
import json
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
    total = sum(len(data[family]) for family in EXPECTED)
    print(f"Catalog data valid: {total} records across {len(EXPECTED)} content groups.")


if __name__ == "__main__":
    main()
