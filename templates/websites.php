<?php
$curated_posts_query = new WP_Query("meta_key=is_expresscurate&meta_value=1&posts_per_page=-1&order=DESC");
$curated_links = array();
$curated_posts = array();
?>
<div class="wrap">
    <div class="expresscurate_menu">
        <?php
        include(sprintf("%s/menu.php", dirname(__FILE__)));?>
    </div>
  <h2 class="expresscurate_displayNone">Top curated websites</h2>
  <div class="expresscurate_marginTop30">
    <?php
    if ($curated_posts_query->have_posts()) {
      while ($curated_posts_query->have_posts()) {
        $curated_posts_query->the_post();
        $meta_values = get_post_meta(get_the_ID());
        if ($meta_values['expresscurate_link_0'][0]) {
          $domain = parse_url($meta_values['expresscurate_link_0'][0]);

          if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain['host'], $regs)) {
            $curated_links[] = $regs['domain'];
            $curated_posts[$regs['domain']][] = '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
          } else {
            $curated_links[] = $domain['host'];
            $curated_posts[$domain['host']][] = '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
          }
        }
        //echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a><br />';
      }
    }
    wp_reset_postdata();
    $top_links = array_count_values($curated_links);
    arsort($top_links);
    $res = array_reverse($top_links);
    if (count($top_links)) {
      ?>
      <ul>
        <?php foreach ($top_links as $key => $top_link) { ?>
			<li>
				<h3 class="expresscurate_topCuratedHeader"><?php echo $key ?> (<?php echo $top_link ?>)<div></div></h3>
				<ul class="expresscurate_topCuratedLink">
					<?php foreach ($curated_posts[$key] as $i => $curated_post) { ?>
					 <li><?php echo $curated_post ?></li>
					<?php 
						if ($i == 4) {
							break;
						}						
					}?>
				</ul>
			</li>
		<?php }?>
      </ul>  
      <?php
    }else{?>
    <span class="expresscurate_notDefined">
        No Curated Post. <a href="<?php echo admin_url(); ?>post-new.php">Curate New Post Now</a>.
    </span>
   <?php }
    ?>
  </div>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery('.expresscurate_topCuratedHeader').on('click', function () {
			var header=jQuery(this);
			var ul=header.parent().find('ul');
			var div=header.find('div');
			
			if(ul.css('display')==='none'){
				ul.slideDown(300);
				header.css('color','#25bfa1');
				div.addClass('expresscurate_rotatedArrow');
			}else{
					ul.slideUp(300);
					header.css('color','#5c5c5c');
					div.removeClass('expresscurate_rotatedArrow');
				}
		 });
	});
</script>
