$(document).ready(function (){
    $("#recipesTable").DataTable({
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
    });


    function show_ingredients(id_recette,obj){
        var tr = $(obj).closest('tr');
        var row = recette_table.row( tr );

        if ( !row.child.isShown() ) {
            // Open this row
            $.ajax({
                method: "POST",
                url: "recette/"+id_recette,
            })
            .done(function( contenu_html ) {
                row.child( contenu_html ).show();
                tr.addClass('shown');
            });
        }
        /*
        else {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }*/
    }
});