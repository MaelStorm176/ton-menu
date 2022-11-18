import {showError, showSuccess} from "./toasts";

$(document).ready(function() {
  const recipes = $('#recipesTable2');
  if (recipes.length > 0) {
    recipes.DataTable();
  }

  $("#profile_profile_picture").on("change", (e) => {
    const image = $("#output");
    if (e.target.files[0]["type"] === "image/jpeg" || e.target.files[0]["type"] === "image/png" || e.target.files[0]["type"] === "image/jpg") {
      image.attr('src', URL.createObjectURL(e.target.files[0]));
    }else{
      showError("Format d'image invalide");
    }
  });

  $("#button-addon2").on("click", (e) => {
    const apiKey = $("#apiKey").val();
    navigator.clipboard.writeText(apiKey).then(() => {
      showSuccess("Clé copiée dans le presse-papier");
    })
    .catch(err => {
      console.error(err);
      showError("Une erreur est survenue");
    });
  });

  $("#button-refresh").on("click", (e) => {
    //refresh apiKey
    $.ajax({
      url: "/profile/refresh-key",
      type: "GET",
      dataType: "json",
      cache: false,
      success: (data) => {
        if (data.success && data.key) {
          $("#apiKey").val(data.key);
          showSuccess("Clé réactualisée");
        } else {
          showError("Une erreur est survenue");
        }
      },
      error: (err) => {
        showError("Une erreur est survenue");
      }
    });
  });
});

