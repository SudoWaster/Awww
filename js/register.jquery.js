$(document).ready(function() {
  $('.form-signin').submit(function(e) {
    e.preventDefault();
    
    var $form = $(this);
    
    var imail   = $form.find('#inputEmail').val(), 
        imail2  = $form.find('#inputEmail2').val(),
        iname   = $form.find('#inputName').val(),
        ilname  = $form.find('#inputLastName').val(),
        ipass   = $form.find('#inputPassword').val();
    
    $form.find('#signin-button').prop('disabled', true);
    
    if (ipass.length < 8) {
      $form.find('#inputPassword').addClass('is-invalid');
      $form.find('#signin-button').prop('disabled', false);
      
      return false;
    }
    
    if(imail != imail2) {
      $form.find('#inputEmail').addClass('is-invalid');
      $form.find('#inputEmail2').addClass('is-invalid');
      $form.find('#signin-button').prop('disabled', false);
      
      return false;
    }
    
    
    $form.find('#inputPassword').removeClass('is-invalid');
    $form.find('#inputEmail').removeClass('is-invalid');
    $form.find('#inputEmail2').removeClass('is-invalid');
    
    var posting = $.post('awww_includes/register_do.php', 
                         { mail: imail, mail2: imail, pass: ipass, name: iname, lastname: ilname });
    
    posting.done(function(data) {
      if (data != "ok") {
        var wut = data;
        $('#' + wut).addClass('is-invalid');
        $form.find('#signin-button').prop('disabled', false);
      } else {
        window.location.href = 'alogin';
      }
    });
    
    return false;
  });
});