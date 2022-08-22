import * as $ from "jquery";

const custom_success = $('#custom-success');
const custom_error = $('#custom-error');
const toast_success = new bootstrap.Toast(custom_success);
const toast_error = new bootstrap.Toast(custom_error);

export function showSuccess(message) {
  custom_success.find(".toast-body").text(message);
  toast_success.show();
}

export function showError(message) {
  custom_error.find(".toast-body").text(message);
  toast_error.show();
}