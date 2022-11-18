/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import '@popperjs/core';

// start the Stimulus application
//import './bootstrap';
import * as $ from 'jquery';
import 'bootstrap';
import 'datatables.net';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'select2';
window.bootstrap = require("bootstrap")

//Définition des paramètres par défaut de datatable
$.extend( true, $.fn.dataTable.defaults, {
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.11.4/i18n/fr_fr.json'
    },
    pageLength: 25,
    responsive: true
} );

$(document).ready(function () {
    /* Taost */
    const toastElList = [].slice.call($('.toast:not([id])'));
    const toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 5000,
            animation: true,
            newestOnTop: true,
            placement: 'top',
            preventDuplicates: true,
            progressBar: true,
            closeButton: true,
            showMethod: 'slideDown',
            hideMethod: 'slideUp'
        });
    });

    /* Tooltips */
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    toastList.forEach(function (toast) {
        toast.show();
    });

    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
})