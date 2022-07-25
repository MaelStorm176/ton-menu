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
      console.log(data);
    }
  });
}