<?php
if (!defined('ABSPATH')) {
    exit;
}

// === PRODUCT DESCRIPTION ===
add_action('wp_ajax_apdg_generate_description', 'apdg_generate_description_callback');

function apdg_generate_description_callback()
{
    check_ajax_referer('apdg_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'product') {
        wp_send_json_error('Invalid product.');
    }

    $product_title = $post->post_title;
    $api_key = "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";
    $lang = get_option('apdg_language') ?: 'English';
    $output_mode = get_option('apdg_output_mode', 'both');

    if ($output_mode === 'long') {
        $prompt = "Write a detailed, SEO-friendly long description in HTML format for the WooCommerce product titled '{$product_title}'. Include <p>, <strong>, and <ul><li> elements. Write the response in {$lang}.";
    } elseif ($output_mode === 'short') {
        $prompt = "Write a concise and engaging 1–2 sentence short description for the product titled '{$product_title}', suitable for product listing pages. Write the response in {$lang}.";
    } else {
        $prompt = "Generate both a long and short product description for the WooCommerce product '{$product_title}'.

Respond in this format exactly:

[LONG_DESCRIPTION]
Your long HTML-formatted, SEO-optimized product description here. Use <p>, <strong>, <ul><li> where needed.

[SHORT_DESCRIPTION]
Your short 1–2 sentence summary here.

Please only use the [LONG_DESCRIPTION] and [SHORT_DESCRIPTION] tags exactly as shown.
\n\nWrite the response in {$lang}.";
    }

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode(array(
            'model' => 'llama-3.3-70b-versatile',

            'messages' => array(
                array('role' => 'system', 'content' => 'You are an expert product copywriter and SEO specialist.'),
                array('role' => 'user', 'content' => $prompt)
            ),
            'temperature' => 0.7
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed.');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    error_log(print_r($data, true));

    if (isset($data['choices'][0]['message']['content'])) {
        wp_send_json_success($data['choices'][0]['message']['content']);
    } else {
        wp_send_json_error('Invalid API response.');
    }
}

// === CATEGORY DESCRIPTION ===
add_action('wp_ajax_apdg_generate_category_description', 'apdg_generate_category_description_callback');

function apdg_generate_category_description_callback()
{
    check_ajax_referer('apdg_nonce', 'nonce');

    $term_id = intval($_POST['term_id']);
    $term = get_term($term_id, 'product_cat');

    if (!$term || is_wp_error($term)) {
        wp_send_json_error('Invalid category.');
    }

    $category_name = $term->name;
    $lang = get_option('apdg_language') ?: 'English';
    $api_key = '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';

    $prompt = "You are an expert WooCommerce SEO content writer and HTML developer. Your task is to write an informative, SEO-optimized, and fully HTML-formatted description for the category '{$category_name}'.

Rules:
- Respond in pure HTML (no plain text)
- Start with a <p> introduction
- Include 1–2 subheadings using <strong>
- Use <ul><li> to list category benefits
- Use simple, user-friendly language
- Write the response in {$lang}.";

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode(array(
            'model' => 'llama-3.3-70b-versatile',
            'messages' => array(
                array('role' => 'system', 'content' => 'You are a skilled WooCommerce SEO content writer.'),
                array('role' => 'user', 'content' => $prompt)
            ),
            'temperature' => 0.7
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed.');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    error_log(print_r($data, true));

    if (isset($data['choices'][0]['message']['content'])) {
        wp_send_json_success($data['choices'][0]['message']['content']);
    } else {
        wp_send_json_error('Invalid API response.');
    }
}

// === BRAND DESCRIPTION ===
add_action('wp_ajax_apdg_generate_brand_description', 'apdg_generate_brand_description_callback');

function apdg_generate_brand_description_callback()
{
    check_ajax_referer('apdg_nonce', 'nonce');

    $term_id = intval($_POST['term_id']);
    $term = get_term($term_id, 'product_brand');

    if (!$term || is_wp_error($term)) {
        wp_send_json_error('Invalid brand.');
    }

    $brand_name = $term->name;
    $lang = get_option('apdg_language') ?: 'English';
    $api_key = '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';

    $prompt = "You are an experienced SEO copywriter and HTML content expert. Your task is to write a unique, SEO-optimized, user-friendly brand description for '{$brand_name}'.

Instructions:
- Response should be fully HTML formatted
- Start with a <p> paragraph
- Add sections with <strong> tags
- Use <ul><li> to list brand advantages
- Avoid technical jargon and keep it simple
- Write the response in {$lang}.";

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode(array(
            'model' => 'llama-3.3-70b-versatile',
            'messages' => array(
                array('role' => 'system', 'content' => 'You are an expert WooCommerce and SEO content creator.'),
                array('role' => 'user', 'content' => $prompt)
            ),
            'temperature' => 0.7
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed.');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    error_log(print_r($data, true));

    if (isset($data['choices'][0]['message']['content'])) {
        wp_send_json_success($data['choices'][0]['message']['content']);
    } else {
        wp_send_json_error('Invalid API response.');
    }
}
// === BULK GENERATION (Product, Category, Brand) ===
add_action('wp_ajax_apdg_bulk_generate', 'apdg_bulk_generate_callback');

function apdg_bulk_generate_callback()
{
    check_ajax_referer('apdg_nonce', 'nonce');

    $type = sanitize_text_field($_POST['content_type']); // product, category, brand
    $mode = sanitize_text_field($_POST['mode']);         // empty, overwrite
    $lang = get_option('apdg_language') ?: 'English';
    $output_mode = get_option('apdg_output_mode', 'both');
    $api_key = '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';

    $items = [];

    if ($type === 'product') {
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query($args);
        foreach ($query->posts as $product) {
            $has_content = trim($product->post_content);
            $has_excerpt = trim($product->post_excerpt);

            if ($mode === 'empty' && !$has_content && !$has_excerpt) {
                $items[] = $product;
            } elseif ($mode === 'overwrite') {
                $items[] = $product;
            }
        }
    }

    if ($type === 'category' || $type === 'brand') {
        $taxonomy = $type === 'category' ? 'product_cat' : 'product_brand';
        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]);
        foreach ($terms as $term) {
            $has_desc = trim($term->description);
            if ($mode === 'empty' && !$has_desc) {
                $items[] = $term;
            } elseif ($mode === 'overwrite') {
                $items[] = $term;
            }
        }
    }

    if (empty($items)) {
        wp_send_json_error('No content found to process.');
    }

    $success_count = 0;

    foreach ($items as $item) {
        $title = $type === 'product' ? $item->post_title : $item->name;

        // Prompt
        if ($type === 'product') {
            if ($output_mode === 'long') {
                $prompt = "Write a detailed, SEO-friendly long description in HTML format for the WooCommerce product titled '{$title}'. Include <p>, <strong>, and <ul><li> elements. Write the response in {$lang}.";
            } elseif ($output_mode === 'short') {
                $prompt = "Write a concise and engaging 1–2 sentence short description for the product titled '{$title}', suitable for product listing pages. Write the response in {$lang}.";
            } else {
                $prompt = "Generate both a long and short product description for the WooCommerce product '{$title}'.

Respond in this format exactly:

[LONG_DESCRIPTION]
Your long HTML-formatted, SEO-optimized product description here. Use <p>, <strong>, <ul><li> where needed.

[SHORT_DESCRIPTION]
Your short 1–2 sentence summary here.

Please only use the [LONG_DESCRIPTION] and [SHORT_DESCRIPTION] tags exactly as shown.
\n\nWrite the response in {$lang}.";
            }
        } elseif ($type === 'category') {
            $prompt = "You are an expert WooCommerce SEO content writer and HTML developer. Write an SEO-friendly and fully HTML-formatted description for the category '{$title}' using <p>, <strong>, <ul><li>. Write the response in {$lang}.";
        } else {
            $prompt = "Write a clear, SEO-optimized, HTML formatted description for the brand '{$title}'. Start with <p>, use <strong> for sections, <ul><li> for benefits. Write the response in {$lang}.";
        }


        // Request to Groq
        $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'llama-3.3-70b-versatile',
                'messages' => array(
                    array('role' => 'system', 'content' => 'You are an expert content writer and SEO specialist.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'temperature' => 0.7
            )),
            'timeout' => 40,
        ));

        if (is_wp_error($response)) continue;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['choices'][0]['message']['content'])) continue;
        $content = $data['choices'][0]['message']['content'];

        // === SAVE ===
        if ($type === 'product') {
            $post_data = ['ID' => $item->ID];
            if (preg_match('/\[LONG_DESCRIPTION\](.*?)\[SHORT_DESCRIPTION\]/s', $content, $m1)) {
                $post_data['post_content'] = trim($m1[1]);
            }
            if (preg_match('/\[SHORT_DESCRIPTION\](.*)/s', $content, $m2)) {
                $post_data['post_excerpt'] = trim($m2[1]);
            }
            if (!isset($post_data['post_content']) && strlen($content) < 300) {
                $post_data['post_excerpt'] = $content;
            }
            if (!isset($post_data['post_excerpt']) && strlen($content) >= 300) {
                $post_data['post_content'] = $content;
            }
            wp_update_post($post_data);
        } else {
            wp_update_term($item->term_id, $type === 'category' ? 'product_cat' : 'product_brand', [
                'description' => $content
            ]);
        }

        $success_count++;
    }

    wp_send_json_success("Successfully generated descriptions for {$success_count} items.");
}
