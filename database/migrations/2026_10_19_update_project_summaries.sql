SET NAMES utf8mb4;

UPDATE projects
SET summary_ar = 'عقد نفذته الشركة لبيع منتوج البنزين الطبيعي، ضمن نشاطها في تسويق المنتجات النفطية وتجهيزها.',
    summary_en = 'A contract executed by the company for the sale of natural gasoline, as part of its petroleum products marketing and supply activities.'
WHERE title_en = 'Natural Gasoline Sale and Purchase Agreement';

UPDATE projects
SET summary_ar = 'عقد تشغيل مشترك لمشروع معمل يُنتج وقود البواخر والأسفلت المؤكسد، ضمن نشاط الشركة في الإنتاج النفطي والصناعي.',
    summary_en = 'A joint operation agreement for a plant project producing marine fuel and oxidized asphalt, as part of the company''s petroleum and industrial production activities.'
WHERE title_en = 'Joint Operation Agreement for the Marine Fuel and Oxidized Asphalt Production Plant Project';
