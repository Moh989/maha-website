SET NAMES utf8mb4;

INSERT INTO roles (slug, name_ar, name_en) VALUES
('administrator', 'مدير عام', 'Administrator'),
('editor', 'محرر محتوى', 'Content Editor')
ON DUPLICATE KEY UPDATE name_ar = VALUES(name_ar), name_en = VALUES(name_en);

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('contact_phone', '+964 785 210 1010', 'contact'),
('contact_email', 'info@almaha-group.com', 'contact'),
('contact_address_ar', 'الجادرية - بغداد، العراق', 'contact'),
('contact_address_en', 'Al-Jadriya - Baghdad, Iraq', 'contact'),
('primary_color', '#D33633', 'design'),
('secondary_color', '#099BCC', 'design'),
('dark_color', '#030606', 'design'),
('analytics_id', '', 'integrations')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group);

INSERT INTO pages (title_ar, title_en, slug, excerpt_ar, excerpt_en, body_ar, body_en, template, hero_image, show_in_nav, status, sort_order, published_at) VALUES
('من نحن', 'About Al-Maha', 'about',
'شركة عراقية خاصة تأسست في بغداد في حزيران 2016 وتعمل ضمن نطاق متكامل يخدم القطاعين النفطي والصناعي.',
'A private Iraqi company established in Baghdad in June 2016 with an integrated scope serving the oil and industrial sectors.',
'تأسست شركة المها للخدمات النفطية وانتاج الاسفلت المؤكسد وزيت التزييت والخدمات العامة محدودة المسؤولية في بغداد في حزيران 2016 كشركة عراقية خاصة. تمتلك الشركة خبرة واسعة في مجال صناعة النفط وإنتاج الأسفلت المؤكسد وزيوت التزييت، مدعومة بمعرفة واحترافية مجموعة من الخبراء الفنيين وروّاد تزيد خبرتهم على 25 عاماً في العمليات الميدانية والمهارات الإدارية في القطاع النفطي. يساهم إشراك الموظفين كأصحاب مصلحة ذوي صلة في تمكين الكوادر وتعزيز الالتزام بالسلامة والتميّز والخدمات.\n\nيغطي نطاق الأعمال الخدمات النفطية وإنتاج وتسويق الأسفلت المؤكسد وزيوت التزييت وتجهيز المواد النفطية والخدمات العامة المتكاملة. يبلغ رأس مال الشركة 2,000,000,000 دينار عراقي، وتعمل داخل العراق مع قابلية تسويق وتصدير الفائض وفق القوانين والتعليمات النافذة.',
'Al-Maha Co. for Oil Services, Production of Oxidized Asphalt & Lubricating Oils and General Services Ltd was established in Baghdad in June 2016 as a private Iraqi company. The company brings extensive experience in the oil industry, oxidized asphalt and lubricating oils, supported by the knowledge and professionalism of technical experts and pioneers with more than 25 years of field operations and oil-sector management experience. Treating employees as relevant stakeholders empowers the workforce and reinforces commitment to safety, excellence and service.\n\nThe business scope covers oil services, oxidized asphalt production and marketing, lubricating oils, petroleum material supply and integrated general services. The company has a stated capital of IQD 2,000,000,000 and operates within Iraq, with the ability to market and export surplus products in accordance with applicable laws and regulations.',
'content', 'refinery-landscape.jpg', 1, 'published', 10, NOW()),

