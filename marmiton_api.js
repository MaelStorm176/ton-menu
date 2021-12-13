var marmiton = require("marmiton-api")
const {Recipe} = require("marmiton-api");

const qb = new marmiton.MarmitonQueryBuilder();
const query = qb
    .withTitleContaining('soja')
    .withoutOven()
    .withPrice(marmiton.RECIPE_PRICE.CHEAP)
    .takingLessThan(45)
    .withDifficulty(marmiton.RECIPE_DIFFICULTY.EASY)
    .build();

async function results() {
    return recipes = await marmiton.searchRecipes(query);
}
results().then(console.log);
