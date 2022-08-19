$(document).ready(function () {
  $("#recipesTable2").DataTable();
  $("#profile_profile_picture").on("change", (e) => {
    const image = $("#output");
    if (e.target.files[0]["type"] == "image/jpeg" || e.target.files[0]["type"] == "image/png" || e.target.files[0]["type"] == "image/jpg") {
      image.attr('src', URL.createObjectURL(e.target.files[0]));
    }else{
      console.log("tamer");
    }
  });
});

