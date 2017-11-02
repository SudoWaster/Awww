var hashReload = function() {
  if (window.location.hash.substr(1,1) == "!") {
  
    $("#brand").addClass('blink');
    
    var hash = window.location.hash.substr(2);
    var address = 'awww_includes/content/pages/' + hash + '.php';
  
    $('#main').load(address, function() {
      norefreshBind();
      $("#brand").removeClass('blink');
    });
  }
}

var norefreshAction = function($elem, event) {
  event.preventDefault();
  
  window.location.hash = '!' + $elem.data('ref');
  hashReload();
}

var norefreshBind = function() {
  $('.no-refresh').click(function(e) { norefreshAction($(this), e) } );
}

$(document).ready(function() {
  norefreshBind();
  hashReload();
});
