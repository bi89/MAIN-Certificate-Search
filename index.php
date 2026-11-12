/**
 * MAIN / AIEAT Certificate Search - Version 2
 * Paste into Code Snippets as PHP (WITHOUT opening <?php).
 * Shortcode: [aieat_certificate_search]
 * Searches Thai/English person names, company names, and certificate IDs.
 */
add_shortcode('aieat_certificate_search', function () {
    global $wpdb;

    // The table has wp_ prefix even though WordPress itself uses wpli_.
    $table = 'wp_aieat_certificates';
    $param = 'aieat_cert_q';
    $submitted = isset($_GET[$param]) && is_string($_GET[$param]);
    $search = $submitted
        ? trim(sanitize_text_field(wp_unslash($_GET[$param])))
        : '';

    // Normalize text for Thai/English substring matching in PHP.
    // For the current small dataset, this avoids database collation differences.
    $normalize = static function ($value) {
        $value = (string) $value;
        if (class_exists('Normalizer')) {
            $normalized = Normalizer::normalize($value, Normalizer::FORM_C);
            if ($normalized !== false) {
                $value = $normalized;
            }
        }
        $value = preg_replace('/[\s\p{Z}\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $value);
        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    };

    $compact_search = $normalize($search);
    $length = function_exists('mb_strlen')
        ? mb_strlen($compact_search, 'UTF-8')
        : preg_match_all('/./u', $compact_search);
    $too_short = ($length < 5);
    $too_long = ($length > 100);
    $results = [];
    $db_error = false;
    $total = 0;

    if ($submitted && $search !== '' && !$too_short && !$too_long) {
        // There are currently 93 records; fetch once and match in PHP.
        // No user input is inserted into SQL.
        $rows = $wpdb->get_results(
            "SELECT certificate_id, cert_type, person_name, company_name
             FROM `{$table}`
             ORDER BY company_name, person_name"
        );
        $db_error = ($wpdb->last_error !== '');

        if (!$db_error && is_array($rows)) {
            foreach ($rows as $row) {
                $fields = [
                    $row->certificate_id,
                    $row->person_name,
                    $row->company_name
                ];
                foreach ($fields as $field) {
                    if (strpos($normalize($field), $compact_search) !== false) {
                        $results[] = $row;
                        break;
                    }
                }
            }
            // Place exact ID matches first.
            usort($results, static function ($a, $b) use ($search) {
                $exact_a = strcasecmp((string) $a->certificate_id, $search) === 0;
                $exact_b = strcasecmp((string) $b->certificate_id, $search) === 0;
                return (int) $exact_b <=> (int) $exact_a;
            });
            $total = count($results);
            $results = array_slice($results, 0, 50);
        }
    }

    $input_id = wp_unique_id('aieat-cert-search-');
    $action = get_permalink(get_queried_object_id());
    if (!$action) {
        $action = home_url('/');
    }

    ob_start();
    ?>
    <div class="aieat-cert-search">
        <style>
        .aieat-cert-search{max-width:900px;margin:24px auto;font:inherit}
        .aieat-cert-search *{box-sizing:border-box}
        .aieat-cert-search .cert-form{display:flex;gap:10px;margin:18px 0 8px}
        .aieat-cert-search .cert-input{flex:1;min-width:0;padding:13px 16px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#111827;font:inherit}
        .aieat-cert-search .cert-btn{padding:12px 22px;border:0;border-radius:9px;background:#175cd3;color:white;font:inherit;font-weight:600;cursor:pointer}
        .aieat-cert-search .cert-btn:hover{background:#1849a9}
        .aieat-cert-search .cert-help{margin:0 0 20px;color:#667085;font-size:14px}
        .aieat-cert-search .cert-info{margin:20px 0;color:#475467}
        .aieat-cert-search .cert-error{padding:13px;background:#fff5f5;color:#9b1c1c;border-radius:8px}
        .aieat-cert-search .cert-results{display:grid;gap:14px}
        .aieat-cert-search .cert-card{padding:20px;border:1px solid #e4e7ec;border-radius:12px;background:#fff}
        .aieat-cert-search .cert-meta{display:flex;flex-wrap:wrap;gap:8px 12px;align-items:center;margin-bottom:10px}
        .aieat-cert-search .cert-type{background:#eff4ff;color:#1849a9;border-radius:20px;padding:3px 10px;font-size:13px}
        .aieat-cert-search .cert-id{font-family:monospace;color:#667085;font-size:14px}
        .aieat-cert-search .cert-card h3{margin:0 0 6px;font-size:19px;line-height:1.5;color:#101828}
        .aieat-cert-search .cert-company{margin:0 0 14px;color:#475467}
        .aieat-cert-search .cert-link{display:inline-block;padding:9px 13px;border:1px solid #175cd3;border-radius:7px;color:#175cd3;text-decoration:none;font-size:14px;font-weight:600}
        .aieat-cert-search .cert-link:hover{background:#eff4ff}
        @media(max-width:540px){.aieat-cert-search .cert-form{flex-direction:column}.aieat-cert-search .cert-btn{width:100%}}
        </style>
        <h2>ค้นหาเกียรติบัตร</h2>
        <form class="cert-form" method="get" action="<?php echo esc_url($action); ?>" role="search">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">ค้นหาเกียรติบัตร</label>
            <input id="<?php echo esc_attr($input_id); ?>" class="cert-input" type="search"
                name="<?php echo esc_attr($param); ?>"
                value="<?php echo esc_attr($search); ?>"
                minlength="5" maxlength="100"
                placeholder="ชื่อบุคคล ชื่อบริษัท หรือ Certificate ID" required>
            <button type="submit" class="cert-btn">ค้นหา</button>
        </form>
        <p class="cert-help">ตัวอย่าง: สุชาธิษณ์, Interroot หรือ A000000 (อย่างน้อย 5 ตัวอักษร)</p>

        <?php if ($submitted): ?>
            <?php if ($search === '' || $too_short): ?>
                <p class="cert-info">กรุณากรอกคำค้นหาอย่างน้อย 5 ตัวอักษร</p>
            <?php elseif ($too_long): ?>
                <p class="cert-error">คำค้นหายาวเกินไป (สูงสุด 100 ตัวอักษร)</p>
            <?php elseif ($db_error): ?>
                <p class="cert-error">ไม่สามารถค้นหาข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>
            <?php elseif ($total === 0): ?>
                <p class="cert-info">ไม่พบเกียรติบัตรที่ตรงกับ “<?php echo esc_html($search); ?>”</p>
            <?php else: ?>
                <p class="cert-info">พบ <?php echo esc_html((string) $total); ?> รายการ<?php echo $total > 50 ? ' (แสดง 50 รายการแรก)' : ''; ?></p>
                <div class="cert-results">
                    <?php foreach ($results as $row):
                        $is_person = $row->cert_type === 'person';
                        $id = strtoupper((string) $row->certificate_id);
                        $valid_id = preg_match('/\A[A-F0-9]{8}\z/', $id);
                        $pdf_url = 'https://main.or.th/cert/files/' . rawurlencode($id) . '.pdf';
                    ?>
                        <article class="cert-card">
                            <div class="cert-meta">
                                <span class="cert-type"><?php echo $is_person ? 'เกียรติบัตรบุคคล' : 'เกียรติบัตรบริษัท'; ?></span>
                                <span class="cert-id"><?php echo esc_html($id); ?></span>
                            </div>
                            <h3><?php echo esc_html($is_person ? (string) $row->person_name : (string) $row->company_name); ?></h3>
                            <?php if ($is_person): ?>
                                <p class="cert-company"><?php echo esc_html((string) $row->company_name); ?></p>
                            <?php endif; ?>
                            <?php if ($valid_id): ?>
                                <a class="cert-link" target="_blank" rel="noopener noreferrer"
                                   href="<?php echo esc_url($pdf_url); ?>">ดู / ดาวน์โหลดเกียรติบัตร (PDF)</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});
