<?php 
/*
Template Name: Blog Page
*/
?>
<?php 
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta( get_the_ID(), 'et_ptemplate_settings', true ) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;

$et_ptemplate_blogstyle = isset( $et_ptemplate_settings['et_ptemplate_blogstyle'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_blogstyle'] : false;

$et_ptemplate_showthumb = isset( $et_ptemplate_settings['et_ptemplate_showthumb'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_showthumb'] : false;

$blog_cats = isset( $et_ptemplate_settings['et_ptemplate_blogcats'] ) ? (array) $et_ptemplate_settings['et_ptemplate_blogcats'] : array();
$et_ptemplate_blog_perpage = isset( $et_ptemplate_settings['et_ptemplate_blog_perpage'] ) ? (int) $et_ptemplate_settings['et_ptemplate_blog_perpage'] : 10;
?>
<?php get_header(); ?>

<div id="content">
	<div class="container clearfix<?php if ( $fullwidth ) echo ' fullwidth'; ?>">
		<div id="left-area">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix entry entry-content' ); ?>>
				<?php
					$thumb = '';
					$width = apply_filters( 'et_blog_image_width', 623 );
					$height = apply_filters( 'et_blog_image_height', 200 );
					$classtext = '';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, '', $titletext, $titletext, false, 'Indeximage' );
					$thumb = $thumbnail["thumb"];
					
					$postinfo = et_get_option( 'fusion_postinfo2' );
					$show_thumb = is_page() ? et_get_option( 'fusion_page_thumbnails', 'false' ) : et_get_option( 'fusion_thumbnails', 'on' );
				?>
					<h1 class="title"><?php the_title(); ?></h1>
				<?php 
					if ( $postinfo && ! is_page() ) {
						echo '<p class="meta-info">';
						et_postinfo_meta( $postinfo, et_get_option( 'fusion_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Fusion' ), esc_html__( '1 comment', 'Fusion' ), '% ' . esc_html__( 'comments', 'Fusion' ) );
						echo '</p>';
					}
				?>
						
				<?php if ( '' != $thumb && 'false' != $show_thumb ) { ?>
					<div class="entry-thumbnail">
						<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
					</div> 	<!-- end .entry-thumbnail -->
				<?php } ?>

				<?php
					the_content();
					wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Fusion' ), 'after' => '</div>' ) );
				?>
					<div id="et_pt_blog" class="responsive">
						<?php $cat_query = ''; 
						if ( !empty($blog_cats) ) $cat_query = '&cat=' . implode(",", $blog_cats);
						else echo '<!-- blog category is not selected -->'; ?>
						<?php 
							$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
						?>
						<?php query_posts("showposts=$et_ptemplate_blog_perpage&paged=" . $et_paged . $cat_query); ?>
						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
						
							<div class="et_pt_blogentry clearfix">
								<h2 class="et_pt_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								
								<p class="et_pt_blogmeta"><?php esc_html_e('Posted','Fusion'); ?> <?php esc_html_e('by','Fusion'); ?> <?php the_author_posts_link(); ?> <?php esc_html_e('on','Fusion'); ?> <?php the_time(get_option('lucid_date_format')) ?> <?php esc_html_e('in','Fusion'); ?> <?php the_category(', ') ?> | <?php comments_popup_link(esc_html__('0 comments','Fusion'), esc_html__('1 comment','Fusion'), '% '.esc_html__('comments','Fusion')); ?></p>
								
								<?php $thumb = '';
								$width = 184;
								$height = 184;
								$classtext = '';
								$titletext = get_the_title();

								$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext);
								$thumb = $thumbnail["thumb"]; ?>
								
								<?php if ( $thumb <> '' && !$et_ptemplate_showthumb ) { ?>
									<div class="et_pt_thumb alignleft">
										<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
										<a href="<?php the_permalink(); ?>"><span class="overlay"></span></a>
									</div> <!-- end .thumb -->
								<?php }; ?>
								
								<?php if (!$et_ptemplate_blogstyle) { ?>
									<p><?php truncate_post(550);?></p>
									<a href="<?php the_permalink(); ?>" class="readmore"><span><?php esc_html_e('read more','Fusion'); ?></span></a>
								<?php } else { ?>
									<?php
										global $more;
										$more = 0;
									?>
									<?php the_content(); ?>
								<?php } ?>
							</div> <!-- end .et_pt_blogentry -->
							
						<?php endwhile; ?>
							<div class="page-nav clearfix">
								<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
								else { ?>
									 <?php get_template_part('includes/navigation'); ?>
								<?php } ?>
							</div> <!-- end .entry -->
						<?php else : ?>
							<?php get_template_part('includes/no-results'); ?>
						<?php endif; wp_reset_query(); ?>
					</div> <!-- end #et_pt_blog -->
				</article> <!-- end .post-->

			<?php endwhile; ?>
		
		</div> <!-- end #left-area -->
		
		<?php if ( ! $fullwidth ) get_sidebar(); ?>
	</div> <!-- .container -->
</div> <!-- #content -->
	
<?php get_footer(); ?>