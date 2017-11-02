var isLoading = false;
var currentAddress = '';

var hashReload = function() {
  // if it is able to load
  if (window.location.hash.substr(1,1) == "!") {
  
    // loading animation
    $("#brand").addClass('blink');
    
    var hash = window.location.hash.substr(2);
    var page = hash.split('?');
    var address = 'awww_includes/content/pages/' + page[0] + '.php?' + page[1];
    
    // fix resources hogging
    if (isLoading && currentAddress == address) {
      return;
    }
    
    currentAddress = address;
    isLoading = true;
    
    // load page
    $('#main').load(address, function() {
      
      isLoading = false;
      norefreshBind();
      
      // turn off animation
      $("#brand").removeClass('blink');
    });
  }
}

var norefreshAction = function($elem, event) {
  event.preventDefault();
  
  window.location.hash = '!' + $elem.data('ref');
  hashReload();
}

var norefreshConfirmAction = function($elem, event) {
  event.preventDefault();
  
  var canDo = confirm($elem.data('msg'));
  
  if(canDo) {
    window.location.hash = '!' + $elem.data('ref');
    hashReload();
  }
}

var norefreshBind = function() {
  $('.no-refresh').click(function(e) { norefreshAction($(this), e) } );
  $('.no-refresh-confirm').click(function(e) { norefreshConfirmAction($(this), e) } );
}

$(document).ready(function() {
  norefreshBind();
  hashReload();
});
