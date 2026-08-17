# موقع شركة المها للخدمات النفطية

موقع مؤسسي ثنائي اللغة ولوحة إدارة مبنيان بـPHP وMySQL من دون WordPress أو إطار عمل ضخم. العربية هي اللغة الافتراضية، مع RTL/LTR فعليين، ووضعين ليلي ونهاري، وإدارة محتوى وصلاحيات ونموذج تواصل آمن.

## المتطلبات

- PHP 8.2 أو أحدث، مع الامتدادات: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` و`json`.
- MySQL 8 أو MariaDB 10.6 أو أحدث.
- Apache مع `mod_rewrite`. يوصى أيضاً بـ`mod_headers` و`mod_deflate`.
- HTTPS في بيئة الإنتاج.
- مساحة كافية للملف التعريفي والصور؛ ملف PDF المرفق كبير الحجم.

## هيكل مهم

- `public/`: نقطة الدخول والأصول العامة فقط.
- `app/`: Controllers وCore وModels وViews وServices والترجمات.
- `database/schema.sql`: بنية قاعدة البيانات.
- `database/seed.sql`: المحتوى الرسمي الأولي من الملف التعريفي، بلا مستخدم أو كلمة مرور.
- `storage/uploads/`: ملفات لوحة التحكم، خارج المجلد العام.
- `storage/logs/`: سجل أخطاء PHP الداخلي.
- `bin/create-admin.php`: إنشاء أول مدير من الطرفية.
- `CONTENT-GAPS.md`: البيانات التي لم ترد في الملف الرسمي.
- `CONTENT-SOURCE-MAP.md`: خريطة النصوص إلى صفحات المصدر.
- `IMAGE-SOURCES.md`: مصادر الصور الخارجية وتراخيصها.
- `QA-REPORT.md`: نتائج الاختبار المنفذة.

## التثبيت المحلي

1. أنشئ ملف البيئة:

   ```bash
   cp .env.example .env
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

   ضع الناتج في `APP_KEY`، واضبط `APP_URL=http://127.0.0.1:8080` وبيانات قاعدة البيانات. استخدم `SESSION_SECURE=false` محلياً فقط.

2. أنشئ قاعدة بيانات ومستخدماً بصلاحيات محدودة، ثم استورد الملفات بالترتيب:

   ```sql
   CREATE DATABASE almaha_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'almaha_user'@'localhost' IDENTIFIED BY 'ضع-كلمة-مرور-قوية-هنا';
   GRANT SELECT, INSERT, UPDATE, DELETE ON almaha_website.* TO 'almaha_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

   ```bash
   mysql -u almaha_user -p almaha_website < database/schema.sql
   mysql -u almaha_user -p almaha_website < database/seed.sql
   ```

3. أنشئ أول مدير. لا تُحفظ كلمة المرور في الملف أو في سجل الأوامر؛ يطلبها الأمر بصورة مخفية:

   ```bash
   php bin/create-admin.php admin@example.com "اسم المدير"
   ```

4. أعط PHP حق الكتابة في التخزين فقط:

   ```bash
   chmod 750 storage storage/logs storage/uploads storage/cache
   ```

   في بعض الخوادم المشتركة قد يلزم `770` للمجلدات المذكورة بحسب مستخدم Apache. لا تستخدم `777`.

5. شغّل الخادم المحلي:

   ```bash
   php -S 127.0.0.1:8080 -t public public/router.php
   ```

   الموقع: `http://127.0.0.1:8080`، ولوحة التحكم: `http://127.0.0.1:8080/admin/login`.

## إعداد ملف `.env`

