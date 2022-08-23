import {showSuccess, showError} from "./toasts";

$(document).ready(function () {
  $("input[name='rating']").click(function () {
    const id = $(this).data("recipe");
    const rating = $(this).val();
    rate(id, rating);
  });

  $("#share-recipe").click(function () {
    navigator.clipboard.writeText($(this).data("link")).then(() => {
      showSuccess("Lien copié dans le presse-papier");
    })
    .catch(err => {
      console.error(err);
      showError("Une erreur est survenue");
    });
  });

  $("#like-recipe").click(function () {
    const id = $(this).data("recipe");
    like(id);
  });

});

function rate(id, rating) {
  $.ajax({
    url: '/rate/' + id,
    type: 'POST',
    data: {
      rating: rating
    },
    success: function (data) {
      if (data.error)
        showError(data.error);
      else {
        showSuccess("Votre note a bien été prise en compte !");
      }
    }
  });
}

function like(id) {
  $.ajax({
    url: '/recipe/like/' + id,
    type: 'POST',
    success: function (data) {
      if (data.error)
        showError(data.error);
      if (data.message) {
        showSuccess(data.message);
      }
      else {
        showSuccess("Vous aimez cette recette !");
        $("#like-recipe").html(`
        <i class='bi bi-heart-fill'></i>
        Vous aimez cette recette !
        `);
      }
    }
  });
}