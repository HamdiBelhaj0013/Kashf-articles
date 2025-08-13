// Helper function for Arabic filename encoding
function rawurlencode_arabic_safe($url) {
    $parsed = parse_url($url);
    if (!isset($parsed['path'])) return $url;
    
    $path_parts = explode('/', $parsed['path']);
    $encoded_parts = array();
    
    foreach ($path_parts as $part) {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $part)) {
            // Contains Arabic characters - encode only the filename part
            $encoded_parts[] = rawurlencode($part);
        } else {
            $encoded_parts[] = $part;
        }
    }
    
    $parsed['path'] = implode('/', $encoded_parts);
    return unparse_url($parsed);
}

function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}

// Disable Yoast Open Graph completely
add_filter('wpseo_opengraph_output', '__return_false');
add_filter('wpseo_twitter_output', '__return_false');

// Alternative: disable specific Yoast meta tags
add_filter('wpseo_opengraph_url', '__return_false');
add_filter('wpseo_opengraph_title', '__return_false');
add_filter('wpseo_opengraph_desc', '__return_false');
add_filter('wpseo_opengraph_image', '__return_false');

function sf_add_social_meta_tags() {
    if (!is_singular()) {
        return;
    }
    
    global $post;
    
    if (!$post) {
        return;
    }
    
    // Title handling
    $title = get_the_title($post->ID);
    if (empty($title)) {
        $title = get_bloginfo('name');
    }
    $title = wp_strip_all_tags($title);
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    $title = esc_attr($title);
    
    // Description handling
    $description = '';
    if (has_excerpt($post->ID)) {
        $description = get_the_excerpt($post->ID);
    } else {
        $content = $post->post_content;
        $content = wp_strip_all_tags($content);
        $content = strip_shortcodes($content);
        $description = wp_trim_words($content, 25, '...');
    }
    
    if (empty($description)) {
        $description = get_bloginfo('description');
    }
    $description = wp_strip_all_tags($description);
    $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
    $description = esc_attr($description);
    
    // FIXED URL HANDLING FOR ARABIC CONTENT
    $url = get_permalink($post->ID);
    
    // Get the clean URL without encoding for social sharing
    $clean_url = home_url($_SERVER['REQUEST_URI']);
    
    // Use the clean URL if it's valid, otherwise fall back to permalink
    if (filter_var($clean_url, FILTER_VALIDATE_URL)) {
        $url = $clean_url;
    }
    
    // Ensure proper encoding for social platforms
    $url = esc_url_raw($url);
    
    // Image handling with Arabic filename fix
    $image = '';
    if (is_page($post->ID)) {
        $image = 'https://kashfmedia.com/wp-content/uploads/2025/05/kashf.png';
    } elseif (has_post_thumbnail($post->ID)) {
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $image = wp_get_attachment_image_url($thumbnail_id, 'full');
        
        // Fix Arabic filenames for social media
        if ($image) {
            $image = str_replace(' ', '%20', $image);
            // Encode Arabic characters properly for social platforms
            $image = rawurlencode_arabic_safe($image);
        }
    }
    
    if (empty($image)) {
        $image = 'https://kashfmedia.com/wp-content/uploads/2025/05/kashf.png';
    }
    $image = esc_url_raw($image);
    
    $site_name = get_bloginfo('name');
    $site_name = esc_attr($site_name);
    
    $type = is_front_page() ? 'website' : 'article';
    $locale = 'ar_AR';
    
    echo "\n<!-- Custom Social Media Meta Tags (Arabic Content Fixed) -->\n";
    
    // Canonical URL
    echo '<link rel="canonical" href="' . $url . '" />' . "\n";
    
    // Open Graph tags
    echo '<meta property="og:title" content="' . $title . '" />' . "\n";
    echo '<meta property="og:description" content="' . $description . '" />' . "\n";
    echo '<meta property="og:url" content="' . $url . '" />' . "\n";
    echo '<meta property="og:image" content="' . $image . '" />' . "\n";
    echo '<meta property="og:image:alt" content="' . $title . '" />' . "\n";
    echo '<meta property="og:type" content="' . $type . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . $site_name . '" />' . "\n";
    echo '<meta property="og:locale" content="' . $locale . '" />' . "\n";
    
    // Twitter tags
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . $description . '" />' . "\n";
    echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
    echo '<meta name="twitter:url" content="' . $url . '" />' . "\n";
    
    // Additional meta tags for better social sharing
    echo '<meta property="fb:app_id" content="" />' . "\n"; // Add your Facebook App ID if you have one
    
    if (is_single()) {
        $published_time = get_the_date('c', $post->ID);
        $modified_time = get_the_modified_date('c', $post->ID);
        echo '<meta property="article:published_time" content="' . $published_time . '" />' . "\n";
        echo '<meta property="article:modified_time" content="' . $modified_time . '" />' . "\n";
        
        // Author information
        $author_name = get_the_author_meta('display_name', $post->post_author);
        if ($author_name) {
            echo '<meta property="article:author" content="' . esc_attr($author_name) . '" />' . "\n";
        }
    }
    
    echo "<!-- End Custom Social Media Meta Tags -->\n\n";
}

// Execute after Yoast to override its meta tags
add_action('wp_head', 'sf_add_social_meta_tags', 99);

// Additional fix: Clean up URLs for social sharing
function fix_arabic_urls_for_sharing() {
    if (is_singular()) {
        echo '<script>
        // Fix social sharing URLs for Arabic content
        if (typeof window.addthis !== "undefined") {
            window.addthis.layers.refresh();
        }
        </script>';
    }
}
add_action('wp_footer', 'fix_arabic_urls_for_sharing');