- `APP_ENV=production` و`APP_DEBUG=false` في الإنتاج.
- `APP_URL` هو الرابط النهائي كاملاً مع `https://` ومن دون شرطة مائلة أخيرة، ويشمل المسار الفرعي عند النشر داخله، مثل `https://example.com/maha-group`.
- `APP_KEY` قيمة عشوائية طويلة، ولا تُرسل بالبريد أو تحفظ في Git.
- حقول `DB_*` تخص قاعدة البيانات.
- `SESSION_SECURE=true` مع HTTPS.
- `UPLOAD_MAX_MB` يجب ألا يتجاوز حدود `upload_max_filesize` و`post_max_size` في PHP.
- لا يتضمن المشروع أي مفتاح أو كلمة مرور حقيقية، و`.env` مستثنى من Git ومن حزمة الإنتاج.

## الرفع على Namecheap أو cPanel

### الخيار الموصى به: Document Root إلى `public`

1. ارفع محتويات الحزمة إلى مجلد خاص بالحساب، مثال: `/home/ACCOUNT/almaha`.
2. اجعل Document Root للنطاق أو Subdomain هو `/home/ACCOUNT/almaha/public`.
3. أنشئ `.env` في `/home/ACCOUNT/almaha/.env`، لا داخل `public`.
4. استورد `schema.sql` ثم `seed.sql` من phpMyAdmin أو Terminal.
5. أنشئ المدير من cPanel Terminal.
6. اضبط ملكية وصلاحيات `storage/logs` و`storage/uploads` و`storage/cache` ليتمكن PHP من الكتابة فيها.

### إذا تعذر تغيير Document Root

يمكن رفع المشروع داخل `public_html`. ملف `.htaccess` الجذري يحجب مجلدات التطبيق والتخزين ثم يوجه الطلبات إلى `public/`. تأكد أن cPanel لا يحذف الملفات المخفية، وأن `AllowOverride` و`mod_rewrite` مفعّلان. يبقى الخيار الأول أكثر أماناً.

### إعداد قاعدة البيانات في cPanel

1. من **MySQL Database Wizard** أنشئ قاعدة بيانات ومستخدماً وكلمة مرور قوية.
2. اربط المستخدم بالقاعدة. امنحه صلاحيات القراءة والإضافة والتعديل والحذف؛ قد يتطلب الاستيراد الأولي `CREATE`, `ALTER`, `INDEX` و`REFERENCES` أيضاً.
3. استورد `database/schema.sql` ثم `database/seed.sql` عبر phpMyAdmin.
4. اكتب الاسم الكامل الذي يضيفه cPanel، مثل `account_almaha`، في `DB_NAME` و`DB_USER`.

لا تعِد تشغيل `seed.sql` على موقع أُجريت عليه تعديلات تحريرية إلا بعد نسخة احتياطية؛ البيانات الأولية تعيد مزامنة الصفحات الرسمية ذات الروابط نفسها.

## SSL والحماية الإنتاجية

1. فعّل AutoSSL في cPanel أو شهادة Namecheap، ثم اختبر أن `https://` يعمل قبل فرض التحويل.
2. غيّر `APP_URL` إلى HTTPS و`SESSION_SECURE=true`.
3. فعّل إعادة توجيه HTTP إلى HTTPS من cPanel أو أضفها على مستوى الاستضافة.
4. اترك `APP_DEBUG=false` وتأكد أن `display_errors=Off` و`expose_php=Off` في إعداد PHP الإنتاجي.
5. احتفظ بنسخ احتياطية دورية من قاعدة البيانات و`storage/uploads` و`.env` في مكان مشفر.
6. راقب `storage/logs/php-error.log`، ولا تجعله قابلاً للوصول من الويب.

يضيف التطبيق رؤوس CSP و`nosniff` و`SAMEORIGIN` وسياسة Referrer وPermissions. ويضيف Apache تخزيناً مؤقتاً للأصول وضغطاً عبر `mod_deflate` عند توفره.

## SMTP

النموذج يحفظ الرسالة في قاعدة البيانات حتى عندما يكون SMTP معطلاً. لتفعيل الإشعار:

```dotenv
SMTP_ENABLED=true
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USER=website@example.com
SMTP_PASS=كلمة-مرور-التطبيق
SMTP_FROM=website@example.com
SMTP_FROM_NAME=Al-Maha Website
SMTP_TO=info@almaha-group.com
```

