<?php
/**
 * Single Product Reviews
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

if (!comments_open()) {
    return;
}

$rating_count = $product->get_rating_count();
$average_rating = $product->get_average_rating();
$reviews = get_comments(array(
    'post_id' => $product->get_id(),
    'status' => 'approve',
    'type' => 'review',
    'number' => 5,
));

// Calculate rating distribution
$rating_distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
$all_reviews = get_comments(array(
    'post_id' => $product->get_id(),
    'status' => 'approve',
    'type' => 'review',
));

foreach ($all_reviews as $review) {
    $rating = get_comment_meta($review->comment_ID, 'rating', true);
    if ($rating && isset($rating_distribution[$rating])) {
        $rating_distribution[$rating]++;
    }
}

$total_ratings = array_sum($rating_distribution);
?>

<h2 class="text-2xl font-bold mb-6 text-text-main dark:text-white">Đánh giá từ khách hàng</h2>
<div class="bg-white dark:bg-gray-800 rounded-xl p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16">
        <!-- Rating Summary (Left) -->
        <div class="flex-shrink-0 min-w-[280px]">
            <div class="flex items-end gap-3 mb-2">
                <span class="text-5xl font-black text-text-main dark:text-white leading-none"><?php echo esc_html(number_format($average_rating, 1)); ?></span>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">trên 5.0</div>
            </div>
            <div class="flex text-primary mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($average_rating)): ?>
                        <span class="material-symbols-outlined fill-current">star</span>
                    <?php elseif ($i - 0.5 <= $average_rating): ?>
                        <span class="material-symbols-outlined fill-current">star_half</span>
                    <?php else: ?>
                        <span class="material-symbols-outlined text-gray-300">star</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Dựa trên <?php echo esc_html($rating_count); ?> nhận xét</p>
            
            <!-- Progress Bars -->
            <div class="space-y-2">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php
                    $count = $rating_distribution[$star];
                    $percent = $total_ratings > 0 ? round(($count / $total_ratings) * 100) : 0;
                    ?>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-3"><?php echo esc_html($star); ?></span>
                        <span class="material-symbols-outlined text-xs text-primary">star</span>
                        <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width: <?php echo esc_attr($percent); ?>%"></div>
                        </div>
                        <span class="w-8 text-right text-gray-500"><?php echo esc_html($percent); ?>%</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Reviews List (Right) -->
        <div class="flex-1 border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-700 pt-6 lg:pt-0 lg:pl-10">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-lg">Bình luận gần đây</h3>
                <?php if (is_user_logged_in()): ?>
                    <button class="text-sm font-semibold text-primary border border-primary px-4 py-2 rounded hover:bg-primary/5 transition-colors write-review-btn">
                        Viết đánh giá
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="space-y-6 reviews-list">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <?php
                        $rating = get_comment_meta($review->comment_ID, 'rating', true);
                        $verified = wc_review_is_from_verified_owner($review->comment_ID);
                        $author = get_comment_author($review);
                        $date = get_comment_date('d/m/Y', $review->comment_ID);
                        ?>
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-6 last:border-0">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-sm">
                                    <?php echo esc_html(strtoupper(substr($author, 0, 2))); ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <div>
                                            <p class="font-bold text-sm"><?php echo esc_html($author); ?></p>
                                            <?php if ($rating): ?>
                                                <div class="flex text-primary text-xs mt-0.5">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $rating): ?>
                                                            <span class="material-symbols-outlined text-[14px] fill-current">star</span>
                                                        <?php else: ?>
                                                            <span class="material-symbols-outlined text-[14px] text-gray-300">star</span>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs text-gray-400"><?php echo esc_html($date); ?></span>
                                    </div>
                                    <?php if ($verified): ?>
                                        <div class="text-green-600 text-xs font-medium flex items-center gap-1 mb-2">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                            Đã mua hàng
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo esc_html(get_comment_text($review)); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                <?php endif; ?>
            </div>
            
            <?php if ($rating_count > 5): ?>
                <div class="mt-6 text-center">
                    <a href="#reviews" class="text-sm font-semibold text-text-secondary hover:text-primary transition-colors">
                        Xem tất cả <?php echo esc_html($rating_count); ?> đánh giá
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Show comment form for reviews
if (comments_open() && is_user_logged_in()):
    comment_form(array(
        'comment_field' => '<div class="comment-form-rating"><label for="rating">Đánh giá của bạn</label><select name="rating" id="rating" required>
            <option value="">Chọn đánh giá</option>
            <option value="5">5 sao</option>
            <option value="4">4 sao</option>
            <option value="3">3 sao</option>
            <option value="2">2 sao</option>
            <option value="1">1 sao</option>
        </select></div>',
    ), $product->get_id());
endif;
?>