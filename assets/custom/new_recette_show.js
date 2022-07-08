function rate(id, value) {
  $.ajax({
    type: 'post',
    url: "/rate/" + id + "/" + value,
    dataType: 'json',
    success: function (json) {
      console.log("Rating good !");
    }
  });
}