$(document).ready(function (){
    const dataTableOptions = {
        responsive: {
            details: {
                type: 'column'
            }
        },
        columnDefs: [ {
            className: 'dtr-control',
            orderable: false,
            targets:   0
        } ],
        pageLength: 10,

    }
    const table = $("#ingredientsTable").DataTable(dataTableOptions);
    if ($("#recipesTable"))
        $("#recipesTable").DataTable(dataTableOptions);
});