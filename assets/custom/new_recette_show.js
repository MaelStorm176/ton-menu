import {showSuccess, showError} from "./toasts";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";

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

  $("#download-recipe").click(function () {
    const id = $(this).data("recipe");
    download(id);
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
      if (data.error){
        showError(data.error);
        return false;
      }
      if (data.message) {
        showSuccess(data.message);
        $("#like-recipe").html(`
          <i class='bi bi-heart'></i>
          Ajouter à mes favoris
        `);
      }
      else {
        showSuccess("Vous aimez cette recette !");
        $("#like-recipe").html(`
          <i class='bi bi-heart-fill'></i>
          Retirer de mes favoris
        `);
      }
    }
  });
}

function download(id){
  const recipeContainer = $("#recipe > .container");
  recipeContainer.find("#main-image").remove();
  const recipe = recipeContainer.html();

  html2canvas(recipeContainer[0], {
    useCORS: true,
    allowTaint: true,
    logging: true,
    scale: 1,
    //proxy: "https://cors-anywhere.herokuapp.com/",
  }).then(canvas => {
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF("p", "mm", [canvas.width/2, canvas.height/2]);
    const width = pdf.internal.pageSize.getWidth();
    const height = pdf.internal.pageSize.getHeight();
    pdf.addImage(imgData, 'PNG', 0, 0, width, height);
    pdf.save('recette.pdf');
  })
  .catch(err => {
    console.error(err);
    showError("Une erreur est survenue");
  });
}