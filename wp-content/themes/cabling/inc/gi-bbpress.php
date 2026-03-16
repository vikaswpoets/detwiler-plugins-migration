<?php
function bbp_new_topic_pre_insert_hook_callback($data)
{
    if (current_user_can('manage_options'))
        return $data;

    $data['post_status'] = bbp_get_pending_status_id();

    return $data;
}

add_filter('bbp_new_topic_pre_insert', 'bbp_new_topic_pre_insert_hook_callback', 10, 1);
add_filter('bbp_new_reply_pre_insert', 'bbp_new_topic_pre_insert_hook_callback', 10, 1);

function bbp_template_notices_callback()
{
    if (current_user_can('manage_options'))
        return;
    ob_start(); ?>
    <div class="bbp-template-notice">
        <ul>
            <li><?php esc_html_e('Your topic/reply will be moderated before being published.', 'cabling'); ?></li>
        </ul>
    </div>
    <?php
    echo ob_get_clean();
}

add_action('bbp_template_notices', 'bbp_template_notices_callback');
function bbp_new_topic_redirect_to_callback($redirect_url, $redirect_to, $topic_id)
{
    if (current_user_can('manage_options'))
        return $redirect_url;
    $forum_id = bbp_get_topic_forum_id($topic_id);
    return bbp_get_forum_permalink($forum_id);
}

add_filter('bbp_new_topic_redirect_to', 'bbp_new_topic_redirect_to_callback', 10, 3);
function bbp_get_forum_post_type_labels_callback($labels)
{
    $labels['name'] = esc_attr__('Areas', 'cabling');
    $labels['menu_name'] = esc_attr__('Forum Areas', 'cabling');
    $labels['singular_name'] = esc_attr__('Area', 'cabling');

    return $labels;
}

add_filter('bbp_get_forum_post_type_labels', 'bbp_get_forum_post_type_labels_callback');

function bbp_get_topic_post_type_labels_callback($labels)
{
    $labels['menu_name'] = esc_attr__('Forum Topics', 'cabling');

    return $labels;
}

add_filter('bbp_get_topic_post_type_labels', 'bbp_get_topic_post_type_labels_callback');
function bbp_get_reply_post_type_labels_callback($labels)
{
    $labels['menu_name'] = esc_attr__('Forum Replies', 'cabling');

    return $labels;
}

add_filter('bbp_get_reply_post_type_labels', 'bbp_get_reply_post_type_labels_callback');

add_action('bbp_template_before_single_forum', 'cabling_bbp_template_before_single_forum_callback');
add_action('bbp_template_before_single_reply', 'cabling_bbp_template_before_single_forum_callback');
add_action('bbp_template_before_single_topic', 'cabling_bbp_template_before_single_forum_callback');
function cabling_bbp_template_before_single_forum_callback()
{
    if (function_exists('bbp_get_template_part'))
        bbp_get_template_part('form', 'search');
}
add_filter('bbp_show_lead_topic', '__return_true');

