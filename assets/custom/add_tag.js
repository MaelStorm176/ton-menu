import {showError} from "./toasts";

$(document).ready(function() {
  let isSubmitted = false;

  $("#tag_recipe").select2({
    tags: true,
    placeholder: "Sélectionner une ou plusieurs recettes",
    allowClear: true,
    multiple: true,
    theme: "bootstrap-5",
    closeOnSelect: true,
    width: "100%",
  });

  $("form[name='tag']").submit(function(e) {
    if (isSubmitted === true) {
      return;
    }
    e.preventDefault();
    const form = $(this);
    const formData = form.serializeArray();

    //On test si le nom de l'ingrédient existe en base
    if ($("input[name='tag[name]']").val() !== "" && formData[0].value !== "") {
      $.ajax({
        url: '/tag/check-name?name=' + formData[0].value,
        type: "get",
        dataType: "json",
        processData: false,
        success: function (data) {
          if (!data.success && data.message) {
            showError(data.message);
          } else {
            isSubmitted = true;
            form.submit();
            return true;
          }
        }
      });
    }
    return false;
  });

  if ($("#recipesTable"))
  {
    $("#recipesTable").DataTable({
      pageLength: 10,
      responsive: true,
    });
  }
});