('الرؤية والرسالة والقيم', 'Vision, Mission & Values', 'vision-mission',
'رؤية الشركة ورسالتها والقيم التي توجه السلوك واتخاذ القرار.',
'The company vision, mission and values guiding behavior and decision-making.',
'الرؤية\nأن تكون شركة المها منصة عراقية رائدة في الخدمات النفطية والمنتجات الصناعية المساندة، قادرة على تزويد السوق بحلول موثوقة في الأسفلت المؤكسد وزيوت التزييت والتجهيزات والخدمات العامة وفق معايير تشغيلية عالية.\n\nالرسالة\nتقديم منتجات وخدمات نفطية وصناعية وعامة بكفاءة والتزام ومرونة، مع بناء علاقات طويلة الأمد مع العملاء والشركاء، وتعزيز القيمة المحلية عبر التشغيل المنظم والتطوير المستمر للقدرات.\n\nالقيم\nالتفاني، المساءلة، التعاون، الكفاءة، الريادة، استشراف المستقبل، احترام الجميع، المسؤولية، والالتزام الثابت بالصحة والسلامة والبيئة. وتشمل القيم المؤسسية: السلامة أولاً، النزاهة والشفافية، الجودة القابلة للقياس، الاستجابة السريعة، العمل بروح الفريق والتحسين المستمر.',
'VISION\nTo become a leading Iraqi platform in oil services and supporting industrial products, capable of supplying the market with reliable solutions in oxidized asphalt, lubricating oils, petroleum supplies and general services in accordance with high operating standards.\n\nMISSION\nTo provide oil, industrial and general services efficiently, responsibly and flexibly, while building long-term relationships with clients and partners and enhancing local value through structured operations and continuous capability development.\n\nVALUES\nDedication, accountability, collaboration, efficiency, leadership, foresight, respect, responsibility and a steadfast commitment to health, safety and the environment. Corporate values include safety first, integrity and transparency, measurable quality, rapid response, teamwork and continuous improvement.',
'content', NULL, 0, 'published', 20, NOW()),

('مجالات العمل', 'Business Lines', 'business-lines',
'مجالات عمل قابلة للتطوير والتخصيص حسب طبيعة العقد والموقع والجهة المستفيدة.',
'Business lines that can be developed and tailored to the contract, site and beneficiary.',
'تدار أنشطة الشركة ضمن مجالات أعمال قابلة للتطوير والتخصيص حسب طبيعة العقد والموقع والجهة المستفيدة، بما يتيح عرض قدرات الشركة بوضوح في ملفات التأهيل والمناقصات والاجتماعات المصرفية والاستثمارية.',
'The company activities are organized into business lines that can be developed and tailored to the nature of the contract, site and beneficiary, presenting the company capabilities clearly in pre-qualification files, tenders, and banking and investment meetings.',
'services', NULL, 1, 'published', 30, NOW()),

('الخدمات النفطية', 'Oil Services', 'oil-services',
'خدمات ومنتجات وتجهيزات مساندة تدعم احتياجات القطاع النفطي واستمرارية المواقع والمنشآت.',
'Services, products and supporting supplies for oil-sector needs and operational continuity.',
'تعمل الشركة ضمن نطاق يغطي الخدمات النفطية والمواد والمنتجات المساندة والتنسيق التشغيلي، بما يساهم في تقليل فجوات التجهيز والخدمات غير الأساسية ودعم استمرارية العمليات في المواقع والمرافق النفطية والصناعية. ويستند هذا النطاق إلى قدرة مؤسسية على إعداد ملفات التأهيل وإجراءات التشغيل وخطط الجودة والسلامة وسجلات الأداء وفق متطلبات العقود والجهات المستفيدة.',
'The company operates across oil services, supporting materials and products, and operational coordination. This scope helps reduce supply and non-core service gaps and supports continuity at oil and industrial sites. It is supported by corporate readiness to prepare pre-qualification files, operating procedures, quality and safety plans, and performance records in line with contract and beneficiary requirements.',
'content', NULL, 0, 'published', 40, NOW()),

('الأسفلت المؤكسد', 'Oxidized Asphalt', 'oxidized-asphalt',
'إنتاج وتسويق أصناف الأسفلت المؤكسد للاستخدامات الصناعية والإنشائية والعزل.',
'Production and marketing of oxidized asphalt grades for industrial, construction and insulation uses.',
'يشمل غرض الشركة إنتاج الأسفلت المؤكسد بكافة أنواعه وتسويقه داخل العراق، مع قابلية تصدير الفائض إلى الخارج وفق القوانين والتعليمات النافذة. ويخدم المنتج الاستخدامات الصناعية والإنشائية والعزل وفق المواصفات الفنية المعتمدة في العقود. كما يشمل نطاق النشاط استيراد المواد واللوازم الداخلة ضمن عمل الشركة وتنفيذ الأعمال المساندة.',
'The company purpose includes producing all types of oxidized asphalt and marketing them inside Iraq, with the ability to export surplus quantities abroad in accordance with applicable laws and regulations. The product serves industrial, construction and insulation uses under technical specifications established in contracts. The scope also covers importing materials and supplies required for the activity and executing supporting works.',
'content', 'asphalt-road.jpg', 0, 'published', 50, NOW()),

