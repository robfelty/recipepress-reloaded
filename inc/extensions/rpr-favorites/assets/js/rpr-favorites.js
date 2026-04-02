document.rprFavorites = new Stoor({namespace: 'rpr'});

if (document.getElementById('favorites-list')) {
  Moon({
    root: '#rpr-favorites-list',
    view: document.getElementById('favorites-list').innerHTML,
    favorites: document.rprFavorites,
    recipes: [],

    onCreate() {
      if (!this.favorites.get('favorites')) {
        return;
      }
      this.recipes = this.favorites.get('favorites');
    },

    createTodo() {
      var _recipes = this.favorites.get('favorites');

      if (!_recipes) {
        _recipes = [];
      }

      _recipes.push({
        value: this.value,
        complete: false,
      });

      this.favorites.set('favorites', _recipes);

      this.update({
        value: '',
        recipes: this.favorites.get('favorites'),
      });
    },

    clearFavorites() {
      this.favorites.clear();
      this.update({
        recipes: [],
      });
    },

    removeFavorite(item) {
      var _favorites = this.favorites.get('favorites');
      var filtered = _favorites.filter(function(el) { return el.id !== item; });

      this.favorites.set('favorites', filtered);
      this.update({
        recipes: this.favorites.get('favorites'),
      });
    },

    emailFavorite(email) {
      console.log(email);
      window.location = 'mailto:?subject=' + email.title + '&body=' + email.description + ' --> '
          + 'Get the Full Recipe Here: ' + email.url;
    },

    onUpdate() {

    },

  });
}

if (document.getElementById('favorites-button')) {
  Moon({
    root: '#rpr-favorites-button',
    view: document.getElementById('favorites-button').innerHTML,
    favorites: document.rprFavorites,
    recipes: [],
    currentRecipe: rprFavoriteRecipe,
    count: 0,
    message: false,

    onCreate() {
      if (!this.favorites.get('favorites')) {
        return;
      }
      this.recipes = this.favorites.get('favorites');
      this.count = this.recipes.length;
    },

    isFavorite() {
      var _recipe = this.currentRecipe;
      var is_favorite = false;
      for (var item of this.recipes) {
        if (item.id === parseInt(_recipe.id)) {
           is_favorite = true;
        }
      }
      return is_favorite;
    },

    addFavorite() {
      var allRecipes = this.recipes;
      this.currentRecipe.date = (new Date()).toDateString();
      this.currentRecipe.id = parseInt(this.currentRecipe.id);
      var thisRecipe = this.currentRecipe;
      var title = this.currentRecipe.title;

      if (typeof window.ga !== 'undefined') {
        window.ga('send', 'event', {
          eventCategory: 'Favorite button',
          eventAction: 'click',
          eventLabel: title
        });
      }

      for (var i of allRecipes) {
        if (i.id === thisRecipe.id) {
          window.location.href = thisRecipe.favorites_page;
          return false;
        }
      }

      allRecipes.push(this.currentRecipe);

      this.favorites.set('favorites', allRecipes);

      this.update({
        count: allRecipes.length,
      });
    },

    onUpdate() {
    },

  });
}
