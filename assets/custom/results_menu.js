const generateResultsMenu = JSON.parse($("#menu").val());
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

  $("#select_nb_jour").change(function(){
    const nb_jours = $(this).val();
    const id_menu = $("#id_menu");
    if (id_menu != null && id_menu.val() !== "" && id_menu.val() !== "0"){
      const url = "/generation-menu/"+nb_jours+"?id_menu="+id_menu.val();
      document.location.href = url;
    }else{
      document.location.href = '/generation-menu/'+nb_jours;
    }
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

  $("#send_menu").submit(function(e){
    e.preventDefault();
    const generatedMenu = $("#menu").val();
    if (generatedMenu == null || generatedMenu === "") {
      alert("Veuillez générer un menu avant de l'envoyer");
    }else{
      $.ajax({
        url: "/ajax/generation-menu/send",
        type: "POST",
        dataType: "json",
        data: {
          menu: generatedMenu,
          nb_jours: $("input[name='nb_jour']").val()
        },
        success: function(data){
          if (data.success === true && data.msg !== "") {
            console.log(data);
            alert(data.msg);
          }else if (data.success === false && data.msg !== "") {
            alert(data.msg);
          } else{
            alert("Une erreur est survenue");
          }
        },
        error: function(data){
          console.log(data);
          alert("Une erreur est survenue");
        }
      });
    }
  });


  $("div[id^=recipe_]").on("click", "button", (event) => refresh_recipe(event));

  /*
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
   */

});

function refresh_recipe(e){
  const recipe_to_reload = $(e.delegateTarget);
  const recipe_id = Number(recipe_to_reload.attr("id").substring(7));
  const type = recipe_to_reload.attr("data-type");
  const recipe_type = type.toLowerCase()+"s";
  const recipe_that_will_be_regenerated_index = generateResultsMenu[recipe_type].findIndex(recipe => recipe === recipe_id);
  const recipe_that_will_be_regenerated = generateResultsMenu[recipe_type][recipe_that_will_be_regenerated_index];

  if (recipe_that_will_be_regenerated !== undefined){
    $.ajax({
      url: "/ajax/generation-menu/refresh",
      type: "GET",
      dataType: "html",
      data: {
        type: type
      },
      success: function(data) {
        const $data = $(data);
        const $test = $($data[2])
        generateResultsMenu[recipe_type][recipe_that_will_be_regenerated_index] = Number($test.attr("id").substring(7));
        recipe_to_reload.replaceWith($data);
        $("#menu").val(JSON.stringify(generateResultsMenu));
        $data.on("click", "button.btn.btn-dark", function (event) {
          refresh_recipe(event);
        });
      }
    });
  }
}

$('#ingredient_filter_ingredients').select2({
    placeholder: "Sélectionner un ou plusieurs ingrédients",
    allowClear: true,
    multiple: true,
    theme: "classic",
    closeOnSelect: true,
    width: "100%"
});