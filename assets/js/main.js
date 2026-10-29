document.addEventListener('DOMContentLoaded', () => {
    // 1. Gestion de l'envoi d'une question (sur index.php)
    const questionForm = document.getElementById('question-form');
    if (questionForm) {
        questionForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                titre: document.getElementById('titre').value,
                contenu: document.getElementById('contenu').value,
                tags: document.getElementById('tags').value
            };

            try {
                const response = await fetch('api/post_question.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + result.error);
                }
            } catch (err) {
                console.error('Erreur réseau', err);
            }
        });
    }

    // 2. Gestion de l'envoi d'une réponse (sur question.php)
    const responseForm = document.getElementById('response-form');
    if (responseForm) {
        responseForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                question_id: document.getElementById('question_id').value,
                contenu: document.getElementById('contenu-reponse').value
            };

            try {
                const response = await fetch('api/post_response.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + result.error);
                }
            } catch (err) {
                console.error('Erreur réseau', err);
            }
        });
    }
});

// Mode basse consommation
function toggleLowBandwidth() {
    document.body.classList.toggle('bg-slate-950');
    document.body.classList.toggle('bg-white');
    document.body.classList.toggle('text-slate-100');
    document.body.classList.toggle('text-slate-900');
    const btn = document.getElementById('bandwidth-btn');
    if(btn) {
        btn.classList.toggle('bg-teranga-gold');
        btn.classList.toggle('text-slate-950');
    }
}