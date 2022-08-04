$(document).ready(function() {
  $("#recipesTable2").DataTable();
  $("#profile_profile_picture").on("change", (e) => {
    const image = $("#output");
    image.attr('src', URL.createObjectURL(e.target.files[0]));
  });
});

