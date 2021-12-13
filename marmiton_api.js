let marmiton = require("marmiton-api")
let fs = require('fs');
const {Recipe} = require("marmiton-api");

const qb = new marmiton.MarmitonQueryBuilder();
const query = qb
    .withType(marmiton.RECIPE_TYPE.STARTER)
    .withType(marmiton.RECIPE_TYPE.MAIN_COURSE)
    .withType(marmiton.RECIPE_TYPE.DESSERT)
    .build()
/*
const query = qb
    .withTitleContaining('soja')
    .withoutOven()
    .withPrice(marmiton.RECIPE_PRICE.CHEAP)
    .takingLessThan(45)
    .withDifficulty(marmiton.RECIPE_DIFFICULTY.EASY)
    .build();

*/

async function results() {
    let recipes;
    return recipes = await marmiton.searchRecipes(query,{limit:50});
}

results().then((recipes) => {
    var path = 'marmiton_results.json';
    if (fs.existsSync(path)) {
        fs.unlinkSync(path);
    }
    fs.writeFile(path, JSON.stringify(recipes), 'utf8',function(err) {
        if (err) throw err;
        console.log('complete');
        return;
    })
});

