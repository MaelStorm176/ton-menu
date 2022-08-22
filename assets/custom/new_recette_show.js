import {showSuccess, showError} from "./toasts";

$(document).ready(function () {
  $("input[name='rating']").click(function () {
    const id = $(this).data("recipe");
    const rating = $(this).val();
    rate(id, rating);
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