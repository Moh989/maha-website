<?php
$isAr = locale() === 'ar';
$fallbackSlides = [
    ['title_ar' => 'حلول مترابطة لقطاع النفط والصناعة', 'title_en' => 'Connected solutions for oil and industry', 'subtitle_ar' => 'المنتج والمادة والخدمة والتنسيق التشغيلي ضمن إطار واحد.', 'subtitle_en' => 'Product, material, service and operational coordination under one framework.', 'image_path' => 'refinery-landscape.jpg', 'cta_url' => '/about'],
];
$slides = $sliders ?: $fallbackSlides;
?>
<section class="hero" data-slider tabindex="0" aria-roledescription="carousel" aria-label="<?= $isAr ? 'أبرز مجالات الشركة' : 'Company highlights' ?>">
    <div class="hero-rail" data-slider-rail>
        <?php foreach ($slides as $index => $slide): ?>
            <article class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                <picture class="hero-media">
                    <?php $base = pathinfo($slide['image_path'], PATHINFO_FILENAME); ?>
                    <source srcset="<?= e(url('/assets/images/' . $base . '.avif')) ?>" type="image/avif">
                    <source srcset="<?= e(url('/assets/images/' . $base . '.webp')) ?>" type="image/webp">
                    <img src="<?= e(url('/assets/images/' . $slide['image_path'])) ?>" width="1800" height="1200" alt="" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                </picture>
                <div class="hero-overlay"></div>
                <div class="hero-grid" aria-hidden="true"></div>
                <div class="hero-content">
                    <p class="eyebrow"><span></span><?= e(__('company_since')) ?></p>
                    <h1><?= e(localized_field($slide, 'title')) ?></h1>
                    <p class="hero-lead"><?= e(localized_field($slide, 'subtitle')) ?></p>
                    <div class="hero-actions"><a class="button primary" href="<?= e(localized_url(ltrim($slide['cta_url'] ?: '/about', '/'))) ?>"><?= e(__('learn_more')) ?> <span aria-hidden="true">↗</span></a><a class="button ghost" href="<?= e(localized_url('/company-profile')) ?>"><?= e(__('download_profile')) ?></a></div>
                </div>
                <div class="hero-index" aria-hidden="true"><strong><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></strong><span></span><small><?= str_pad((string) count($slides), 2, '0', STR_PAD_LEFT) ?></small></div>
            </article>
        <?php endforeach; ?>
    </div>
    <noscript><style>.hero-slide:first-child{opacity:1;visibility:visible}.hero-slide:not(:first-child){display:none}</style></noscript>
    <div class="hero-controls">
        <button type="button" data-slider-prev aria-label="<?= $isAr ? 'الشريحة السابقة' : 'Previous slide' ?>">←</button>
        <div class="hero-pagination" data-slider-pagination></div>
        <button type="button" data-slider-next aria-label="<?= $isAr ? 'الشريحة التالية' : 'Next slide' ?>">→</button>
    </div>
    <a class="hero-scroll" href="#company-intro"><span><?= $isAr ? 'مرّر للاستكشاف' : 'Scroll to explore' ?></span><i></i></a>
</section>

<section class="intro-section section" id="company-intro">
    <div class="section-number">01</div>
    <div class="section-heading reveal">
        <p class="eyebrow dark"><span></span><?= $isAr ? 'نبذة مؤسسية' : 'Corporate Overview' ?></p>
        <h2><?= $isAr ? 'خبرة راسخة، ونطاق عمل متكامل.' : 'Established experience. Integrated scope.' ?></h2>
    </div>
    <div class="intro-layout">
        <div class="intro-statement reveal">
            <p><?= $isAr
                ? 'تأسست شركة المها في بغداد في حزيران 2016 كشركة عراقية خاصة، وتمتلك خبرة واسعة في صناعة النفط وإنتاج الأسفلت المؤكسد وزيوت التزييت، مدعومة بخبراء فنيين وروّاد تزيد خبرتهم في العمليات الميدانية والإدارة النفطية على 25 عاماً.'
                : 'Al-Maha was established in Baghdad in June 2016 as a private Iraqi company, with broad experience in the oil industry, oxidized asphalt and lubricating oils, supported by technical experts and pioneers with more than 25 years of field and oil-sector management experience.' ?></p>
            <a class="text-link" href="<?= e(localized_url('/about')) ?>"><?= e(__('learn_more')) ?> <span aria-hidden="true">↗</span></a>
        </div>
        <div class="fact-panel reveal">
            <div><strong data-counter="2016">2016</strong><span><?= $isAr ? 'سنة التأسيس' : 'Year established' ?></span></div>
            <div><strong data-counter="2">2</strong><span><?= $isAr ? 'مليار دينار عراقي رأس المال' : 'Billion IQD capital' ?></span></div>
            <div><strong><b data-counter="25">25</b>+</strong><span><?= $isAr ? 'عاماً من خبرة الروّاد' : 'Years of pioneer experience' ?></span></div>
        </div>
    </div>
