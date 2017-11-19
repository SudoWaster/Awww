/*
 * PRESENCE
 *
 */
var setCheckboxPresence = function($elem) {
  var groupID = $elem.data('group');
  var userID  = $elem.data('user');
  var date    = $elem.data('date');
  var checked = $elem.is(':checked') ? 1 : 0;
  
  $.post ( 'awww_includes/content/pages/setpresence.php', 
          { gid: groupID, day: date, uid: userID, present: checked},
         function(data) { console.log(data); });
}


/*
 * ACHIEVEMENTS
 *
 */

var addAchievementAction = function($elem, e) {
  e.preventDefault();
  
  $('.new-achievement').toggleClass('hidden');
  $elem.toggleClass('hidden');
};

var saveAchievementAction = function(e) {
  e.preventDefault();
  
  var form = '#achievement-add-form';
  
  var g_id    = $(form + ' .group-id').val();
  var a_name  = $(form + ' .achievement-title').val();
  var a_desc  = $(form + ' .achievement-desc').val();
  
  $.post('awww_includes/content/pages/saveachievement.php', 
         { gid: g_id, title: a_name, desc: a_desc },
        function() {
          location.reload();
        });
  
  return false;
};

var setCheckboxAchievement = function($elem) {
  var aID = $elem.data('achiev');
  var userID  = $elem.data('user');
  var checked = $elem.is(':checked') ? 1 : 0;
  
  $.post ( 'awww_includes/content/pages/setachievement.php', 
          { aid: aID, uid: userID, assigned: checked },
         function(data) { console.log(data); });
}

/*
 * POSTS
 *
 */
var editGroupAction = function($elem, e) {
  e.preventDefault();
  
  $('.group-info').toggleClass('hidden');
  $('.group-edit').toggleClass('hidden');
};

var saveGroupAction = function(e) {
  e.preventDefault();
  
  var form = '#group-edit-form';
  
  var g_id    = $(form + ' #gid').val();
  var g_name  = $(form + ' #gname').val();
  var g_desc  = $(form + ' #gdesc').val();
  var g_vac   = $(form + ' #gvac').val();
  
  $.post('awww_includes/content/pages/savegroup.php', 
         { id: g_id, name: g_name, desc: g_desc, vacancies: g_vac },
        function() {
          location.reload();
        });
  
  return false;
};

var addGroupAction = function(e) {
  e.preventDefault();
  
  var form = '#group-add-form';
  
  var g_name  = $(form + ' #gname').val();
  var g_desc  = $(form + ' #gdesc').val();
  var g_vac   = $(form + ' #gvac').val();
  
  $.post('awww_includes/content/pages/savegroup.php', 
         { name: g_name, desc: g_desc, vacancies: g_vac },
        function(data) {
          window.location.hash = '!group?id=' + data;
          location.reload();
        });
  
  return false;
};

/*
 * POSTS
 *
 */
var editAction = function($elem, e) {
  e.preventDefault();
  
  var post = '#post-' + $elem.data('post');
  
  $(post + ' .post-content').toggleClass('hidden');
  $(post + ' .post-content-editable').toggleClass('hidden');
};

var newAction = function($elem, e) {
  e.preventDefault();
  
  $('#post-new .post-content-editable').toggleClass('hidden');
  $elem.toggleClass('hidden');
};

var saveAction = function(editor) {
  
  var p_id= $(editor.getContainer()).parent().find('.post-id').val();
  var g_id= $(editor.getContainer()).parent().find('.group-id').val();
  var p_c = editor.getContent();
  
  $.post('awww_includes/content/pages/savepost.php', 
         { id: p_id, gid: g_id, post: p_c },
        function() {
          location.reload();
        });
  
  return false;
};


/*
 * GENERAL
 *
 */

var bindButtons = function() {
  $('.new-button').click(function(e) { newAction($(this), e); });
  $('.edit-button').click(function(e) { editAction($(this), e); });
  
  $('.edit-group-button').click(function(e) { editGroupAction($(this), e); });
  
  $('.add-achievement-button').click(function(e) { addAchievementAction($(this), e); });
};

$(document).ready(function() {
  tinymce.init({ 
    selector: '.post-content-area',
    plugins: 'save link image',
    toolbar: 'save | undo redo | fontselect fontsizeselect | bold italic underine | link image',
    save_onsavecallback: function(editor) { saveAction(editor); }
  });
  
  bindButtons();
  
  $('#group-edit-form').submit(function(e) { saveGroupAction(e); return false; });
  $('#group-add-form').submit(function(e) { addGroupAction(e); return false; });
  $('#achievement-add-form').submit(function(e) { saveAchievementAction(e); return false; });
  
  $('.presence-check').change(function(e) { setCheckboxPresence($(this)); });
  $('.achievement-check').change(function(e) { setCheckboxAchievement($(this)); });
});