('زيوت التزييت', 'Lubricating Oils', 'lubricating-oils',
'نصب وصيانة وحدات إنتاج الزيوت والدهون وتجهيز ومعالجة منتجات الزيوت الخفيفة والثقيلة.',
'Installation and maintenance of oil and grease production units, and supply and treatment of light and heavy oil products.',
'يشمل نشاط الشركة نصب وصيانة وحدات إنتاج الزيوت والدهون، وتجهيز زيت الوقود الناتج من التقطير الجوي للنفط الخام، ومعالجة المنتجات النفطية التي تنتج من زيت التزييت الخفيف وزيت التزييت الثقيل والكازاويل الثقيل، بما يدعم احتياجات القطاع النفطي والصناعي. وترتبط المنتجات بالمواصفات وإجراءات الفحص وسجلات القبول والمسؤوليات الواضحة وفق متطلبات العقود.',
'The company activities include installing and maintaining oil and grease production units, supplying fuel oil produced from atmospheric distillation of crude oil, and treating petroleum products derived from light lubricating oil, heavy lubricating oil and heavy gasoil, in support of oil and industrial sector needs. Products are linked to specifications, inspection procedures, acceptance records and clear responsibilities according to contract requirements.',
'content', 'industrial-supply.jpg', 0, 'published', 60, NOW()),

('تجهيز المواد والمستلزمات النفطية', 'Petroleum Materials & Supplies', 'petroleum-supplies',
'توريد وتجهيز المواد الخاصة بالقطاع النفطي والمستلزمات الداخلة ضمن نشاط الشركة.',
'Supply of oil-sector materials and inputs connected to the company activities.',
'يشمل نطاق الشركة توريد وتجهيز المواد الخاصة بالقطاع النفطي والمستلزمات الداخلة في نشاطها، مع استيراد المواد واللوازم المطلوبة وفق القوانين والتعليمات النافذة. ويتيح النطاق الواسع للمواد والمنتجات والخدمات المساندة تلبية احتياجات متعددة ضمن عقد أو إطار تعاون واحد، بما يدعم استمرارية المواقع النفطية والصناعية.',
'The company scope includes supplying materials for the oil sector and inputs connected to its activities, including importing required materials and supplies in accordance with applicable laws and regulations. The broad scope of materials, products and support services enables multiple needs to be addressed under one contract or cooperation framework, supporting continuity at oil and industrial sites.',
'content', NULL, 0, 'published', 70, NOW()),

('الخدمات العامة', 'General Services', 'general-services',
'إدارة مرافق وتشغيل وصيانة وتنظيف وضيافة وخدمات تكميلية للمواقع والمجمعات.',
'Facilities management, operations, maintenance, cleaning, hospitality and complementary support.',
'يشمل نشاط الشركة إدارة المطاعم والفنادق والمجمعات السكنية والصناعية والإدارية والاجتماعية للدوائر الرسمية وشبه الرسمية والقطاع المختلط والخاص. كما يشمل خدمات الإدامة والصيانة والتصليحات والتشغيل والتنظيف والخبرات التكميلية التي تحقق نشاط الشركة وتدعم استمرارية العمليات في المواقع والمجمعات.',
'The company activities include managing restaurants, hotels, and residential, industrial, administrative and social complexes for official and semi-official entities, mixed-sector organizations and the private sector. The scope also covers maintenance, repairs, operations, cleaning and complementary expertise that supports the company activities and operational continuity at sites and complexes.',
'content', NULL, 0, 'published', 80, NOW()),

('القدرات التشغيلية', 'Operational Capabilities', 'capabilities',
'قابلية نمو وجاهزية مؤسسية ونطاق متكامل يدعم استمرارية العمليات.',
'Growth capacity, corporate readiness and an integrated scope supporting operational continuity.',
'قابلية النمو\nهيكل أعمال يسمح بتوسيع خطوط الإنتاج والتوريد والخدمات تدريجياً حسب الطلب والتمويل والشراكات الفنية.\n\nالجاهزية المؤسسية\nإمكانية بناء ملفات تأهيل وإجراءات تشغيل وخطط جودة وسلامة وسجلات أداء بطريقة تلائم متطلبات الشركات الكبرى والجهات الحكومية.\n\nنقطة قوة رئيسية\nنطاق عمل واسع يغطي مواد ومنتجات نفطية وخدمات مساندة، بما يعزز قدرة الشركة على تلبية احتياجات متعددة بعقد أو إطار تعاون واحد.\n\nالقيمة للقطاع النفطي\nتقليل فجوات التجهيز والخدمات غير الأساسية ودعم استمرارية العمليات في المواقع والمرافق النفطية والصناعية.',
'GROWTH CAPACITY\nA business structure supporting gradual expansion in production, supply and services according to demand, financing and technical partnerships.\n\nCORPORATE READINESS\nThe ability to build pre-qualification files, operating procedures, quality and safety plans, and performance records aligned with major companies and government entities.\n\nCORE STRENGTH\nA broad scope covering petroleum products, materials and support services, enabling multiple needs to be addressed under one contract or cooperation framework.\n\nVALUE TO THE OIL SECTOR\nReducing supply and non-core service gaps while supporting operational continuity at oil and industrial sites.',
'content', NULL, 0, 'published', 90, NOW()),