</section>

<section class="business-section section dark-section">
    <div class="industrial-lines" aria-hidden="true"></div>
    <div class="section-number">02</div>
    <div class="section-heading reveal">
        <p class="eyebrow"><span></span><?= $isAr ? 'خطوط الأعمال' : 'Business Lines' ?></p>
        <h2><?= $isAr ? 'من المنتج إلى استمرارية التشغيل.' : 'From product to operational continuity.' ?></h2>
        <p><?= $isAr ? 'مجالات قابلة للتطوير والتخصيص حسب طبيعة العقد والموقع والجهة المستفيدة.' : 'Business lines structured to adapt to the contract, site and beneficiary.' ?></p>
    </div>
    <div class="business-list">
        <?php foreach ($services as $index => $service): ?>
            <a class="business-row reveal" href="<?= e(localized_url('/' . $service['slug'])) ?>">
                <span class="business-num"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <h3><?= e(localized_field($service, 'title')) ?></h3>
                <p><?= e(localized_field($service, 'summary')) ?></p>
                <i aria-hidden="true">↗</i>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="product-story asphalt-story section">
    <div class="product-visual reveal-image">
        <picture><source srcset="<?= e(url('/assets/images/asphalt-road.avif')) ?>" type="image/avif"><source srcset="<?= e(url('/assets/images/asphalt-road.webp')) ?>" type="image/webp"><img src="<?= e(url('/assets/images/asphalt-road.jpg')) ?>" width="1600" height="1200" loading="lazy" alt="<?= $isAr ? 'معدات رصف الأسفلت في موقع عمل صناعي' : 'Asphalt paving equipment at an industrial worksite' ?>"></picture>
        <span class="media-caption">OXIDIZED ASPHALT / 01</span>
    </div>
    <div class="product-copy reveal">
        <p class="eyebrow dark"><span></span><?= $isAr ? 'الأسفلت المؤكسد' : 'Oxidized Asphalt' ?></p>
        <h2><?= $isAr ? 'إنتاج وتسويق للاستخدامات الصناعية والإنشائية والعزل.' : 'Production and marketing for industrial, construction and insulation uses.' ?></h2>
        <p><?= $isAr ? 'يشمل نطاق الشركة إنتاج أصناف الأسفلت المؤكسد وتسويقها داخل العراق، مع قابلية تصدير الفائض وفق القوانين والتعليمات النافذة.' : 'The company scope covers the production and marketing of oxidized asphalt grades inside Iraq, with the ability to export surplus in accordance with applicable laws and regulations.' ?></p>
        <a class="button outline" href="<?= e(localized_url('/oxidized-asphalt')) ?>"><?= e(__('learn_more')) ?> <span aria-hidden="true">↗</span></a>
    </div>
</section>

<section class="lubricants-section section">
    <div class="liquid-orbit" aria-hidden="true"></div>
    <div class="lubricants-copy reveal">
        <span class="outline-word">LUBRICANTS</span>
        <p class="eyebrow"><span></span><?= $isAr ? 'زيوت التزييت' : 'Lubricating Oils' ?></p>
        <h2><?= $isAr ? 'وحدات إنتاج، معالجة وتجهيز.' : 'Production units, processing and supply.' ?></h2>
        <p><?= $isAr ? 'يشمل النشاط نصب وصيانة وحدات إنتاج الزيوت والدهون وتجهيز ومعالجة منتجات الزيوت الخفيفة والثقيلة بما يدعم احتياجات القطاعين النفطي والصناعي.' : 'Activities include installing and maintaining oil and grease production units and supplying and treating light and heavy oil products in support of oil and industrial sector needs.' ?></p>
        <a class="text-link light" href="<?= e(localized_url('/lubricating-oils')) ?>"><?= e(__('learn_more')) ?> <span aria-hidden="true">↗</span></a>
    </div>
    <div class="lubricants-image reveal-image"><picture><source srcset="<?= e(url('/assets/images/industrial-supply.avif')) ?>" type="image/avif"><source srcset="<?= e(url('/assets/images/industrial-supply.webp')) ?>" type="image/webp"><img src="<?= e(url('/assets/images/industrial-supply.jpg')) ?>" width="1800" height="1200" loading="lazy" alt="<?= $isAr ? 'عامل يتعامل مع براميل صناعية داخل مستودع' : 'Industrial worker handling drums in a warehouse' ?>"></picture></div>
