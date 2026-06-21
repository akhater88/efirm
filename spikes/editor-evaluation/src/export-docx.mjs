/**
 * TipTap JSON → .docx export path.
 *
 * Pipeline: TipTap JSON → walk nodes → docx library → .docx binary
 *
 * This walks the TipTap JSON tree and constructs a .docx document using
 * the `docx` npm library (same library used to generate fixtures).
 */

import {
  Document, Packer, Paragraph, TextRun, HeadingLevel,
  AlignmentType, Table, TableRow, TableCell, WidthType,
  BorderStyle, LevelFormat,
} from 'docx';

/**
 * Detect if text is predominantly Arabic (RTL).
 * Uses Unicode block detection: Arabic (0600–06FF), Arabic Supplement, Arabic Extended.
 */
function isArabicText(text) {
  if (!text) return false;
  const arabicChars = (text.match(/[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/g) || []).length;
  const totalAlpha = (text.match(/[^\s\d\p{P}]/gu) || []).length;
  return totalAlpha > 0 && (arabicChars / totalAlpha) > 0.3;
}

/**
 * Extract text content from a TipTap JSON node (recursively).
 */
function extractText(node) {
  if (node.type === 'text') return node.text || '';
  if (!node.content) return '';
  return node.content.map(extractText).join('');
}

/**
 * Convert TipTap marks to TextRun options.
 */
function marksToRunOptions(marks = []) {
  const opts = {};
  for (const mark of marks) {
    switch (mark.type) {
      case 'bold': opts.bold = true; break;
      case 'italic': opts.italics = true; break;
      case 'underline': opts.underline = {}; break;
      case 'strike': opts.strike = true; break;
    }
  }
  return opts;
}

/**
 * Convert a TipTap inline content array to TextRun[].
 */
function inlineToRuns(content = [], isRtl = false) {
  if (content.length === 0) {
    // Empty paragraph — return a single empty run
    return [new TextRun({ text: '', font: 'Arial', size: 24 })];
  }

  return content.map(node => {
    if (node.type === 'text') {
      const markOpts = marksToRunOptions(node.marks);
      return new TextRun({
        text: node.text || '',
        rightToLeft: isRtl,
        font: 'Arial',
        size: 24,
        ...markOpts,
      });
    }
    if (node.type === 'hardBreak') {
      return new TextRun({ break: 1 });
    }
    // Fallback: extract text
    return new TextRun({
      text: extractText(node),
      rightToLeft: isRtl,
      font: 'Arial',
      size: 24,
    });
  });
}

/**
 * Map TipTap heading level to docx HeadingLevel.
 */
function headingLevel(level) {
  const map = {
    1: HeadingLevel.HEADING_1,
    2: HeadingLevel.HEADING_2,
    3: HeadingLevel.HEADING_3,
    4: HeadingLevel.HEADING_4,
    5: HeadingLevel.HEADING_5,
    6: HeadingLevel.HEADING_6,
  };
  return map[level] || HeadingLevel.HEADING_1;
}

/**
 * Convert a TipTap JSON table node to a docx Table.
 */
function tableToDocx(node) {
  const cellBorder = {
    top: { style: BorderStyle.SINGLE, size: 1 },
    bottom: { style: BorderStyle.SINGLE, size: 1 },
    left: { style: BorderStyle.SINGLE, size: 1 },
    right: { style: BorderStyle.SINGLE, size: 1 },
  };

  const rows = (node.content || []).map(rowNode => {
    const cells = (rowNode.content || []).map(cellNode => {
      const cellParagraphs = convertNodes(cellNode.content || []);
      return new TableCell({
        borders: cellBorder,
        children: cellParagraphs.length > 0 ? cellParagraphs : [new Paragraph({ children: [] })],
      });
    });
    return new TableRow({ children: cells });
  });

  return new Table({
    rows,
    width: { size: 100, type: WidthType.PERCENTAGE },
  });
}

/**
 * Convert an array of TipTap JSON nodes to docx elements.
 */
function convertNodes(nodes) {
  const elements = [];

  for (const node of nodes) {
    const fullText = extractText(node);
    const isRtl = isArabicText(fullText) ||
                  node.attrs?.textAlign === 'right' ||
                  node.attrs?.dir === 'rtl';

    switch (node.type) {
      case 'paragraph': {
        elements.push(new Paragraph({
          bidirectional: isRtl,
          alignment: isRtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
          children: inlineToRuns(node.content, isRtl),
        }));
        break;
      }

      case 'heading': {
        const level = node.attrs?.level || 1;
        elements.push(new Paragraph({
          heading: headingLevel(level),
          bidirectional: isRtl,
          alignment: isRtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
          children: inlineToRuns(node.content, isRtl),
        }));
        break;
      }

      case 'bulletList': {
        for (const listItem of (node.content || [])) {
          for (const itemContent of (listItem.content || [])) {
            const itemText = extractText(itemContent);
            const itemRtl = isArabicText(itemText);
            elements.push(new Paragraph({
              bullet: { level: 0 },
              bidirectional: itemRtl,
              alignment: itemRtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
              children: inlineToRuns(itemContent.content, itemRtl),
            }));
          }
        }
        break;
      }

      case 'orderedList': {
        for (const listItem of (node.content || [])) {
          for (const itemContent of (listItem.content || [])) {
            const itemText = extractText(itemContent);
            const itemRtl = isArabicText(itemText);
            elements.push(new Paragraph({
              numbering: { reference: 'default-numbering', level: 0 },
              bidirectional: itemRtl,
              alignment: itemRtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
              children: inlineToRuns(itemContent.content, itemRtl),
            }));
          }
        }
        break;
      }

      case 'table': {
        elements.push(tableToDocx(node));
        break;
      }

      case 'blockquote': {
        // Flatten blockquote children
        if (node.content) {
          elements.push(...convertNodes(node.content));
        }
        break;
      }

      case 'horizontalRule': {
        elements.push(new Paragraph({
          children: [new TextRun({ text: '───────────────────────────────────' })],
        }));
        break;
      }

      default:
        // Unknown node type — try to extract text
        if (node.content) {
          elements.push(...convertNodes(node.content));
        }
        break;
    }
  }

  return elements;
}

/**
 * Export TipTap JSON document to .docx buffer.
 *
 * @param {object} json - TipTap JSON document (root node with type: 'doc')
 * @returns {Promise<Buffer>} .docx binary
 */
export async function exportDocx(json) {
  const children = convertNodes(json.content || []);

  const doc = new Document({
    numbering: {
      config: [{
        reference: 'default-numbering',
        levels: [{
          level: 0,
          format: LevelFormat.DECIMAL,
          text: '%1.',
          alignment: AlignmentType.LEFT,
        }],
      }],
    },
    sections: [{
      properties: {},
      children,
    }],
  });

  return await Packer.toBuffer(doc);
}
