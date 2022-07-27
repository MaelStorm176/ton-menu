$(document).ready(function (){
  console.log("results_menu.js loaded");

  $("p[id^='jour_']").click(function(){
    const id = $(this).attr("id");
    const jour = id.substring(5);
    const show_jour = $("#show_jour_"+jour);
    const hide_jour = $("div[id^='show_jour_']").not(show_jour);
    show_jour.removeClass("d-none");
    show_jour.addClass("d-block");
    hide_jour.removeClass("d-block");
    hide_jour.addClass("d-none");
    $(this).addClass("active");
    $("p[id^='jour_']").not(this).removeClass("active");
  });

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