('القطاعات التي تخدمها الشركة', 'Sectors Served', 'sectors',
'جهات وقطاعات مستفيدة من نطاق المنتجات والمواد والخدمات والتنسيق التشغيلي.',
'Beneficiaries served through the company product, material, service and operational coordination scope.',
'تخدم القيمة الاستراتيجية للشركة عدة جهات مستفيدة تشمل شركات النفط والمصافي، ومقاولي البنى التحتية والطرق، والمنشآت الصناعية، والجهات الحكومية وشبه الحكومية، والقطاع الخاص الذي يحتاج إلى خدمات إدارة المرافق والتشغيل والصيانة والضيافة والخدمات المساندة.',
'The company strategic value serves multiple beneficiaries, including oil companies and refineries, infrastructure and road contractors, industrial facilities, government and semi-government entities, and private-sector organizations requiring facilities management, operations, maintenance, hospitality and support services.',
'content', NULL, 0, 'published', 100, NOW()),

('الجودة والسلامة والبيئة', 'Quality, Safety & Environment', 'quality-safety',
'قيم ومبادئ تشغيلية مثبتة في الملف التعريفي دون ادعاء شهادات أو اعتمادات غير منشورة.',
'Operating principles documented in the company profile, without claiming unpublished certificates or accreditations.',
'السلامة أولاً\nلا يبدأ أي عمل موقعي أو تشغيلي دون فهم المخاطر والسيطرة عليها، لأن سلامة العامل والموقع والبيئة هي أساس الاستمرارية.\n\nالجودة القابلة للقياس\nربط المنتج أو الخدمة بمواصفات وإجراءات فحص وسجلات قبول ومسؤوليات واضحة.\n\nالنزاهة والشفافية\nالتعامل بعقود واضحة وعروض دقيقة وفواتير ومستندات قابلة للتدقيق بما يحمي الشركة والعميل معاً.\n\nلا يورد الملف التعريفي شهادات ISO أو اعتمادات HSE محددة، لذلك لا تنسب هذه الصفحة إلى الشركة أي شهادة أو اعتماد غير منشور.',
'SAFETY FIRST\nNo site or operational activity should begin without understanding and controlling risks. The safety of people, the site and the environment is the foundation of continuity.\n\nMEASURABLE QUALITY\nLinking every product or service to specifications, inspection procedures, acceptance records and clear responsibilities.\n\nINTEGRITY AND TRANSPARENCY\nClear contracts, accurate offers, and auditable invoices and documents that protect both the company and the client.\n\nThe company profile does not list specific ISO certificates or HSE accreditations; this page therefore makes no such unpublished claim.',
'content', NULL, 0, 'published', 110, NOW()),

('العقود التي نفذتها الشركة', 'Contracts Executed by the Company', 'projects',
'العقود الواردة صراحة في الملف التعريفي الرسمي للشركة.',
'Contracts expressly included in the official company profile.',
'تظهر هنا العقود المدرجة في الملف التعريفي الرسمي فقط. لا تُعرض أسماء عملاء أو قيم تعاقدية أو نتائج غير منشورة.',
'Only contracts listed in the official company profile appear here. No unpublished client names, contract values or outcomes are displayed.',
'projects', NULL, 1, 'published', 120, NOW()),

