#!/usr/bin/env node
/* Rebuild the JSON import manifest from the original static detail-page catalogs. */
const fs = require("fs");
const path = require("path");

const theme = path.resolve(__dirname, "..");
const assets = path.join(theme, "assets", "js", "reusable-component-js");

function extractObject(source, declaration) {
  let start = source.indexOf(declaration);
  if (start < 0) throw new Error(`Could not find ${declaration}`);
  start = source.indexOf("{", start);
  let depth = 0;
  let quote = null;
  let escaped = false;
  let lineComment = false;
  let blockComment = false;
  for (let index = start; index < source.length; index += 1) {
    const char = source[index];
    const next = source[index + 1];
    if (lineComment) {
      if (char === "\n") lineComment = false;
      continue;
    }
    if (blockComment) {
      if (char === "*" && next === "/") { blockComment = false; index += 1; }
      continue;
    }
    if (quote) {
      if (escaped) { escaped = false; continue; }
      if (char === "\\") { escaped = true; continue; }
      if (char === quote) quote = null;
      continue;
    }
    if (char === "/" && next === "/") { lineComment = true; index += 1; continue; }
    if (char === "/" && next === "*") { blockComment = true; index += 1; continue; }
    if (char === "\"" || char === "'" || char === "`") { quote = char; continue; }
    if (char === "{") depth += 1;
    if (char === "}" && --depth === 0) return source.slice(start, index + 1);
  }
  throw new Error(`Unclosed object for ${declaration}`);
}

function readCatalog(file, declaration) {
  const source = fs.readFileSync(path.join(assets, file), "utf8");
  return Function(`"use strict"; return (${extractObject(source, declaration)});`)();
}

const catalog = {
  services: readCatalog("single-pages.js", "const SERVICES ="),
  industries: readCatalog("single-pages.js", "const INDUSTRIES ="),
  projects: readCatalog("single-pages.js", "const PROJECTS ="),
  case_studies: readCatalog("single-pages.js", "const CASES ="),
  posts: readCatalog("blog.js", "const ALIFY_BLOG_POSTS ="),
};
const output = path.join(theme, "import-source", "catalog-content.json");
fs.writeFileSync(output, `${JSON.stringify(catalog, null, 2)}\n`);
console.log(`Wrote ${output} (${Object.values(catalog).reduce((n, group) => n + Object.keys(group).length, 0)} records).`);
