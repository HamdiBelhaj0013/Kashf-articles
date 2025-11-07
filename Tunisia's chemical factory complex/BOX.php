// Enhanced version - replace the existing function with this improved one
add_filter('wpseo_estimated_reading_time_minutes', 'enhanced_exclude_html_blocks_reading_time', 10, 2);
function enhanced_exclude_html_blocks_reading_time($minutes, $post) {
    $content = get_post_field('post_content', $post);
    
    // Remove HTML code blocks (more comprehensive)
    $content = preg_replace('/<pre[^>]*>.*?<\/pre>/s', '', $content);
    $content = preg_replace('/<code[^>]*>.*?<\/code>/s', '', $content);
    
    // Remove Elementor shortcodes and widgets
    $content = preg_replace('/\[.*?\]/s', '', $content);
    $content = preg_replace('/\[\/.*?\]/s', '', $content);
    
    // Remove Elementor specific content
    $content = preg_replace('/<div[^>]*elementor[^>]*>.*?<\/div>/s', '', $content);
    
    // Remove HTML comments
    $content = preg_replace('/<!--.*?-->/s', '', $content);
    
    // Remove script and style tags
    $content = preg_replace('/<script[^>]*>.*?<\/script>/s', '', $content);
    $content = preg_replace('/<style[^>]*>.*?<\/style>/s', '', $content);
    
    // Remove all remaining HTML tags
    $content = strip_tags($content);
    
    // Remove extra whitespace
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    // Calculate reading time for Arabic content
    $word_count = str_word_count($content);
    
    // Adjusted for Arabic reading speed (slower than English)
    $words_per_minute = 180; // Arabic is typically read slower
    $new_minutes = max(1, ceil($word_count / $words_per_minute)); // Minimum 1 minute
    
    return $new_minutes;
}

// Also fix the custom shortcode reading time
add_filter('wp_footer', 'update_custom_reading_time_script');
function update_custom_reading_time_script() {
    if (is_single()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update the custom reading time display
            const readingInfo = document.getElementById('reading-info');
            if (readingInfo) {
                const readingTimeP = readingInfo.querySelector('p');
                if (readingTimeP) {
                    // Get clean content for calculation
                    let content = document.querySelector('.entry-content, .post-content, .content');
                    if (content) {
                        let textContent = content.innerText || content.textContent || '';
                        
                        // Remove extra whitespace
                        textContent = textContent.replace(/\s+/g, ' ').trim();
                        
                        // Count words (Arabic-friendly)
                        const wordCount = textContent.split(' ').length;
                        const wordsPerMinute = 180; // Adjusted for Arabic
                        const readingTime = Math.max(1, Math.ceil(wordCount / wordsPerMinute));
                        
                        // Update display
                        readingTimeP.innerHTML = readingTime + ' دقيقة';
                    }
                }
            }
        });
        </script>
        <?php
    }
}