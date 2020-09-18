<?php
add_action( 'wp_ajax_ae_send_invite_custom', 'ae_send_invite_custom' );
add_action( 'wp_ajax_nopriv_ae_send_invite_custom', 'ae_send_invite_custom' );
function ae_send_invite_custom() {
    global $wpdb;
    extract($_POST);
    global $user_ID;
    try {
        if ( isset( $_POST['data'] ) && $_POST['data'] ) {
            $frontaction = new AE_User_Front_Actions( new AE_Users() );
            $frontaction->mail   = Fre_Mailing_Extend::get_instance();
            $mail_success = $frontaction->mail->invite_mail_custom( $_POST['user_id'], $_POST['data']['project_invites'] );
            if ( $mail_success || true ) {

                $invited        = $_POST['user_id'];
                $send_invite    = $user_ID;
                $invite_project = $_POST['data']['project_invites'];
                /**
                 * do action when user have a new invite
                 *
                 * @param int $invited invited user id
                 * @param int $send_invite user send invite
                 * @param Array $invite_project list of projects
                 *
                 * @since 1.3
                 * @author Dakachi
                 */
                foreach ( $invite_project as $key => $value ) {
                    do_action( 'fre_new_invite', $invited, $send_invite, $value );
                }


                $resp = array(
                    'success' => true,
                    'msg'     => __( 'Your invite has been sent!', ET_DOMAIN )
                );
            } else {
                $resp = array(
                    'success' => false,
                    'msg'     => __( 'Currently, you do not have any project available to invite this user.', ET_DOMAIN )
                );
            }
        } else {
            $resp = array(
                'success' => false,
                'msg'     => __( "Please choose at least one project!", ET_DOMAIN )
            );
        }
    } catch ( Exception $e ) {
        $resp = array(
            'success' => false,
            'msg'     => $e->getMessage()
        );
    }
    wp_send_json( $resp );
}

Class Fre_Mailing_Extend extends Fre_Mailing {

    public static $instance;

    static function get_instance() {
        if ( self::$instance == null ) {
            self::$instance = new Fre_Mailing_Extend();
        }

        return self::$instance;
    }

    /**
     * invite a freelancer to work on current user project
     *
     * @param int $user_id The user will be invite
     * @param int $project_id The project will be send
     *
     * @since 1.0
     * @author Dakachi
     */
    function invite_mail_custom( $user_id, $project_id ) {
        global $current_user, $user_ID;
        if ( $user_id && $project_id ) {

            // $user = new WP_User($user_id);
            // get user email
            $user_email = get_the_author_meta( 'user_email', $user_id );

            // mail subject
            $subject = sprintf( __( "You have an invitation to view a new posted job from %s.", ET_DOMAIN ), get_option( 'blogname' ) );

            // build list of project send to freelancer
            $project_info = '';
            foreach ( $project_id as $key => $value ) {
                // check invite this project or not
                if ( fre_check_invited( $user_id, $value ) ) {
                    continue;
                }
                $project_link = get_permalink( $value );
                $project_tile = get_the_title( $value );
                // create a invite message
                fre_create_invite( $user_id, $value );

                $project_info .= '<li><p>' . $project_tile . '</p><p>' . $project_link . '</p></li>';
            }

            if ( $project_info == '' ) {
                return false;
            }
            $project_info = '<ul>' . $project_info . '</ul>';

            // get mail template
            $message = '';
            $opt_send = ae_get_option('opt_new_invite_mail', true);

            if($opt_send){ //1.8.8
                if ( ae_get_option( 'invite_mail_template' ) ) {
                    $message = ae_get_option( 'invite_mail_template' );
                }

                // replace project list by placeholder
                $message = str_replace( '[project_list]', $project_info, $message );

                // send mail
                return $this->wp_mail( $user_email, $subject, $message, array(
                    'user_id' => $user_id,
                    'post'    => $value
                ) );
            }
        }
    }
    /**
     * Email to author's project
     */
    function bid_mail_custom( $bid_id ) {

        $project_id  = get_post_field( 'post_parent', $bid_id );
        $post_author = get_post_field( 'post_author', $project_id );
        $author      = get_userdata( $post_author );
        if ( $author ) {
            $message = ae_get_option( 'bid_mail_template' );
            $bid_msg = get_post_field( 'post_content', $bid_id );
            $message = str_replace( '[message]', $bid_msg, $message );
            //$subject = sprintf( __( "Your project posted on %s has a new bid.", ET_DOMAIN ), get_option( 'blogname' ) );
            $subject = sprintf( __( "Someone expressed interest on your job posted on %s.", ET_DOMAIN ), get_option( 'blogname' ) );
            $opt_send = ae_get_option('opt_new_bid_email', true); // 1.8.8
            if($opt_send){
                return $this->wp_mail( $author->user_email, $subject, $message, array(
                    'post'    => $project_id,
                    'user_id' => $post_author
                ), '' );
            }
        }

        return false;
    }

}