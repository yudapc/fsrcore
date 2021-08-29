<?php
//Remove fre_escrow_settings from admin area
add_action( 'init', 'remove_fre_escrow_settings_admin' );
function remove_fre_escrow_settings_admin() {
    // remove the filter
    remove_filter( 'ae_admin_menu_pages', 'fre_escrow_settings' );
    add_filter( 'ae_admin_menu_pages', 'fre_escrow_settings_override', 1, 99);
    function fre_escrow_settings_override($pages) {
        unset($pages['1']);
        return $pages;
    }
}
//Send Notification to slack for new project
add_action( 'fre_assign_project', 'send_notification_to_slack_after_new_contract', 2, 20 );
function send_notification_to_slack_after_new_contract ($project, $bid_id)
{
    $project_id = $project->ID;
    $bid_id = get_post_meta($project_id,'accepted',true);
    $employer_id = get_post_field( 'post_author', $project_id );
    $talent_id = get_post_field( 'post_author', $bid_id );
    $job_budget = get_post_meta($project_id,'et_budget',true);
    $job_average = get_post_meta($talent_id,'bid_average',true);
    $project_deadline = get_post_meta($project_id,'project_deadline',true);
    $project_country = wp_get_object_terms( $project_id, 'country', array( 'fields' => 'names' ) );
    $project_category = wp_get_object_terms( $project_id, 'project_category', array( 'fields' => 'names' ) );
    $project_skill = wp_get_object_terms( $project_id, 'skill', array( 'fields' => 'names' ) );
    $mil_args = array(
                'post_type'      => 'ae_milestone',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'post_parent'    => $project_id,
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_key'       => 'position_order'
            );
    $mil_query = new WP_Query( $mil_args );
    $projectmilestones = array();
    if ( $mil_query->have_posts() ) {
        while ( $mil_query->have_posts() ) {
            $mil_query->the_post();
            $projectmilestones[] = get_the_title();
        }
    }
    wp_reset_postdata();
    $project_description = get_post_field('post_content', $project_id);
    $project_title = get_the_title($project_id);
    $talent_args = array(
        'author'        =>  $talent_id,
        'orderby'       =>  'post_date',
        'order'         =>  'ASC',
        'post_type' => 'fre_profile',
        'posts_per_page' => 1
    );
    $talent_profile = get_posts( $talent_args );
    $talent_profile_data = $talent_profile [0];
    $talent_profile_id = $talent_profile_data->ID;
    $talent_name = get_the_title($talent_profile_id);
    $about_talent = get_post_field('post_content', $talent_profile_id);
    $talent_country = wp_get_object_terms( $talent_profile_id, 'country', array( 'fields' => 'names' ) );
    $talent_skills = wp_get_object_terms( $talent_profile_id, 'skill', array( 'fields' => 'names' ) );
    $talent_hour_rate = get_post_meta($talent_profile_id,'hour_rate',true);
    $talent_rating_score = get_post_meta($talent_profile_id,'rating_score',true);
    $talent_total_projects_worked = get_post_meta($talent_profile_id,'total_projects_worked',true);
    $talent_experience = get_post_meta($talent_profile_id,'et_experience',true);
    $talent_user_data = get_userdata($talent_id);
    $talent_email = $talent_user_data->data->user_email;
    $employer_user_data = get_userdata($employer_id);
    $employee_email = $employer_user_data->data->user_email;
    $employee_name = $employer_user_data->data->display_name;

    //Total Job Worked Talent
    if($talent_total_projects_worked != ''){
        $talent_total_projects_worked = '*Total Job Worked:* '.$talent_total_projects_worked;
    }else{
        $talent_total_projects_worked = '';
    }
    //Experience Talent
    if($talent_experience != ''){
        $talent_experience = '*Experience:* '.$talent_experience;
    }else{
        $talent_experience = '';
    }
    //Rating Score Talent
    if($talent_rating_score != ''){
        $talent_rating_score = '*Rating Score:* '.$talent_rating_score;
    }else{
        $talent_rating_score = '';
    }
    //Hour Rate Talent
    if($talent_hour_rate != ''){
        $talent_hour_rate = '*Hour Rate:* '.$talent_hour_rate;
    }else{
        $talent_hour_rate = '';
    }
    //About Talent
    if($about_talent != ''){
        $about_talent = '*About Talent:* '.strip_tags($about_talent);
    }else{
        $about_talent = '';
    }
    //Job Title
    if($project_title != ''){
        $project_title = '*Job Title:* '.strip_tags($project_title);
    }else{
        $project_title = '';
    }
    //Job description
    if($project_description != ''){
        $project_description = '*Job Description:* '.strip_tags($project_description);
    }else{
        $project_description = '';
    }
    //Job budget
    if($job_budget != ''){
        $job_budget = '*Job Budget:* '.$job_budget;
    }else{
        $job_budget = '';
    }
    //Talent Budget for this job
    if($job_average != ''){
        $job_average = '*Talent rate for this job:* '.$job_average;
    }else{
        $job_average = '';
    }
    //Job Deadline
    if($project_deadline != ''){
        $project_deadline = '*Job Deadline:* '.$project_deadline;
    }else{
        $project_deadline = '';
    }
    //Job Category
    if(!empty($project_category)){
        $project_category_count = count($project_category);
        $project_category = implode(', ', $project_category);
        if($project_category_count == '1'){
            $project_category = '*Job Category:* '.$project_category;
        }else{
            $project_category = '*Job Categories:* '.$project_category;
        }
    }else{
        $project_category = '';
    }
    //Job Country
    if(!empty($project_country)){
        $project_country_count = count($project_country);
        $project_country = implode(', ', $project_country);
        if($project_country_count == '1'){
            $project_country = '*Job Country:* '.$project_country;
        }else{
            $project_country = '*Job Countries:* '.$project_country;
        }
    }else{
        $project_country = '';
    }
    //Job Skills
    if(!empty($project_skill)){
        $project_skill_count = count($project_skill);
        $project_skill = implode(', ', $project_skill);
        if($project_skill_count == '1'){
            $project_skill = '*Job Skill:* '.$project_skill;
        }else{
            $project_skill = '*Job Skills:* '.$project_skill;
        }
    }else{
        $project_skill = '';
    }
    //Job Milestone
    if(!empty($projectmilestones)){
        $projectmilestones_count = count($projectmilestones);
        $projectmilestones = implode(', ', $projectmilestones);
        if($projectmilestones_count == '1'){
            $projectmilestones = '*Job Milestone:* '.strip_tags($projectmilestones);
        }else{
            $projectmilestones = '*Job Milestones:* '.strip_tags($projectmilestones);
        }
    }else{
        $projectmilestones = '';
    }
    //Talent Country
    if(!empty($talent_country)){
        $talent_country_count = count($talent_country);
        $talent_country = implode(', ', $talent_country);
        if($talent_country_count == '1'){
            $talent_country = '*Talent Country:* '.$talent_country;
        }else{
            $talent_country = '*Talent Countries:* '.$talent_country;
        }
    }else{
        $talent_country = '';
    }
    //Talent Skills
    if(!empty($talent_skills)){
        $talent_skills_count = count($talent_skills);
        $talent_skills = implode(', ', $talent_skills);
        if($talent_skills_count == '1'){
            $talent_skills = '*Talent Skill:* '.$talent_skills;
        }else{
            $talent_skills = '*Talent Skills:* '.$talent_skills;
        }
    }else{
        $talent_skills = '';
    }
    $employee_link = get_author_posts_url($employer_id);
    $talent_link = get_author_posts_url($talent_id);

    $message = "Contract initiated between *<$employee_link|$employee_name>* and *<$talent_link|$talent_name>* \nContact the parties involved to set up the invoice and payout and confirm details.";
    //$message = "*Job Detail:* \n$project_title\n$project_description\n$project_category\n$project_skill\n$projectmilestones\n$project_country\n$job_budget\n$project_deadline\n\n\n*Employee Detail: *\n*Employee Name:* $employee_name\n*Employee Email:* $employee_email\n\n\n*Talent Detail: *\n*Talent Name:* $talent_name\n*Talent Email:* $talent_email\n$about_talent\n$talent_country\n$talent_skills\n$talent_hour_rate\n$talent_rating_score\n$talent_experience\n$talent_total_projects_worked\n$job_average";
    //$channel = '#testing'; //Scott
    $channel = '#payment'; //Client
    //$slack_username = 'leadwithdigital.slack.com'; //Scott
    $slack_username = 'fullstackremote.slack.com'; //client
    $ch = curl_init("https://slack.com/api/chat.postMessage");
    $data = http_build_query([
        //"token" => "xoxb-540032502243-1188088125991-ZWpBLARKWLs4PdiwIPF7tdpP",//scott
        "token" => "xoxb-1105774642880-1209295909540-IeOqNUeZo1GHcHR5PK6Om7mQ", //Client
        "channel" => $channel, //"#mychannel",
        "text" => $message, //"Hello, Foo-Bar channel message.",
        "username" => $slack_username,
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
}
// Function to change from email address
function fsremotestage_sender_email( $original_email_address ) {
    return 'hire@fullstackremote.com';
}
// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'fsremotestage_sender_email' );
//overide the header color
if (!function_exists('et_get_customization')) {
    /**
     * Get and return customization values for
     * @since 1.0
     */
    function et_get_customization() {
        $style = get_option('ae_theme_customization', true);
        $style = wp_parse_args($style, array(
            'background' => '#ffffff',
            // changed by dennis
            // 'header' => '#2980B9',
            'header' => '#FFFFFF',
            'heading' => '#37393a',
            'text' => '#7b7b7b',
            'action_1' => '#8E44AD',
            'action_2' => '#3783C4',
            'project_color' => '#3783C4',
            'profile_color' => '#3783C4',
            'footer' => '#F4F6F5',
            'footer_bottom' => '#fff',
            'font-heading-name' => 'Raleway,sans-serif',
            'font-heading' => 'Raleway',
            'font-heading-size' => '15px',
            'font-heading-style' => 'normal',
            'font-heading-weight' => 'normal',
            'font-text-name' => 'Raleway, sans-serif',
            'font-text' => 'Raleway',
            'font-text-size' => '15px',
            'font-text-style' => 'normal',
            'font-text-weight' => 'normal',
            'font-action' => 'Open Sans, Arial, Helvetica, sans-serif',
            'font-action-size' => '15px',
            'font-action-style' => 'normal',
            'font-action-weight' => 'normal',
            'layout' => 'content-sidebar'
        ));
        return $style;
    }
}
//Override email content
add_filter('ae_filter_auth_email', 'ae_custom_mail_content');
function ae_custom_mail_content($content) {
    $content = str_replace("Confirm link", "Confirm", $content);
    return $content;
}
//override email header
add_filter('ae_get_mail_header', 'ae_custom_mail_header');
function ae_custom_mail_header($mail_header) {
    $logo_url = get_template_directory_uri() . "/img/logo-de.png";
    $options  = AE_Options::get_instance();

    // save this setting to theme options
    $site_logo = $options->site_logo;
    if ( ! empty( $site_logo ) ) {
        $logo_url = $site_logo['large'][0];
    }

    $logo_url = apply_filters( 'ae_mail_logo_url', $logo_url );

    $customize = et_get_customization();
    $css = apply_filters('et_mail_css','');
    $mail_header = '<html>
                    <head>'.$css.'
                    </head>
                    <body style="font-family: Arial, sans-serif;font-size: 0.9em;margin: 0; padding: 0; color: #222222;">
                    <div style="margin: 0px auto; width:600px; border: 1px solid ' . $customize['background'] . '">
                        <table width="100%" cellspacing="0" cellpadding="0">
                        <tr style="background: ' . $customize['header'] . '; height: 63px; vertical-align: middle;">
                            <td style="padding: 10px 5px 10px 20px; width: 20%;">
                                <img style="max-height: 100px;width:250px;object-fit:contain;" src="' . $logo_url . '" alt="' . get_option( 'blogname' ) . '">
                            </td>
                            <td style="padding: 10px 20px 10px 5px">
                                <span style="text-shadow: 0 0 1px #151515; color: #b0b0b0;">' . get_option( 'blogdescription' ) . '</span>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height: 5px; background-color: ' . $customize['background'] . ';"></td></tr>
                        <tr>
                            <td colspan="2" style="background: #ffffff; color: #222222; line-height: 18px; padding: 10px 20px;">';

    return $mail_header;
}
//override email footer
add_filter('ae_get_mail_footer', 'ae_custom_mail_footer');
function ae_custom_mail_footer($mail_footer){
    $footer_email = "hire@fullstackremote.com";
    $info = apply_filters( 'ae_mail_footer_contact_info', get_option( 'blogname' ) . ' <br>
                    ' . $footer_email . ' <br>' );
    $customize = et_get_customization();
    $copyright = apply_filters( 'get_copyright', ae_get_option( 'copyright' ) );

    $mail_footer = '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="background: ' . $customize['background'] . '; padding: 10px 20px; color: #666;">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="vertical-align: top; text-align: left; width: 50%;">' . $copyright . '</td>
                                    <td style="text-align: right; width: 50%;">' . $info . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </table>
                </div>
                </body>
                </html>';

    return $mail_footer;
}
//Extend core functionality
//Remove validatation for budget field from post project form
/*add_filter( 'fre_project_required_fields', 'remove_validation_for_budget_field' );
function remove_validation_for_budget_field($require_fields) {
    unset($require_fields[0]);
    return $require_fields;
}*/
//Remove validation for bid time field
add_filter( 'fre_bid_required_field', 'remove_validation_for_bid_time_field' );
function remove_validation_for_bid_time_field($bid_required_field) {
    unset($bid_required_field[1]);
    return $bid_required_field;
}
//Add user in profile list after register
add_filter( 'ae_after_insert_user','add_talebt_in_profile_list_after_register');
function add_talebt_in_profile_list_after_register($result) {
    $user_ID = $result->id;
    $user = get_userdata( $user_ID );
    $user_roles = $user->roles;
    if ( in_array( 'freelancer', $user_roles, true ) ) {
        $user_login = $result->user_login;
        $display_name = $result->display_name;
        if($display_name == ''){
            $profile_title = $user_login;
        }else{
            $profile_title = $display_name;
        }
        $new_talent_profile = array(
            'post_title' => $profile_title,
            'post_status' => 'publish',
            'post_author' => $user_ID,
            'post_type' => 'fre_profile',
        );
        $talent_profile_id = wp_insert_post($new_talent_profile);
        update_post_meta( $talent_profile_id,'user_available','on' );
        update_post_meta( $talent_profile_id,'et_professional_title',$user_login );
        update_post_meta( $talent_profile_id,'hour_rate','0' );
        update_user_meta( $user_ID,'user_profile_id',$talent_profile_id );
        return $result;
    }else{
        return $result;
    }
}
//Required files for override theme core functionalities
//require_once dirname(__FILE__) . '/aecore/class-ae-base.php';
require_once get_template_directory() . '/includes/aecore/class-ae-base.php';
require_once get_template_directory() . '/includes/aecore/class-ae-mailing.php';
require_once get_template_directory() . '/includes/aecore/class-ae-post.php';
require_once get_template_directory() . '/includes/bids.php';
require_once get_template_directory() . '/includes/mailing.php';
require_once dirname(__FILE__) . '/invitation_email.php';
require_once dirname(__FILE__) . '/new_bid_email.php';