let marmiton = require("marmiton-api")
let fs = require('fs');
const {Recipe} = require("marmiton-api");

//Variables pour le temps d'execution du programme
let start = new Date();
let end;

//Les 3 requetes builder pour Entree | Plat | Dessert
const qbStarter = new marmiton.MarmitonQueryBuilder();
const qbMain = new marmiton.MarmitonQueryBuilder();
const qbDessert = new marmiton.MarmitonQueryBuilder();

//Nombre d'entrées, plats, dessert | Si limit_number = 50, on requetera 50 entrees, 50 plats, 50 desserts
const limit_number = Number(50);

//On sélectionne les entrées
const queryStarter = qbStarter
    .withType(marmiton.RECIPE_TYPE.STARTER)
    .withPhoto()
    .build();

//On sélectionne les plats
const queryMain = qbMain
    .withType(marmiton.RECIPE_TYPE.MAIN_COURSE)
    .withPhoto()
    .build();

//On sélectionne les desserts
const queryDessert = qbDessert
    .withType(marmiton.RECIPE_TYPE.DESSERT)
    .withPhoto()
    .build();

/*
const query = qb
    .withTitleContaining('soja')
    .withoutOven()
    .withPrice(marmiton.RECIPE_PRICE.CHEAP)
    .takingLessThan(45)
    .withDifficulty(marmiton.RECIPE_DIFFICULTY.EASY)
    .build();

*/

async function results(query) {
    return await marmiton.searchRecipes(query,{limit:limit_number});
}

let resultsStarter = results(queryStarter);
let resultsMain = results(queryMain);
let resultsDessert = results(queryDessert);

resultsStarter.then((recipesStarter) => {
    resultsMain.then((recipesMain) => {
        resultsDessert.then((recipesDessert) => {
            end = new Date();
            console.log('Durée des requetes : ' + ((end.getTime() - start.getTime())/1000) + ' sec');

            start = new Date();
            const recipesJSON = JSON.stringify([recipesStarter,recipesMain,recipesDessert]);
            let path = 'marmiton_results.json';
            if (fs.existsSync(path)) {
                fs.unlinkSync(path);
            }
            fs.writeFile(path,recipesJSON,'utf8',function (err) {
                if (err) throw err;
                end = new Date();
                console.log('Durée écriture fichier : ' + ((end.getTime() - start.getTime())/1000) + ' sec');
            })
        });
    });
});

