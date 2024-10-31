jQuery(function($) {
  jQuery('form.checkout').on('click', 'input.qty', function(e) {
    var data = {
      action    : 'order_update_quantity',
      post_data : $('form.checkout').serialize(),
      security  : QCFWC.ajax_nonce,
    };
    $.post(
      QCFWC.ajax_url,
      data,
      function(response) {
        $('body').trigger('update_checkout');
      }
    )
  })
});