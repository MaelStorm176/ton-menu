import "blueimp-file-upload";

const addTagLink = document.createElement('button');
addTagLink.type = 'button';
addTagLink.classList.add('add_tag_list')
addTagLink.classList.add('btn');
addTagLink.classList.add('btn-primary');
addTagLink.innerText = 'Ajouter une étape';
addTagLink.dataset.collectionHolderClass='steps'

const newLinkLi = document.createElement('li');
newLinkLi.classList.add('list-group-item');
newLinkLi.append(addTagLink);

const collectionHolder = document.querySelector('ul.steps')
collectionHolder.appendChild(newLinkLi);

const addFormToCollection = (e) => {
  e.preventDefault();
  const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

  const item = document.createElement('li');
  item.classList.add('form-group');
  item.classList.add('list-group-item');
  item.innerHTML = collectionHolder
    .dataset
    .prototype
    .replace(
      /__name__/g,
      collectionHolder.dataset.index
    );

  collectionHolder.appendChild(item);

  collectionHolder.dataset.index++;

  // add a delete link to the new form
  addTagFormDeleteLink(item);
}

const addTagFormDeleteLink = (item) => {
  const removeFormButton = document.createElement('button');
  removeFormButton.type = 'button';
  removeFormButton.innerText = 'Supprimer cette étape';
  removeFormButton.classList.add('remove_step_list');
  removeFormButton.classList.add('btn');
  removeFormButton.classList.add('btn-primary');

  item.append(removeFormButton);

  removeFormButton.addEventListener('click', (e) => {
    e.preventDefault();
    // remove the li for the tag form
    item.remove();
  });
}


addTagLink.addEventListener("click", addFormToCollection);

$(document).ready(function() {
  const recipeImages = $('.input-images');
  const dataTableOptions = {
    pageLength: 5,
    responsive: true,
    info: false,
    lengthChange: false,
  }
  recipeImages.imageUploader({
    maxSize: 2 * 1024 * 1024,
    maxFiles: 10
  });

  //$("fieldset").remove();
  $("#ingredientsTable").DataTable(dataTableOptions);
  $("#tagsTable").DataTable(dataTableOptions);
});