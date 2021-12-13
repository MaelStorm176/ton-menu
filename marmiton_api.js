let marmiton = require("marmiton-api")
let fs = require('fs');
const {Recipe} = require("marmiton-api");

const qbStarter = new marmiton.MarmitonQueryBuilder();
const qbMain = new marmiton.MarmitonQueryBuilder();
const qbDessert = new marmiton.MarmitonQueryBuilder();

//On sélectionne les entrées
const queryStarter = qbStarter
    .withType(marmiton.RECIPE_TYPE.STARTER)
    .build();

//On sélectionne les plats
const queryMain = qbMain
    .withType(marmiton.RECIPE_TYPE.MAIN_COURSE)
    .build();

//On sélectionne les desserts
const queryDessert = qbDessert
    .withType(marmiton.RECIPE_TYPE.DESSERT)
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
    let recipes;
    return recipes = await marmiton.searchRecipes(query,{limit:50});
}

let resultsStarter = results(queryStarter);
let resultsMain = results(queryMain);
let resultsDessert = results(queryDessert);

resultsStarter.then((recipesStarter) => {
    resultsMain.then((recipesMain) => {
        resultsDessert.then((recipesDessert) => {
            const recipesJSON = JSON.stringify([recipesStarter,recipesMain,recipesDessert]);
            let path = 'marmiton_results.json';
            if (fs.existsSync(path)) {
                fs.unlinkSync(path);
            }
            fs.writeFile(path,recipesJSON,'utf8',function (err) {
                if (err) throw err;
                console.log('complete');
                return;
            })
        });
    });
});



/*
results(queryStarter).then((recipes) => {
    fs.appendFileSync(path, JSON.stringify(recipes), 'utf8',function(err) {
        if (err) throw err;
        console.log('complete Entrées');
        return;
    })
});

results(queryMain).then((recipes) => {
    fs.appendFileSync(path, JSON.stringify(recipes), 'utf8',function(err) {
        if (err) throw err;
        console.log('complete Plats');
        return;
    })
});

results(queryDessert).then((recipes) => {
    fs.appendFileSync(path, JSON.stringify(recipes), 'utf8',function(err) {
        if (err) throw err;
        console.log('complete Desserts');
        return;
    })
});
*/
