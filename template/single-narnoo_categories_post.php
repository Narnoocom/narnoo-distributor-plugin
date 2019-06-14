<?php get_header(); 

$narnoo_queried_object = get_queried_object();

global $post;

$narnno_args = array(
    'post_parent' => $post->ID,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'post_type' => $narnoo_queried_object->post_type, 
    );

$narnoo_query = new WP_Query( $narnno_args );
//print_r($narnoo_query );

?>
    
    <?php do_action( 'narnoo_before_main_content' ); ?>

    <?php if($narnoo_queried_object->post_parent == 0): ?>

			<div id="narnoo_categories_post" <?php post_class(); ?>>

            <header class="entry-header">
				
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			
			</header><!-- .entry-header -->

			<div class="entry-content">
				
				<?php 
					if ( has_post_thumbnail() ) { 
						the_post_thumbnail( 'full' );
					}

					the_content( sprintf(
						__( 'Continue reading %s', 'twentyfifteen' ),
						the_title( '<span class="screen-reader-text">', '</span>', false )
					) );
				?>

				<div class="narnno-subcategory">
					<h2>Sub Category</h2>
					
					<?php if( $narnoo_query->have_posts() ) : ?>
		                <?php while ( $narnoo_query->have_posts() ) :  $narnoo_query->the_post(); ?>

		                    <div class="col-xs-12 col-lg-3">
		                        <a href="<?php echo get_post_permalink(); ?>">
		                            <div class="narnoo_attr_img_wrapper">
		                                <?php 
		                                if ( has_post_thumbnail() ) { 
		                                    echo the_post_thumbnail(); 
		                                } else {
		                                    ?> <img width="1800" src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>images/no-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="<?php the_title(); ?>" /> <?php
		                                }
		                                ?>
		                            </div>
		                            <h6 class="narnoo_product_listing_link">
		                                <?php the_title(); ?>    
		                            </h6>
		                        </a>

		                    </div>
		                    
		            	<?php endwhile; ?>
		            <?php endif; ?>
		            <?php wp_reset_postdata(); ?>

					<?php  /* 
					if ( $narnoo_query->have_posts() ) :
						while ( $narnoo_query->have_posts() ) : $narnoo_query->the_post(); ?>
						  
						    <h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
						    
					<?php	endwhile;
					endif;
					wp_reset_postdata();  */  ?>

				</div>

			</div>

        </div>

    <?php else: ?>	

    	<?php while ( have_posts() ) : the_post(); ?>

			<div id="narnoo_categories_post" <?php post_class(); ?>>

	            <header class="entry-header">
					
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				
				</header><!-- .entry-header -->

				<div class="entry-content">
					
					<?php 
						if ( has_post_thumbnail() ) { 
							the_post_thumbnail( 'full' );
						}

						the_content( sprintf(
							__( 'Continue reading %s', 'twentyfifteen' ),
							the_title( '<span class="screen-reader-text">', '</span>', false )
						) );
					?>
					
					<?php 
					  echo do_shortcode( '[ncm_product_search search="false" date="true"]' ); 
					?>
					
				</div>

	        </div>

    	<?php endwhile; ?>

  	<?php endif; ?>

	<?php do_action( 'narnoo_after_main_content' ); ?>

	<?php do_action( 'narnoo_get_sidebar' ); ?>

<?php get_footer(); ?>