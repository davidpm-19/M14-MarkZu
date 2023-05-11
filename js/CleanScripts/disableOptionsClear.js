
//Aplicado en la carga de págia

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("keydown", function (e) {
        if (
            e.ctrlKey &&
            (e.keyCode == "61" ||
                e.keyCode == "107" ||
                e.keyCode == "173" ||
                e.keyCode == "109" ||
                e.keyCode == "187" ||
                e.keyCode == "189")
        ) {
            e.preventDefault();
        }
    });

    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    document.addEventListener(
        "wheel",
        function (e) {
            if (e.ctrlKey) {
                e.preventDefault();
            }
        },
        {
            passive: false
        }
    );
    document.onkeydown = function (e) {
        if (event.keyCode == 123) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
            return false;
        }
    }
});