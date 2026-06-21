/**
 * TipTap 2 schema definition for the round-trip test.
 *
 * Defines the extensions and generates JSON from HTML (and vice versa)
 * using @tiptap/html in a Node.js (JSDOM) environment.
 */

import { generateJSON, generateHTML } from '@tiptap/html';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TextAlign from '@tiptap/extension-text-align';
import { JSDOM } from 'jsdom';

// JSDOM polyfill — TipTap's HTML utilities expect a browser DOM
const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>');
globalThis.document = dom.window.document;
globalThis.HTMLElement = dom.window.HTMLElement;
globalThis.Element = dom.window.Element;
globalThis.Node = dom.window.Node;
try { globalThis.navigator = dom.window.navigator; } catch { /* read-only in some Node versions */ }
if (!globalThis.window) {
  try { globalThis.window = dom.window; } catch { /* read-only */ }
}

/**
 * The TipTap extensions we use — this is the schema that production will also use.
 * Matches what F-03.4 will install.
 */
export const extensions = [
  StarterKit.configure({
    // StarterKit includes: Document, Paragraph, Text, Bold, Italic, Strike,
    // Code, Heading, BulletList, OrderedList, ListItem, Blockquote, HardBreak,
    // HorizontalRule, CodeBlock, History, Dropcursor, Gapcursor
  }),
  Underline,
  Table.configure({ resizable: false }),
  TableRow,
  TableCell,
  TableHeader,
  TextAlign.configure({
    types: ['heading', 'paragraph'],
    alignments: ['left', 'center', 'right'],
  }),
];

/**
 * Convert HTML string to TipTap JSON document.
 */
export function htmlToJSON(html) {
  return generateJSON(html, extensions);
}

/**
 * Convert TipTap JSON document back to HTML string.
 */
export function jsonToHTML(json) {
  return generateHTML(json, extensions);
}