function get_topic_related()
{
    $topic_id = bbp_get_topic_id();

    $tags = bbp_get_topic_tag_list($topic_id);
    $forum_id = bbp_get_topic_forum_id($topic_id);

    if ($tags) {

        $args = array(
            'post_type' => bbp_get_topic_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => 8,
            'post__not_in' => array($topic_id),
            'post_parent' => $forum_id,
        );

        $related_topics_query = new WP_Query($args);

        // Check if there are related topics
        if ($related_topics_query->have_posts()) {
            echo '<div class="related-topics alignfull">';
            echo '<div class="container">';
            echo '<h2 class="pre-heading heading-center">' . __('RELATED CONVERSATIONS', 'cabling') . '</h2>';
            echo '<div class="row">';
            while ($related_topics_query->have_posts()) {
                $related_topics_query->the_post();
                $id = get_the_ID(); ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bbp-forum-box">
                        <a class="bbp-forum-title" href="<?php bbp_topic_permalink($id); ?>"
                           title="<?php bbp_topic_title($id); ?>">
                            <h4><?php bbp_topic_title($id); ?></h4>
                        </a>
                        <div class="bbp-forum-count">
                            <i class="fa-light fa-messages me-2"></i>
                            <?php printf(__('%s Conversations', 'cabling'), bbp_get_topic_reply_count($id)); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        // Restore original post data
        wp_reset_postdata();
    }

}

function comments_open_for_blog($open, $post_id)
{
    return in_array(get_post_type($post_id), ['post']) ? true : $open;
}

add_filter("comments_open", "comments_open_for_blog", 10, 2);

// Add the reCAPTCHA widget to the comment form
function add_recaptcha_to_comment_form($fields)
{
    if (isset($fields['url'])) {
        unset($fields['url']);
    }
    $fields['recaptcha'] = '<div class="g-recaptcha" data-sitekey="' . get_field('gcapcha_sitekey_v2', 'option') . '"></div>';

    return $fields;
}

add_filter('comment_form_default_fields', 'add_recaptcha_to_comment_form');

// Verify reCAPTCHA on comment submission
function verify_recaptcha_on_comment_submit($comment_data)
{
    $verify_recaptcha = cabling_verify_recaptcha($_POST['g-recaptcha-response']);

    if (empty($verify_recaptcha)) {
        wp_die(__('reCAPTCHA verification failed. Please try again!', 'cabling'));
    }


    return $comment_data;
}

add_filter('preprocess_comment', 'verify_recaptcha_on_comment_submit');

add_filter('excerpt_length', function () {
    return 30;
});



function cabling_comment_callback($comment, $args, $depth)
{
    $tag = ('div' === $args['style']) ? 'div' : 'li';

    $commenter = wp_get_current_commenter();
    $show_pending_links = !empty($commenter['comment_author']);

    if ($commenter['comment_author_email']) {
        $moderation_note = __('Your comment is awaiting moderation.');
    } else {
        $moderation_note = __('Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.');
    }
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class($comment->post_parent ? 'parent' : '', $comment); ?>>
    <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
        <footer class="comment-meta">
            <div class="comment-author vcard">
                <?php
                if (0 != $args['avatar_size']) {
                    echo get_avatar($comment, $args['avatar_size']);
                }
                ?>
                <?php
                $comment_author = get_comment_author_link($comment);

                if ('0' == $comment->comment_approved && !$show_pending_links) {
                    $comment_author = get_comment_author($comment);
                }

                printf(
                /* translators: %s: Comment author link. */
                    __('%s <span class="says">says:</span>'),
                    sprintf('<b class="fn">%s</b>', $comment_author)
                );
                ?>
                <div class="comment-time"><?php printf(__('%s ago', 'cabling'), human_time_diff(get_comment_time('U'), current_time('timestamp'))); ?></div>
                <?php
                if ('1' == $comment->comment_approved || $show_pending_links) {
                    comment_reply_link(
                        array_merge(
                            $args,
                            array(
                                'add_below' => 'div-comment',
                                'depth' => $depth,
                                'max_depth' => $args['max_depth'],
                                'before' => '<div class="reply">',
                                'after' => '</div>',
                            )
                        )
                    );
                }
                ?>
            </div><!-- .comment-author -->

            <div class="comment-metadata">
                <?php
                edit_comment_link(__('Edit'), ' <span class="edit-link">', '</span>');
                ?>
            </div><!-- .comment-metadata -->

            <?php if ('0' == $comment->comment_approved) : ?>
                <em class="comment-awaiting-moderation"><?php echo $moderation_note; ?></em>
            <?php endif; ?>
        </footer><!-- .comment-meta -->

        <div class="comment-content">
            <?php comment_text(); ?>
        </div><!-- .comment-content -->
    </article><!-- .comment-body -->
    <?php
}

function comment_form_defaults_custom($args)
{
    $required_indicator = ' ' . wp_required_field_indicator();
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $html5 = 'html5' === $args['format'];

    // Define attributes in HTML5 or XHTML syntax.
    $required_attribute = ($html5 ? ' required' : ' required="required"');

    $args['title_reply'] = __('LEAVE A COMMENT', 'cabling');
    $args['title_reply_to'] = __('LEAVE A COMMENT TO %s', 'cabling');
    $args['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s btn-red" value="%4$s" />';
    $args['comment_field'] = sprintf(
        '<p class="comment-form-comment">%s %s</p>',
        sprintf(
            '<label for="comment" class="hidden">%s%s</label>',
            _x('Comment', 'noun'),
            $required_indicator
        ),
        sprintf(
            '<textarea id="comment" name="comment" cols="45" rows="8" placeholder="%s" maxlength="65525"' . $required_attribute . '></textarea>',
            _x('Type your message here', 'cabling'),
        )
    );
    $args['fields']['email'] = sprintf(
        '<p class="comment-form-email">%s %s</p>',
        sprintf(
            '<label for="email" class="hidden">%s%s</label>',
            __('Email'),
            ($req ? $required_indicator : '')
        ),
        sprintf(
            '<input id="email" name="email" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes" autocomplete="email" %s placeholder="%s" />',
            ($html5 ? 'type="email"' : 'type="text"'),
            esc_attr($commenter['comment_author_email']),
            ($req ? $required_attribute : ''),
            _x('Email Address*', 'cabling'),
        )
    );
    $args['fields']['author'] = sprintf(
        '<p class="comment-form-author">%s %s</p>',
        sprintf(
            '<label for="author" class="hidden">%s%s</label>',
            __('Name'),
            ($req ? $required_indicator : '')
        ),
        sprintf(
            '<input id="author" name="author" type="text" value="%s" size="30" maxlength="245" autocomplete="name" %s placeholder="%s" />',
            esc_attr($commenter['comment_author']),
            ($req ? $required_attribute : ''),
            _x('Name*', 'cabling'),
        )
    );

    return $args;
}

add_filter('comment_form_defaults', 'comment_form_defaults_custom');
