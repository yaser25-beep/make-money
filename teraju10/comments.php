<?php
/**
 * Template komentar.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<div class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$comment_count = get_comments_number();
			echo esc_html(
				sprintf(
					/* translators: %d: number of comments. */
					_n( '%d Komentar', '%d Komentar', $comment_count, 'teraju10' ),
					$comment_count
				)
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p><?php esc_html_e( 'Kolom komentar sudah ditutup.', 'teraju10' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => __( 'Tinggalkan komentar', 'teraju10' ),
			'comment_field'      => '<p><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Tulis komentar...', 'teraju10' ) . '" required></textarea></p>',
			'class_submit'       => 'submit',
		)
	);
	?>
</div>
