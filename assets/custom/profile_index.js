import {showError, showSuccess} from "./toasts";

$(document).ready(function() {
  $("#recipesTable2").DataTable();
  $("#profile_profile_picture").on("change", (e) => {
    const image = $("#output");
    if (e.target.files[0]["type"] == "image/jpeg" || e.target.files[0]["type"] == "image/png" || e.target.files[0]["type"] == "image/jpg") {
      image.attr('src', URL.createObjectURL(e.target.files[0]));
    }else{
      console.log("tamer");
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
});

