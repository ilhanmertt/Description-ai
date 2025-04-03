console.log("✅ admin.js loaded");

// 🔁 Bulk AI Generation function
// 🔁 Bulk AI Generation function (Admin Panel)
function runBulkGeneration(type, mode) {
    if (!confirm(`This will generate ${mode === 'empty' ? 'new' : 'all'} ${type} descriptions. Continue?`)) return;

    const buttonId = `#apdg-bulk-${type}-${mode}`;
    const button = jQuery(buttonId);
    button.text('🧠 Processing...');
    button.prop('disabled', true);

    jQuery.ajax({
        type: 'POST',
        url: apdg_ajax_object.ajax_url,
        data: {
            action: 'apdg_bulk_generate',
            nonce: apdg_ajax_object.nonce,
            content_type: type,
            mode: mode
        },
        success: function (response) {
            console.groupCollapsed(`🧠 Bulk AI Generation (${type.toUpperCase()} - ${mode.toUpperCase()})`);

            if (response.success) {
                console.log("✅ Status: SUCCESS");
                console.log("🟢 Message:", response.data.message || response.data);

                if (Array.isArray(response.data.responses)) {
                    response.data.responses.forEach((item, index) => {
                        console.groupCollapsed(`📦 ${index + 1}. ${item.title}`);
                        console.log(item.content);
                        console.groupEnd();
                    });
                }

                const msg = typeof response.data === 'object' && response.data.message
                    ? response.data.message
                    : response.data;

                alert('✅ ' + msg);
            } else {
                console.log("❌ Status: FAILED");
                console.error("🛑 Error:", response.data);
                alert(`❌ Failed: ${response.data}`);
            }

            console.groupEnd();

            button.text(mode === 'empty' ? '🧠 Only for empty fields' : '🔁 Regenerate all');
            button.prop('disabled', false);
        },
        error: function (xhr, status, error) {
            console.groupCollapsed("❌ Bulk AI AJAX Error");
            console.error("XHR:", xhr);
            console.error("Status:", status);
            console.error("Error:", error);
            console.groupEnd();

            alert('❌ An error occurred.');
            button.text(mode === 'empty' ? '🧠 Only for empty fields' : '🔁 Regenerate all');
            button.prop('disabled', false);
        }
    });
}




jQuery(document).ready(function ($) {
    // === PRODUCT description ===
    $('#apdg-generate-btn').on('click', function () {
        const button = $(this);
        button.text('🧠 Generating description...');
        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: apdg_ajax_object.ajax_url,
            data: {
                action: 'apdg_generate_description',
                nonce: apdg_ajax_object.nonce,
                post_id: $('#post_ID').val()
            },
            success: function (response) {
                console.groupCollapsed("🧠 Single Product AI Response");
                console.log("✅ Success?", response.success);
                console.log("📦 Response Content:", response.data);
                console.log("🧾 Full JSON:", JSON.stringify(response, null, 2));
                console.groupEnd();
                
                if (response.success) {
                    const fullText = response.data;
                    console.log("🧠 AI Response:", fullText);

                    let longDesc = '';
                    let shortDesc = '';

                    const longMatch =
                        fullText.match(/\[LONG_DESCRIPTION\](.*?)\[SHORT_DESCRIPTION\]/s) ||
                        fullText.match(/\*\*LONG_DESCRIPTION\*\*\s*```([\s\S]*?)```/);

                    const shortMatch =
                        fullText.match(/\[SHORT_DESCRIPTION\](.*)/s) ||
                        fullText.match(/\*\*SHORT_DESCRIPTION\*\*\s*```([\s\S]*?)```/);


                    if (longMatch && longMatch[1]) {
                        longDesc = longMatch[1].trim();
                    }

                    if (shortMatch && shortMatch[1]) {
                        shortDesc = shortMatch[1].trim();
                    }

                    if (longDesc) {
                        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                            tinymce.get('content').setContent(longDesc);
                        } else {
                            $('#content').val(longDesc);
                        }
                    }

                    if (shortDesc) {
                        if (typeof tinymce !== 'undefined' && tinymce.get('excerpt')) {
                            tinymce.get('excerpt').setContent(shortDesc);
                        } else {
                            $('#excerpt').val(shortDesc);
                        }
                    }

                    if (!longDesc && !shortDesc && fullText.length < 300) {
                        if (typeof tinymce !== 'undefined' && tinymce.get('excerpt')) {
                            tinymce.get('excerpt').setContent(fullText.trim());
                        } else {
                            $('#excerpt').val(fullText.trim());
                        }
                    }

                    if (!longDesc && !shortDesc && fullText.length >= 300) {
                        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                            tinymce.get('content').setContent(fullText.trim());
                        } else {
                            $('#content').val(fullText.trim());
                        }
                    }

                } else {
                    alert('Failed to generate description: ' + response.data);
                }

                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            },
            error: function () {
                alert('An error occurred.');
                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            }
        });
    });

    // === CATEGORY description ===
    $('#apdg-generate-category-btn').on('click', function () {
        const button = $(this);
        const termId = button.data('term-id');

        button.text('🧠 Generating description...');
        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: apdg_ajax_object.ajax_url,
            data: {
                action: 'apdg_generate_category_description',
                nonce: apdg_ajax_object.nonce,
                term_id: termId
            },
            success: function (response) {
                if (response.success) {
                    $('textarea#description').val(response.data);
                } else {
                    alert('Failed to generate category description: ' + response.data);
                }

                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            },
            error: function () {
                alert('An error occurred.');
                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            }
        });
    });

    // === BRAND description ===
    $('#apdg-generate-brand-btn').on('click', function () {
        const button = $(this);
        const termId = button.data('term-id');

        button.text('🧠 Generating description...');
        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: apdg_ajax_object.ajax_url,
            data: {
                action: 'apdg_generate_brand_description',
                nonce: apdg_ajax_object.nonce,
                term_id: termId
            },
            success: function (response) {
                if (response.success) {
                    $('textarea#description').val(response.data);
                } else {
                    alert('Failed to generate brand description: ' + response.data);
                }

                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            },
            error: function () {
                alert('An error occurred.');
                button.text('🧠 Write with AI');
                button.prop('disabled', false);
            }
        });
    });

    // === BULK buttons ===
    $('#apdg-bulk-product-empty').on('click', function () {
        runBulkGeneration('product', 'empty');
    });
    $('#apdg-bulk-product-overwrite').on('click', function () {
        runBulkGeneration('product', 'overwrite');
    });

    $('#apdg-bulk-category-empty').on('click', function () {
        runBulkGeneration('category', 'empty');
    });
    $('#apdg-bulk-category-overwrite').on('click', function () {
        runBulkGeneration('category', 'overwrite');
    });

    $('#apdg-bulk-brand-empty').on('click', function () {
        runBulkGeneration('brand', 'empty');
    });
    $('#apdg-bulk-brand-overwrite').on('click', function () {
        runBulkGeneration('brand', 'overwrite');
    });
});
