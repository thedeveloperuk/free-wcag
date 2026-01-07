- [ ] Add feature for automatic alt tagging. We pass each image to OpenAI API and get a suitable alt tag for it, then update it in wordpress. We'll use an external service to do this, which users have to pay £9/year for unlimited alt tag generation.
 
Technical Specification: AI Alt-Text Generator for WordPress
1. Executive Summary
This plugin automates accessibility and SEO compliance by generating descriptive alt tags for WordPress media library images. It utilizes OpenAI’s GPT-5 Vision capabilities to analyze images and return concise, context-aware descriptions.

Core Features:

Real-Time Generation: Automatically generates alt text when a user uploads a new image.

Bulk Processing: Uses the OpenAI Batch API (50% cost savings) to backfill alt text for the existing media library.

2. API Configuration
Provider: OpenAI API

Model: gpt-5 (or gpt-5-mini for cost optimization)

Endpoint: https://api.openai.com/v1/chat/completions

Authentication: Bearer Token (Store securely in wp_options)

System Prompt Strategy
To ensure the alt text is useful for SEO and screen readers, the system prompt must enforce brevity.

Prompt: "You are an SEO expert. Analyze this image and provide a purely descriptive alt text. Maximum 15 words. Do not use phrases like 'image of' or 'photo of'. Focus on the main subject and context."

3. Workflow 1: Real-Time Uploads (New Images)
Trigger: WordPress Action Hook add_attachment or wp_generate_attachment_metadata. Method: Synchronous API call (or queued via WP-Cron/Action Scheduler to prevent upload timeouts).

PHP Implementation Guide
File: includes/class-image-processor.php

PHP

<?php

function generate_alt_text_on_upload($metadata, $attachment_id) {
    // 1. Check if alt text already exists (optional: skip if user added one manually)
    $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    if (!empty($existing_alt)) {
        return $metadata;
    }

    // 2. Get the image URL
    $image_url = wp_get_attachment_url($attachment_id);
    
    // 3. Prepare payload for GPT-5
    $api_key = get_option('ai_alt_plugin_api_key');
    
    $payload = [
        'model' => 'gpt-5', 
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Generate concise alt text for this image (max 15 words, SEO friendly).'],
                    ['type' => 'image_url', 'image_url' => ['url' => $image_url]]
                ]
            ]
        ],
        'max_tokens' => 50
    ];

    // 4. Send Request
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode($payload),
        'timeout' => 30 // Vision requests can take time
    ]);

    // 5. Process Response & Update Database
    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $alt_text = $body['choices'][0]['message']['content'] ?? '';

        if ($alt_text) {
            // Sanitize and save to WordPress Post Meta
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
        }
    }

    return $metadata;
}

// Hook into WordPress upload process
add_filter('wp_generate_attachment_metadata', 'generate_alt_text_on_upload', 10, 2);
4. Workflow 2: Bulk Processing (Existing Library)
Trigger: Admin Dashboard Button ("Process All Missing Alt Tags"). Method: OpenAI Batch API. This is critical for avoiding timeouts and reducing costs when processing thousands of existing images.

The Batch Architecture
Since WordPress is stateless, we cannot keep a script running for hours. We must use a 3-step Asynchronous Flow:

Step 1 (Generation): Create a JSONL file containing all images missing alt tags and upload it to OpenAI.

Step 2 (Monitoring): A scheduled WP-Cron task checks the Batch Status every hour.

Step 3 (Completion): Once the batch status is completed, download the results file and update the database rows.

PHP Logic for Step 1 (Creating the Batch)
PHP

function trigger_bulk_batch_process() {
    // 1. Query images without alt text
    $args = [
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => 500, // Process in chunks
        'meta_query'     => [
            [
                'key'     => '_wp_attachment_image_alt',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ];
    $images = get_posts($args);
    
    // 2. Build JSONL Content
    $jsonl_content = '';
    foreach ($images as $img) {
        $url = wp_get_attachment_url($img->ID);
        $request_id = "img_" . $img->ID; // Store ID to match back later
        
        $line = [
            "custom_id" => $request_id,
            "method" => "POST",
            "url" => "/v1/chat/completions",
            "body" => [
                "model" => "gpt-5",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => [
                            ["type" => "text", "text" => "Describe for alt text. Max 15 words."],
                            ["type" => "image_url", "image_url" => ["url" => $url]]
                        ]
                    ]
                ],
                "max_tokens" => 50
            ]
        ];
        $jsonl_content .= json_encode($line) . "\n";
    }

    // 3. Upload File to OpenAI & Create Batch (Standard API calls omitted for brevity)
    // ... code to POST to https://api.openai.com/v1/files ...
    // ... code to POST to https://api.openai.com/v1/batches ...
    
    // 4. Save Batch ID to DB for tracking
    update_option('current_alt_text_batch_id', $batch_id_from_response);
}
5. UI/UX Requirements
To make this plugin user-friendly for WordPress admins, the settings page (Settings > AI Alt Text) needs:

