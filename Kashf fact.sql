SELECT 
    p.ID as post_id,
    p.post_title,
    CONCAT('https://kashfmedia.com/', 
           YEAR(p.post_date), '/', 
           LPAD(MONTH(p.post_date), 2, '0'), '/', 
           LPAD(DAY(p.post_date), 2, '0'), '/', 
           p.post_name, '/') as post_link,
    p.post_date,
    COUNT(CASE WHEN v.vote = 'صحيح' THEN 1 END) as صحيح_votes,
    COUNT(CASE WHEN v.vote = 'مضلل' THEN 1 END) as مضلل_votes,
    COUNT(CASE WHEN v.vote = 'خاطئ' THEN 1 END) as خاطئ_votes,
    COUNT(v.id) as total_votes,
    COUNT(DISTINCT v.user_ip) as unique_voters,
    -- Percentage calculations
    ROUND((COUNT(CASE WHEN v.vote = 'صحيح' THEN 1 END) * 100.0 / COUNT(v.id)), 2) as صحيح_percentage,
    ROUND((COUNT(CASE WHEN v.vote = 'مضلل' THEN 1 END) * 100.0 / COUNT(v.id)), 2) as مضلل_percentage,
    ROUND((COUNT(CASE WHEN v.vote = 'خاطئ' THEN 1 END) * 100.0 / COUNT(v.id)), 2) as خاطئ_percentage
FROM wp_posts p
INNER JOIN votes v ON p.ID = v.post_id
WHERE p.post_status = 'publish'
GROUP BY p.ID, p.post_title, p.post_name, p.post_date
ORDER BY total_votes DESC;