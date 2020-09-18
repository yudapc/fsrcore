<?php
//Remove fre_escrow_settings from admin area
add_action( 'init', 'remove_ae_insert_bid' );
function remove_ae_insert_bid() {
    // remove the filter
    remove_action( 'ae_insert_bid', 'fre_update_after_bidding', 12, 1 );
    add_action( 'ae_insert_bid', 'fre_update_after_bidding', 1, 99);
}
/*
 * update project and bid after have a bid succesfull.
*/
function fre_update_after_bidding( $bid_id ) {
    global $user_ID;
    if ( 'publish' != get_post_status( $bid_id ) ) {
        wp_update_post( array(
            'ID'          => $bid_id,
            'post_status' => 'publish'
        ) );
    }

    $project_id = get_post_field( 'post_parent', $bid_id );

    //update avg bids for project
    $total_bids = get_number_bids( $project_id );
    $avg        = get_post_meta( $bid_id, 'bid_average', true );
    if ( $total_bids > 0 ) {
        $avg = get_total_cost_bids( $project_id ) / $total_bids;
    }

    update_post_meta( $project_id, 'bid_average', number_format( $avg, 2 ) );
    update_post_meta( $project_id, 'total_bids', $total_bids );

    $newBid = new Fre_BidAction_Extend();
    $newBid->mail->bid_mail_custom( $bid_id );
    // $pay_credit = get_credit_to_pay(); disable from 1.8.9 - trash code.

    if ( ae_get_option( 'pay_to_bid', false ) ) {
        $number_free_bid = fre_get_free_bid_current_month();
        if( $number_free_bid > 0 ){ // add from 1.8.9
            fre_update_free_bid(); // -1 time free bid on this month.
        } else {
            if( ! is_acti_fre_membership() ){
                $pay_credit = - 1; // -1 time bidding.
                update_credit_number( $user_ID, $pay_credit );
            } else {
                update_remain_bid_of_membership();
            }
        }
    }
    wp_send_json( array(
        'success' => true,
        'msg'     => __( 'Your interest has been submitted.', ET_DOMAIN )
    ) );
}



/**
 * class control all action related to a bid object
 * @author Dan
 */
class Fre_BidAction_Extend extends Fre_BidAction {
	public static $instance;

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new Fre_BidAction_Extend();
		}

		return self::$instance;
	}

	public function __construct( $post_type = BID ) {

		// init mail instance to send mail
		$this->mail = Fre_Mailing_Extend::get_instance();
	}
}
/**
 * accept a bid for project
 * @author Dan
 */
add_action( 'wp_ajax_ae_accept_bid_custom', 'ae_accept_bid_custom' );
add_action( 'wp_ajax_nopriv_ae_accept_bid_custom', 'ae_accept_bid_custom' );
function ae_accept_bid_custom() {
    $request = $_POST;
    $bid_id  = isset( $request['bid_id'] ) ? $request['bid_id'] : '';
    $FreBidAction = new Fre_BidAction();
    $result  = $FreBidAction->assign_project( $bid_id );

    if ( ! is_wp_error( $result ) ) {

        /**
         * fire action fre_accept_bid after accept a bid
         *
         * @param int $bid_id the id of accepted bid
         * @param Array $request
         *
         * @since 1.2
         * @author Dakachi
         */
        do_action( 'fre_accept_bid', $bid_id );

        // send message to client
        wp_send_json( array(
            'success' => true,
            'msg'     => __( 'Job has been assigned successfully.', ET_DOMAIN )
        ) );
    }

    wp_send_json( array(
        'success' => false,
        'msg'     => $result->get_error_message()
    ) );
}