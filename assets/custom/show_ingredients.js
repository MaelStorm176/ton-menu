import 'datatables.net';
import 'datatables.net-bs5';

$(document).ready(function (){
    $("#ingredientsTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.4/i18n/fr_fr.json'
        }
    });
});