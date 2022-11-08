$(document).ready(function (){
    let recipes_table = $("#recipesTable").DataTable({
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

    $("td[id^='recette_ingredients_trigger_']").click(function () {
        const parent_tr = $('tr.parent');
        parent_tr.removeClass('parent shown');
        parent_tr.each((children) => {
            const children_tr = parent_tr[children];
            const row = recipes_table.row(children_tr);
        })
        show_ingredients($(this).data("id"), $(this));
    });

    function show_ingredients(id_recette, obj){
        const tr = $(obj).closest('tr');
        const row = recipes_table.row( tr );

        if ( !row.child.isShown() ) {
            // Open this row
            $.ajax({
                method: "POST",
                url: "../recipe/"+id_recette+"/ingredients",
                dataType: 'html'
            })
            .done(function (contenu_html) {
                $(".dtr-details li:last-child").append(contenu_html);
                row.child.show();
                tr.addClass('shown');
            });
        }
    }
});