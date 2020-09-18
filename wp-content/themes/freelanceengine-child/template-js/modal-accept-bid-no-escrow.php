<div class="modal fade" id="accept-bid-no-escrow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title">
					<?php _e( "Hire now", ET_DOMAIN ) ?>
                </h4>
            </div>
            <div class="modal-body">
                <form role="form" id="accept_bid_no_escrow" class="fre-modal-form">
                    <div class="fre-content-confirm">
                        <h2><?php _e( 'Are you sure you want to hire this person now?', ET_DOMAIN ); ?></h2>
                        <p><?php _e( "Once you click Confirm, your job status will change to Processing and the contract will be initiated. You are responsible for paying 2 weeks upfront. A representative from FullStackRemove will contact you to confirm details and send invoice and set up payment schedule.", ET_DOMAIN ) ?></p>
                    </div>
                    <div class="fre-form-btn">
                        <button type="button" class="fre-normal-btn"
                                id="submit_accept_bid"><?php _e( "Confirm", ET_DOMAIN ) ?></button>
                        <span class="fre-form-close" data-dismiss="modal"><?php _e( 'Cancel', ET_DOMAIN ); ?></span>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog login -->
</div><!-- /.modal -->