</section>

<section class="capabilities-section section">
    <div class="section-number">03</div>
    <div class="section-heading reveal"><p class="eyebrow dark"><span></span><?= $isAr ? 'القدرة المؤسسية' : 'Corporate Capacity' ?></p><h2><?= $isAr ? 'جاهزية تُبنى حول متطلبات العمل.' : 'Readiness built around operating requirements.' ?></h2></div>
    <div class="capability-grid">
        <?php
        $caps = $isAr ? [
            ['قابلية النمو', 'توسيع تدريجي لخطوط الإنتاج والتوريد والخدمات بحسب الطلب والتمويل والشراكات الفنية.'],
            ['الجاهزية المؤسسية', 'إعداد ملفات التأهيل وإجراءات التشغيل وخطط الجودة والسلامة وسجلات الأداء.'],
            ['نقطة قوة رئيسية', 'نطاق واسع يغطي المنتجات والمواد النفطية والخدمات المساندة ضمن إطار تعاون واحد.'],
            ['قيمة للقطاع النفطي', 'تقليل فجوات التجهيز والخدمات غير الأساسية ودعم استمرارية العمليات.'],
        ] : [
            ['Growth Capacity', 'Gradual expansion of production, supply and services according to demand, financing and technical partnerships.'],
            ['Corporate Readiness', 'Pre-qualification files, operating procedures, quality and safety plans, and performance records.'],
            ['Core Strength', 'A broad scope covering petroleum products, materials and support services under one cooperation framework.'],
            ['Value to the Oil Sector', 'Reducing supply and non-core service gaps while supporting operational continuity.'],
        ];
        foreach ($caps as $index => [$heading, $text]): ?>
            <article class="capability-card reveal"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($heading) ?></h3><p><?= e($text) ?></p></article>
        <?php endforeach; ?>
    </div>
    <a class="text-link" href="<?= e(localized_url('/capabilities')) ?>"><?= $isAr ? 'استكشف القدرات' : 'Explore capabilities' ?> <span aria-hidden="true">↗</span></a>
</section>

<section class="framework-section section dark-section">
    <div class="section-number">04</div>
    <div class="section-heading reveal"><p class="eyebrow"><span></span><?= $isAr ? 'إطار العمل' : 'Operating Framework' ?></p><h2><?= $isAr ? 'أربعة أبعاد، مسار واحد.' : 'Four dimensions. One operating path.' ?></h2></div>
    <div class="framework-track">
        <?php $framework = $isAr ? [
            ['البعد التجاري', 'تسويق محلي وتصدير للفائض واستيراد المواد الداخلة في النشاط.'],
            ['البعد الإنتاجي', 'إنتاج الأسفلت المؤكسد والزيوت والمنتجات المرتبطة بالطاقة والصناعة.'],
            ['البعد القانوني', 'تنفيذ الأعمال وفق القوانين والتعليمات النافذة وإعداد ملفات الامتثال.'],
            ['البعد الخدمي', 'إدارة مرافق وتشغيل وصيانة ونظافة وضيافة للمواقع والمجمعات.'],
        ] : [
            ['Commercial', 'Local marketing, export of surplus and import of input materials.'],
            ['Production', 'Oxidized asphalt, oils and products connected to energy and industry.'],
            ['Legal', 'Execution under applicable laws, regulations and contract compliance files.'],
            ['Service', 'Facilities management, operations, maintenance, cleaning and hospitality support.'],
        ]; foreach ($framework as $index => [$heading, $text]): ?>
            <article class="framework-step reveal"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h3><?= e($heading) ?></h3><p><?= e($text) ?></p></div></article>
        <?php endforeach; ?>
    </div>
</section>

