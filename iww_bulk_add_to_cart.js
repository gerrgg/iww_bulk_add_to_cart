jQuery(document).ready( function($){
  function debounce(func, wait, immediate) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  };

  // global captures every input
  var capture = [];
  var sitename = window.location.protocol + '//' + window.location.hostname;
  var $discount_box = $('#current_discount');
  // TODO: Prices must reflect each variation = maybe put each in its own div and change that way

  // console.log( base_price );
  $('.var-bulk-update').blur( function(){
    // order_list resets with each change
    var order_list = [];
    var qty_total = 0;
    var qty = this.value;
    var id = jQuery(this).attr('id');
    /*
    TODO: Not sure if there is a better way to do key : value pairs in JS
    Pass the id as the KEY, and QTY as the value
    */
    capture[id] = qty;
    // loop
    for (var key in capture ){
      // validation
      if( capture.hasOwnProperty(key) ){
        qty_total += parseInt(capture[key]);
        // do not add if qty is 0; for accidental clicks.
        if( capture[key] != 0 ){
          // formatting; if qty is 1, only push the key, not the value for -> woocommerce_maybe_add_multiple_products_to_cart()
          ( capture[key] > 1 ) ? order_list.push( key+':'+capture[key] ) : order_list.push( key );
        }
        // order_list.push( key+':'+capture[key] );
      }
    }
    order_list = order_list.join(',');

    $discount_box.html(
      '<h3 class="m-0">Current Discount: ' + get_current_discount( qty_total ) + '%</h3>' + get_items_away_msg( qty_total )
   );

    $('.iww-bulk-price').each( function(){
      var base_price = $(this).next().text();
      $(this).html( get_discounted_price( base_price, qty_total ) );
    } );

    $( '#iww_bulk_form_submit' ).attr( 'href', sitename+ '/cart/?add-to-cart=' + order_list );

  });

  const maybePluralize = (count, noun, suffix = 's') => `${count} ${noun}${count !== 1 ? suffix : ''}`;

  function get_items_away_msg( qty_total ){
    while( qty_total >= 0 && qty_total < 12 ){
      return '<p class="lead">' + maybePluralize(items_away( qty_total ), 'item') +' away from next discount.</p>';
    }
    return "";
  }

  // TODO: Would be very smart to define the discount ranges in a constant; and better yet get those ranges from the DB
  function items_away( qty ){
   if( qty < 4 ){
     return 4 - qty;
   } else if ( qty >= 4 && qty < 7 ) {
     return 7 - qty;
   } else {
     return 12 - qty;
   }
  }

  function get_current_discount(qty_total){
    var discount = 0
    if( qty_total >= 4 && qty_total <= 6 ){
      discount = 5;
    }
    else if( qty_total >= 7 && qty_total <= 11 ){
      discount = 10;
    }
    else if( qty_total > 11 ){
      discount = 15;
    }
    return discount;
  }

  function get_discounted_price(base_price, qty_total){
    return( discount( base_price, get_current_discount( qty_total ) ) );
  }

  function discount( price, discount ){
    if( discount === 0 ) return;
    var percent = 1 - ( discount / 100 );
    var discount_price = ( price * percent );
    return '$' + discount_price.toFixed(2);
  }

  function find_price( method = "object" ){
    var $price_loc = $( '.woocommerce-variation-price' ).find( 'span.price' ).find( '.woocommerce-Price-amount.amount' );
     if( ! $price_loc.length ){
       $price_loc = $( '#price_range' ).find( 'span' ).find( '.woocommerce-Price-amount.amount' );
     }
     if( method === 'object' ){
       return $price_loc;
     } else {
       return $price_loc.text();
     }

  }


    $( 'form' ).on( 'change', 'input.qty', function() {
      var options = $( 'table.variations select' );
      var ready = [];
      var price;

      // check the values of each of the selections
      options.each( function() {
        ready.push( this.value.length );
      });

      // all selections have been made
      if ( ready.every( readyCheck ) ){
        // on init, qty is 1 so highlight tier 0
        $( 'td.price-tier-0' ).addClass( 'table-success' );
        // when quantity is changed
          var $price_loc = find_price();
          $( 'del' ).hide();
          // store old price
          var old_price = '$' + $( '.iww-no-discount' ).html();
          var qty = this.value;
          // strip all classes
          $( 'td.price-tier-0, td.price-tier-1, td.price-tier-2, td.price-tier-3' ).removeClass( 'table-success' );
          // highlight which box is applicable

          var ranges = $('.price-ranges').text().match( /\d+/g );
          // console.log(ranges);
          if( qty >= +ranges[2] && qty <= +ranges[3] ){
            $( 'td.price-tier-1' ).addClass( 'table-success' );
          }
          else if( qty >= +ranges[4] && qty <= +ranges[5] ){
            $( 'td.price-tier-2' ).addClass( 'table-success' );
          }
          else if( qty >= +ranges[6] ){
            $( 'td.price-tier-3' ).addClass( 'table-success' );
          }else{
            $( 'td.price-tier-0' ).addClass( 'table-success' );
            // $price_loc.html( old_price );
          }
          // grab the text from whatever box is highlighted
          var new_price = $( '.table-success' ).text();

          // if there is no new price, grab the old!
          if( new_price.length > 0 ){
            $price_loc.html( new_price );
          } else {
            $price_loc.html( old_price );
          }
        }

      });

});
