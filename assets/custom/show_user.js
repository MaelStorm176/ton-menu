$(document).ready(function (){
      const recipes_table = $("#recipesTable");
      const ingredients_table = $("#ingredientsTable");
      const comments_table = $("#commentsTable");
      const users_table = $("#usersTable");
      const dataTableOptions = {
        info: false,
        responsive: true,
        pageLength: 10,
      }
      if (recipes_table.length) {
        recipes_table.DataTable(dataTableOptions);
      }
      if (comments_table.length) {
        comments_table.DataTable(dataTableOptions);
      }
      if (ingredients_table.length) {
        ingredients_table.DataTable(dataTableOptions);
      }
      if (users_table.length) {
        users_table.DataTable();
      }
});