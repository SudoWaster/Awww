$(document).ready(function() {
  $('.form-signin').submit(function(e) {
    e.preventDefault();
    
    var $form = $(this);
    
    var imail  = $form.find('#inputEmail').val(), 
        ipass  = $form.find('#inputPassword').val();
    
    var posting = $.post('awww_includes/login_do.php', 
                         { login: imail, pass: ipass });
    
    posting.done(function(data) {
      window.location.href = 'awww-panel.php';
      console.log("ejże");
    });
  });
});