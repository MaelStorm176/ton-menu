import "blueimp-file-upload";
import {showError} from "./toasts";

$(document).ready(function() {
  /*
  const ingredientImages = $('.input-images');
  ingredientImages.imageUploader({
    maxSize: 2 * 1024 * 1024,
    maxFiles: 1
  });
   */
  let isSubmitted = false;
  $("form[name='ingredient']").submit(function(e) {
    if (isSubmitted === true) {
      return;
    }
    e.preventDefault();
    const form = $(this);
    const formData = form.serializeArray();

    //On test si le nom de l'ingrédient existe en base
    if ($("input[name='name']").val() !== "" && formData[0].value !== "") {
      $.ajax({
        url: '/ingredient/check-name?name=' + formData[0].value,
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