('الشراكات وفرص التعاون', 'Partnerships & Cooperation', 'partnerships',
'إطار مرن للتعاون الفني والتشغيلي والاستثماري بما يدعم النمو والقيمة المحلية.',
'A flexible framework for technical, operational and investment cooperation supporting growth and local value.',
'يدعم هيكل أعمال الشركة التوسع التدريجي في الإنتاج والتوريد والخدمات حسب الطلب والتمويل والشراكات الفنية. وتتمثل رسالة الشركة في بناء علاقات طويلة الأمد مع العملاء والشركاء وتعزيز القيمة المحلية عبر التشغيل المنظم والتطوير المستمر للقدرات.\n\nيمكن أن تشمل فرص التعاون تطوير خطوط الإنتاج والتوريد والخدمات، إعداد أطر تعاون تلبي احتياجات متعددة، وبناء ملفات التأهيل والامتثال المناسبة لطبيعة العقد والجهة المستفيدة. تخضع أي فرصة لمتطلبات القوانين والتعليمات النافذة والمراجعة الفنية والتجارية والقانونية.',
'The company business structure supports gradual expansion in production, supply and services according to demand, financing and technical partnerships. Its mission emphasizes long-term relationships with clients and partners and stronger local value through structured operations and continuous capability development.\n\nCooperation opportunities may cover the development of production, supply and services, frameworks addressing multiple needs, and pre-qualification and compliance files tailored to the contract and beneficiary. Any opportunity remains subject to applicable laws, regulations, and technical, commercial and legal review.',
'content', NULL, 1, 'published', 130, NOW()),

('اتصل بنا', 'Contact Al-Maha', 'contact',
'للطلبات التجارية وفرص التعاون والاستفسارات الفنية والعامة.',
'For commercial requests, cooperation opportunities, and technical or general inquiries.',
'', '', 'contact', NULL, 1, 'published', 140, NOW()),

('سياسة الخصوصية', 'Privacy Policy', 'privacy',
'كيفية جمع واستخدام وحماية البيانات المقدمة عبر الموقع.',
'How information submitted through this website is collected, used and protected.',
'يجمع الموقع البيانات التي يقدمها المستخدم طوعاً عبر نموذج التواصل، وتشمل الاسم وبيانات الجهة والبريد والهاتف والدولة ونوع الطلب والخدمة والرسالة. تستخدم هذه البيانات للرد على الطلب، وإدارة المراسلات المهنية، وحماية الموقع من الإساءة. تحفظ الرسائل ضمن قاعدة بيانات الموقع ولا تباع لأطراف ثالثة. قد تستخدم بيانات تقنية محدودة مثل عنوان الشبكة بصورة مجزأة وسجل المتصفح لأغراض الأمان وتحديد معدل الإرسال. يمكن طلب تصحيح البيانات أو حذفها عبر البريد الرسمي للشركة، مع مراعاة الالتزامات النظامية وحفظ السجلات. لا يتضمن الموقع حالياً معرفاً لخدمة تحليلات خارجية، ويمكن تفعيلها لاحقاً بعد تحديث هذه السياسة عند الحاجة.',
'The website collects information voluntarily submitted through the contact form, including name, organization details, email, phone, country, request type, service and message. This information is used to respond to the request, manage professional correspondence and protect the website from abuse. Messages are stored in the website database and are not sold to third parties. Limited technical data, such as a hashed network address and browser information, may be used for security and rate limiting. Requests to correct or delete data may be sent to the company official email, subject to legal obligations and record-retention requirements. No external analytics identifier is currently embedded; such a service may be enabled later after this policy is updated as required.',
'content', NULL, 0, 'published', 150, NOW()),

('شروط الاستخدام', 'Terms of Use', 'terms',
'الشروط العامة لاستخدام محتوى الموقع وقنوات التواصل الرقمية.',
'General terms governing use of the website content and digital contact channels.',
'يعرض هذا الموقع معلومات مؤسسية مستندة إلى الملف التعريفي الرسمي للشركة. لا يشكل المحتوى عرضاً تعاقدياً أو التزاماً بالتوريد أو الإنتاج أو التصدير، وتخضع جميع الأعمال لعقود ومواصفات وموافقات وقوانين وتعليمات نافذة. لا يجوز نسخ الشعار أو استخدامه بطريقة توحي بوجود علاقة أو اعتماد دون إذن. الروابط أو الصور المرخصة من أطراف خارجية تخضع لشروط أصحابها كما هو موضح في ملف مصادر الصور. تُرسل الاستفسارات عبر النموذج على مسؤولية المستخدم من حيث دقة البيانات المقدمة.',
'This website presents corporate information based on the company official profile. The content is not a contractual offer or a commitment to supply, produce or export; all work is subject to contracts, specifications, approvals, and applicable laws and regulations. The logo may not be copied or used in a way that implies a relationship or endorsement without permission. Licensed third-party images are subject to their source terms as documented in the image sources file. Users are responsible for the accuracy of information submitted through the contact form.',
'content', NULL, 0, 'published', 160, NOW()),

