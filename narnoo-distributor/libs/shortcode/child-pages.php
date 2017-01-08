<?php
global $post; 	
	
extract( shortcode_atts( array(
	'type' 	=> 'narnoo_attraction'
	), $atts ) );


if ( empty( $post->post_parent ) ){

	$args = array(
		'post_type'      => $type,
		'posts_per_page' => -1,
		'post_parent'    => $post->ID,
		'order'          => 'ASC',
		'orderby'        => 'menu_order'
		);


	$query = new WP_Query( $args );

	if ( $query->have_posts() ){
		echo '<ul>';
		while ( $query->have_posts() ) { 
			$query->the_post();

			echo '<li><a href="'.get_the_permalink().'">'.get_the_title(). '</a></li>'; 

		}
		echo '</ul>';
	}

}  
?>