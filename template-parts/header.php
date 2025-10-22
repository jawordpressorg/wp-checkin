<?php
/**
 * Header template for wp-checkin
 *
 * @var array $args Arguments.
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="wp-checkin-header">

	<div class="wp-checkin-container">
		<nav class="wp-checkin-nav">
			<a class="wp-checkin-home" href="<?php echo network_home_url(); ?>">
				<span class="dashicons dashicons-admin-home"></span>
			</a>

			<a class="wp-checkin-logo" href="<?php echo home_url( 'checkin' ); ?>">
				<?php if ( ! has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo wp_checkin_url( 'build/img/wapuu.png' ); ?>" alt="WP Checkin" width="100"
						height="100" />
				<?php endif; ?>
			</a>

			<a class="wp-checkin-source" href="https://github.com/jawordpressorg/wp-checkin/" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-editor-code"></span>
			</a>
		</nav>
	</div>

</header>

<div class="wp-checkin-container">

	<h1 class="wp-checkin-title"><?php bloginfo( 'name' ); ?></h1>

	<main class="wp-checkin-main">

