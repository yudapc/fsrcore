jQuery(document).ready(function () {
  var container = jQuery(document).find('.post-type-fre_profile #misc-publishing-actions');
  var uid = jQuery(document).find('#post_author').val();
  container.append('<div class="misc-pub-section"><a href="/profile/?impersonate=' + uid + '">Edit profile meta in front-end</a></div>');
});