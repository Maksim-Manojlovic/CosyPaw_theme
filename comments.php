<?php
/**
 * Comments template.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail if the post is password-protected and the visitor hasn't entered it.
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area entry">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-area__title">
			<?php
			/* translators: %s: number of comments. */
			printf( esc_html__( 'Komentari (%s)', 'cosypaw' ), esc_html( number_format_i18n( (int) get_comments_number() ) ) );
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Prethodni', 'cosypaw' ),
				'next_text' => esc_html__( 'Sledeći', 'cosypaw' ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="no-comments"><?php esc_html_e( 'Komentari su zatvoreni.', 'cosypaw' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit'  => 'btn btn--primary',
			'title_reply'   => esc_html__( 'Ostavi komentar', 'cosypaw' ),
			'label_submit'  => esc_html__( 'Pošalji', 'cosypaw' ),
		)
	);
	?>
</section>
