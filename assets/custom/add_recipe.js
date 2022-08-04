import "blueimp-file-upload";

/** STEPS **/
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


/** QUANTITIES **/
const addQuantityLink = document.createElement('button');
addQuantityLink.type = 'button';
addQuantityLink.classList.add('add_tag_list')
addQuantityLink.classList.add('btn');
addQuantityLink.classList.add('btn-primary');
addQuantityLink.innerText = 'Ajouter une quantité';
addQuantityLink.dataset.collectionHolderClass='quantites'

const newQuantityLinkLi = document.createElement('li');
newQuantityLinkLi.classList.add('list-group-item');
newQuantityLinkLi.append(addQuantityLink);

const collectionHolderQuantity = document.querySelector('ul.quantites')
collectionHolderQuantity.appendChild(newQuantityLinkLi);

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
addQuantityLink.addEventListener("click", addFormToCollection);

$(document).ready(function() {
  const recipeImages = $('.input-images');
  recipeImages.imageUploader({
    maxSize: 2 * 1000 * 1000,
    maxFiles: 10
  });

  $("#recette_ingredients").select2({
    placeholder: "Sélectionner un ou plusieurs ingrédients",
    allowClear: true,
    multiple: true,
    theme: "classic",
    closeOnSelect: true,
    width: "100%"
  });

  $("#recette_recipeTags").select2({
    placeholder: "Sélectionner un ou plusieurs tags",
    allowClear: true,
    multiple: true,
    theme: "classic",
    closeOnSelect: true,
    width: "100%"
  });
});