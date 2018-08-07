	


<div class="row">
<div class="title">
<h2 class="blue">Latest Post</h2></div>
<div class="lay1">


<?php if(of_get_option('frontcat_checkbox') == "1"){ ?>
<?php if(is_front_page()) { 
	 $args = array(
				   'cat' => ''.$os_front = of_get_option('front_cat').'',
				   'post_type' => 'post',
				   'paged' => ( get_query_var('paged') ? get_query_var('paged') : 1),
				   'posts_per_page' => ''.$os_fonts = of_get_option('frontnum_select').'');
	 new WP_Query( $args );
} ?>
<?php }?>

                   
				   
				   <?php if(have_posts()): ?><?php while(have_posts()): ?><?php the_post(); ?>
                <div <?php post_class(); ?> id="post-<?php the_ID(); ?>"> 
                             
 
   
   
   
              

                  
            

          
            
                <div class="post_image">
                
               <a class="imgpost">
			   
			     <?php  if ( get_the_post_thumbnail() != '' ) {
						        
								 echo '<div class=" imgwrap">';
    
                                 echo '<a href="';esc_url( the_permalink()); echo '" >';
                                 the_post_thumbnail();
                                 echo '</a>';
                                 echo '</div>';
                                 } else {
    
                                echo '<div class=" imgwrap">';
                                echo '<a href="'; esc_url( the_permalink()); echo '">';
     							echo '<img src="';
     							echo  isis_catch_that_image();
     							echo '" alt="" />';
     							echo '</a>';
    							echo '</div>';
    					};?>
               
               </a>      
                     <!--CALL TO POST IMAGE-->
         
                   
                   <div class="ch-item " > 
                   
                     <div class="ch-info">
                    
                  
						<h3><?php $category = get_the_category(); if($category[0]){echo '<a href="'.get_category_link($category[0]->term_id ).'">+'.$category[0]->cat_name.'</a>';}?></h3>
                           
                          
                        
                        <p><?php the_time('d'); ?><?php the_time('S'); ?> <?php the_time('M'); ?> <?php the_time('Y'); ?></p>
                        
                         <a class="more2"  href="<?php the_permalink();?>" >More</a>
                       
                        
                        
                        
                        </div></div>
                        
                   
                  </div>
                
               
                 
                 <div class="postcontent">
                    <h2 class="postitle"><a href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                    <p> <?php echo excerpt(20); ?>   </p>
            </div>   
           
    
                  
  				 
                </div>         
           
           
           
      <?php endwhile ?> 

            <?php endif ?>  
            
       <?php get_template_part('pagination'); ?>             
       </div>       

    </div>
 
    </div>