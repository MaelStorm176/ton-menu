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
        alert(data.error);
      else {
        const toast_html = '<div id="toast-success" class="toast toast-success" role="status" aria-live="polite" aria-atomic="true">' +
          '<div class="toast-header bg-primary">' +
          '<strong class="mr-auto">Succes !</strong>' +
          '<small class="text-muted">à l\'instant</small>' +
          '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">' +
          '<span aria-hidden="true">&times;</span>' +
          '</button>' +
          '</div>' +
          '<div class="toast-body">Votre avis a bien été pris en compte !</div>' +
          '</div>';
        $('.toast-container').append(toast_html);

        const toast = new bootstrap.Toast(document.getElementById('toast-success'), {
          autohide: true,
          delay: 5000,
          animation: true,
          newestOnTop: true,
          placement: 'top',
          preventDuplicates: true,
          progressBar: true,
          closeButton: true,
          showMethod: 'slideDown',
          hideMethod: 'slideUp'
        });

        toast.show();
      }
    }
  });
}