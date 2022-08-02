$(document).ready(function() {
  $('.input-group-prepend').on('click', 'button[data-bs-toggle="collapse"]', function (event) {
    $($(this).attr('data-bs-target')).show();
    event.stopPropagation();
  });

  $('#search_recipe_tags').select2({
    placeholder: "Sélectionner un ou plusieurs tags",
    allowClear: true,
    multiple: true,
    theme: "classic"
    /*
    ajax: {
      url: '/api/tags',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // search term
          page: params.page
        };
      }
    }*/
  });
});