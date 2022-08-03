$(document).ready(function() {

  $(".dropdown-toggle").on("click", function() {
    const dropdown = $("#cascade");
    dropdown.is(":visible") ? dropdown.hide() : dropdown.show();
  });

  $('#search_recipe_tags').select2({
    placeholder: "Sélectionner un ou plusieurs tags",
    allowClear: true,
    multiple: true,
    theme: "classic",
    closeOnSelect: true,
    width: "100%"

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

  $('#search_recipe_ingredients').select2({
    placeholder: "Sélectionner un ou plusieurs ingrédients",
    allowClear: true,
    multiple: true,
    theme: "classic",
    closeOnSelect: true,
    width: "100%"
  });
});