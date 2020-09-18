<?php
/**
 * The template for displaying profile in a loop
 * @since  1.0
 * @package FreelanceEngine
 * @category Template
 */
global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get( PROFILE );
$current = $post_object->current_post;
if(!$current){
    return;
}
$hou_rate = (int) $current->hour_rate;
$postid = $current->ID;
$postdata = get_post($postid);
$convert = $post_object->convert($postdata);
?>
<li class="profile-item">
    <div class="profile-list-wrap">
        <a class="profile-list-avatar" href="<?php echo $current->permalink; ?>">
            <?php echo get_avatar($post->post_author); ?>
        </a>
        <h2 class="profile-list-title">
            <a href="<?php echo $current->permalink; ?>"><?php the_author_meta( 'display_name', $post->post_author ); ?></a>
        </h2>
        <p class="profile-list-subtitle"><?php echo $current->et_professional_title;?></p>
        <div class="profile-list-info">
            <div class="profile-list-detail">
                <!-- <span class="rate-it" data-score="<?php echo $current->rating_score ; ?>"></span> -->
                <span><?php echo $current->experience ?></span>
                <!-- <span><?php echo $current->project_worked; ?></span> -->

                <!-- <?php if( $hou_rate > 0 ) { echo '<span>'; echo $current->hourly_rate_price; echo '</span>'; } ?> -->

                <!-- <span style="font-weight: normal"><?php //echo ($current->earned); ?></span> -->
            </div>
            <div class="profile-list-desc">
	            <?php echo $current->excerpt;?>
            </div>
            <div class="free-skill">
                <ul class="free-skill-list">
                    <?php
                        $i = 1;
                        if ( isset($convert->tax_input['skill']) && $convert->tax_input['skill'] ) {
                            $total_skill   = count( $convert->tax_input['skill'] );
                            $string_length = 0;
                            foreach ( $convert->tax_input['skill'] as $tax ) {
                                $string_length += strlen( $tax->name );
                                ?>
                                <li><span class="skill-name-profile"><?php echo $tax->name; ?></span></li>
                                <?php
                                if ( $string_length > 20 ) {
                                    break;
                                }
                                if ( $i >= 4 ) {
                                    break;
                                }
                                $i ++;
                            }
                            if ( $i < $total_skill ) {
                                echo '<li><span class="skill-name-profile">+' . ( $total_skill - $i ) . '</span></li>';
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</li>
