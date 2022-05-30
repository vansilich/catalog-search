const search_api_suggests_url = 'http://demo-search.loc/api/catalogs/suggests';

const search_form = document.querySelector('form');
const search_input = document.querySelector('.main-input');
const suggestions_container = document.querySelector('.suggestions');

const suggestItemClass = 'suggest-item';

search_input.addEventListener('input', debounce(showSuggestions, 200) );
document.addEventListener('click', (event) => {

    const target = event.target;
    if (target.classList.contains(suggestItemClass)) {
        event.preventDefault();
        handleSuggestionSelect(target)
    }
});

async function showSuggestions(){

    clearSuggests();

    const search_value = search_input.value;
    const suggests_response = await getSuggests( search_value );
    const suggests = JSON.parse(await suggests_response.text()).suggests;

    clearSuggests();

    if (suggests.length !== 0) {

        suggests.forEach( suggest => {
            let suggest_HTML = document.createElement("a");
            suggest_HTML.setAttribute('href', '')
            suggest_HTML.classList.add(suggestItemClass);
            suggest_HTML.innerText = suggest

            suggestions_container.appendChild(suggest_HTML);
        });

        suggestions_container.style.display = 'block';
    }

}

function clearSuggests(){
    suggestions_container.style.display = 'none';
    suggestions_container.replaceChildren();
}

function handleSuggestionSelect(suggestionElement){
    search_input.value = suggestionElement.text;
    search_form.submit();
}

async function getSuggests( text ) {
    return await fetch(search_api_suggests_url, {
        method: 'POST',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json'
        },
        redirect: 'follow',
        body: JSON.stringify({
            'search': text
        })
    })
}

function debounce(func, wait = 500) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), wait)
    }
}