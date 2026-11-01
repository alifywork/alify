#!/usr/bin/env python3
"""Check that theme-enqueued styles and scripts exist and local source assets are bundled."""
import re
from pathlib import Path

THEME = Path(__file__).resolve().parents[1]
SETUP = (THEME / "inc" / "theme-setup.php").read_text(encoding="utf-8")


def quoted_values(block):
    return re.findall(r"'([^']+)'", block)


def check_paths(paths, label, errors):
    for path in paths:
        if not (THEME / path).is_file():
            errors.append(f"Missing enqueued {label}: {path}")


def main():
    errors = []
    style_match = re.search(r"\$styles\s*=\s*array\((.*?)\);", SETUP, re.S)
    script_match = re.search(r"\$scripts\s*=\s*array\((.*?)\);", SETUP, re.S)
    if not style_match or not script_match:
        raise SystemExit("Could not read the theme's enqueued asset lists.")
    styles = ["assets/css/" + item for item in quoted_values(style_match.group(1))]
    styles += ["assets/css/bootstrap-css/bootstrap.min.css", "assets/css/alify-wordpress.css"]
    scripts = ["assets/js/" + item for item in quoted_values(script_match.group(1))]
    scripts += [
        "assets/js/bootstrap-js/bootstrap.bundle.min.js",
        "assets/gsap/gsap.min.js",
        "assets/gsap/ScrollTrigger.min.js",
        "assets/js/reusable-component-js/single-pages.js",
    ]
    check_paths(styles, "stylesheet", errors)
    check_paths(scripts, "script", errors)

    # Validate explicit assets/ references in source HTML/JS/CSS and CSS url() references.
    source_files = list((THEME / "import-source").glob("*.html"))
    source_files += list((THEME / "assets").rglob("*.css"))
    source_files += list((THEME / "assets").rglob("*.js"))
    legacy_renderer = (THEME / "inc" / "template-functions.php").read_text(encoding="utf-8")
    inline_background_count = 0
    for source in source_files:
        text = source.read_text(encoding="utf-8", errors="replace")
        if source.parent == THEME / "import-source":
            inline_background_count += len(re.findall(r"style\s*=\s*(['\"]).*?background-image\s*:", text, re.I | re.S))
        for match in re.finditer(r"(?:assets/)[A-Za-z0-9_./@%+~-]+", text):
            ref = match.group(0).split("?")[0].split("#")[0]
            if not (THEME / ref).is_file():
                errors.append(f"Missing referenced asset {ref} in {source.relative_to(THEME)}")
        if source.suffix == ".css":
            for match in re.finditer(r"url\(\s*(['\"]?)([^'\")]+)\1\s*\)", text, re.I):
                ref = match.group(2).strip()
                if not ref or ref.startswith(("data:", "http:", "https:", "//", "#")):
                    continue
                if not (source.parent / ref.split("?")[0].split("#")[0]).resolve().is_file():
                    errors.append(f"Missing CSS URL {ref} in {source.relative_to(THEME)}")
    if inline_background_count and ("function alify_kses_legacy_markup" not in legacy_renderer or "$attributes['style']=true" not in legacy_renderer):
        errors.append(f"The renderer must sanitize and preserve {inline_background_count} source inline background-image styles.")

    if errors:
        print("Theme asset audit failed:\n- " + "\n- ".join(sorted(set(errors))))
        raise SystemExit(1)
    print(f"Theme asset audit passed: {len(styles)} stylesheets, {len(scripts)} scripts and {inline_background_count} source inline backgrounds are covered; local assets resolve.")


if __name__ == "__main__":
    main()
