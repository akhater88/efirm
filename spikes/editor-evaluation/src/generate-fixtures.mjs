/**
 * Generate synthetic bilingual contract .docx fixtures for the editor evaluation spike.
 *
 * Produces 3 files in fixtures/:
 *   01-bilateral-nda-ar-en.docx  — Bilingual NDA (Arabic primary, English secondary)
 *   02-spa-ar.docx               — Arabic-only Share Purchase Agreement
 *   03-supply-agreement-en.docx  — English-only Supply Agreement
 */

import {
  Document, Packer, Paragraph, TextRun, HeadingLevel,
  AlignmentType, Table, TableRow, TableCell, WidthType,
  BorderStyle, NumberFormat,
} from 'docx';
import { writeFileSync, mkdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const fixturesDir = join(__dirname, '..', 'fixtures');
mkdirSync(fixturesDir, { recursive: true });

// ─── Helpers ──────────────────────────────────────────────────────────────────

function rtlParagraph(text, { bold = false, italic = false, heading = null } = {}) {
  const options = {
    bidirectional: true,
    alignment: AlignmentType.RIGHT,
    children: [
      new TextRun({
        text,
        bold,
        italics: italic,
        rightToLeft: true,
        font: 'Arial',
        size: heading ? 28 : 24,
      }),
    ],
  };
  if (heading) {
    options.heading = heading;
  }
  return new Paragraph(options);
}

function ltrParagraph(text, { bold = false, italic = false, heading = null } = {}) {
  const options = {
    alignment: AlignmentType.LEFT,
    children: [
      new TextRun({
        text,
        bold,
        italics: italic,
        font: 'Arial',
        size: heading ? 28 : 24,
      }),
    ],
  };
  if (heading) {
    options.heading = heading;
  }
  return new Paragraph(options);
}

function rtlBullet(text, level = 0) {
  return new Paragraph({
    bidirectional: true,
    alignment: AlignmentType.RIGHT,
    bullet: { level },
    children: [
      new TextRun({ text, rightToLeft: true, font: 'Arial', size: 24 }),
    ],
  });
}

function ltrNumbered(text, level = 0) {
  return new Paragraph({
    alignment: AlignmentType.LEFT,
    numbering: { reference: 'default-numbering', level },
    children: [
      new TextRun({ text, font: 'Arial', size: 24 }),
    ],
  });
}

function mixedRunParagraph(runs, { rtl = false, heading = null } = {}) {
  const options = {
    bidirectional: rtl,
    alignment: rtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
    children: runs.map(r =>
      new TextRun({
        text: r.text,
        bold: r.bold || false,
        italics: r.italic || false,
        underline: r.underline ? {} : undefined,
        rightToLeft: rtl,
        font: 'Arial',
        size: heading ? 28 : 24,
      })
    ),
  };
  if (heading) {
    options.heading = heading;
  }
  return new Paragraph(options);
}

function signatureTable() {
  const cellBorder = {
    top: { style: BorderStyle.SINGLE, size: 1 },
    bottom: { style: BorderStyle.SINGLE, size: 1 },
    left: { style: BorderStyle.SINGLE, size: 1 },
    right: { style: BorderStyle.SINGLE, size: 1 },
  };
  const cellWidth = { size: 50, type: WidthType.PERCENTAGE };

  function cell(text, rtl = false) {
    return new TableCell({
      borders: cellBorder,
      width: cellWidth,
      children: [
        new Paragraph({
          bidirectional: rtl,
          alignment: rtl ? AlignmentType.RIGHT : AlignmentType.LEFT,
          children: [
            new TextRun({ text, rightToLeft: rtl, font: 'Arial', size: 20 }),
          ],
        }),
      ],
    });
  }

  return new Table({
    rows: [
      new TableRow({ children: [cell('الطرف الأول / Party 1', true), cell('الطرف الثاني / Party 2', true)] }),
      new TableRow({ children: [cell('التوقيع / Signature: ___________', true), cell('التوقيع / Signature: ___________', true)] }),
      new TableRow({ children: [cell('التاريخ / Date: ___/___/2026', true), cell('التاريخ / Date: ___/___/2026', true)] }),
      new TableRow({ children: [cell('المسمى الوظيفي / Title: ___________', true), cell('المسمى الوظيفي / Title: ___________', true)] }),
    ],
    width: { size: 100, type: WidthType.PERCENTAGE },
  });
}

// ─── Fixture 1: Bilateral NDA (AR + EN) ───────────────────────────────────────

async function generateNDA() {
  const doc = new Document({
    sections: [{
      properties: {},
      children: [
        rtlParagraph('اتفاقية عدم إفصاح متبادلة', { heading: HeadingLevel.HEADING_1 }),
        ltrParagraph('Mutual Non-Disclosure Agreement', { heading: HeadingLevel.HEADING_1 }),

        new Paragraph({ children: [] }), // spacer

        rtlParagraph('أبرمت هذه الاتفاقية بتاريخ 17 يونيو 2026 بين:'),
        rtlParagraph('الطرف الأول: شركة الأردن للتوريدات، شركة مساهمة خاصة مسجلة في المملكة الأردنية الهاشمية ("الطرف المفصح")'),
        rtlParagraph('الطرف الثاني: شركة Acme MENA Holdings Ltd، شركة محدودة مسجلة في مركز دبي المالي العالمي ("الطرف المتلقي")'),

        new Paragraph({ children: [] }),

        rtlParagraph('1. تعريفات', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('"المعلومات السرية" تعني أي معلومات تقنية أو تجارية أو مالية يفصح عنها أحد الطرفين للآخر، سواء كانت مكتوبة أو شفهية أو إلكترونية.'),

        new Paragraph({ children: [] }),

        ltrParagraph('2. Obligations of the Receiving Party', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('The Receiving Party shall hold all Confidential Information in strict confidence and shall not disclose such information to any third party without the prior written consent of the Disclosing Party.'),

        new Paragraph({ children: [] }),

        rtlParagraph('3. الاستثناءات', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('لا تشمل المعلومات السرية أي معلومات: (أ) كانت متاحة للعموم وقت الإفصاح، أو (ب) أصبحت متاحة للعموم دون خطأ من الطرف المتلقي.'),

        new Paragraph({ children: [] }),

        rtlParagraph('4. المدة والإنهاء', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('تسري هذه الاتفاقية لمدة سنتين (2) من تاريخ التوقيع، وتتجدد تلقائياً لفترات مماثلة ما لم يخطر أحد الطرفين الآخر كتابياً قبل 30 يوماً من انتهاء المدة.'),

        new Paragraph({ children: [] }),

        ltrParagraph('5. Governing Law', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('This Agreement shall be governed by and construed in accordance with the laws of the Hashemite Kingdom of Jordan. Any dispute arising hereunder shall be referred to the competent courts of Amman.'),

        new Paragraph({ children: [] }),

        signatureTable(),
      ],
    }],
  });

  const buffer = await Packer.toBuffer(doc);
  writeFileSync(join(fixturesDir, '01-bilateral-nda-ar-en.docx'), buffer);
  console.log('✓ Generated 01-bilateral-nda-ar-en.docx');
}

// ─── Fixture 2: Share Purchase Agreement (AR only) ────────────────────────────

async function generateSPA() {
  const doc = new Document({
    sections: [{
      properties: {},
      children: [
        rtlParagraph('اتفاقية شراء أسهم', { heading: HeadingLevel.HEADING_1 }),

        new Paragraph({ children: [] }),

        rtlParagraph('أبرمت هذه الاتفاقية بتاريخ 17 يونيو 2026 بين شركة الأردن للتوريدات (المشار إليها فيما يلي بـ "البائع") وشركة Acme MENA Holdings (المشار إليها فيما يلي بـ "المشتري").'),

        new Paragraph({ children: [] }),

        rtlParagraph('1. موضوع الاتفاقية', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('يقوم البائع ببيع جميع الأسهم العادية للشركة محل الصفقة إلى المشتري وفقاً للشروط المنصوص عليها في هذه الاتفاقية.'),

        new Paragraph({ children: [] }),

        rtlParagraph('2. ثمن الشراء', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('يبلغ ثمن الشراء الإجمالي مبلغ 500,000 دولار أمريكي (خمسمائة ألف دولار أمريكي)، يُدفع على النحو التالي:'),

        rtlBullet('50% عند التوقيع'),
        rtlBullet('25% خلال 30 يوماً من تاريخ الإغلاق'),
        rtlBullet('25% خلال 90 يوماً من تاريخ الإغلاق'),

        new Paragraph({ children: [] }),

        rtlParagraph('3. الضمانات والإقرارات', { heading: HeadingLevel.HEADING_2 }),
        mixedRunParagraph([
          { text: 'يقر البائع ويضمن أن الشركة ' },
          { text: 'ليس عليها أي التزامات مالية غير مفصح عنها', bold: true },
          { text: ' وأنها ملتزمة بجميع القوانين المعمول بها في المملكة الأردنية الهاشمية.' },
        ], { rtl: true }),

        new Paragraph({ children: [] }),

        rtlParagraph('4. القانون الواجب التطبيق', { heading: HeadingLevel.HEADING_2 }),
        rtlParagraph('تخضع هذه الاتفاقية للقانون الأردني، ويختص بنظر أي نزاع ينشأ عنها محكمة الاستئناف الأردنية في عمّان.'),
      ],
    }],
  });

  const buffer = await Packer.toBuffer(doc);
  writeFileSync(join(fixturesDir, '02-spa-ar.docx'), buffer);
  console.log('✓ Generated 02-spa-ar.docx');
}

// ─── Fixture 3: Supply Agreement (EN only) ────────────────────────────────────

async function generateSupplyAgreement() {
  const doc = new Document({
    numbering: {
      config: [{
        reference: 'default-numbering',
        levels: [{
          level: 0,
          format: NumberFormat.DECIMAL,
          text: '%1.',
          alignment: AlignmentType.LEFT,
        }],
      }],
    },
    sections: [{
      properties: {},
      children: [
        ltrParagraph('Supply Agreement', { heading: HeadingLevel.HEADING_1 }),

        new Paragraph({ children: [] }),

        ltrParagraph('This Supply Agreement ("Agreement") is entered into as of June 17, 2026, by and between Jordan Supplies Co. (the "Supplier") and Acme MENA Holdings Ltd. (the "Buyer").'),

        new Paragraph({ children: [] }),

        ltrParagraph('1. Scope of Supply', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('The Supplier agrees to supply the goods described in Schedule A attached hereto, in accordance with the specifications, quantities, and delivery schedule set forth therein.'),

        new Paragraph({ children: [] }),

        ltrParagraph('2. Pricing and Payment', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('The total contract value is USD 250,000.00 (Two Hundred Fifty Thousand United States Dollars). Payment terms:'),

        ltrNumbered('30% advance payment upon execution of this Agreement'),
        ltrNumbered('40% upon delivery confirmation'),
        ltrNumbered('30% within 60 days of acceptance'),

        new Paragraph({ children: [] }),

        ltrParagraph('3. Warranties', { heading: HeadingLevel.HEADING_2 }),
        mixedRunParagraph([
          { text: 'The Supplier warrants that all goods delivered shall be ' },
          { text: 'free from defects in material and workmanship', italic: true },
          { text: ' for a period of twelve (12) months from the date of delivery.' },
        ]),

        new Paragraph({ children: [] }),

        ltrParagraph('4. Limitation of Liability', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('The aggregate liability of the Supplier under this Agreement shall not exceed 100% of the total contract value. In no event shall either party be liable for indirect, incidental, or consequential damages.'),

        new Paragraph({ children: [] }),

        ltrParagraph('5. Governing Law and Dispute Resolution', { heading: HeadingLevel.HEADING_2 }),
        ltrParagraph('This Agreement shall be governed by the laws of the Hashemite Kingdom of Jordan. Any dispute shall be resolved by arbitration under the rules of the Amman Chamber of Commerce.'),
      ],
    }],
  });

  const buffer = await Packer.toBuffer(doc);
  writeFileSync(join(fixturesDir, '03-supply-agreement-en.docx'), buffer);
  console.log('✓ Generated 03-supply-agreement-en.docx');
}

// ─── Run ──────────────────────────────────────────────────────────────────────

console.log('Generating synthetic contract fixtures...\n');
await generateNDA();
await generateSPA();
await generateSupplyAgreement();
console.log('\nAll fixtures generated in fixtures/');
