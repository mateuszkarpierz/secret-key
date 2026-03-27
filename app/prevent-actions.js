/**
 * prevent-actions.js (login)
 * Podstawowa ochrona przed inspekcją strony.
 *
 * UWAGA: To NIE jest pełna ochrona — każdy może zobaczyć źródło strony
 * przez serwer lub narzędzia sieciowe. Traktuj to jako deterrent, nie
 * jako prawdziwą blokadę. Prawdziwą ochroną jest szyfrowanie po stronie serwera.
 */

(function() {

    // ─── Blokada prawego przycisku myszy ───
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // ─── Blokada skrótów klawiszowych ───
    var blocked = {
        ctrl: ['u', 's', 'a', 'p', 'i', 'j', 'c', 'x'],
        ctrlShift: ['i', 'j', 'c', 'k'],
        keys: ['F12', 'F11']
    };

    document.addEventListener('keydown', function(e) {
        var key = e.key.toLowerCase();

        // F12, F11
        if (blocked.keys.indexOf(e.key) !== -1) {
            e.preventDefault();
            return;
        }

        // Ctrl + klawisz
        if (e.ctrlKey && !e.shiftKey && blocked.ctrl.indexOf(key) !== -1) {
            e.preventDefault();
            return;
        }

        // Ctrl + Shift + klawisz
        if (e.ctrlKey && e.shiftKey && blocked.ctrlShift.indexOf(key) !== -1) {
            e.preventDefault();
            return;
        }
    });

    // ─── Blokada kopiowania i wycinania ───
    document.addEventListener('copy', function(e) { e.preventDefault(); });
    document.addEventListener('cut',  function(e) { e.preventDefault(); });

    // ─── Blokada zaznaczania (jako backup do CSS user-select: none) ───
    document.addEventListener('selectstart', function(e) { e.preventDefault(); });

    // ─── Wykrywanie DevTools (heurystyka przez rozmiar okna) ───
    // Nie jest niezawodna, ale może zniechęcić przypadkowych użytkowników
    var devToolsThreshold = 160;
    setInterval(function() {
        var widthDiff  = window.outerWidth  - window.innerWidth;
        var heightDiff = window.outerHeight - window.innerHeight;
        if (widthDiff > devToolsThreshold || heightDiff > devToolsThreshold) {
            // DevTools prawdopodobnie otwarte — można tutaj zareagować
            // np. document.body.innerHTML = '';
            // Zakomentowane domyślnie — odkomentuj jeśli chcesz agresywniejszą ochronę
        }
    }, 1000);

})();
