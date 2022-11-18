$(document).ready(function() {

  $("#filter").on("click", function() {
    const dropdown = $("#cascade");
    dropdown.is(":visible") ? dropdown.hide() : dropdown.show();
  });

  $('#search_recipe_tags').select2({
    placeholder: "Sélectionner un ou plusieurs tags",
    allowClear: true,
    multiple: true,
    theme: "bootstrap-5",
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
    theme: "bootstrap-5",
    closeOnSelect: true,
    width: "100%"
  });

  $('#search_recipe_author').select2({
    placeholder: "Sélectionner un ou plusieurs chefs",
    allowClear: true,
    multiple: true,
    theme: "bootstrap-5",
    closeOnSelect: true,
    width: "100%"
  });
});