استخدم كلمة مرور تطبيق أو بيانات SMTP مخصصة، وليس كلمة مرور البريد الرئيسية. المنفذ 465 يستخدم عادة `SMTP_ENCRYPTION=ssl`؛ والمنفذ 587 يستخدم `tls`.

## لوحة التحكم

يدعم النظام مديراً عاماً ومحرر محتوى. المدير وحده يدير المستخدمين والإعدادات وسجل العمليات. يستطيع المحرر إدارة المحتوى والوسائط والرسائل. كل عمليات الإنشاء والتعديل والنشر والإخفاء والحذف مرتبطة بقاعدة البيانات ومحميّة بـCSRF.

حالات المحتوى:

- `draft`: مسودة لا تظهر للعامة.
- `published`: منشور.
- `hidden`: مخفي وقابل لإعادة التفعيل.

الحذف المناسب Soft Delete ويمكن تتبعه في سجل العمليات. ترتيب العناصر يتم من حقل الترتيب الرقمي. مرجع الملف المنشور من مكتبة الوسائط هو `/media/ID` ويظهر تحت اسم الملف في القائمة.

## الصور والأداء

- الصور الأساسية محفوظة محلياً بصيغ JPEG وWebP وAVIF.
- أول صورة Hero ذات أولوية، والبقية Lazy Loaded.
- الأبعاد ثابتة للحد من Layout Shift.
- JavaScript محمّل بـ`defer`، ولا توجد مكتبة Frontend خارجية.
- `prefers-reduced-motion` يوقف الحركات غير الضرورية.
- يدعم Slider السحب والمؤشر ولوحة المفاتيح والتوقف عند Hover/Focus وعند إخفاء الصفحة.
- تُحمّل بيئة `production` تلقائياً ملفات `*.min.css` و`*.min.js` المصغّرة، ويضيف `.htaccess` ضغط النقل عند تفعيل `mod_deflate`.

## SEO والتحليلات

يتضمن الموقع Metadata عربية وإنجليزية، Canonical، `hreflang`، Open Graph، Twitter Cards، Organization وBreadcrumb JSON-LD، Sitemap XML وRobots و404 فعلية. يمكن إدخال معرف التحليلات لاحقاً في الإعدادات بعد اختيار الأداة وتحديث سياسة الخصوصية؛ لا يوجد معرف وهمي أو كود تتبع مفعل حالياً.

## الاختبار

فحص الصياغة:

```bash
find app config routes public bin tools -name '*.php' -print0 | xargs -0 -n1 php -l
node --check public/assets/js/site.js
node --check public/assets/js/admin.js
```

اختبار HTTP الوظيفي يحتاج خادم الموقع وقاعدة اختبار ومديراً مؤقتاً:

```bash
QA_ADMIN_EMAIL='qa@example.test' QA_ADMIN_PASSWORD='كلمة-اختبار-مؤقتة' \
php tools/qa_http.php http://127.0.0.1:8080
```

لا تستخدم بيانات مدير الإنتاج في الاختبار. النتائج الفعلية لهذه النسخة موثقة في `QA-REPORT.md`.

## Cron

لا يحتاج الموقع إلى Cron حالياً. إذا أضيف لاحقاً تنظيف دوري للسجلات أو جدولة نشر، يجب توفير أمر مستقل محمي يعمل من CLI فقط قبل إضافة مهمة Cron.

## النشر والتحديث

- خذ نسخة احتياطية قبل كل تحديث.
- ارفع الملفات الجديدة مع الحفاظ على `.env` و`storage/uploads`.
- لا ترفع حسابات أو كلمات مرور داخل `database/seed.sql`.
- راجع `CONTENT-GAPS.md` قبل تفعيل الأخبار أو المعرض أو الأسئلة الشائعة.
- بعد النشر امسح Cache الاستضافة/CDN واختبر العربية والإنجليزية والوضعين والنموذج ولوحة التحكم على HTTPS.
