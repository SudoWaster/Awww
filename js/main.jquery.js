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
      linkBind();
      
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

var norefreshConfirmReloadAction = function($elem, event) {
  event.preventDefault();
  
  var canDo = confirm($elem.data('msg'));
  
  if(canDo) {
    var page = $elem.data('ref').split('?');
    var address = 'awww_includes/content/pages/' + page[0] + '.php?' + page[1];
    
    isLoading = true;
    
    $.get(address, function(data) {
      isLoading = false;
      location.reload();
    } );
  }
}

var updateAccount = function ($elem, e) {
  e.preventDefault();
  
  var uname   = $elem.find('#inputName').val();
  var ulname  = $elem.find('#inputLastName').val();
  var umail   = $elem.find('#inputMail').val();
  var upass   = $elem.find('#inputPassword').val();
  var unpass  = $elem.find('#inputNewPassword').val();  
  
  $.post('awww_includes/content/pages/updateprofile.php', 
         { name: uname, lname: ulname, mail: umail, npass: unpass, pass: upass }, 
         function(data) { location.reload();  });
  
  return false;
}

var scrollTo = function($elem, event) {
  event.preventDefault();
  
  var target = '#' + $elem.data('ref');
  
  $('html, body').animate({
      scrollTop: $(target).offset().top
  }, 300);
}

var linkBind = function() {
  $('.no-refresh').click(function(e) { norefreshAction($(this), e) } );
  $('.no-refresh-confirm').click(function(e) { norefreshConfirmAction($(this), e) } );
  
  $('.no-refresh-confirm-reload').click(function(e) { norefreshConfirmReloadAction($(this), e) } );
  
  $('.scroll-to').click(function(e) { scrollTo($(this), e) } );
  
  $('#account-form').submit(function(e) { updateAccount($(this), e); return false; });
}

$(document).ready(function() {
  linkBind();
  hashReload();
});
