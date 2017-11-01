var norefreshAction = function($elem, event) {
  event.preventDefault();
  
  var address = '/awww_includes/content/' + $elem.data('ref') . '.php';
  
  $('#main').load(address, function() {
    norefreshBind();
  });
}

var norefreshBind = function() {
  $('.no-refresh').click(function(e) { norefresh-action($(this), e) } );
}

$(document).ready(function() {
  norefreshBind();
});
