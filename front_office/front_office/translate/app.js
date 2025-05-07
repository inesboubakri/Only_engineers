async function translateText() {
    const text = document.getElementById('text').value;
    const language = document.getElementById('language').value;

    const response = await fetch('translate.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ text, language })
    });

    const result = await response.json();

    if (result.success) {
        document.getElementById('result').innerText = result.translation;
    } else {
        document.getElementById('result').innerText = `Erreur: ${result.error}`;
    }
}