<section class="sectors-section section">
    <div class="sector-visual reveal-image"><picture><source srcset="<?= e(url('/assets/images/refinery-tower.avif')) ?>" type="image/avif"><source srcset="<?= e(url('/assets/images/refinery-tower.webp')) ?>" type="image/webp"><img src="<?= e(url('/assets/images/refinery-tower.jpg')) ?>" width="800" height="1200" loading="lazy" alt="<?= $isAr ? 'برج معالجة في منشأة نفطية' : 'Processing tower at an oil facility' ?>"></picture></div>
    <div class="sector-copy">
        <div class="section-heading reveal"><p class="eyebrow dark"><span></span><?= $isAr ? 'القطاعات المستفيدة' : 'Sectors Served' ?></p><h2><?= $isAr ? 'حلول تتقاطع مع البنية التحتية والصناعة.' : 'Solutions spanning infrastructure and industry.' ?></h2></div>
        <div class="sector-list reveal">
            <?php $sectors = $isAr ? ['شركات النفط والمصافي', 'مقاولو البنى التحتية والطرق', 'المنشآت الصناعية', 'الجهات الحكومية وشبه الحكومية', 'القطاع الخاص وإدارة المرافق'] : ['Oil companies and refineries', 'Infrastructure and road contractors', 'Industrial facilities', 'Government and semi-government entities', 'Private sector and facilities management']; foreach ($sectors as $index => $sector): ?>
                <div><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($sector) ?></h3></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="values-band section">
    <div class="values-intro reveal"><p class="eyebrow"><span></span><?= $isAr ? 'قيم مؤسسية' : 'Corporate Values' ?></p><h2><?= $isAr ? 'السلامة أولاً. جودة قابلة للقياس.' : 'Safety first. Measurable quality.' ?></h2><p><?= $isAr ? 'لا يبدأ أي عمل موقعي أو تشغيلي دون فهم المخاطر والسيطرة عليها؛ وترتبط المنتجات والخدمات بمواصفات وإجراءات فحص وسجلات قبول ومسؤوليات واضحة.' : 'No site or operational activity begins without understanding and controlling risks; products and services are linked to specifications, inspection procedures, acceptance records and clear responsibilities.' ?></p></div>
    <div class="values-list">
        <?php $values = $isAr ? ['السلامة أولاً', 'النزاهة والشفافية', 'الجودة القابلة للقياس', 'الاستجابة السريعة', 'العمل بروح الفريق', 'التحسين المستمر'] : ['Safety First', 'Integrity & Transparency', 'Measurable Quality', 'Rapid Response', 'Teamwork', 'Continuous Improvement']; foreach ($values as $index => $value): ?><div class="reveal"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= e($value) ?></strong></div><?php endforeach; ?>
    </div>
</section>

<?php if ($projects): ?>
<section class="projects-preview section">
    <div class="section-heading reveal"><p class="eyebrow dark"><span></span><?= $isAr ? 'عقود موثقة' : 'Documented Contracts' ?></p><h2><?= $isAr ? 'اعمال الشركة' : 'Company Work' ?></h2></div>
    <div class="project-preview-grid">
        <?php foreach ($projects as $index => $project): ?><article class="project-preview-card reveal"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e(localized_field($project, 'title')) ?></h3><p><?= e(localized_field($project, 'summary')) ?></p></article><?php endforeach; ?>
    </div>
    <a class="button outline" href="<?= e(localized_url('/projects')) ?>"><?= e(__('projects')) ?> <span aria-hidden="true">↗</span></a>
</section>
<?php endif; ?>

<section class="cta-section">
    <picture><source srcset="<?= e(url('/assets/images/refinery-landscape.avif')) ?>" type="image/avif"><source srcset="<?= e(url('/assets/images/refinery-landscape.webp')) ?>" type="image/webp"><img src="<?= e(url('/assets/images/refinery-landscape.jpg')) ?>" width="1800" height="1200" loading="lazy" alt=""></picture>
    <div class="cta-overlay"></div><div class="cta-content reveal"><p class="eyebrow"><span></span><?= $isAr ? 'فرص التعاون' : 'Cooperation Opportunities' ?></p><h2><?= $isAr ? 'لنحوّل المتطلبات التشغيلية إلى إطار عمل واضح.' : 'Turn operating requirements into a clear framework.' ?></h2><a class="button primary" href="<?= e(localized_url('/contact')) ?>"><?= e(__('request_quote')) ?> <span aria-hidden="true">↗</span></a></div>
</section>
