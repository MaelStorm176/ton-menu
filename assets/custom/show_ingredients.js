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
        length: 10,
    }
    $("#ingredientsTable").DataTable(dataTableOptions);
    $("#recipesTable").DataTable(dataTableOptions);
});