('تحميل الملف التعريفي', 'Download Company Profile', 'company-profile',
'النسخة الرسمية الكاملة من الملف التعريفي للشركة لعام 2026.',
'The complete official 2026 company profile.',
'', '', 'profile', NULL, 0, 'published', 170, NOW()),

('معرض الصور', 'Media Gallery', 'gallery', 'صور معتمدة ومنشورة من إدارة الموقع.', 'Images reviewed and published by the website administration.', '', '', 'gallery', NULL, 0, 'hidden', 180, NULL),
('الأخبار والتحديثات', 'News & Updates', 'news', 'تُفعّل بعد إضافة أخبار حقيقية معتمدة.', 'Enabled after approved factual news is added.', '', '', 'content', NULL, 0, 'hidden', 190, NULL),
('الأسئلة الشائعة', 'Frequently Asked Questions', 'faq', 'تُفعّل بعد اعتماد الأسئلة والإجابات من الشركة.', 'Enabled after questions and answers are approved by the company.', '', '', 'content', NULL, 0, 'hidden', 200, NULL)
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), title_en = VALUES(title_en), excerpt_ar = VALUES(excerpt_ar), excerpt_en = VALUES(excerpt_en), body_ar = VALUES(body_ar), body_en = VALUES(body_en), template = VALUES(template), status = VALUES(status), sort_order = VALUES(sort_order), updated_at = NOW();

INSERT INTO services (title_ar, title_en, slug, summary_ar, summary_en, image_path, status, sort_order) VALUES
('الخدمات النفطية', 'Oil Services', 'oil-services', 'خدمات ومنتجات وتجهيزات مساندة للقطاع النفطي والمواقع الصناعية.', 'Services, products and supporting supplies for oil-sector and industrial sites.', 'refinery-landscape.jpg', 'published', 10),
('إنتاج الأسفلت المؤكسد', 'Oxidized Asphalt Production', 'oxidized-asphalt', 'إنتاج وتسويق أصناف الأسفلت المؤكسد للاستخدامات الصناعية والإنشائية والعزل.', 'Production and marketing of oxidized asphalt grades for industrial, construction and insulation uses.', 'asphalt-road.jpg', 'published', 20),
('زيوت التزييت', 'Lubricating Oils', 'lubricating-oils', 'نصب وصيانة وحدات إنتاج الزيوت والدهون وتجهيز ومعالجة المنتجات الخفيفة والثقيلة.', 'Installation and maintenance of oil and grease units, and supply and treatment of light and heavy products.', 'industrial-supply.jpg', 'published', 30),
('تجهيز المواد النفطية', 'Petroleum Materials Supply', 'petroleum-supplies', 'توريد وتجهيز المواد الخاصة بالقطاع النفطي والمستلزمات الداخلة في نشاط الشركة.', 'Supply of oil-sector materials and inputs connected to company activities.', 'refinery-tower.jpg', 'published', 40),
('الخدمات العامة', 'General Services', 'general-services', 'إدارة مرافق وتشغيل وصيانة وتنظيف وضيافة وخدمات تكميلية.', 'Facilities management, operations, maintenance, cleaning, hospitality and support.', NULL, 'published', 50)
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), title_en = VALUES(title_en), summary_ar = VALUES(summary_ar), summary_en = VALUES(summary_en), image_path = VALUES(image_path), status = VALUES(status), sort_order = VALUES(sort_order), updated_at = NOW();