API Key Input: Field to paste the OpenAI Sk-key.

Model Selector: Dropdown to choose between gpt-5 (Better quality) and gpt-4o-mini (Cheaper).

Prompt Customization: A text area to override the default system instruction (e.g., "Translate description to Spanish").

Bulk Action Status: A progress bar showing the status of the current Batch job (e.g., "Processing... 45%").

6. Security & Performance Guardrails
Capability Check: Ensure only admins (manage_options) can trigger bulk updates or view API keys.

Nonce Verification: All admin actions must verify WP Nonces to prevent CSRF attacks.

Error Handling: The plugin must gracefully handle OpenAI errors (e.g., 429 Too Many Requests) by implementing exponential backoff.

Image Size: To save tokens, pass the plugin's "Medium" or "Large" image size URL to the API, rather than the "Original" (Full) size, which might be unnecessarily large.

7. Next Step for the Developer
Action: Create the plugin folder structure.

ai-alt-text.php (Main plugin file)

includes/class-api-handler.php (Handles OpenAI communication)

includes/class-batch-processor.php (Handles the JSONL logic)

admin/settings-page.php (The UI)



AI Alt-Text Prompting SpecificationTo get the best results from GPT-5, the API call must include a "System Instruction" that acts as a set of guardrails. This prevents the AI from being too wordy or using "fluffy" language (like "This is an image of...").1. The Master System PromptCopy and paste this into the plugin’s API handler.PlaintextYou are an expert in Web Accessibility (WCAG 2.2) and Image SEO. 
Your task is to generate 'alt' text for images uploaded to a WordPress site.

### CRITICAL RULES:
1. BREVITY: Keep descriptions between 5 and 15 words.
2. NO FILLER: Never start with "Image of", "Photo of", or "A picture showing". Start directly with the subject.
3. FORMAT: Output ONLY the alt text. No quotes, no intro, no "Here is your description".
4. PUNCTUATION: End with a period so screen readers pause correctly.
5. DECORATIVE: If the image is a spacer, a solid color, or purely decorative with no meaning, return the word "NULL".

### CATEGORY-SPECIFIC GUIDANCE:
- PRODUCTS: Focus on the brand, color, and key features (e.g., "Silver stainless steel 15-inch laptop on a white desk").
- LANDSCAPES: Focus on the mood and time of day (e.g., "Snow-capped mountains reflecting in a calm lake at sunrise").
- PEOPLE: Describe the action and setting, not physical appearance unless relevant (e.g., "A smiling doctor consulting with an elderly patient in a bright office").
- TEXT/LOGOS: Transcribe the text exactly (e.g., "Blue and white Acme Corp company logo").
- CHARTS/DATA: Summarize the main trend (e.g., "Bar chart showing a 20% increase in annual sales for 2025").
2. Developer Implementation (Python/PHP logic)When sending the request to the API, your developer should structure the message like this to maximize GPT-5's reasoning:API Message StructureJSON"messages": [
    {
        "role": "system",
        "content": "[The Master System Prompt above]"
    },
    {
        "role": "user",
        "content": [
            {
                "type": "text", 
                "text": "Generate alt text for this image. Context: This image is for a [Insert WordPress Category or Post Title] page."
            },
            {
                "type": "image_url",
                "image_url": { "url": "IMAGE_URL_HERE", "detail": "low" }
            }
        ]
    }
]
Note: Using "detail": "low" is recommended for alt tags to save on API costs, as GPT-5 is smart enough to recognize most subjects even in low resolution.3. Best Practice Examples for TestingUse these to verify the plugin is working correctly:Image Content❌ Bad AI Alt Text (Too wordy)✅ Good Alt Text (Plugin Goal)A Coffee Cup"An image of a white ceramic mug filled with dark coffee sitting on a wooden table.""Steaming black coffee in a white mug on a rustic wooden table."Sales Chart"A screenshot of a line graph with a blue line going up and some numbers on the side.""Line graph showing steady growth in website traffic over six months."Company Logo"The logo for the company called TechFlow which is blue and has a swoosh.""TechFlow corporate logo in blue and white."