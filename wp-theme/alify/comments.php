<?php
/**
 * Comments & Discussion Template (`comments.php`)
 * Styled natively for ALIFY Single Blog Articles (`single.php`).
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

/**
 * Custom Comment Item Callback for Clean ALIFY Markup (Zero Bootstrap Utility Classes).
 */
if ( ! function_exists( 'alify_render_comment_card' ) ) {
	function alify_render_comment_card( $comment, $args, $depth ) {
		$author_name = get_comment_author( $comment );
		$words       = preg_split( '/\s+/', trim( (string) $author_name ) );
		$initials    = strtoupper( substr( $words[0], 0, 1 ) . ( isset( $words[1] ) ? substr( $words[1], 0, 1 ) : '' ) );
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'alify-comment-item' ); ?>>
			<article id="div-comment-<?php comment_ID(); ?>" class="alify-comment-card">
				<header class="alify-comment-head">
					<div class="alify-comment-author-wrap">
						<span class="alify-comment-avatar" aria-hidden="true"><?php echo esc_html( $initials ? $initials : 'A' ); ?></span>
						<div>
							<strong class="alify-comment-author"><?php echo esc_html( $author_name ); ?></strong>
							<span class="alify-comment-date">
								<?php
								printf(
									/* translators: 1: date, 2: time */
									esc_html__( '%1$s at %2$s', 'alify' ),
									esc_html( get_comment_date( '', $comment ) ),
									esc_html( get_comment_time() )
								);
								?>
							</span>
						</div>
					</div>
					<div class="alify-comment-reply">
						<?php
						comment_reply_link(
							array_merge(
								$args,
								array(
									'depth'     => $depth,
									'max_depth' => $args['max_depth'],
									'reply_text'=> __( 'Reply', 'alify' ),
								)
							)
						);
						?>
					</div>
				</header>

				<?php if ( '0' === (string) $comment->comment_approved ) : ?>
					<p class="alify-comment-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'alify' ); ?></p>
				<?php endif; ?>

				<div class="alify-comment-content">
					<?php comment_text(); ?>
				</div>
			</article>
		<?php
	}
}
?>
<div id="comments" class="alify-comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="alify-comments-title">
			<?php
			$alify_comment_count = (int) get_comments_number();
			printf(
				/* translators: %d: number of comments */
				esc_html( _n( '%d Reader Comment', '%d Reader Comments', $alify_comment_count, 'alify' ) ),
				$alify_comment_count
			);
			?>
		</h2>

		<ol class="alify-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'callback'   => 'alify_render_comment_card',
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="alify-comments-closed"><?php esc_html_e( 'Comments are closed for this article.', 'alify' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_container'    => 'alify-comment-respond',
			'class_form'         => 'alify-comment-form',
			'title_reply'        => __( 'Join the Discussion', 'alify' ),
			'title_reply_before' => '<h3 id="reply-title" class="alify-reply-title">',
			'title_reply_after'  => '</h3>',
			'class_submit'       => 'alify-comment-submit-btn',
			'label_submit'       => __( 'Post Comment', 'alify' ),
		)
	);
	?>
</div>