INSERT INTO products (title_ar, title_en, slug, summary_ar, summary_en, image_path, status, sort_order) VALUES
('الأسفلت المؤكسد', 'Oxidized Asphalt', 'oxidized-asphalt-product', 'أصناف للاستخدامات الصناعية والإنشائية والعزل وفق المواصفات التعاقدية.', 'Grades for industrial, construction and insulation uses under contract specifications.', 'asphalt-road.jpg', 'published', 10),
('منتجات زيوت التزييت', 'Lubricating Oil Products', 'lubricating-oil-products', 'منتجات زيوت خفيفة وثقيلة ومعالجة مرتبطة باحتياجات القطاعين النفطي والصناعي.', 'Light and heavy oil products and treatment connected to oil and industrial sector needs.', 'industrial-supply.jpg', 'published', 20),
('الوقود والمنتجات الثقيلة', 'Fuel & Heavy Products', 'fuel-heavy-products', 'زيت الوقود الناتج من التقطير الجوي والكازاويل الثقيل وفق الطلبات والمواصفات التعاقدية.', 'Fuel oil from atmospheric distillation and heavy gasoil according to orders and contract specifications.', 'refinery-landscape.jpg', 'published', 30)
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), title_en = VALUES(title_en), summary_ar = VALUES(summary_ar), summary_en = VALUES(summary_en), image_path = VALUES(image_path), status = VALUES(status), sort_order = VALUES(sort_order), updated_at = NOW();

INSERT INTO projects (title_ar, title_en, summary_ar, summary_en, contract_date, image_path, status, sort_order) VALUES
('عقد بيع منتوج البنزين الطبيعي', 'Natural Gasoline Sale and Purchase Agreement', 'عقد مدرج بعنوانه ضمن قسم العقود التي نفذتها الشركة في الملف التعريفي الرسمي. لم تُنشر في الملخص العام قيمة العقد أو اسم العميل أو نتائج التنفيذ، لذلك لا تعرض هذه الصفحة تلك البيانات.', 'A contract listed by title under the contracts executed section of the official profile. The public summary does not publish the contract value, client name or execution outcomes, so this page does not display them.', NULL, NULL, 'published', 10),
('عقد تشغيل مشترك لمشروع معمل إنتاج وقود البواخر والأسفلت المؤكسد', 'Joint Operation Agreement for the Marine Fuel and Oxidized Asphalt Production Plant Project', 'عقد تشغيل مشترك مدرج بعنوانه في الملف التعريفي الرسمي. تُحجب التفاصيل غير الواردة في الملخص المؤسسي العام عن العرض على الموقع.', 'A joint operation agreement listed by title in the official profile. Details not stated in the general corporate summary are not displayed on the website.', NULL, NULL, 'published', 20)
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), summary_ar = VALUES(summary_ar), summary_en = VALUES(summary_en), status = VALUES(status), sort_order = VALUES(sort_order), updated_at = NOW();

INSERT INTO slider_items (title_ar, title_en, subtitle_ar, subtitle_en, image_path, cta_url, status, sort_order) VALUES
('حلول موثوقة لقطاع النفط والصناعة', 'Reliable solutions for oil and industry', 'منتجات ومواد وخدمات وتنسيق تشغيلي ضمن إطار مؤسسي واحد.', 'Products, materials, services and operational coordination within one corporate framework.', 'refinery-landscape.jpg', '/about', 'published', 10),
('الأسفلت المؤكسد للاستخدامات الصناعية والإنشائية', 'Oxidized asphalt for industrial and construction uses', 'إنتاج وتسويق داخل العراق مع قابلية تصدير الفائض وفق القوانين والتعليمات النافذة.', 'Production and marketing inside Iraq, with surplus export capability under applicable laws and regulations.', 'asphalt-road.jpg', '/oxidized-asphalt', 'published', 20),
('زيوت ومنتجات تدعم استمرارية التشغيل', 'Oils and products supporting operational continuity', 'نصب وصيانة وحدات الإنتاج وتجهيز ومعالجة المنتجات الخفيفة والثقيلة.', 'Production unit installation and maintenance, and supply and treatment of light and heavy products.', 'industrial-supply.jpg', '/lubricating-oils', 'published', 30)
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), subtitle_ar = VALUES(subtitle_ar), subtitle_en = VALUES(subtitle_en), image_path = VALUES(image_path), cta_url = VALUES(cta_url), status = VALUES(status), sort_order = VALUES(sort_order), updated_at = NOW();

INSERT INTO seo_metadata (entity_type, entity_id, title_ar, title_en, description_ar, description_en)
SELECT 'pages', id,
    CONCAT(title_ar, ' | شركة المها'),
    CONCAT(title_en, ' | Al-Maha Co.'),
    LEFT(excerpt_ar, 500),
    LEFT(excerpt_en, 500)
FROM pages
ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), title_en = VALUES(title_en), description_ar = VALUES(description_ar), description_en = VALUES(description_en), updated_at = NOW();
