/**
 * .docx → TipTap JSON import path.
 *
 * Pipeline: .docx binary → mammoth → HTML → TipTap generateJSON → JSON
 *
 * mammoth converts .docx to semantic HTML, preserving:
 * - Headings (h1–h6)
 * - Paragraphs
 * - Bold, italic, underline
 * - Ordered and unordered lists
 * - Tables
 *
 * Key question for this spike: does mammoth preserve RTL direction markers
 * from the .docx (<w:bidi> elements)?
 */

import mammoth from 'mammoth';
import { readFileSync } from 'fs';
import { htmlToJSON } from './tiptap-schema.mjs';

/**
 * Import a .docx file and return TipTap JSON + intermediate HTML.
 *
 * @param {string} filePath - Path to the .docx file
 * @returns {{ json: object, html: string, warnings: string[] }}
 */
export async function importDocx(filePath) {
  const buffer = readFileSync(filePath);

  // mammoth options: map .docx styles to HTML elements
  const options = {
    styleMap: [
      // Map Word's built-in heading styles
      "p[style-name='Heading 1'] => h1:fresh",
      "p[style-name='Heading 2'] => h2:fresh",
      "p[style-name='Heading 3'] => h3:fresh",
    ],
    // Preserve inline formatting
    includeDefaultStyleMap: true,
  };

  const result = await mammoth.convertToHtml({ buffer }, options);

  const html = result.value;
  const warnings = result.messages.map(m => `[${m.type}] ${m.message}`);

  // Convert HTML to TipTap JSON
  const json = htmlToJSON(html);

  return { json, html, warnings };
}

/**
 * Analyze the structure of a TipTap JSON document.
 *
 * @param {object} json - TipTap JSON document
 * @returns {object} Structure counts
 */
export function analyzeStructure(json) {
  const counts = {
    paragraphs: 0,
    headings: { h1: 0, h2: 0, h3: 0 },
    bulletLists: 0,
    orderedLists: 0,
    listItems: 0,
    tables: 0,
    tableRows: 0,
    tableCells: 0,
    boldRuns: 0,
    italicRuns: 0,
    underlineRuns: 0,
    totalTextLength: 0,
    rtlMarkers: 0,
    ltrMarkers: 0,
  };

  function walk(node) {
    if (!node) return;

    switch (node.type) {
      case 'paragraph':
        counts.paragraphs++;
        break;
      case 'heading':
        const level = `h${node.attrs?.level || 1}`;
        if (counts.headings[level] !== undefined) counts.headings[level]++;
        break;
      case 'bulletList':
        counts.bulletLists++;
        break;
      case 'orderedList':
        counts.orderedLists++;
        break;
      case 'listItem':
        counts.listItems++;
        break;
      case 'table':
        counts.tables++;
        break;
      case 'tableRow':
        counts.tableRows++;
        break;
      case 'tableCell':
      case 'tableHeader':
        counts.tableCells++;
        break;
      case 'text':
        counts.totalTextLength += (node.text || '').length;
        // Check for direction in marks or attrs
        if (node.marks) {
          for (const mark of node.marks) {
            if (mark.type === 'bold') counts.boldRuns++;
            if (mark.type === 'italic') counts.italicRuns++;
            if (mark.type === 'underline') counts.underlineRuns++;
          }
        }
        break;
    }

    // Check textAlign attrs for direction hints
    if (node.attrs?.textAlign === 'right') counts.rtlMarkers++;
    if (node.attrs?.textAlign === 'left') counts.ltrMarkers++;

    // Check for dir attribute (if present)
    if (node.attrs?.dir === 'rtl') counts.rtlMarkers++;
    if (node.attrs?.dir === 'ltr') counts.ltrMarkers++;

    // Recurse into children
    if (node.content) {
      for (const child of node.content) {
        walk(child);
      }
    }
  }

  walk(json);
  return counts;
}
