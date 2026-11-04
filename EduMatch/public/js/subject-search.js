const input = document.querySelector('#subject-search');
const suggestions = document.querySelector('#subject-suggestions');
const form = document.querySelector('#subject-search-form');

if (input && suggestions && form) {
    const options = Array.from(suggestions.querySelectorAll('.subject-suggestion'));
    const normalize = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase()
        .trim();

    const distance = (left, right) => {
        const row = Array.from({ length: right.length + 1 }, (_, index) => index);

        for (let i = 1; i <= left.length; i += 1) {
            let diagonal = row[0];
            row[0] = i;

            for (let j = 1; j <= right.length; j += 1) {
                const above = row[j];
                row[j] = Math.min(row[j] + 1, row[j - 1] + 1, diagonal + (left[i - 1] === right[j - 1] ? 0 : 1));
                diagonal = above;
            }
        }

        return row[right.length];
    };

    const closeSuggestions = () => {
        suggestions.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };

    const renderSuggestions = () => {
        const query = normalize(input.value);
        const exactMatches = options.filter((option) => normalize(option.dataset.subject).includes(query));
        let matches = exactMatches;

        if (query.length >= 3 && matches.length === 0) {
            const closest = options
                .map((option) => ({ option, distance: distance(query, normalize(option.dataset.subject)) }))
                .sort((left, right) => left.distance - right.distance)[0];
            const threshold = Math.max(1, Math.ceil(query.length * 0.25));

            if (closest && closest.distance <= threshold) {
                matches = [closest.option];
            }
        }

        options.forEach((option) => {
            option.closest('li').hidden = !matches.includes(option);
        });

        suggestions.hidden = matches.length === 0;
        input.setAttribute('aria-expanded', String(matches.length > 0));
    };

    input.addEventListener('focus', renderSuggestions);
    input.addEventListener('input', renderSuggestions);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuggestions();
        } else if (event.key === 'ArrowDown' && !suggestions.hidden) {
            event.preventDefault();
            suggestions.querySelector('li:not([hidden]) button')?.focus();
        }
    });

    suggestions.addEventListener('click', (event) => {
        const option = event.target.closest('.subject-suggestion');

        if (option) {
            input.value = option.dataset.subject;
            form.requestSubmit();
        }
    });

    suggestions.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuggestions();
            input.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            closeSuggestions();
        }
    });
}
