<?php 
/**
 * The template for displaying 404 pages (Not Found)
 *
 * Imonthemes 
 */



get_header(); ?>

	<div class="row">
        <div class="large-11">
	
		<div>
			<h1><?php esc_attr__( '404', 'isis' ); ?></h1>
            <h2><?php esc_attr__( 'Page not found!', 'isis' ); ?></h2>
			<p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_attr__( 'Return home?', 'isis' ); ?></a>
			</p>
		</div>	
		
	</div>
    
    </div>

<?php get_footer(); ?>