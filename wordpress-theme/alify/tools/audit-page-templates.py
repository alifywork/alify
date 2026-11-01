#!/usr/bin/env python3
"""Static check that imported WordPress pages have named page-template counterparts."""
import re
from pathlib import Path

THEME = Path(__file__).resolve().parents[1]
EXPECTED = {
    "home", "about", "services", "industries", "case-studies", "work", "blog",
    "testimonials", "faq", "pricing", "contact", "book-call", "start-project",
    "privacy-policy", "terms-of-service", "thank-you",
}


def main():
    importer = (THEME / "inc" / "importer.php").read_text(encoding="utf-8")
    mapping = re.search(r"\$files=array\((.*?)\);", importer, re.S)
    if not mapping:
        raise SystemExit("Could not find the static page import map in importer.php")
    slugs = set(re.findall(r"=>\s*'([a-z0-9-]+)'", mapping.group(1)))
    if slugs != EXPECTED:
        raise SystemExit(f"Importer page map differs: missing={sorted(EXPECTED-slugs)}, extra={sorted(slugs-EXPECTED)}")
    post_types = (THEME / "inc" / "post-types.php").read_text(encoding="utf-8")
    if "'has_archive' => false" not in post_types:
        raise SystemExit("Collection CPTs must not compete with the named ACF archive pages")
    if "'rewrite' => false" not in post_types:
        raise SystemExit("Service CPT generic rewrites must stay disabled for category-based short URLs")
    errors = []
    names = set()
    for slug in sorted(EXPECTED):
        template = THEME / "templates" / "pages" / f"{slug}.php"
        if not template.is_file():
            errors.append(f"Missing page template: {slug}")
            continue
        source = template.read_text(encoding="utf-8")
        name = re.search(r"Template Name:\s*(.+)", source)
        if not name or "Template Post Type: page" not in source:
            errors.append(f"Invalid WordPress template header: {slug}")
        elif name.group(1).strip() in names:
            errors.append(f"Duplicate template label: {name.group(1).strip()}")
        else:
            names.add(name.group(1).strip())
        if slug == "home":
            if "alify_render_sections('home_sections','option')" not in source:
                errors.append("Home template does not render the imported ACF homepage sections")
        elif "template-parts/pages/content" not in source:
            errors.append(f"Template does not use the ACF page renderer: {slug}")
    if errors:
        print("Page template audit failed:\n- " + "\n- ".join(errors))
        raise SystemExit(1)
    print(f"Page template mapping valid: {len(EXPECTED)} imported pages have unique named templates.")


if __name__ == "__main__":
    main()
