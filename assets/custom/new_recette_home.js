$(document).ready(function() {
  $('.input-group-prepend').on('click', 'button[data-bs-toggle="collapse"]', function (event) {
    $($(this).attr('data-bs-target')).show();
    event.stopPropagation();
  });
});