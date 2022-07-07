$(document).ready(function (){
  console.log("results_menu.js loaded");

  /*
  $("#save_menu").click(function(){
    if (generatedMenu == null || generatedMenu === "") {
      alert("Veuillez générer un menu avant de sauvegarder");
    }else{
      $.ajax({
        url: "/ajax/generation-menu/save_menu",
        type: "POST",
        dataType: "json",
        data: {
          menu: generatedMenu
        },
        success: function(data){
          if (data.success === true && data.msg !== "" && data.id !== "") {
            console.log(data);
            alert(data.msg);
            $("#save_menu").data("id", data.id);
          }else if (data.success === false && data.msg !== "") {
            alert(data.msg);
          } else{
            alert("Une erreur est survenue");
          }
        }
      });
    }
  });
  */

  $("button[id^='refresh_day_']").click(function(){
    const day = $(this).data("day");
    $.ajax({
      url: "/ajax/generation-menu/refresh",
      type: "GET",
      dataType: "json",
      data: {
        day: true
      },
      success: function(data){
        if (data.success === true && data.msg !== "") {
          console.log(data);
          alert(data.msg);
          $("#refresh_day_"+day).data("day", data.day);
        }else if (data.success === false && data.msg !== "") {
          alert(data.msg);
        } else{
          alert("Une erreur est survenue");
        }
      }
    });
  });

});

